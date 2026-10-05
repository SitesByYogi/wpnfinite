(function () {
  'use strict';

  function bindHeaderNavigation(root) {
    root = root || document;
    const toggle = root.querySelector('.wpnfinite-nav-toggle');
    const nav = root.querySelector('.wpnfinite-navigation');
    if (toggle && nav && !toggle.dataset.wpnfiniteBound) {
      toggle.dataset.wpnfiniteBound = '1';
      toggle.addEventListener('click', function () {
        const expanded = this.getAttribute('aria-expanded') === 'true';
        this.setAttribute('aria-expanded', String(!expanded));
        nav.classList.toggle('is-open');
      });
    }
    root.querySelectorAll('.wpnfinite-navigation .menu-item-has-children > .wpnfinite-nav-parent').forEach(function (button) {
      if (button.dataset.wpnfiniteBound) return;
      button.dataset.wpnfiniteBound = '1';
      button.addEventListener('click', function () {
        const parent = button.parentElement;
        if (!parent) return;
        const open = parent.classList.toggle('is-open');
        button.setAttribute('aria-expanded', String(open));
      });
    });
    root.querySelectorAll('.wpnfinite-navigation .menu-item-has-children > a').forEach(function (link) {
      if (link.dataset.wpnfiniteBound) return;
      link.dataset.wpnfiniteBound = '1';
      link.addEventListener('click', function (event) {
        if (window.innerWidth > 991) return;
        const parent = link.parentElement;
        if (parent && !parent.classList.contains('is-open')) {
          event.preventDefault();
          parent.classList.add('is-open');
        }
      });
    });
  }

  function canNavigate(link, event) {
    if (!link || !link.href || event.defaultPrevented || event.button !== 0) return false;
    if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return false;
    if (link.target && link.target !== '_self') return false;
    if (link.hasAttribute('download') || link.getAttribute('rel') === 'external') return false;
    if (link.closest('#wpadminbar')) return false;

    const url = new URL(link.href, window.location.href);
    if (url.origin !== window.location.origin) return false;
    if (!/^https?:$/.test(url.protocol)) return false;
    if (url.pathname === window.location.pathname && url.search === window.location.search && url.hash) return false;

    // Keep sensitive/action-oriented WordPress and WooCommerce flows as normal page loads.
    const blocked = /\/(wp-admin|wp-login\.php|cart|checkout|my-account)(\/|$)/i;
    if (blocked.test(url.pathname)) return false;
    if (/\.(pdf|zip|jpe?g|png|gif|webp|svg|mp3|m4a|aac|ogg|wav|flac|mp4|mov|webm)$/i.test(url.pathname)) return false;
    if (url.searchParams.has('add-to-cart') || url.searchParams.has('wc-ajax')) return false;
    return true;
  }

  function syncHead(nextDoc) {
    document.title = nextDoc.title;
    const selectors = [
      'meta[name="description"]', 'link[rel="canonical"]',
      'meta[property^="og:"]', 'meta[name^="twitter:"]'
    ];
    selectors.forEach(function (selector) {
      document.head.querySelectorAll(selector).forEach(function (node) { node.remove(); });
      nextDoc.head.querySelectorAll(selector).forEach(function (node) {
        document.head.appendChild(node.cloneNode(true));
      });
    });
  }

  function executeContentScripts(container) {
    container.querySelectorAll('script').forEach(function (oldScript) {
      const script = document.createElement('script');
      Array.from(oldScript.attributes).forEach(function (attr) { script.setAttribute(attr.name, attr.value); });
      script.textContent = oldScript.textContent;
      oldScript.replaceWith(script);
    });
  }

  async function navigate(url, options) {
    options = options || {};
    if (document.documentElement.classList.contains('wpnfinite-navigating')) return;
    document.documentElement.classList.add('wpnfinite-navigating');

    try {
      const response = await fetch(url, {
        credentials: 'same-origin',
        headers: { 'X-WPNfinite-Navigation': '1' }
      });
      if (!response.ok || !response.headers.get('content-type') || response.headers.get('content-type').indexOf('text/html') === -1) {
        window.location.href = url;
        return;
      }

      const html = await response.text();
      const nextDoc = new DOMParser().parseFromString(html, 'text/html');
      const currentMain = document.querySelector('#primary');
      const nextMain = nextDoc.querySelector('#primary');
      if (!currentMain || !nextMain) {
        window.location.href = url;
        return;
      }

      // Only replace the page content. Persistent header/footer/plugin UI (including
      // Nfinite's global audio element) stays alive, so playback is uninterrupted.
      currentMain.innerHTML = nextMain.innerHTML;
      executeContentScripts(currentMain);

      const currentTopics = document.querySelector('.wpnfinite-topics-bar');
      const nextTopics = nextDoc.querySelector('.wpnfinite-topics-bar');
      if (currentTopics && nextTopics) currentTopics.innerHTML = nextTopics.innerHTML;

      document.body.className = nextDoc.body.className;
      syncHead(nextDoc);
      bindHeaderNavigation(document);

      if (!options.popstate) history.pushState({ wpnfinite: true }, '', url);

      const destination = new URL(url, window.location.href);
      if (destination.hash) {
        requestAnimationFrame(function () {
          const target = document.getElementById(destination.hash.slice(1));
          if (target) target.scrollIntoView(); else window.scrollTo(0, 0);
        });
      } else {
        window.scrollTo(0, 0);
      }

      // Give plugins one stable lifecycle event for re-binding page-specific UI.
      document.dispatchEvent(new CustomEvent('wpnfinite:navigation-complete', {
        detail: { url: window.location.href, document: nextDoc }
      }));
    } catch (error) {
      window.location.href = url;
    } finally {
      document.documentElement.classList.remove('wpnfinite-navigating');
    }
  }

  document.addEventListener('DOMContentLoaded', function () {
    bindHeaderNavigation(document);
    if (!history.state || !history.state.wpnfinite) {
      history.replaceState({ wpnfinite: true }, '', window.location.href);
    }
  });

  document.addEventListener('click', function (event) {
    const link = event.target.closest('a[href]');
    if (!canNavigate(link, event)) return;
    event.preventDefault();
    navigate(link.href);
  });

  window.addEventListener('popstate', function () {
    navigate(window.location.href, { popstate: true });
  });
})();


/* =========================================================
   WPNFINITE 1.3.13
   Close mobile navigation after continuous navigation
   ========================================================= */
(function () {
    'use strict';

    function closeMobileNavigation() {
        document.querySelectorAll(
            '.menu-toggle, .wpnfinite-menu-toggle, .wpnfinite-nav-toggle, ' +
            '[data-menu-toggle], [data-nav-toggle], ' +
            'button[aria-controls*="menu"], button[aria-controls*="nav"]'
        ).forEach(function (toggle) {
            var expanded = toggle.getAttribute('aria-expanded');
            var looksOpen =
                expanded === 'true' ||
                toggle.classList.contains('is-active') ||
                toggle.classList.contains('active') ||
                toggle.classList.contains('open');

            if (looksOpen) {
                try {
                    toggle.click();
                    return;
                } catch (e) {}
            }

            toggle.setAttribute('aria-expanded', 'false');
            toggle.classList.remove('is-active', 'active', 'open');
        });

        [
            'menu-open',
            'nav-open',
            'mobile-menu-open',
            'wpnfinite-menu-open',
            'wpnfinite-nav-open'
        ].forEach(function (className) {
            document.documentElement.classList.remove(className);
            document.body.classList.remove(className);
        });

        document.querySelectorAll(
            '.main-navigation, .primary-navigation, .wpnfinite-navigation, ' +
            '.wpnfinite-mobile-nav, .wpnfinite-primary-nav'
        ).forEach(function (nav) {
            nav.classList.remove('toggled', 'is-open', 'open', 'active');
        });
    }

    document.addEventListener('click', function (event) {
        if (window.matchMedia && !window.matchMedia('(max-width: 900px)').matches) {
            return;
        }

        var link = event.target.closest(
            '.main-navigation a, .primary-navigation a, ' +
            '.wpnfinite-navigation a, .wpnfinite-mobile-nav a, ' +
            '.wpnfinite-primary-nav a, nav a'
        );

        if (!link) return;

        var href = link.getAttribute('href');
        if (!href || href.charAt(0) === '#') return;
        if (link.target && link.target !== '_self') return;

        window.setTimeout(closeMobileNavigation, 0);
    }, true);

    document.addEventListener('wpnfinite:navigation-complete', closeMobileNavigation);

    window.addEventListener('popstate', function () {
        window.setTimeout(closeMobileNavigation, 0);
    });
})();
