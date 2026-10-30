(function () {
  const revealItems = document.querySelectorAll(
    '.of-products .of-product, .of-product-detail-top > *, .of-detail-description'
  );
  if ('IntersectionObserver' in window && revealItems.length) {
    const revealObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

    revealItems.forEach((item, index) => {
      item.classList.add('of-scroll-reveal');
      item.style.setProperty('--of-reveal-delay', `${(index % 5) * 65}ms`);
      revealObserver.observe(item);
    });
  }

  const searchOverlay = document.querySelector('[data-of-search-overlay]');
  const headerSearch = document.querySelector('.of-search');
  const headerSearchInput = headerSearch?.querySelector('input[type="search"]');
  const modalSearchInput = searchOverlay?.querySelector('[data-of-search-modal-input]');
  let searchFocusReturn = null;
  function openSearch(event) {
    event?.preventDefault();
    if (!searchOverlay) return;
    searchFocusReturn = document.activeElement;
    searchOverlay.classList.add('is-open');
    searchOverlay.setAttribute('aria-hidden', 'false');
    document.body.classList.add('of-search-open');
    if (modalSearchInput && headerSearchInput) modalSearchInput.value = headerSearchInput.value;
    requestAnimationFrame(() => modalSearchInput?.focus());
  }
  function closeSearch() {
    if (!searchOverlay) return;
    searchOverlay.classList.remove('is-open');
    searchOverlay.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('of-search-open');
    const restoreTarget = searchFocusReturn === headerSearchInput ? headerSearch?.querySelector('button') : searchFocusReturn;
    restoreTarget?.focus?.();
  }
  headerSearch?.addEventListener('submit', openSearch);
  headerSearchInput?.addEventListener('focus', openSearch);
  searchOverlay?.querySelectorAll('[data-of-search-close]').forEach((button) => button.addEventListener('click', closeSearch));
  searchOverlay?.addEventListener('click', (event) => {
    if (event.target === searchOverlay) closeSearch();
  });

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

    const $body = window.jQuery(document.body);
    const deliveryRadios = document.querySelectorAll('input[name="of_delivery_area"]');
    [['#billing_phone', 11], ['#billing_address_1', 255]].forEach(([selector, max]) => {
      const input = document.querySelector(selector);
      const label = input?.closest('.form-row')?.querySelector('label');
      if (!input || !label) return;
      const counter = document.createElement('span');
      counter.className = 'of-field-counter';
      label.append(counter);
      const updateCounter = () => { counter.textContent = `${input.value.length}/${max}`; };
      input.addEventListener('input', updateCounter);
      updateCounter();
    });
    const syncDeliverySelection = () => {
      document.querySelectorAll('.of-delivery-option').forEach((option) => {
        option.classList.toggle('is-selected', Boolean(option.querySelector('input[name="of_delivery_area"]:checked')));
      });
    };
    const syncCheckoutTotals = () => {
      const subtotal = document.querySelector('#order_review .cart-subtotal td');
      const delivery = document.querySelector('#order_review tr.fee td');
      const total = document.querySelector('#order_review .order-total td');
      const subtotalTarget = document.querySelector('[data-of-checkout-subtotal]');
      const deliveryTarget = document.querySelector('[data-of-checkout-delivery]');
      const totalTarget = document.querySelector('[data-of-checkout-total]');
      if (subtotal && subtotalTarget) subtotalTarget.innerHTML = subtotal.innerHTML;
      if (delivery && deliveryTarget) deliveryTarget.innerHTML = delivery.innerHTML;
      if (total && totalTarget) totalTarget.innerHTML = total.innerHTML;
    };
    deliveryRadios.forEach((radio) => radio.addEventListener('change', () => {
      syncDeliverySelection();
      $body.trigger('update_checkout');
    }));
    $body.on('updated_checkout', syncCheckoutTotals);
    syncCheckoutTotals();
  }
  document.addEventListener('click', async (event) => {
    const control = event.target.closest('[data-of-cart-quantity]');
    if (!control) return;
    event.preventDefault();
    if (control.dataset.busy === 'true') return;
    control.dataset.busy = 'true';
    control.setAttribute('aria-disabled', 'true');
    const body = new URLSearchParams({
      action: 'overview_fashion_cart_quantity',
      cart_key: control.dataset.cartKey,
      quantity: control.dataset.quantity,
      nonce: control.dataset.nonce,
    });
    try {
      const response = await fetch(control.dataset.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
        body,
      });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.data?.message || 'Cart update failed');
      Object.entries(result.data.fragments || {}).forEach(([selector, markup]) => {
        const template = document.createElement('template');
        template.innerHTML = markup.trim();
        const replacement = template.content.firstElementChild;
        if (!replacement) return;
        document.querySelectorAll(selector).forEach((current) => current.replaceWith(replacement.cloneNode(true)));
      });
      syncCartBar();
      if (control.closest('.of-checkout-right') && window.jQuery) window.jQuery(document.body).trigger('update_checkout');
      if (window.jQuery) window.jQuery(document.body).trigger('wc_fragments_refreshed');
    } catch (error) {
      console.error(error);
      control.dataset.busy = 'false';
      control.removeAttribute('aria-disabled');
    }
  });
  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
      drawer?.classList.remove('is-open');
      cartDrawer?.classList.remove('is-open');
      closeSearch();
    }
  });
})();
