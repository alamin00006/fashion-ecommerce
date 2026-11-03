<?php
/** Sajal Online order confirmation page. */
defined('ABSPATH') || exit;

$order = isset($order) && $order instanceof WC_Order ? $order : (isset($order_id) ? wc_get_order($order_id) : false);
if (!$order) {
    return;
}

$order_id = $order->get_id();
$thankyou_products = wc_get_products(array('status' => 'publish', 'limit' => 24, 'orderby' => 'date', 'order' => 'DESC', 'type' => 'simple', 'meta_query' => array(array('key' => '_of_is_new_product', 'value' => 'yes'))));
$thankyou_products = array_values(array_filter($thankyou_products, static function ($product) { return $product instanceof WC_Product && $product->is_purchasable(); }));
do_action('woocommerce_before_thankyou', $order_id);
?>
<main class="of-thankyou-page">
  <header class="of-thankyou-brandbar">
    <div class="of-thankyou-logo"><?php overview_fashion_logo(); ?></div>
  </header>

  <?php if ($order->has_status('failed')) : ?>
    <section class="of-thankyou-card of-thankyou-failed">
      <div class="of-thankyou-mark" aria-hidden="true">!</div>
      <h1><?php esc_html_e('পেমেন্ট সম্পন্ন হয়নি', 'overview-fashion'); ?></h1>
      <p><?php esc_html_e('আপনার অর্ডারটি সম্পন্ন করা যায়নি। অনুগ্রহ করে আবার চেষ্টা করুন।', 'overview-fashion'); ?></p>
      <a class="button of-thankyou-button" href="<?php echo esc_url($order->get_checkout_payment_url()); ?>"><?php esc_html_e('আবার চেষ্টা করুন', 'overview-fashion'); ?></a>
    </section>
  <?php else : ?>
    <section class="of-thankyou-card of-thankyou-success">
      <div class="of-thankyou-mark" aria-hidden="true">✓</div>
      <p class="of-thankyou-eyebrow"><?php esc_html_e('অর্ডার সফলভাবে গ্রহণ করা হয়েছে', 'overview-fashion'); ?></p>
      <h1><?php esc_html_e('অর্ডার কনফার্ম হয়েছে!', 'overview-fashion'); ?></h1>
      <p><?php esc_html_e('আপনার অর্ডারটি আমরা পেয়েছি। অর্ডারের তথ্য নিচে দেওয়া হলো।', 'overview-fashion'); ?></p>
      <div class="of-thankyou-meta">
        <div><span><?php esc_html_e('তারিখ', 'overview-fashion'); ?></span><strong><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></strong></div>
        <div><span><?php esc_html_e('মোট', 'overview-fashion'); ?></span><strong><?php echo wp_kses_post($order->get_formatted_order_total()); ?></strong></div>
        <div><span><?php esc_html_e('পেমেন্ট', 'overview-fashion'); ?></span><strong><?php echo esc_html($order->get_payment_method_title()); ?></strong></div>
      </div>
    </section>
  <?php endif; ?>

  <?php if ($thankyou_products) : ?>
    <section class="of-checkout-latest of-thankyou-offers" data-of-checkout-latest>
      <div class="of-checkout-latest-heading"><h2><?php echo esc_html(get_theme_mod('of_thankyou_offer_heading', 'এখান থেকে যেকোনো প্রোডাক্ট কিনলেই পাচ্ছেন কমপক্ষে ২০% ডিসকাউন্ট')); ?></h2><div><button type="button" data-of-latest-prev aria-label="আগের প্রোডাক্ট">‹</button><button type="button" data-of-latest-next aria-label="পরের প্রোডাক্ট">›</button></div></div>
      <div class="of-checkout-latest-track" data-of-latest-track>
        <?php foreach ($thankyou_products as $offer_product) : ?>
          <?php
            $offer_discount = 0;
            if ($offer_product->is_on_sale()) {
                $offer_regular = (float) $offer_product->get_regular_price();
                $offer_sale = (float) $offer_product->get_sale_price();
                if ($offer_regular > 0 && $offer_sale > 0 && $offer_sale < $offer_regular) {
                    $offer_discount = (int) floor((($offer_regular - $offer_sale) / $offer_regular) * 100);
                }
            }
          ?>
          <article class="of-checkout-latest-product" data-product-id="<?php echo esc_attr($offer_product->get_id()); ?>">
            <a class="of-checkout-latest-image" href="<?php echo esc_url(get_permalink($offer_product->get_id())); ?>"><?php echo wp_kses_post($offer_product->get_image('woocommerce_thumbnail', array('loading' => 'lazy'))); ?><?php if ($offer_discount > 0) : ?><span class="of-checkout-latest-discount">-<?php echo esc_html($offer_discount); ?>%</span><?php endif; ?></a>
            <a class="of-checkout-latest-name" href="<?php echo esc_url(get_permalink($offer_product->get_id())); ?>"><?php echo esc_html($offer_product->get_name()); ?></a>
            <div class="of-checkout-latest-price"><?php echo wp_kses_post($offer_product->get_price_html()); ?></div>
            <a class="of-button of-checkout-latest-add" href="<?php echo esc_url(get_permalink($offer_product->get_id())); ?>"><?php esc_html_e('এটি কিনুন', 'overview-fashion'); ?></a>
          </article>
        <?php endforeach; ?>
      </div>
      <p class="of-checkout-latest-message" role="status" aria-live="polite"></p>
    </section>
  <?php endif; ?>

  <?php overview_fashion_render_contact_cta('of-thankyou-contact-cta'); ?>

  <?php do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order_id); ?>
  <?php do_action('woocommerce_thankyou', $order_id); ?>

  <a class="of-thankyou-home" href="<?php echo esc_url(home_url('/')); ?>">
    <span aria-hidden="true">←</span> <?php esc_html_e('কেনাকাটা চালিয়ে যান', 'overview-fashion'); ?>
  </a>
</main>
