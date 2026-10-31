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
      <?php woocommerce_template_single_price(); ?>
      <?php woocommerce_template_single_add_to_cart(); ?>
      <ul class="of-product-promises" aria-label="<?php esc_attr_e('Shopping benefits', 'overview-fashion'); ?>"><li><span aria-hidden="true">✓</span><?php esc_html_e('Cash on delivery', 'overview-fashion'); ?></li><li><span aria-hidden="true">↗</span><?php esc_html_e('Delivery across Bangladesh', 'overview-fashion'); ?></li><li><span aria-hidden="true">↺</span><?php esc_html_e('Easy exchange', 'overview-fashion'); ?></li></ul>
    </section>
  </div>
  <section class="of-detail-description"><h2><?php esc_html_e('Description', 'overview-fashion'); ?></h2><div><?php if ($product->get_description()) { echo wp_kses_post(wpautop($product->get_description())); } else { echo '<p>' . esc_html__('Please contact us for product size and care details.', 'overview-fashion') . '</p>'; } ?></div></section>
  <?php overview_fashion_render_product_recommendations($product); ?>
  <p class="of-back-home"><a href="<?php echo esc_url(home_url('/')); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12H3m0 0 7-7m-7 7 7 7"/></svg><?php esc_html_e('Back to Home', 'overview-fashion'); ?></a></p>
</article>
<?php do_action('woocommerce_after_single_product'); ?>
