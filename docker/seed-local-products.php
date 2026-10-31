<?php
// Local preview data only. This file is mounted into the one-shot setup container.
require '/var/www/html/wp-load.php';

if (!class_exists('WooCommerce')) {
    echo "WooCommerce is not installed; sample products were skipped.\n";
    return;
}

require_once ABSPATH . 'wp-admin/includes/image.php';

$preview_categories = array(
    'us-polo-cable-half' => 'U.s Polo cable Half',
    'net-polo' => 'Old Many Net Polo',
    'jali-polo' => 'Old Money Jali Polo',
    'old-money-polo' => 'old money polo',
    'shirt' => 'shirt',
    'us-polo-half' => 'U.S. POLO HALF',
    'zipper-polo' => 'Old maney ziper polo',
    'rl-zipper' => 'Ralph lauren cable knit ziper polo',
    'sweater' => 'Band color sweter',
    'cable-polo' => 'Old Many Cable Polo',
    'shoes' => 'SHOES',
);
$category_ids = array();
foreach ($preview_categories as $slug => $name) {
    $term = term_exists($slug, 'product_cat');
    if (!$term) {
        $term = wp_insert_term($name, 'product_cat', array('slug' => $slug));
    } else {
        $term_id = (int) (is_array($term) ? $term['term_id'] : $term);
        $existing_term = get_term($term_id, 'product_cat');
        if ($existing_term && !is_wp_error($existing_term) && $existing_term->name !== $name) {
            $term = wp_update_term($term_id, 'product_cat', array('name' => $name));
        }
    }
    if (!is_wp_error($term)) {
        $category_ids[$slug] = (int) (is_array($term) ? $term['term_id'] : $term);
    }
}

$preview_products = array(
    array('sku' => 'OF-LOCAL-POLO', 'name' => 'Old Money Knit Polo', 'slug' => 'local-old-money-knit-polo', 'category' => 'old-money-polo', 'price' => '850', 'regular' => '999', 'image' => 'products/old-money-polo.jpg', 'description' => 'Local preview product. Premium knit polo in a timeless style.'),
    array('sku' => 'OF-LOCAL-ZIP', 'name' => 'Ralph lauren zipper polo Navy', 'slug' => 'local-cable-knit-zipper-polo', 'category' => 'rl-zipper', 'price' => '1190', 'regular' => '1990', 'image' => 'products/ralph-navy.png', 'description' => 'Local preview product. Soft cable knit with a neat quarter zip.'),
    array('sku' => 'OF-LOCAL-ZIP-BLACK', 'name' => 'Ralph Lauren zipper polo black', 'slug' => 'local-zipper-polo-black', 'category' => 'rl-zipper', 'price' => '1190', 'regular' => '1990', 'image' => 'products/ralph-black.png', 'description' => 'Local preview product. Cable knit quarter zip in black.'),
    array('sku' => 'OF-LOCAL-ZIP-CREAM', 'name' => 'Ralph Lauren zipper polo cream', 'slug' => 'local-zipper-polo-cream', 'category' => 'rl-zipper', 'price' => '1190', 'regular' => '1990', 'image' => 'products/ralph-cream.png', 'description' => 'Local preview product. Cable knit quarter zip in cream.'),
    array('sku' => 'OF-LOCAL-ZIP-MERON', 'name' => 'Ralph Lauren zipper polo Meron', 'slug' => 'local-zipper-polo-meron', 'category' => 'rl-zipper', 'price' => '1190', 'regular' => '1990', 'image' => 'products/ralph-red.png', 'description' => 'Local preview product. Cable knit quarter zip in maroon.'),
    array('sku' => 'OF-LOCAL-ZIP-COFFEE', 'name' => 'Ralph Lauren zipper polo Coffee', 'slug' => 'local-zipper-polo-coffee', 'category' => 'rl-zipper', 'price' => '1190', 'regular' => '1990', 'image' => 'products/ralph-ash.png', 'description' => 'Local preview product. Cable knit quarter zip in coffee.'),
    array('sku' => 'OF-LOCAL-ZIP-ASH', 'name' => 'Ralph lauren zipper polo ash', 'slug' => 'local-zipper-polo-ash', 'category' => 'rl-zipper', 'price' => '1190', 'regular' => '1990', 'image' => 'products/ralph-offwhite.png', 'description' => 'Local preview product. Cable knit quarter zip in ash.'),
    array('sku' => 'OF-LOCAL-SHOES', 'name' => 'Suede Everyday Shoes', 'slug' => 'local-suede-everyday-shoes', 'category' => 'shoes', 'price' => '1490', 'regular' => '1790', 'image' => 'products/shoes-product.jpg', 'description' => 'Local preview product. Lightweight suede shoes for everyday wear.'),
);

$theme_assets = WP_CONTENT_DIR . '/themes/overview-fashion/assets/';
foreach ($preview_products as $position => $row) {
    $existing_product_id = wc_get_product_id_by_sku($row['sku']);
    $image_id = 0;
    $image_path = $theme_assets . $row['image'];
    if (file_exists($image_path)) {
        $image_title = sanitize_file_name(pathinfo($image_path, PATHINFO_FILENAME));
        $existing_image = get_posts(array('post_type' => 'attachment', 'post_status' => 'inherit', 'title' => $image_title, 'posts_per_page' => 1, 'fields' => 'ids'));
        if ($existing_image) {
            $image_id = (int) $existing_image[0];
        } else {
            $upload = wp_upload_bits(basename($image_path), null, file_get_contents($image_path));
            if (empty($upload['error'])) {
                $mime = wp_check_filetype($upload['file']);
                $attachment_id = wp_insert_attachment(array(
                    'post_mime_type' => $mime['type'],
                    'post_title' => $image_title,
                    'post_status' => 'inherit',
                ), $upload['file']);
                if (!is_wp_error($attachment_id)) {
                    $metadata = wp_generate_attachment_metadata($attachment_id, $upload['file']);
                    wp_update_attachment_metadata($attachment_id, $metadata);
                    $image_id = (int) $attachment_id;
                }
            }
        }
    }

    if ($existing_product_id) {
        $existing_product = wc_get_product($existing_product_id);
        if ($image_id) { set_post_thumbnail($existing_product_id, $image_id); }
        if ($existing_product) { $existing_product->set_menu_order($position); $existing_product->save(); }
        continue;
    }

    $product = new WC_Product_Simple();
    $product->set_name($row['name']);
    $product->set_slug($row['slug']);
    $product->set_sku($row['sku']);
    $product->set_status('publish');
    $product->set_catalog_visibility('visible');
    $product->set_regular_price($row['regular']);
    $product->set_sale_price($row['price']);
    $product->set_description($row['description']);
    $product->set_menu_order($position);
    if (!empty($category_ids[$row['category']])) {
        $product->set_category_ids(array($category_ids[$row['category']]));
    }
    if ($image_id) {
        $product->set_image_id($image_id);
    }
    $product->save();
}

$cod_settings = get_option('woocommerce_cod_settings', array());
$cod_settings['enabled'] = 'yes';
$cod_settings['title'] = 'Cash on delivery';
$cod_settings['description'] = 'Pay cash when your order arrives.';
update_option('woocommerce_cod_settings', $cod_settings);

update_option('woocommerce_default_country', 'BD-13');
update_option('woocommerce_store_city', 'Dhaka');
update_option('woocommerce_store_postcode', '1000');
$preview_zone = null;
foreach (WC_Shipping_Zones::get_zones() as $zone_data) {
    if ($zone_data['zone_name'] === 'Bangladesh local preview') {
        $preview_zone = new WC_Shipping_Zone((int) $zone_data['zone_id']);
        break;
    }
}
if (!$preview_zone) {
    $preview_zone = new WC_Shipping_Zone();
    $preview_zone->set_zone_name('Bangladesh local preview');
    $preview_zone->set_zone_order(0);
    $preview_zone->add_location('BD', 'country');
    $preview_zone->save();
}
echo "Added local sample products and enabled cash on delivery.\n";
