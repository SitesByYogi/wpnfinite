(function () {
  'use strict';

  function enhance(root) {
    root = root || document;

    // Keep fan utility controls accessible after WPNfinite's in-place navigation.
    root.querySelectorAll('.nfinite-add-to-playlist select').forEach(function (select) {
      if (!select.getAttribute('title')) select.setAttribute('title', 'Choose playlist');
    });

    // When the plugin says a playlist action succeeded, give the control a
    // stable visual state without replacing Nfinite's own AJAX behavior.
    root.querySelectorAll('[data-playlist-add-message]').forEach(function (message) {
      if (message.dataset.wpnfiniteObserved) return;
      message.dataset.wpnfiniteObserved = '1';
      new MutationObserver(function () {
        var holder = message.closest('.nfinite-add-to-playlist');
        if (!holder) return;
        holder.classList.toggle('is-complete', message.textContent.trim().length > 0);
      }).observe(message, { childList: true, characterData: true, subtree: true });
    });
  }

  document.addEventListener('DOMContentLoaded', function () { enhance(document); });
  document.addEventListener('wpnfinite:navigation-complete', function () { enhance(document); });
})();
