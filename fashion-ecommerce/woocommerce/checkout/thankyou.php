<?php
/** Sajal Online order confirmation page. */
defined('ABSPATH') || exit;

$order = isset($order) && $order instanceof WC_Order ? $order : (isset($order_id) ? wc_get_order($order_id) : false);
if (!$order) {
    return;
}

$order_id = $order->get_id();
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
        <div><span><?php esc_html_e('অর্ডার নম্বর', 'overview-fashion'); ?></span><strong>#<?php echo esc_html($order->get_order_number()); ?></strong></div>
        <div><span><?php esc_html_e('তারিখ', 'overview-fashion'); ?></span><strong><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></strong></div>
        <div><span><?php esc_html_e('মোট', 'overview-fashion'); ?></span><strong><?php echo wp_kses_post($order->get_formatted_order_total()); ?></strong></div>
        <div><span><?php esc_html_e('পেমেন্ট', 'overview-fashion'); ?></span><strong><?php echo esc_html($order->get_payment_method_title()); ?></strong></div>
      </div>
    </section>
  <?php endif; ?>

  <?php do_action('woocommerce_thankyou_' . $order->get_payment_method(), $order_id); ?>
  <?php do_action('woocommerce_thankyou', $order_id); ?>

  <a class="of-thankyou-home" href="<?php echo esc_url(home_url('/')); ?>">
    <span aria-hidden="true">←</span> <?php esc_html_e('কেনাকাটা চালিয়ে যান', 'overview-fashion'); ?>
  </a>
</main>
