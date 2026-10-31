<?php
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<?php get_template_part('template-parts/hero-slider'); ?>
<?php get_template_part('template-parts/mobile-category-strip'); ?>
<div id="of-home-products">
  <?php overview_fashion_home_product_section(__('New Products', 'overview-fashion'), array('limit' => -1, 'orderby' => 'date', 'order' => 'DESC', 'meta_query' => array(array('key' => '_of_is_new_product', 'value' => 'yes'))), function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : '', 'h1'); ?>
  <?php foreach (overview_fashion_home_categories() as $of_home_category) : ?>
    <?php overview_fashion_home_product_section($of_home_category->name, array('limit' => 10, 'orderby' => 'date', 'order' => 'DESC', 'category' => array($of_home_category->slug)), get_term_link($of_home_category)); ?>
  <?php endforeach; ?>
</div>
<?php get_footer(); ?>
