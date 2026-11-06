(function () {
  if (document.querySelector('.of-thankyou-page')) {
    const homeLink = document.querySelector('.of-thankyou-home');
    const homeUrl = homeLink?.href || '/';
    window.history.pushState({ ofThankyouPage: true }, '', window.location.href);
    window.addEventListener('popstate', () => window.location.replace(homeUrl), { once: true });
  }

  const checkoutReturnUrl = new URL(window.location.href);
  if (checkoutReturnUrl.searchParams.has('of_checkout_return')) {
    checkoutReturnUrl.searchParams.delete('of_checkout_return');
    window.history.replaceState(window.history.state, '', checkoutReturnUrl.pathname + checkoutReturnUrl.search + checkoutReturnUrl.hash);
    window.jQuery?.(document.body).trigger('wc_fragment_refresh');
  }

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
      const anySizeInCart = Array.from(document.querySelectorAll('.of-cart-item[data-product-id]')).some((item) => item.dataset.productId === String(productId));
      summary?.querySelector('.of-mobile-order-note')?.classList.toggle('is-visible', anySizeInCart);
      if (orderButton && orderButton.dataset.loading !== 'true') {
        orderButton.classList.toggle('is-already-in-cart', sameSizeInCart);
        orderButton.textContent = sameSizeInCart ? overviewFashionCart.addedLabel : overviewFashionCart.orderLabel;
      }
    };
    picker.querySelectorAll('input[name="of_size"]').forEach((input) => input.addEventListener('change', () => {
      syncOrderButton(input);
      summary?.querySelector('[data-of-fbt]')?.dispatchEvent(new Event('of-fbt-refresh-total'));
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
      refreshFbtTotals();
      button.textContent = overviewFashionCart.addedLabel;
      button.classList.add('is-already-in-cart');
      button.classList.add('added');
      form.closest('.of-detail-summary')?.querySelector('.of-mobile-order-note')?.classList.add('is-visible');
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
  const refreshFbtTotals = () => document.querySelectorAll('[data-of-fbt]').forEach((bundle) => bundle.dispatchEvent(new Event('of-fbt-refresh-total')));
  document.querySelectorAll('[data-of-fbt]').forEach((bundle) => {
    const addButton = bundle.querySelector('[data-of-fbt-add]');
    const readyButtonLabel = overviewFashionCart.fbtBuyLabel || 'পণ্যগুলো কিনুন';
    const syncButtonState = () => {
      const selectedRows = Array.from(bundle.querySelectorAll('.of-fbt-item:not(.is-current)')).filter((row) => row.querySelector('.of-fbt-check')?.checked);
      const needsSize = selectedRows.some((row) => !row.querySelector('.of-fbt-info select')?.value);
      if (addButton) {
        addButton.disabled = needsSize || selectedRows.length === 0;
        addButton.textContent = needsSize ? (overviewFashionCart.fbtSizeRequiredLabel || 'সাইজ নির্বাচন করুন') : readyButtonLabel;
      }
    };
    const updateTotal = () => {
      const selectedMainSize = bundle.closest('.of-detail-summary')?.querySelector('.of-size-picker input[name="of_size"]:checked')?.value || 'M';
      const totalNode = bundle.querySelector('[data-of-fbt-total]');
      let total = Number(totalNode?.getAttribute(`data-base-${selectedMainSize.toLowerCase()}`) || 0);
      bundle.querySelectorAll('.of-fbt-check:checked:not(:disabled)').forEach((input) => { total += Number(input.dataset.price || 0); });
      const decimals = Number(overviewFashionCart.priceDecimals ?? 0);
      const formatted = new Intl.NumberFormat('en-BD', { minimumFractionDigits: decimals, maximumFractionDigits: decimals }).format(total);
      if (totalNode) totalNode.textContent = `${overviewFashionCart.currencySymbol || '৳'} ${formatted}`;
    };
    bundle.addEventListener('of-fbt-refresh-total', updateTotal);
    bundle.addEventListener('change', (event) => {
      if (event.target.matches('.of-fbt-check')) event.target.closest('.of-fbt-item')?.classList.toggle('is-selected', event.target.checked);
      updateTotal();
      syncButtonState();
    });
    bundle.querySelectorAll('.of-fbt-check').forEach((input) => input.closest('.of-fbt-item')?.classList.toggle('is-selected', input.checked));
    updateTotal();
    syncButtonState();
    addButton?.addEventListener('click', async (event) => {
      const button = event.currentTarget;
      const errorNode = bundle.querySelector('.of-fbt-error');
      const summary = bundle.closest('.of-detail-summary');
      const selectedMainSize = summary?.querySelector('.of-size-picker input[name="of_size"]:checked')?.value || 'M';
      const selectedRows = Array.from(bundle.querySelectorAll('.of-fbt-item')).filter((row) => !row.classList.contains('is-current') && row.querySelector('.of-fbt-check')?.checked);
      if (!selectedRows.length) return;
      const items = selectedRows.map((row) => ({ id: row.querySelector('.of-fbt-check').value, size: row.querySelector('.of-fbt-info select').value }));
      button.disabled = true;
      button.setAttribute('aria-busy', 'true');
      if (errorNode) { errorNode.hidden = true; errorNode.textContent = ''; }
      try {
        const body = new URLSearchParams({ action: 'overview_fashion_add_fbt_products', nonce: overviewFashionCart.addNonce, current_product: bundle.dataset.currentProduct, product_id: bundle.dataset.currentProduct, current_size: selectedMainSize, items: JSON.stringify(items) });
        const response = await fetch(overviewFashionCart.ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest' }, body });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.data?.message || 'Could not add the selected products.');
        Object.entries(result.data.fragments || {}).forEach(([selector, markup]) => {
          const template = document.createElement('template');
          template.innerHTML = markup.trim();
          const replacement = template.content.firstElementChild;
          if (replacement) document.querySelectorAll(selector).forEach((current) => current.replaceWith(replacement.cloneNode(true)));
        });
        refreshFbtTotals();
        bundle.querySelector('.of-mobile-order-note')?.classList.add('is-visible');
        cartBar?.classList.add('is-visible');
        if (window.jQuery) window.jQuery(document.body).trigger('added_to_cart', [result.data.fragments || {}, result.data.cart_hash, window.jQuery(button)]);
      } catch (error) {
        if (errorNode) { errorNode.textContent = error.message; errorNode.hidden = false; }
      } finally {
        button.removeAttribute('aria-busy');
        syncButtonState();
      }
    });
  });
  document.querySelectorAll('[data-of-checkout-latest]').forEach((section) => {
    const track = section.querySelector('[data-of-latest-track]');
    const products = Array.from(track?.querySelectorAll('.of-checkout-latest-product') || []);
    if (!track || products.length < 2) return;
    let slideIndex = 0;
    let autoSlide;
    const showSlide = (index) => {
      slideIndex = (index + products.length) % products.length;
      track.scrollTo({ left: products[slideIndex].offsetLeft - products[0].offsetLeft, behavior: 'smooth' });
    };
    const startAutoSlide = () => {
      window.clearInterval(autoSlide);
      if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        autoSlide = window.setInterval(() => showSlide(slideIndex + 1), 3000);
      }
    };
    section.querySelector('[data-of-latest-prev]')?.addEventListener('click', () => { showSlide(slideIndex - 1); startAutoSlide(); });
    section.querySelector('[data-of-latest-next]')?.addEventListener('click', () => { showSlide(slideIndex + 1); startAutoSlide(); });
    section.addEventListener('mouseenter', () => window.clearInterval(autoSlide));
    section.addEventListener('mouseleave', startAutoSlide);
    section.addEventListener('focusin', () => window.clearInterval(autoSlide));
    section.addEventListener('focusout', (event) => { if (!section.contains(event.relatedTarget)) startAutoSlide(); });
    document.addEventListener('visibilitychange', () => document.hidden ? window.clearInterval(autoSlide) : startAutoSlide());
    startAutoSlide();
  });
  document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-of-checkout-latest-add]');
    if (!button) return;
    const card = button.closest('.of-checkout-latest-product');
    const size = card?.querySelector('.of-checkout-latest-size')?.value;
    const message = button.closest('[data-of-checkout-latest]')?.querySelector('.of-checkout-latest-message');
    if (!card || !size) {
      if (message) { message.textContent = 'সাইজ নির্বাচন করুন।'; message.classList.add('is-error'); }
      card?.querySelector('.of-checkout-latest-size')?.focus();
      return;
    }
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    if (message) { message.textContent = ''; message.classList.remove('is-error'); }
    try {
      const body = new URLSearchParams({ action: 'overview_fashion_add_product_to_cart', nonce: overviewFashionCart.addNonce, product_id: card.dataset.productId, quantity: '1', of_size: size });
      const response = await fetch(overviewFashionCart.ajaxUrl, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest' }, body });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.data?.message || 'পণ্যটি কার্টে যোগ করা যায়নি।');
      Object.entries(result.data.fragments || {}).forEach(([selector, markup]) => {
        const template = document.createElement('template');
        template.innerHTML = markup.trim();
        const replacement = template.content.firstElementChild;
        if (replacement) document.querySelectorAll(selector).forEach((current) => current.replaceWith(replacement.cloneNode(true)));
      });
      button.textContent = 'আরও কিনুন';
      button.disabled = false;
      if (message) { message.textContent = ''; message.classList.remove('is-error'); }
      window.jQuery?.(document.body).trigger('wc_fragment_refresh');
      window.jQuery?.(document.body).trigger('update_checkout');
      cartBar?.classList.add('is-visible');
    } catch (error) {
      if (message) { message.textContent = error.message; message.classList.add('is-error'); }
      button.disabled = false;
    } finally {
      button.removeAttribute('aria-busy');
    }
  });
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
      const total = document.querySelector('#order_review .order-total td');
      const totals = document.querySelector('[data-of-checkout-totals]');
      const subtotalTarget = document.querySelector('[data-of-checkout-subtotal]');
      const deliveryTarget = document.querySelector('[data-of-checkout-delivery]');
      const totalTarget = document.querySelector('[data-of-checkout-total]');
      if (subtotal && subtotalTarget) subtotalTarget.innerHTML = subtotal.innerHTML;
      if (total && totalTarget) totalTarget.innerHTML = total.innerHTML;
      if (totals && deliveryTarget) {
        const selectedArea = document.querySelector('input[name="of_delivery_area"]:checked')?.value || 'dhaka';
        const amount = Number(selectedArea === 'outside' ? totals.dataset.outsideCharge : totals.dataset.dhakaCharge);
        const count = Array.from(document.querySelectorAll('#order_review .of-checkout-qty-control > span')).reduce((sum, item) => sum + Number(item.textContent.trim() || 0), 0);
        const freeEnabled = totals.dataset.freeShippingEnabled === '1';
        const isFree = freeEnabled && count >= Number(totals.dataset.freeShippingMinItems || 2);
        const formattedAmount = new Intl.NumberFormat('en-BD', { maximumFractionDigits: 0 }).format(amount);
        deliveryTarget.textContent = isFree ? 'ফ্রি' : `৳ ${formattedAmount}`;
      }
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
      refreshFbtTotals();
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
