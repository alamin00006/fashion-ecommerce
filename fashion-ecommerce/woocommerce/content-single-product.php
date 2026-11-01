<?php
/** Custom WooCommerce single product content layout. */
if (!defined('ABSPATH')) { exit; }
global $product;
$product = wc_get_product(get_the_ID());
if (!$product) { return; }
$categories = wc_get_product_category_list($product->get_id(), ', ');
do_action('woocommerce_before_single_product');
?>
<article id="product-<?php the_ID(); ?>" <?php wc_product_class('of-product-detail', $product); ?>>
  <div class="of-product-detail-top">
    <div class="of-detail-gallery"><?php do_action('woocommerce_before_single_product_summary'); ?></div>
    <section class="of-detail-summary summary entry-summary">
      <?php woocommerce_template_single_title(); ?>
      <div class="of-detail-meta"><?php if ($categories) : ?><div class="of-detail-category"><?php echo wp_kses_post($categories); ?></div><?php endif; ?><?php overview_fashion_single_product_rating($product); ?></div>
      <div class="of-detail-price-row"><?php woocommerce_template_single_price(); ?><?php overview_fashion_single_product_discount_badge($product); ?></div>
      <?php woocommerce_template_single_add_to_cart(); ?>
      <?php overview_fashion_render_frequently_bought_together($product); ?>
    <ul class="of-product-promises" aria-label="<?php esc_attr_e('Shopping benefits', 'overview-fashion'); ?>"><li><span aria-hidden="true">✓</span><?php esc_html_e('Cash on delivery', 'overview-fashion'); ?></li><li><span aria-hidden="true">↗</span><?php esc_html_e('Delivery across Bangladesh', 'overview-fashion'); ?></li><li><span aria-hidden="true">↺</span><?php esc_html_e('Easy exchange', 'overview-fashion'); ?></li></ul>
    </section>
  </div>
  <?php overview_fashion_render_free_delivery_notice(); ?>
  <section class="of-detail-description"><h2><?php esc_html_e('Description', 'overview-fashion'); ?></h2><div><?php if ($product->get_description()) { echo wp_kses_post(wpautop($product->get_description())); } else { echo '<p>' . esc_html__('Please contact us for product size and care details.', 'overview-fashion') . '</p>'; } ?></div><div class="of-call-to-order"><span class="of-call-icon" aria-hidden="true">☎</span><strong class="of-call-number">01969905799</strong><a class="of-call-messenger" href="<?php echo esc_url(overview_fashion_messenger_url()); ?>" target="_blank" rel="noopener" aria-label="Messenger"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2C6.4 2 2 6.1 2 11.3c0 2.9 1.4 5.5 3.7 7.2V22l3.4-1.9c.9.2 1.9.4 2.9.4 5.6 0 10-4.1 10-9.2S17.6 2 12 2Zm1 12.4-2.6-2.8-5 2.8 5.5-5.8 2.7 2.8 4.9-2.8-5.5 5.8Z"/></svg></a></div></section>
  <?php overview_fashion_render_product_recommendations($product); ?>
  <p class="of-back-home"><a href="<?php echo esc_url(home_url('/')); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12H3m0 0 7-7m-7 7 7 7"/></svg><?php esc_html_e('Back to Home', 'overview-fashion'); ?></a></p>
</article>
<?php do_action('woocommerce_after_single_product'); ?>
