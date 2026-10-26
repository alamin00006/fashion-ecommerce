(function () {
  const track = document.querySelector('[data-of-slider]');
  if (track) {
    const slides = Array.from(track.children);
    const dots = document.querySelector('[data-of-dots]');
    let index = 0;
    const buttons = slides.map((_, i) => {
      const button = document.createElement('button');
      button.type = 'button';
      button.setAttribute('aria-label', `Go to slide ${i + 1}`);
      button.addEventListener('click', () => show(i));
      dots.appendChild(button);
      return button;
    });
    function show(next) {
      index = (next + slides.length) % slides.length;
      track.style.transform = `translateX(-${index * 100}%)`;
      buttons.forEach((button, i) => button.classList.toggle('active', i === index));
    }
    document.querySelector('[data-of-prev]').addEventListener('click', () => show(index - 1));
    document.querySelector('[data-of-next]').addEventListener('click', () => show(index + 1));
    show(0);
    window.setInterval(() => show(index + 1), 5500);
  }
  const drawer = document.querySelector('[data-of-drawer]');
  document.querySelector('[data-of-menu-open]')?.addEventListener('click', () => drawer.classList.add('is-open'));
  document.querySelectorAll('[data-of-menu-close]').forEach((button) => button.addEventListener('click', () => drawer.classList.remove('is-open')));
  const cartDrawer = document.querySelector('[data-of-cart-drawer]');
  document.querySelectorAll('[data-of-cart-open]').forEach((button) => button.addEventListener('click', () => cartDrawer?.classList.add('is-open')));
  document.querySelectorAll('[data-of-cart-close]').forEach((button) => button.addEventListener('click', () => cartDrawer?.classList.remove('is-open')));
  const cartBar = document.querySelector('[data-of-cart-bar]');
  function syncCartBar() {
    const count = Array.from(document.querySelectorAll('[data-of-cart-count]')).some((badge) => Number(badge.textContent.trim()) > 0);
    cartBar?.classList.toggle('is-visible', count);
  }
  if (window.jQuery) {
    window.jQuery(document.body).on('added_to_cart', () => cartBar?.classList.add('is-visible'));
    window.jQuery(document.body).on('removed_from_cart wc_fragments_refreshed', syncCartBar);
  }
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      drawer?.classList.remove('is-open');
      cartDrawer?.classList.remove('is-open');
    }
  });
})();
