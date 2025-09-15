// Entry: front-end enhancements (kept tiny by default)
document.documentElement.classList.add('js-enabled');
console.log('WPNfinite app loaded');
// Sticky header: add body class on scroll for solid background
function wpnHeaderScroll() {
    const scrolled = window.scrollY > 8;
    document.body.classList.toggle('header-scrolled', scrolled);
}
window.addEventListener('scroll', wpnHeaderScroll, { passive: true });
wpnHeaderScroll();

// Toggle solid header when page is scrolled
(function () {
    const nav = document.getElementById('navbar');
    if (!nav) return;
    const onScroll = () => nav.classList.toggle('scrolled', window.scrollY > 50);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
})();
