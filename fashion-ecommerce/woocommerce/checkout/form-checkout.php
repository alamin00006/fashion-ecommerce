<?php
/** Sajal Online checkout layout. */
defined('ABSPATH') || exit;
$checkout = WC()->checkout();
$delivery_area = WC()->session ? WC()->session->get('of_delivery_area', 'dhaka') : 'dhaka';
do_action('woocommerce_before_checkout_form', $checkout);
?>
<div class="of-checkout-page">
  <div class="of-checkout-brandbar">
    <a class="of-checkout-back" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('Back to home', 'overview-fashion'); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12H3m0 0 7-7m-7 7 7 7"/></svg></a>
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
            <span class="of-delivery-check" aria-hidden="true"></span><span><?php esc_html_e('Dhaka', 'overview-fashion'); ?> <small>(2 days)</small></span><strong><?php echo wp_kses_post(wc_price(80)); ?></strong>
          </label>
          <label class="of-delivery-option <?php echo $delivery_area === 'outside' ? 'is-selected' : ''; ?>">
            <input type="radio" name="of_delivery_area" value="outside" <?php checked($delivery_area, 'outside'); ?>>
            <span class="of-delivery-check" aria-hidden="true"></span><span><?php esc_html_e('Outside Dhaka', 'overview-fashion'); ?> <small>(2–3 days)</small></span><strong><?php echo wp_kses_post(wc_price(130)); ?></strong>
          </label>
        </section>
        <section class="of-checkout-card of-checkout-totals" data-of-checkout-totals>
          <div><span><?php esc_html_e('Subtotal', 'overview-fashion'); ?></span><strong data-of-checkout-subtotal><?php echo wp_kses_post(WC()->cart->get_cart_subtotal()); ?></strong></div>
          <div><span><?php esc_html_e('Delivery Charge', 'overview-fashion'); ?></span><strong data-of-checkout-delivery><?php echo wp_kses_post(wc_price($delivery_area === 'outside' ? 130 : 80)); ?></strong></div>
          <div class="of-checkout-grand-total"><span><?php esc_html_e('Total', 'overview-fashion'); ?></span><strong data-of-checkout-total><?php echo wp_kses_post(WC()->cart->get_total()); ?></strong></div>
        </section>
        <?php do_action('woocommerce_checkout_before_order_review'); ?>
        <div class="of-place-order-wrap"><?php woocommerce_checkout_payment(); ?></div>
      </div>
      <aside class="of-checkout-right">
        <div id="order_review" class="woocommerce-checkout-review-order"><?php woocommerce_order_review(); ?></div>
      </aside>
    </div>
  </form>
</div>
<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
