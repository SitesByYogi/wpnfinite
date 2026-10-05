jQuery(function ($) {
	'use strict';

	let frame;

	$(document).on('click', '.wpnfinite-select-audio', function (event) {
		event.preventDefault();

		if (frame) {
			frame.open();
			return;
		}

		frame = wp.media({
			title: (window.WPNfiniteProductAudio || {}).title || 'Select Product Audio Preview',
			button: {
				text: (window.WPNfiniteProductAudio || {}).button || 'Use this audio'
			},
			library: {
				type: 'audio'
			},
			multiple: false
		});

		frame.on('select', function () {
			const attachment = frame.state().get('selection').first().toJSON();
			$('#_wpnfinite_audio_preview').val(attachment.url).trigger('change');
		});

		frame.open();
	});

	$(document).on('click', '.wpnfinite-clear-audio', function (event) {
		event.preventDefault();
		$('#_wpnfinite_audio_preview').val('').trigger('change');
	});
});
