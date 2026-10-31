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
  const searchResults = searchOverlay?.querySelector('[data-of-search-results]');
  let searchFocusReturn = null;
  let searchTimer = 0;
  let searchRequest = null;
  let searchSequence = 0;
  function renderSearchResults(items) {
    if (!searchResults) return;
    searchResults.replaceChildren();
    if (!items.length) {
      const empty = document.createElement('p');
      empty.className = 'of-search-empty';
      empty.textContent = 'No products found.';
      searchResults.append(empty);
    } else {
      items.forEach((item) => {
        const link = document.createElement('a');
        link.className = 'of-search-result';
        link.href = item.url;
        const image = document.createElement('img');
        image.src = item.image;
        image.alt = '';
        image.loading = 'lazy';
        const details = document.createElement('span');
        details.className = 'of-search-result-details';
        const title = document.createElement('strong');
        title.textContent = item.name;
        const price = document.createElement('span');
        price.className = 'of-search-result-price';
        price.innerHTML = item.price;
        details.append(title, price);
        link.append(image, details);
        if (item.category) {
          const category = document.createElement('span');
          category.className = 'of-search-result-category';
          category.textContent = item.category;
          link.append(category);
        }
        searchResults.append(link);
      });
    }
    searchResults.hidden = false;
  }
  async function searchProducts(query, sequence) {
    searchRequest?.abort();
    searchRequest = new AbortController();
    const body = new URLSearchParams({ action: 'overview_fashion_product_search', nonce: overviewFashionCart.searchNonce, query });
    try {
      const response = await fetch(overviewFashionCart.ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body, signal: searchRequest.signal });
      const result = await response.json();
      if (!response.ok || !result.success || sequence !== searchSequence) return;
      renderSearchResults(result.data.items || []);
    } catch (error) {
      if (error.name !== 'AbortError') console.error(error);
    }
  }
  modalSearchInput?.addEventListener('input', () => {
    const query = modalSearchInput.value.trim();
    window.clearTimeout(searchTimer);
    searchSequence += 1;
    const sequence = searchSequence;
    if (query.length < 2) {
      searchRequest?.abort();
      if (searchResults) { searchResults.replaceChildren(); searchResults.hidden = true; }
      return;
    }
    searchTimer = window.setTimeout(() => searchProducts(query, sequence), 220);
  });
  function openSearch(event) {
    event?.preventDefault();
    if (!searchOverlay) return;
    searchFocusReturn = document.activeElement;
    searchOverlay.classList.add('is-open');
    searchOverlay.setAttribute('aria-hidden', 'false');
    document.body.classList.add('of-search-open');
    if (modalSearchInput && headerSearchInput) {
      modalSearchInput.value = headerSearchInput.value;
      modalSearchInput.dispatchEvent(new Event('input', { bubbles: true }));
    }
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
  function closeCartDrawer() {
    const isBottomSheet = cartDrawer?.classList.contains('is-bottom-open') && cartDrawer.classList.contains('is-open');
    cartDrawer?.classList.remove('is-open');
    window.setTimeout(() => cartDrawer?.classList.remove('is-bottom-open'), isBottomSheet ? 520 : 0);
  }
  document.querySelectorAll('[data-of-cart-open]').forEach((button) => button.addEventListener('click', (event) => {
    if (button.classList.contains('of-mobile-cart-bar') && !event.target.closest('.of-mobile-cart-bag')) {
      window.location.assign(overviewFashionCart.checkoutUrl);
      return;
    }
    if (!cartDrawer) return;
    const useBottomSheet = Boolean(button.closest('[data-of-cart-bar]')) && window.matchMedia('(max-width: 680px)').matches;
    cartDrawer.classList.remove('is-open', 'is-bottom-open');
    if (useBottomSheet) {
      cartDrawer.classList.add('is-bottom-open', 'is-preparing-bottom');
      cartDrawer.offsetHeight;
      cartDrawer.classList.remove('is-preparing-bottom');
      cartDrawer.classList.add('is-open');
    } else {
      cartDrawer.classList.add('is-open');
    }
  }));
  document.querySelectorAll('[data-of-cart-close]').forEach((button) => button.addEventListener('click', closeCartDrawer));
  const cartSidebar = document.querySelector('.of-cart-sidebar');
  cartSidebar?.querySelectorAll('[data-of-sidebar-tab]').forEach((tab) => tab.addEventListener('click', () => {
    const showHistory = tab.dataset.ofSidebarTab === 'history';
    cartSidebar.classList.toggle('is-history', showHistory);
    cartSidebar.querySelectorAll('[data-of-sidebar-tab]').forEach((button) => {
      const active = button === tab;
      button.classList.toggle('active', active);
      button.setAttribute('aria-pressed', active ? 'true' : 'false');
    });
  }));
  document.querySelectorAll('.of-size-picker').forEach((picker) => {
    const refreshControls = async (input) => {
      const summary = picker.closest('.of-detail-summary');
      const productId = summary?.querySelector('form.cart [name="add-to-cart"]')?.value || summary?.querySelector('.single_add_to_cart_button')?.value;
      if (!productId) return;
      const body = new URLSearchParams({ action: 'overview_fashion_refresh_product_cart_controls', nonce: overviewFashionCart.addNonce, product_id: productId, of_size: input.value });
      try {
        const response = await fetch(overviewFashionCart.ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' }, body });
        const result = await response.json();
        if (!response.ok || !result.success) return;
        const template = document.createElement('template');
        template.innerHTML = result.data.html.trim();
        const replacement = template.content.firstElementChild;
        const current = summary?.querySelector('[data-of-single-product-cart-controls]');
        if (replacement && current) current.replaceWith(replacement);
      } catch (error) {
        console.error(error);
      }
    };
    const syncOrderButton = (input) => {
      if (!input) return;
      picker.querySelectorAll('label').forEach((label) => label.classList.toggle('selected', label.contains(input) && input.checked));
      const summary = picker.closest('.of-detail-summary');
      const orderButton = summary?.querySelector('.single_add_to_cart_button');
      const productId = summary?.querySelector('form.cart [name="add-to-cart"]')?.value || orderButton?.value;
      const sameSizeInCart = Array.from(document.querySelectorAll('.of-cart-item[data-product-id]')).some((item) => item.dataset.productId === String(productId) && item.dataset.productSize === input.value);
      if (orderButton && orderButton.dataset.loading !== 'true') {
        orderButton.classList.toggle('is-already-in-cart', sameSizeInCart);
        orderButton.textContent = sameSizeInCart ? overviewFashionCart.addedLabel : overviewFashionCart.orderLabel;
      }
    };
    picker.querySelectorAll('input[name="of_size"]').forEach((input) => input.addEventListener('change', () => {
      syncOrderButton(input);
      refreshControls(input);
    }));
    syncOrderButton(picker.querySelector('input[name="of_size"]:checked'));
  });
  document.addEventListener('click', (event) => {
    const button = event.target.closest('.of-detail-summary .single_add_to_cart_button');
    if (!button) return;
    button.classList.remove('of-click-3d');
    void button.offsetWidth;
    button.classList.add('of-click-3d');
    window.setTimeout(() => button.classList.remove('of-click-3d'), 340);
  });
  document.addEventListener('submit', async (event) => {
    const form = event.target.closest('.of-detail-summary form.cart');
    if (!form || !window.fetch) return;
    event.preventDefault();
    const button = form.querySelector('.single_add_to_cart_button');
    const productId = form.querySelector('[name="add-to-cart"]')?.value || button?.value || form.querySelector('[name="product_id"]')?.value;
    if (!productId || !button || button.dataset.loading === 'true') return;
    const formData = new FormData(form);
    formData.set('product_id', productId);
    formData.set('quantity', form.querySelector('[name="quantity"]')?.value || '1');
    button.dataset.loading = 'true';
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    try {
      const payload = new URLSearchParams();
      payload.set('action', 'overview_fashion_add_product_to_cart');
      payload.set('nonce', overviewFashionCart.addNonce);
      formData.forEach((value, key) => payload.set(key, value));
      payload.set('product_id', productId);
      const response = await fetch(overviewFashionCart.ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest' }, body: payload });
      const payloadResult = await response.json();
      if (!response.ok || !payloadResult.success) throw new Error(payloadResult.data?.message || 'Could not add this product.');
      const result = payloadResult.data;
      if (result.fragments) {
        Object.entries(result.fragments).forEach(([selector, markup]) => {
          const template = document.createElement('template');
          template.innerHTML = markup.trim();
          const replacement = template.content.firstElementChild;
          if (replacement) document.querySelectorAll(selector).forEach((current) => current.replaceWith(replacement.cloneNode(true)));
        });
      }
      button.textContent = overviewFashionCart.addedLabel;
      button.classList.add('is-already-in-cart');
      button.classList.add('added');
      cartBar?.classList.add('is-visible');
      button.dataset.loading = 'false';
      button.disabled = false;
      button.removeAttribute('aria-busy');
    } catch (error) {
      console.error(error);
      let status = form.querySelector('.of-add-to-cart-error');
      if (!status) {
        status = document.createElement('p');
        status.className = 'of-add-to-cart-error';
        status.setAttribute('role', 'alert');
        form.append(status);
      }
      status.textContent = error.message || 'পণ্যটি কার্টে যোগ করা যায়নি। আবার চেষ্টা করুন।';
      button.dataset.loading = 'false';
      button.disabled = false;
      button.removeAttribute('aria-busy');
    }
  });
  const cartBar = document.querySelector('[data-of-cart-bar]');
  const mobileCartLabel = cartBar?.querySelector(':scope > span:nth-child(2)');
  if (mobileCartLabel) mobileCartLabel.textContent = overviewFashionCart.orderConfirmLabel;
  function syncCartBar() {
    const count = Array.from(document.querySelectorAll('[data-of-cart-count]')).some((badge) => Number(badge.textContent.trim()) > 0);
    cartBar?.classList.toggle('is-visible', count);
  }
  if (window.jQuery) {
    window.jQuery(document.body).on('added_to_cart', () => cartBar?.classList.add('is-visible'));
    window.jQuery(document.body).on('removed_from_cart wc_fragments_refreshed', syncCartBar);

    const $body = window.jQuery(document.body);
    const deliveryRadios = document.querySelectorAll('input[name="of_delivery_area"]');
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
    const detailSummary = control.closest('.of-detail-summary');
    const selectedSize = detailSummary?.querySelector('.of-size-picker input[name="of_size"]:checked');
    const productId = detailSummary?.querySelector('form.cart [name="add-to-cart"]')?.value;
    if (selectedSize) body.set('of_size', selectedSize.value);
    if (productId) body.set('product_id', productId);
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
      closeCartDrawer();
      closeSearch();
    }
  });
})();
