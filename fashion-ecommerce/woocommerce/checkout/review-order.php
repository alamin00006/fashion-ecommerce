<?php
/** Order summary table used by the custom checkout layout. */
defined('ABSPATH') || exit;
?>
<table class="shop_table woocommerce-checkout-review-order-table of-order-table">
  <thead>
    <tr><th colspan="2"><div class="of-order-heading"><strong><?php esc_html_e('Your order', 'overview-fashion'); ?></strong><span><?php esc_html_e('Items:', 'overview-fashion'); ?> <b><?php echo esc_html(count(WC()->cart->get_cart())); ?></b> &nbsp; <?php esc_html_e('Quantity:', 'overview-fashion'); ?> <b><?php echo esc_html(WC()->cart->get_cart_contents_count()); ?></b></span></div></th></tr>
  </thead>
  <tbody>
    <?php do_action('woocommerce_review_order_before_cart_contents'); ?>
    <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
      $product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
      if (!$product || !$product->exists() || $cart_item['quantity'] < 1 || !apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) { continue; }
      $product_name = apply_filters('woocommerce_cart_item_name', $product->get_name(), $cart_item, $cart_item_key);
      $quantity = (int) $cart_item['quantity'];
      $quantity_nonce = wp_create_nonce('overview_fashion_cart_quantity');
      $ajax_url = admin_url('admin-ajax.php');
    ?>
      <tr class="cart_item">
        <td class="of-order-product">
          <span class="of-order-image"><?php echo wp_kses_post($product->get_image('woocommerce_thumbnail')); ?></span>
          <span class="of-order-product-info"><strong><?php echo wp_kses_post($product_name); ?></strong><?php echo wc_get_formatted_cart_item_data($cart_item); ?><small><?php echo wp_kses_post(wc_price((float) $product->get_price())); ?> × <?php echo esc_html($quantity); ?> pc</small></span>
          <button type="button" class="of-checkout-remove" data-of-cart-quantity data-cart-key="<?php echo esc_attr($cart_item_key); ?>" data-quantity="0" data-nonce="<?php echo esc_attr($quantity_nonce); ?>" data-ajax-url="<?php echo esc_url($ajax_url); ?>" aria-label="<?php esc_attr_e('Remove item', 'overview-fashion'); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m-9 0 1 13h10l1-13M10 11v5m4-5v5"></path></svg></button>
          <div class="of-checkout-qty-control"><button type="button" data-of-cart-quantity data-cart-key="<?php echo esc_attr($cart_item_key); ?>" data-quantity="<?php echo esc_attr(max(0, $quantity - 1)); ?>" data-nonce="<?php echo esc_attr($quantity_nonce); ?>" data-ajax-url="<?php echo esc_url($ajax_url); ?>" aria-label="<?php esc_attr_e('Decrease quantity', 'overview-fashion'); ?>">−</button><span><?php echo esc_html($quantity); ?></span><button type="button" data-of-cart-quantity data-cart-key="<?php echo esc_attr($cart_item_key); ?>" data-quantity="<?php echo esc_attr($quantity + 1); ?>" data-nonce="<?php echo esc_attr($quantity_nonce); ?>" data-ajax-url="<?php echo esc_url($ajax_url); ?>" aria-label="<?php esc_attr_e('Increase quantity', 'overview-fashion'); ?>">+</button></div>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php do_action('woocommerce_review_order_after_cart_contents'); ?>
  </tbody>
  <tfoot>
    <tr class="cart-subtotal"><th><?php esc_html_e('Subtotal', 'woocommerce'); ?></th><td><?php wc_cart_totals_subtotal_html(); ?></td></tr>
    <?php foreach (WC()->cart->get_fees() as $fee) : ?>
      <tr class="fee"><th><?php echo esc_html($fee->name); ?></th><td><?php wc_cart_totals_fee_html($fee); ?></td></tr>
    <?php endforeach; ?>
    <?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) : ?>
      <?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
        <?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : ?><tr class="tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>"><th><?php echo esc_html($tax->label); ?></th><td><?php echo wp_kses_post($tax->formatted_amount); ?></td></tr><?php endforeach; ?>
      <?php else : ?><tr class="tax-total"><th><?php echo esc_html(WC()->countries->tax_or_vat()); ?></th><td><?php wc_cart_totals_taxes_total_html(); ?></td></tr><?php endif; ?>
    <?php endif; ?>
    <?php do_action('woocommerce_review_order_before_order_total'); ?>
    <tr class="order-total"><th><?php esc_html_e('Total', 'woocommerce'); ?></th><td><?php wc_cart_totals_order_total_html(); ?></td></tr>
    <?php do_action('woocommerce_review_order_after_order_total'); ?>
  </tfoot>
</table>
