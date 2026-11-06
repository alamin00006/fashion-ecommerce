<?php
/** Sajal Online checkout layout. */
defined('ABSPATH') || exit;
$checkout = WC()->checkout();
$delivery_area = WC()->session ? WC()->session->get('of_delivery_area', 'dhaka') : 'dhaka';
$delivery_charge_dhaka = 80;
$delivery_charge_outside = 130;
$free_shipping_enabled = (bool) get_theme_mod('of_multi_item_free_shipping', true);
$free_shipping_min_items = max(2, absint(get_theme_mod('of_free_shipping_min_items', 2)));
$cart_quantity = WC()->cart ? (int) WC()->cart->get_cart_contents_count() : 0;
$free_shipping_eligible = $free_shipping_enabled && $cart_quantity >= $free_shipping_min_items;
$checkout_back_url = home_url('/');
$checkout_cart_items = WC()->cart ? WC()->cart->get_cart() : array();
if ($checkout_cart_items) {
    $first_checkout_item = reset($checkout_cart_items);
    $first_checkout_product_id = !empty($first_checkout_item['product_id']) ? absint($first_checkout_item['product_id']) : 0;
    if ($first_checkout_product_id) { $checkout_back_url = add_query_arg('of_checkout_return', '1', get_permalink($first_checkout_product_id)); }
}
$checkout_cart_product_ids = WC()->cart ? array_values(array_unique(array_map(static function ($item) { return (int) $item['product_id']; }, WC()->cart->get_cart()))) : array();
$checkout_latest_products = wc_get_products(array('status' => 'publish', 'limit' => 24, 'orderby' => 'date', 'order' => 'DESC', 'meta_query' => array(array('key' => '_of_is_new_product', 'value' => 'yes'))));
$checkout_latest_products = array_values(array_filter($checkout_latest_products, static function ($product) { return $product instanceof WC_Product && $product->is_type('simple') && $product->is_purchasable(); }));
$checkout_latest_products = array_slice($checkout_latest_products, 0, 8);
do_action('woocommerce_before_checkout_form', $checkout);
?>
<div class="of-checkout-page">
  <div class="of-checkout-brandbar">
    <a class="of-checkout-back" href="<?php echo esc_url($checkout_back_url); ?>" aria-label="<?php esc_attr_e('Back to product', 'overview-fashion'); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12H3m0 0 7-7m-7 7 7 7"/></svg></a>
    <div class="of-checkout-logo"><?php overview_fashion_logo(); ?></div>
  </div>
  <form name="checkout" method="post" class="checkout woocommerce-checkout of-checkout-form" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data" aria-label="<?php esc_attr_e('Checkout', 'woocommerce'); ?>">
    <div class="of-checkout-columns">
      <div class="of-checkout-left">
        <?php if ($checkout->get_checkout_fields()) : ?>
          <section class="of-checkout-card of-customer-fields">
            <?php do_action('woocommerce_checkout_billing'); ?>
          </section>
        <?php endif; ?>
        <section class="of-checkout-card of-delivery-card">
          <h2><span aria-hidden="true">&#128666;</span> <?php esc_html_e('Delivery Charge', 'overview-fashion'); ?></h2>
          <label class="of-delivery-option <?php echo $delivery_area === 'dhaka' ? 'is-selected' : ''; ?>">
            <input type="radio" name="of_delivery_area" value="dhaka" <?php checked($delivery_area, 'dhaka'); ?>>
            <span class="of-delivery-check" aria-hidden="true"></span><span><?php esc_html_e('ঢাকার ভিতরে', 'overview-fashion'); ?></span><strong><?php echo wp_kses_post(wc_price(80)); ?></strong>
          </label>
          <label class="of-delivery-option <?php echo $delivery_area === 'outside' ? 'is-selected' : ''; ?>">
            <input type="radio" name="of_delivery_area" value="outside" <?php checked($delivery_area, 'outside'); ?>>
            <span class="of-delivery-check" aria-hidden="true"></span><span><?php esc_html_e('ঢাকার বাহিরে', 'overview-fashion'); ?> <small>(সারা দেশে)</small></span><strong><?php echo wp_kses_post(wc_price(130)); ?></strong>
          </label>
        </section>
        <section class="of-checkout-card of-checkout-totals" data-of-checkout-totals data-free-shipping-enabled="<?php echo esc_attr($free_shipping_enabled ? '1' : '0'); ?>" data-free-shipping-min-items="<?php echo esc_attr($free_shipping_min_items); ?>" data-dhaka-charge="<?php echo esc_attr($delivery_charge_dhaka); ?>" data-outside-charge="<?php echo esc_attr($delivery_charge_outside); ?>">
          <div><span><?php esc_html_e('Subtotal', 'overview-fashion'); ?></span><strong data-of-checkout-subtotal><?php echo wp_kses_post(WC()->cart->get_cart_subtotal()); ?></strong></div>
          <div><span><?php esc_html_e('Delivery Charge', 'overview-fashion'); ?></span><strong data-of-checkout-delivery><?php echo $free_shipping_eligible ? esc_html__('ফ্রি', 'overview-fashion') : wp_kses_post(wc_price($delivery_area === 'outside' ? $delivery_charge_outside : $delivery_charge_dhaka)); ?></strong></div>
          <div class="of-checkout-grand-total"><span><?php esc_html_e('Total', 'overview-fashion'); ?></span><strong data-of-checkout-total><?php echo wp_kses_post(WC()->cart->get_total()); ?></strong></div>
        </section>
        <?php do_action('woocommerce_checkout_before_order_review'); ?>
        <div class="of-place-order-wrap"><?php woocommerce_checkout_payment(); ?><?php overview_fashion_render_contact_cta('of-checkout-contact-cta'); ?></div>
      </div>
      <aside class="of-checkout-right">
        <div id="order_review" class="woocommerce-checkout-review-order"><?php woocommerce_order_review(); ?></div>
        <?php if ($checkout_latest_products) : ?>
          <section class="of-checkout-latest" data-of-checkout-latest>
            <div class="of-checkout-latest-heading"><h2>নতুন প্রোডাক্টে অফার চলছে!!!</h2><div><button type="button" data-of-latest-prev aria-label="<?php esc_attr_e('Previous products', 'overview-fashion'); ?>">‹</button><button type="button" data-of-latest-next aria-label="<?php esc_attr_e('Next products', 'overview-fashion'); ?>">›</button></div></div>
            <div class="of-checkout-latest-track" data-of-latest-track>
              <?php foreach ($checkout_latest_products as $latest_product) : ?>
                <?php
                  $latest_discount = 0;
                  if ($latest_product->is_on_sale()) {
                      $latest_regular = (float) $latest_product->get_regular_price();
                      $latest_sale = (float) $latest_product->get_sale_price();
                      if ($latest_regular > 0 && $latest_sale > 0 && $latest_sale < $latest_regular) {
                          $latest_discount = (int) floor((($latest_regular - $latest_sale) / $latest_regular) * 100);
                      }
                  }
                ?>
                <article class="of-checkout-latest-product" data-product-id="<?php echo esc_attr($latest_product->get_id()); ?>">
                  <a class="of-checkout-latest-image" href="<?php echo esc_url(get_permalink($latest_product->get_id())); ?>"><?php echo wp_kses_post($latest_product->get_image('woocommerce_thumbnail', array('loading' => 'lazy'))); ?><?php if ($latest_discount > 0) : ?><span class="of-checkout-latest-discount">-<?php echo esc_html($latest_discount); ?>%</span><?php endif; ?></a>
                  <a class="of-checkout-latest-name" href="<?php echo esc_url(get_permalink($latest_product->get_id())); ?>"><?php echo esc_html($latest_product->get_name()); ?></a>
                  <div class="of-checkout-latest-price"><?php echo wp_kses_post($latest_product->get_price_html()); ?></div>
                  <select class="of-checkout-latest-size" aria-label="<?php echo esc_attr(sprintf(__('Size for %s', 'overview-fashion'), $latest_product->get_name())); ?>"><option value="" selected disabled><?php esc_html_e('সাইজ নির্বাচন করুন', 'overview-fashion'); ?></option><option value="M">M</option><option value="L">L</option><option value="XL">XL</option></select>
                  <button class="of-button of-checkout-latest-add" type="button" data-of-checkout-latest-add><?php echo in_array($latest_product->get_id(), $checkout_cart_product_ids, true) ? esc_html__('আরও কিনুন', 'overview-fashion') : esc_html__('এটি কিনুন', 'overview-fashion'); ?></button>
                </article>
              <?php endforeach; ?>
            </div>
            <p class="of-checkout-latest-message" role="status" aria-live="polite"></p>
          </section>
        <?php endif; ?>
      </aside>
    </div>
  </form>
</div>
<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
