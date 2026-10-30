<?php
/** Custom WooCommerce single product content layout. */
if (!defined('ABSPATH')) { exit; }
global $product;
$product = wc_get_product(get_the_ID());
if (!$product) { return; }
$categories = wc_get_product_category_list($product->get_id(), ', ');
$related_ids = wc_get_related_products($product->get_id(), 16);
$related_products = $related_ids ? wc_get_products(['include' => $related_ids, 'limit' => 16, 'status' => 'publish']) : [];
do_action('woocommerce_before_single_product');
?>
<article id="product-<?php the_ID(); ?>" <?php wc_product_class('of-product-detail', $product); ?>>
  <div class="of-product-detail-top">
    <div class="of-detail-gallery"><?php do_action('woocommerce_before_single_product_summary'); ?></div>
    <section class="of-detail-summary summary entry-summary">
      <?php woocommerce_template_single_title(); ?>
      <?php if ($categories) : ?><div class="of-detail-category"><?php echo wp_kses_post($categories); ?></div><?php endif; ?>
      <?php woocommerce_template_single_rating(); ?>
      <?php woocommerce_template_single_price(); ?>
      <?php if ($product->is_on_sale()) : $regular = (float) $product->get_regular_price(); $current = (float) $product->get_sale_price(); if ($regular > 0 && $current > 0) : ?><span class="of-detail-discount">-<?php echo esc_html((int) floor((($regular - $current) / $regular) * 100)); ?>%</span><?php endif; endif; ?>
      <?php woocommerce_template_single_add_to_cart(); ?>
    </section>
  </div>
  <section class="of-detail-description"><h2><?php esc_html_e('Description', 'overview-fashion'); ?></h2><div><?php if ($product->get_description()) { echo wp_kses_post(wpautop($product->get_description())); } else { echo '<p>' . esc_html__('Please contact us for product size and care details.', 'overview-fashion') . '</p>'; } ?></div></section>
  <?php if ($related_products) : ?><section class="of-similar-products"><h2><?php esc_html_e('Similar Products', 'overview-fashion'); ?></h2><div class="of-products"><?php foreach ($related_products as $related_product) { overview_fashion_product_card($related_product); } ?></div></section><?php endif; ?>
  <p class="of-back-home"><a href="<?php echo esc_url(home_url('/')); ?>">&larr; <?php esc_html_e('Back to Home', 'overview-fashion'); ?></a></p>
</article>
<?php do_action('woocommerce_after_single_product'); ?>
