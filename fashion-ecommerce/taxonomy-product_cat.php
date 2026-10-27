<?php
if (!defined('ABSPATH')) { exit; }
get_header();

$of_category = get_queried_object();
$of_sort = isset($_GET['of_sort']) ? sanitize_key(wp_unslash($_GET['of_sort'])) : 'default';
$of_sort_options = array(
    'default' => __('Default', 'overview-fashion'),
    'price' => __('Price: Low to High', 'overview-fashion'),
    'price-desc' => __('Price: High to Low', 'overview-fashion'),
    'discount' => __('Biggest discount', 'overview-fashion'),
);
if (!isset($of_sort_options[$of_sort])) { $of_sort = 'default'; }

$of_catalog_args = array(
    'status' => 'publish',
    'limit' => -1,
    'category' => array($of_category->slug),
    'orderby' => 'menu_order',
    'order' => 'ASC',
);
if ($of_sort === 'price') { $of_catalog_args['orderby'] = 'price'; $of_catalog_args['order'] = 'ASC'; }
if ($of_sort === 'price-desc') { $of_catalog_args['orderby'] = 'price'; $of_catalog_args['order'] = 'DESC'; }
if ($of_sort === 'discount') { $of_catalog_args['orderby'] = 'meta_value_num'; $of_catalog_args['meta_key'] = '_sale_price'; $of_catalog_args['order'] = 'ASC'; }

$of_category_url = get_term_link($of_category);
if (is_wp_error($of_category_url)) { $of_category_url = home_url('/'); }
?>
<div class="of-catalog-toolbar">
  <h1 class="of-catalog-title"><?php echo esc_html($of_category->name); ?></h1>
  <details class="of-sort-menu">
    <summary><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h9m4 0h3M4 17h3m4 0h9M13 4v6M9 14v6"></path><circle cx="15" cy="7" r="2"></circle><circle cx="9" cy="17" r="2"></circle></svg><?php esc_html_e('Sorting', 'overview-fashion'); ?></summary>
    <div class="of-sort-options">
      <?php foreach ($of_sort_options as $of_key => $of_label) : ?>
        <a class="<?php echo $of_sort === $of_key ? 'active' : ''; ?>" href="<?php echo esc_url(add_query_arg('of_sort', $of_key, $of_category_url)); ?>"><?php echo esc_html($of_label); ?></a>
      <?php endforeach; ?>
    </div>
  </details>
</div>
<?php
$of_products = wc_get_products($of_catalog_args);
if ($of_products) {
    echo '<div class="of-products">';
    foreach ($of_products as $of_product) { overview_fashion_product_card($of_product); }
    echo '</div>';
} else {
    echo '<p class="of-no-results">' . esc_html__('No products in this category yet.', 'overview-fashion') . '</p>';
}
get_footer();
