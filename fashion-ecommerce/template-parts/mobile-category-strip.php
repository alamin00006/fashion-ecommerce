<?php
if (!defined('ABSPATH')) { exit; }
if (!function_exists('overview_fashion_categories')) { return; }
$of_home_url = home_url('/');
$of_active_category = function_exists('is_product_category') && is_product_category() ? get_queried_object()->slug : '';
?>
<section class="of-category-strip" aria-label="<?php esc_attr_e('Shop by category', 'overview-fashion'); ?>">
  <nav>
    <a class="of-category-chip<?php echo !$of_active_category ? ' current' : ''; ?>" href="<?php echo esc_url($of_home_url); ?>">
      <span class="of-category-chip-image of-category-chip-all"><svg viewBox="0 0 40 40" aria-hidden="true"><path d="M10 9a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v22a2 2 0 0 1-2 2h-4a2 2 0 0 1-2-2V9Zm13 1.2a2 2 0 0 1 2.8 0l3 3a2 2 0 0 1 0 2.8L19 26l-5.8 1.2L14.4 21l8.6-10.8ZM23 25l4-4 5 5a2 2 0 0 1-1.4 3.4H18l5-4.4Z"/></svg></span><span><?php esc_html_e('All', 'overview-fashion'); ?></span>
    </a>
    <?php foreach (overview_fashion_categories() as $of_cat) :
      $of_thumb = get_term_meta($of_cat->term_id, 'thumbnail_id', true);
      if (!$of_thumb && function_exists('wc_get_products')) {
          $of_cat_products = wc_get_products(array('status' => 'publish', 'limit' => 1, 'category' => array($of_cat->slug)));
          if ($of_cat_products) { $of_thumb = $of_cat_products[0]->get_image_id(); }
      }
      $of_term_url = get_term_link($of_cat);
      if (is_wp_error($of_term_url)) { continue; }
    ?>
      <a class="of-category-chip<?php echo $of_active_category === $of_cat->slug ? ' current' : ''; ?>" href="<?php echo esc_url($of_term_url); ?>">
        <span class="of-category-chip-image">
          <?php if ($of_thumb) { echo wp_get_attachment_image($of_thumb, 'thumbnail', false, array('loading' => 'lazy')); } else { echo esc_html(substr($of_cat->name, 0, 1)); } ?>
        </span>
        <span><?php echo esc_html($of_cat->name); ?></span>
      </a>
    <?php endforeach; ?>
  </nav>
</section>
