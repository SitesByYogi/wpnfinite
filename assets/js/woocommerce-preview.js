document.addEventListener('DOMContentLoaded', function () {
	let audio = null;
	let activeButton = null;

	function resetButton(button) {
		if (!button) return;
		button.classList.remove('is-playing', 'is-loading');
		const icon = button.querySelector('.wpnfinite-product-preview__icon');
		const label = button.querySelector('.wpnfinite-product-preview__label');
		if (icon) icon.textContent = '▶';
		if (label) label.textContent = 'Preview';
		button.setAttribute('aria-pressed', 'false');
	}

	function setPlaying(button) {
		if (!button) return;
		button.classList.remove('is-loading');
		button.classList.add('is-playing');
		const icon = button.querySelector('.wpnfinite-product-preview__icon');
		const label = button.querySelector('.wpnfinite-product-preview__label');
		if (icon) icon.textContent = '❚❚';
		if (label) label.textContent = 'Pause';
		button.setAttribute('aria-pressed', 'true');
	}

	function stopOtherNativeAudio(current) {
		document.querySelectorAll('audio').forEach(function (candidate) {
			if (candidate !== current && !candidate.paused) {
				candidate.pause();
			}
		});
	}

	document.addEventListener('play', function (event) {
		if (!(event.target instanceof HTMLAudioElement)) return;

		stopOtherNativeAudio(event.target);

		if (audio && event.target !== audio && !audio.paused) {
			audio.pause();
			resetButton(activeButton);
		}
	}, true);

	document.addEventListener('click', function (event) {
		const button = event.target.closest('.wpnfinite-product-preview');

		if (!button) return;

		event.preventDefault();
		event.stopPropagation();

		const source = button.getAttribute('data-wpnfinite-audio-preview');
		if (!source) return;

		// Same product = toggle.
		if (audio && activeButton === button) {
			if (audio.paused) {
				stopOtherNativeAudio(audio);
				audio.play().then(function () {
					setPlaying(button);
				}).catch(function () {
					resetButton(button);
				});
			} else {
				audio.pause();
				resetButton(button);
			}
			return;
		}

		// Different product = stop prior preview.
		if (audio) {
			audio.pause();
			audio.currentTime = 0;
			audio.removeAttribute('src');
			audio.load();
		}

		resetButton(activeButton);
		activeButton = button;
		button.classList.add('is-loading');

		audio = new Audio();
		audio.preload = 'metadata';
		audio.src = source;

		audio.addEventListener('playing', function () {
			setPlaying(button);
		});

		audio.addEventListener('pause', function () {
			if (!audio.ended) {
				resetButton(button);
			}
		});

		audio.addEventListener('ended', function () {
			resetButton(button);
			activeButton = null;
		});

		audio.addEventListener('error', function () {
			resetButton(button);
			activeButton = null;
		});

		stopOtherNativeAudio(audio);

		audio.play().catch(function () {
			resetButton(button);
			activeButton = null;
		});
	});


	// Keep the active music product visually in sync with preview playback.
	document.addEventListener('click', function (event) {
		const button = event.target.closest('.wpnfinite-product-preview');
		if (!button) return;

		document.querySelectorAll('.wpnfinite-music-product.is-preview-active, .wpnfinite-music-product-page.is-preview-active')
			.forEach(function (node) {
				node.classList.remove('is-preview-active');
			});

		const card = button.closest('.wpnfinite-music-product');
		if (card) {
			card.classList.add('is-preview-active');
		}

		if (document.body.classList.contains('wpnfinite-music-product-page')) {
			document.body.classList.add('is-preview-active');
		}
	});

});
