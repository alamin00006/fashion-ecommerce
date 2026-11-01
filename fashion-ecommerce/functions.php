<?php
/** Overview Fashion theme setup and storefront helpers. */

if (!defined('ABSPATH')) { exit; }

function overview_fashion_setup() {
    load_theme_textdomain('overview-fashion', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array('height' => 120, 'width' => 320, 'flex-height' => true, 'flex-width' => true));
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    register_nav_menus(array('primary' => __('Primary navigation', 'overview-fashion')));
}
add_action('after_setup_theme', 'overview_fashion_setup');


function overview_fashion_assets() {
    wp_enqueue_style('overview-fashion-fonts', 'https://fonts.googleapis.com/css2?family=Cinzel:wght@700&family=Manrope:wght@400;500;600;700;800&display=swap', array(), null);
    $style_path = get_stylesheet_directory() . '/style.css';
    $script_path = get_template_directory() . '/assets/theme.js';
    wp_enqueue_style('overview-fashion-style', get_stylesheet_uri(), array(), file_exists($style_path) ? filemtime($style_path) : wp_get_theme()->get('Version'));
    $script_version = file_exists($script_path) ? filemtime($script_path) . '-' . substr(md5_file($script_path), 0, 8) : wp_get_theme()->get('Version');
    wp_enqueue_script('overview-fashion-script', get_template_directory_uri() . '/assets/theme.js', array(), $script_version, true);
    wp_localize_script('overview-fashion-script', 'overviewFashionCart', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'checkoutUrl' => function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : home_url('/'),
        'addNonce' => wp_create_nonce('overview_fashion_add_to_cart'),
        'searchNonce' => wp_create_nonce('overview_fashion_product_search'),
        'fbtBuyLabel' => get_theme_mod('of_fbt_buy_label', 'পণ্যগুলো কিনুন'),
        'fbtSizeRequiredLabel' => get_theme_mod('of_fbt_size_required_label', 'সাইজ নির্বাচন করুন'),
        'currencySymbol' => function_exists('get_woocommerce_currency_symbol') ? html_entity_decode(get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8') : '৳',
        'priceDecimals' => function_exists('wc_get_price_decimals') ? wc_get_price_decimals() : 0,
        'orderLabel' => __('এটি অর্ডার করুন', 'overview-fashion'),
        'addedLabel' => __('আরও কিনুন', 'overview-fashion'),
        'orderConfirmLabel' => __('অর্ডার কনফার্ম করুন', 'overview-fashion'),
    ));
}
add_action('wp_enqueue_scripts', 'overview_fashion_assets');

function overview_fashion_logo() {
    $sajal_logo_path = get_template_directory() . '/assets/sajal-logo.png';
    if (file_exists($sajal_logo_path)) {
        echo '<a class="custom-logo-link of-sajal-logo" href="' . esc_url(home_url('/')) . '" rel="home"><img src="' . esc_url(get_template_directory_uri() . '/assets/sajal-logo.png') . '" alt="' . esc_attr__('SAJAL — Explore your true style', 'overview-fashion') . '"></a>';
        return;
    }
    if (has_custom_logo()) {
        the_custom_logo();
    } else {
        echo '<a class="of-brand-name" href="' . esc_url(home_url('/')) . '">' . esc_html(get_bloginfo('name') ?: 'Overview Fashion') . '</a>';
    }
}

function overview_fashion_categories($limit = 0) {
    if (!class_exists('WooCommerce')) { return array(); }
    $args = array('taxonomy' => 'product_cat', 'hide_empty' => false, 'orderby' => 'name', 'order' => 'ASC');
    $default_category = (int) get_option('default_product_cat');
    if ($default_category) { $args['exclude'] = array($default_category); }
    if ($limit > 0) { $args['number'] = $limit; }
    $terms = get_terms($args);
    if (is_wp_error($terms)) { return array(); }
    $order = array('us-polo-cable-half', 'net-polo', 'old-many-net-polo', 'jali-polo', 'old-money-jali-polo', 'old-money-polo', 'shirt', 'us-polo-half', 'zipper-polo', 'old-maney-ziper-polo', 'rl-zipper', 'ralph-lauren-cable-knit-ziper-polo', 'sweater', 'band-color-sweter', 'cable-polo', 'old-many-cable-polo', 'shoes');
    usort($terms, function ($a, $b) use ($order) {
        $ai = array_search($a->slug, $order, true);
        $bi = array_search($b->slug, $order, true);
        $ai = $ai === false ? count($order) : $ai;
        $bi = $bi === false ? count($order) : $bi;
        return $ai === $bi ? strcasecmp($a->name, $b->name) : $ai - $bi;
    });
    return $terms;
}

function overview_fashion_home_categories() {
    $categories = overview_fashion_categories();
    $default_position = array();
    foreach ($categories as $position => $category) { $default_position[$category->term_id] = $position; }
    $categories = array_values(array_filter($categories, function ($category) {
        return get_term_meta($category->term_id, '_of_home_category_visible', true) !== 'no';
    }));
    usort($categories, function ($a, $b) use ($default_position) {
        $a_order = get_term_meta($a->term_id, '_of_home_category_order', true);
        $b_order = get_term_meta($b->term_id, '_of_home_category_order', true);
        $a_order = is_numeric($a_order) ? (int) $a_order : (($default_position[$a->term_id] ?? 0) + 1) * 10;
        $b_order = is_numeric($b_order) ? (int) $b_order : (($default_position[$b->term_id] ?? 0) + 1) * 10;
        return $a_order === $b_order ? strcasecmp($a->name, $b->name) : $a_order - $b_order;
    });
    return $categories;
}

function overview_fashion_category_home_fields($term = null) {
    $is_edit = $term instanceof WP_Term;
    $visible = !$is_edit || get_term_meta($term->term_id, '_of_home_category_visible', true) !== 'no';
    $order = $is_edit ? get_term_meta($term->term_id, '_of_home_category_order', true) : '';
    wp_nonce_field('of_home_category_settings', 'of_home_category_nonce');
    if ($is_edit) { echo '<tr class="form-field"><th scope="row"><label for="of-home-category-visible">' . esc_html__('Homepage section', 'overview-fashion') . '</label></th><td>'; }
    else { echo '<div class="form-field">'; }
    echo '<label><input id="of-home-category-visible" type="checkbox" name="of_home_category_visible" value="1" ' . checked($visible, true, false) . '> ' . esc_html__('Show this category on the homepage', 'overview-fashion') . '</label>';
    if ($is_edit) { echo '<p class="description">' . esc_html__('Uncheck to hide this category section on the homepage. It will remain available in the category menu.', 'overview-fashion') . '</p></td></tr><tr class="form-field"><th scope="row"><label for="of-home-category-order">' . esc_html__('Homepage order', 'overview-fashion') . '</label></th><td>'; }
    else { echo '</div><div class="form-field"><label for="of-home-category-order">' . esc_html__('Homepage order', 'overview-fashion') . '</label>'; }
    echo '<input id="of-home-category-order" type="number" min="0" step="1" name="of_home_category_order" value="' . esc_attr($order) . '" placeholder="' . esc_attr__('Automatic', 'overview-fashion') . '">';
    if ($is_edit) { echo '<p class="description">' . esc_html__('Use a smaller number to show this category earlier. Leave blank to use the default order.', 'overview-fashion') . '</p></td></tr>'; }
    else { echo '<p class="description">' . esc_html__('Use a smaller number to show this category earlier. Leave blank to use the default order.', 'overview-fashion') . '</p></div>'; }
}
add_action('product_cat_add_form_fields', 'overview_fashion_category_home_fields');
add_action('product_cat_edit_form_fields', 'overview_fashion_category_home_fields');

function overview_fashion_save_category_home_fields($term_id) {
    if (!current_user_can('manage_product_terms') || empty($_POST['of_home_category_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['of_home_category_nonce'])), 'of_home_category_settings')) { return; }
    update_term_meta($term_id, '_of_home_category_visible', isset($_POST['of_home_category_visible']) ? 'yes' : 'no');
    $order = isset($_POST['of_home_category_order']) ? trim((string) wp_unslash($_POST['of_home_category_order'])) : '';
    if ($order === '') { delete_term_meta($term_id, '_of_home_category_order'); }
    else { update_term_meta($term_id, '_of_home_category_order', absint($order)); }
}
add_action('created_product_cat', 'overview_fashion_save_category_home_fields');
add_action('edited_product_cat', 'overview_fashion_save_category_home_fields');

function overview_fashion_cart_count() {
    return function_exists('WC') && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
}

function overview_fashion_cart_drawer_counts() {
    $cart = function_exists('WC') && WC()->cart ? WC()->cart : null;
    $items = $cart ? count($cart->get_cart()) : 0;
    $quantity = $cart ? $cart->get_cart_contents_count() : 0;
    echo '<span class="of-cart-drawer-counts" data-of-cart-drawer-counts>' . esc_html__('Items:', 'overview-fashion') . ' <strong>' . esc_html($items) . '</strong> &nbsp; ' . esc_html__('Quantity:', 'overview-fashion') . ' <strong>' . esc_html($quantity) . '</strong></span>';
}

function overview_fashion_product_size_field() {
    global $product;
    if (!$product || !$product->is_type('simple')) { return; }
    $selected = isset($_POST['of_size']) ? sanitize_text_field(wp_unslash($_POST['of_size'])) : 'M';
    if (!in_array($selected, array('M', 'L', 'XL'), true)) { $selected = 'M'; }
    echo '<fieldset class="of-size-picker"><legend>' . esc_html__('SIZE:', 'overview-fashion') . '</legend><div>';
    foreach (array('M', 'L', 'XL') as $size) {
        echo '<label class="' . ($selected === $size ? 'selected' : '') . '"><input type="radio" name="of_size" value="' . esc_attr($size) . '" ' . checked($selected, $size, false) . '><span>' . esc_html($size) . '</span></label>';
    }
    echo '</div></fieldset>';
}
add_action('woocommerce_before_add_to_cart_button', 'overview_fashion_product_size_field', 5);
function overview_fashion_mobile_order_note() {
    echo '<p class="of-mobile-order-note">' . esc_html__('অর্ডার কনফার্ম করতে নিচের “অর্ডার কনফার্ম করুন” বাটনে ক্লিক করুন।', 'overview-fashion') . '</p>';
}
add_action('woocommerce_after_add_to_cart_button', 'overview_fashion_mobile_order_note');
add_action('woocommerce_before_add_to_cart_button', function () {
    global $product;
    overview_fashion_single_product_cart_controls($product);
}, 15);

function overview_fashion_cart_item_size($cart_item) {
    $size = !empty($cart_item['of_size']) ? (string) $cart_item['of_size'] : '';
    foreach (($cart_item['variation'] ?? array()) as $attribute => $value) {
        if (strpos($attribute, 'size') !== false && $value !== '') { return strtoupper((string) $value); }
    }
    return $size ?: 'M';
}

function overview_fashion_single_add_to_cart_text($label, $product = null) {
    $selected_size = isset($_REQUEST['of_size']) ? sanitize_text_field(wp_unslash($_REQUEST['of_size'])) : 'M';
    if (!$product instanceof WC_Product) { return __('এটি অর্ডার করুন', 'overview-fashion'); }
    if (function_exists('WC') && WC()->cart) {
        foreach (WC()->cart->get_cart() as $cart_item) {
            if ((int) $cart_item['product_id'] === (int) $product->get_id() && overview_fashion_cart_item_size($cart_item) === $selected_size) {
                return __('আরও কিনুন', 'overview-fashion');
            }
        }
    }
    return __('এটি অর্ডার করুন', 'overview-fashion');
}
add_filter('woocommerce_product_single_add_to_cart_text', 'overview_fashion_single_add_to_cart_text', 10, 2);

function overview_fashion_add_new_product_admin_field() {
    echo '<div class="options_group">';
    woocommerce_wp_checkbox(array(
        'id' => '_of_is_new_product',
        'label' => __('Show in New Products section', 'overview-fashion'),
        'description' => __('Display this product in the homepage New Products section.', 'overview-fashion'),
    ));
    echo '</div>';
}
add_action('woocommerce_product_options_general_product_data', 'overview_fashion_add_new_product_admin_field');

function overview_fashion_auto_related_product_ids($product_id, $limit = 20) {
    $product = wc_get_product($product_id);
    if (!$product) { return array(); }
    $terms = wp_get_object_terms($product_id, 'product_cat', array('fields' => 'ids'));
    if (!is_wp_error($terms) && $terms) {
        $matches = get_posts(array('post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => $limit, 'post__not_in' => array($product_id), 'fields' => 'ids', 'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true, 'tax_query' => array(array('taxonomy' => 'product_cat', 'field' => 'term_id', 'terms' => $terms))));
        if ($matches) { return array_map('absint', $matches); }
    }
    $tags = wp_get_object_terms($product_id, 'product_tag', array('fields' => 'ids'));
    if (!is_wp_error($tags) && $tags) {
        $matches = get_posts(array('post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => $limit, 'post__not_in' => array($product_id), 'fields' => 'ids', 'orderby' => 'date', 'order' => 'DESC', 'no_found_rows' => true, 'tax_query' => array(array('taxonomy' => 'product_tag', 'field' => 'term_id', 'terms' => $tags))));
        if ($matches) { return array_map('absint', $matches); }
    }
    return array();
}

function overview_fashion_product_cart_size_totals($product_id) {
    $totals = array('M' => 0.0, 'L' => 0.0, 'XL' => 0.0);
    if (!function_exists('WC') || !WC()->cart) { return $totals; }
    foreach (WC()->cart->get_cart() as $cart_item) {
        if ((int) ($cart_item['product_id'] ?? 0) !== (int) $product_id) { continue; }
        $size = overview_fashion_cart_item_size($cart_item);
        if (isset($totals[$size])) { $totals[$size] += (float) wc_get_price_to_display($cart_item['data']) * (int) $cart_item['quantity']; }
    }
    return $totals;
}

function overview_fashion_render_frequently_bought_together($product) {
    if (!$product instanceof WC_Product) { return; }
    $extra_ids = overview_fashion_auto_related_product_ids($product->get_id(), 20);
    $extras = array();
    foreach ($extra_ids as $extra_id) {
        $extra = wc_get_product($extra_id);
        if ($extra && $extra->is_purchasable() && $extra->is_type('simple')) { $extras[] = $extra; }
    }
    if (!$extras) { return; }
    $items = $extras;
    $totals = overview_fashion_product_cart_size_totals($product->get_id());
    $current_size = isset($_REQUEST['of_size']) ? sanitize_text_field(wp_unslash($_REQUEST['of_size'])) : 'M';
    if (!isset($totals[$current_size])) { $current_size = 'M'; }
    echo '<section class="of-fbt" data-of-fbt data-current-product="' . esc_attr($product->get_id()) . '"><h2>' . esc_html(get_theme_mod('of_fbt_heading', 'আপনি কি এখান থেকে কিছু নিতে চান?')) . '</h2><div class="of-fbt-items">';
    foreach ($items as $item) {
        $image_id = $item->get_image_id();
        $image = $image_id ? wp_get_attachment_image($image_id, 'woocommerce_thumbnail', false, array('loading' => 'lazy')) : wc_placeholder_img('woocommerce_thumbnail');
        $price_content = wp_kses_post($item->get_price_html());
        if ($item->is_on_sale()) {
            $regular = (float) $item->get_regular_price();
            $current = (float) $item->get_sale_price();
            if ($regular > 0 && $current > 0 && $current < $regular) {
                $discount = (int) floor((($regular - $current) / $regular) * 100);
                $regular_display = wc_get_price_to_display($item, array('price' => $regular));
                $current_display = wc_get_price_to_display($item, array('price' => $current));
                $price_content = '<span class="of-fbt-current-price">' . wc_price($current_display) . '</span><span class="of-fbt-old-price"><small class="of-fbt-discount">-' . esc_html($discount) . '%</small><del>' . wc_price($regular_display) . '</del></span>';
            }
        }
        echo '<label class="of-fbt-item"><input type="checkbox" class="of-fbt-check" value="' . esc_attr($item->get_id()) . '" data-price="' . esc_attr(wc_get_price_to_display($item)) . '"><span class="of-fbt-thumb">' . $image . '</span><span class="of-fbt-info"><strong>' . esc_html($item->get_name()) . '</strong><select aria-label="' . esc_attr(sprintf(__('Size for %s', 'overview-fashion'), $item->get_name())) . '"><option value="" selected disabled>' . esc_html(get_theme_mod('of_fbt_size_placeholder', 'Choose a size')) . '</option><option value="M">M</option><option value="L">L</option><option value="XL">XL</option></select></span><span class="of-fbt-price">' . $price_content . '</span></label>';
    }
    echo '</div><div class="of-fbt-footer"><strong>' . esc_html(get_theme_mod('of_fbt_total_label', 'Total:')) . ' <span data-of-fbt-total data-of-fbt-current-total data-base-m="' . esc_attr($totals['M']) . '" data-base-l="' . esc_attr($totals['L']) . '" data-base-xl="' . esc_attr($totals['XL']) . '">' . wp_kses_post(wc_price($totals[$current_size])) . '</span></strong><button class="of-button" type="button" data-of-fbt-add>' . esc_html(get_theme_mod('of_fbt_buy_label', 'পণ্যগুলো কিনুন')) . '</button></div><p class="of-fbt-error" role="alert" hidden></p></section>';
}

function overview_fashion_save_new_product_admin_field($product) {
    $is_new_product = isset($_POST['_of_is_new_product']) && wc_clean(wp_unslash($_POST['_of_is_new_product'])) === '1';
    $product->update_meta_data('_of_is_new_product', $is_new_product ? 'yes' : 'no');
}
add_action('woocommerce_admin_process_product_object', 'overview_fashion_save_new_product_admin_field');

function overview_fashion_add_size_to_cart_item($cart_item_data, $product_id) {
    if (!isset($_POST['of_size'])) { return $cart_item_data; }
    $size = sanitize_text_field(wp_unslash($_POST['of_size']));
    if (in_array($size, array('M', 'L', 'XL'), true)) {
        $cart_item_data['of_size'] = $size;
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'overview_fashion_add_size_to_cart_item', 10, 2);

/** Merge cart rows created by older versions, which assigned a random key to every size. */
function overview_fashion_merge_duplicate_cart_lines($cart) {
    if (!$cart instanceof WC_Cart) { return; }
    $groups = array();
    foreach ($cart->get_cart() as $cart_key => $item) {
        $identity = $item;
        foreach (array('key', 'data', 'quantity', 'line_tax_data', 'line_subtotal', 'line_subtotal_tax', 'line_total', 'line_tax', 'of_size_key') as $field) {
            unset($identity[$field]);
        }
        $fingerprint = md5(wp_json_encode($identity));
        if (!isset($groups[$fingerprint])) { $groups[$fingerprint] = array('keys' => array(), 'quantity' => 0); }
        $groups[$fingerprint]['keys'][] = $cart_key;
        $groups[$fingerprint]['quantity'] += (int) $item['quantity'];
    }
    $changed = false;
    foreach ($groups as $group) {
        if (count($group['keys']) < 2) { continue; }
        $keep_key = array_shift($group['keys']);
        $cart->set_quantity($keep_key, $group['quantity'], false);
        foreach ($group['keys'] as $duplicate_key) { $cart->remove_cart_item($duplicate_key); }
        $changed = true;
    }
    if ($changed) { $cart->calculate_totals(); }
}
add_action('woocommerce_cart_loaded_from_session', 'overview_fashion_merge_duplicate_cart_lines', 20, 1);

function overview_fashion_show_cart_size($item_data, $cart_item) {
    if (!empty($cart_item['of_size'])) { $item_data[] = array('key' => __('Size', 'overview-fashion'), 'value' => $cart_item['of_size']); }
    return $item_data;
}
add_filter('woocommerce_get_item_data', 'overview_fashion_show_cart_size', 10, 2);

function overview_fashion_save_order_item_size($item, $cart_item_key, $values) {
    if (!empty($values['of_size'])) { $item->add_meta_data(__('Size', 'overview-fashion'), $values['of_size'], true); }
}
add_action('woocommerce_checkout_create_order_line_item', 'overview_fashion_save_order_item_size', 10, 3);

function overview_fashion_product_card($product) {
    if (!$product instanceof WC_Product) { return; }
    $image_id = $product->get_image_id();
    $image = $image_id ? wp_get_attachment_image($image_id, 'woocommerce_thumbnail', false, array('loading' => 'lazy')) : wc_placeholder_img('woocommerce_thumbnail');
    $sale = '';
    if ($product->is_on_sale()) {
        $regular = (float) ($product->is_type('variable') ? $product->get_variation_regular_price('min', true) : $product->get_regular_price());
        $current = (float) ($product->is_type('variable') ? $product->get_variation_sale_price('min', true) : $product->get_sale_price());
        $discount = $regular > 0 && $current > 0 ? (int) floor((($regular - $current) / $regular) * 100) : 0;
        $sale = '<span class="of-sale">-' . esc_html($discount) . '%</span>';
    }
    echo '<article class="of-product">' . $sale;
    echo '<a class="of-product-image" href="' . esc_url(get_permalink($product->get_id())) . '">' . $image . '</a>';
    echo '<h3><a href="' . esc_url(get_permalink($product->get_id())) . '">' . esc_html($product->get_name()) . '</a></h3>';
    echo '<p class="of-price">' . wp_kses_post($product->get_price_html()) . '</p>';
    // This card button opens the product page; it must not trigger WooCommerce's AJAX cart handler.
    $button_class = 'button of-order-product-button';
    echo '<a href="' . esc_url(get_permalink($product->get_id())) . '" aria-label="' . esc_attr(sprintf(__('%s অর্ডার করুন', 'overview-fashion'), $product->get_name())) . '" class="' . esc_attr($button_class) . '">' . esc_html__('এটি অর্ডার করুন', 'overview-fashion') . '</a>';
    echo '</article>';
}

function overview_fashion_single_product_discount_badge($product) {
    if (!$product instanceof WC_Product || !$product->is_on_sale()) { return; }
    $regular = (float) ($product->is_type('variable') ? $product->get_variation_regular_price('min', true) : $product->get_regular_price());
    $current = (float) ($product->is_type('variable') ? $product->get_variation_sale_price('min', true) : $product->get_sale_price());
    if ($regular <= 0 || $current <= 0 || $current >= $regular) { return; }
    $discount = (int) floor((($regular - $current) / $regular) * 100);
    echo '<span class="of-detail-sale-badge">-' . esc_html($discount) . '%</span>';
}

function overview_fashion_render_product_recommendations($product) {
    if (!$product instanceof WC_Product) { return; }
    $product_id = $product->get_id();
    $recommendations = wc_get_products(array('status' => 'publish', 'limit' => 8, 'orderby' => 'date', 'order' => 'DESC', 'exclude' => array($product_id)));
    if (!$recommendations) { return; }
    echo '<section class="of-similar-products of-recommendation-section"><h2>' . esc_html__('Latest Products', 'overview-fashion') . '<span class="of-similar-ornament" aria-hidden="true"><svg viewBox="0 0 120 18"><path d="M3 9h34c5 0 7-2 10-5 2-2 5-2 7 0l6 5 6-5c2-2 5-2 7 0 3 3 5 5 10 5h34M3 9h34c5 0 7 2 10 5 2 2 5 2 7 0l6-5 6 5c2 2 5 2 7 0 3-3 5-5 10-5h34"/><path d="M53 9c0-3 3-6 7-6s7 3 7 6-3 6-7 6-7-3-7-6Zm3 0c2-2 4-2 6 0-2 2-4 2-6 0Z"/><circle cx="60" cy="9" r="1"/></svg></span></h2><div class="of-products">';
    foreach ($recommendations as $recommended_product) { overview_fashion_product_card($recommended_product); }
    echo '</div></section>';
}

function overview_fashion_products($args = array()) {
    if (!class_exists('WooCommerce')) {
        echo '<p>' . esc_html__('Install and activate WooCommerce to display products here.', 'overview-fashion') . '</p>';
        return;
    }
    $defaults = array('status' => 'publish', 'limit' => 8, 'orderby' => 'date', 'order' => 'DESC', 'return' => 'objects');
    $products = wc_get_products(wp_parse_args($args, $defaults));
    if (!$products) {
        echo '<p>' . esc_html__('No products have been added yet.', 'overview-fashion') . '</p>';
        return;
    }
    echo '<div class="of-products">';
    foreach ($products as $product) { overview_fashion_product_card($product); }
    echo '</div>';
}

function overview_fashion_home_product_section($title, $args, $url = '', $heading_tag = 'h2') {
    if (!in_array($heading_tag, array('h1', 'h2'), true)) { $heading_tag = 'h2'; }
    $products = class_exists('WooCommerce') ? wc_get_products(wp_parse_args($args, array('status' => 'publish', 'limit' => -1, 'orderby' => 'date', 'order' => 'DESC', 'return' => 'objects'))) : array();
    if (!$products) { return; }
    echo '<section class="of-home-product-section"><div class="of-home-section-heading"><' . $heading_tag . '>' . esc_html($title) . '</' . $heading_tag . '>';
    if ($url) { echo '<a href="' . esc_url($url) . '">' . esc_html__('View all', 'overview-fashion') . ' <span aria-hidden="true">→</span></a>'; }
    echo '</div>';
    if ($products) {
        echo '<div class="of-products">';
        foreach ($products as $section_product) { overview_fashion_product_card($section_product); }
        echo '</div>';
    }
    echo '</section>';
}

function overview_fashion_single_product_rating($product) {
    if (!$product instanceof WC_Product) { return; }
    if ($product->get_rating_count() > 0) {
        woocommerce_template_single_rating();
        return;
    }
    echo '<div class="of-product-rating-stars" aria-label="' . esc_attr__('Rated 5 out of 5', 'overview-fashion') . '"><span aria-hidden="true">★★★★★</span></div>';
}

function overview_fashion_single_product_cart_controls($product) {
    if (!$product instanceof WC_Product || !function_exists('WC') || !WC()->cart) { return; }
    $selected_size = isset($_REQUEST['of_size']) ? sanitize_text_field(wp_unslash($_REQUEST['of_size'])) : 'M';
    $cart_item = null;
    $cart_key = '';
    foreach (WC()->cart->get_cart() as $key => $item) {
        if ((int) $item['product_id'] === (int) $product->get_id() && overview_fashion_cart_item_size($item) === $selected_size) { $cart_item = $item; $cart_key = $key; break; }
    }
    if (!$cart_item) { echo '<div data-of-single-product-cart-controls></div>'; return; }
    $quantity = (int) $cart_item['quantity'];
    $nonce = wp_create_nonce('overview_fashion_cart_quantity');
    $ajax_url = admin_url('admin-ajax.php');
    echo '<div class="of-single-product-cart-controls" data-of-single-product-cart-controls><div class="of-single-product-cart-row"><strong>' . wp_kses_post(wc_price((float) $cart_item['data']->get_price() * $quantity)) . '</strong>';
    echo '<a class="of-remove-item" href="' . esc_url(overview_fashion_cart_quantity_url($cart_key, 0)) . '" data-of-cart-quantity data-cart-key="' . esc_attr($cart_key) . '" data-quantity="0" data-nonce="' . esc_attr($nonce) . '" data-ajax-url="' . esc_url($ajax_url) . '" aria-label="' . esc_attr__('Remove item', 'overview-fashion') . '"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m-9 0 1 13h10l1-13M10 11v5m4-5v5"></path></svg></a>';
    echo '<div class="of-qty-control"><a href="' . esc_url(overview_fashion_cart_quantity_url($cart_key, max(0, $quantity - 1))) . '" data-of-cart-quantity data-cart-key="' . esc_attr($cart_key) . '" data-quantity="' . esc_attr(max(0, $quantity - 1)) . '" data-nonce="' . esc_attr($nonce) . '" data-ajax-url="' . esc_url($ajax_url) . '" aria-label="' . esc_attr__('Decrease quantity', 'overview-fashion') . '">−</a><span>' . esc_html($quantity) . '</span><a href="' . esc_url(overview_fashion_cart_quantity_url($cart_key, $quantity + 1)) . '" data-of-cart-quantity data-cart-key="' . esc_attr($cart_key) . '" data-quantity="' . esc_attr($quantity + 1) . '" data-nonce="' . esc_attr($nonce) . '" data-ajax-url="' . esc_url($ajax_url) . '" aria-label="' . esc_attr__('Increase quantity', 'overview-fashion') . '">+</a></div></div>';
    echo '</div>';
}

function overview_fashion_product_category_link($term) {
    if (is_wp_error($term)) { return; }
    $url = get_term_link($term);
    if (is_wp_error($url)) { return; }
    $thumb_id = get_term_meta($term->term_id, 'thumbnail_id', true);
    echo '<a href="' . esc_url($url) . '">';
    if ($thumb_id) { echo wp_get_attachment_image($thumb_id, 'woocommerce_thumbnail', false, array('loading' => 'lazy')); }
    else { echo '<span class="of-category-placeholder">' . esc_html(substr($term->name, 0, 1)) . '</span>'; }
    echo '<span>' . esc_html($term->name) . '</span></a>';
}

function overview_fashion_shop_page_url() {
    if (function_exists('wc_get_page_permalink')) { return wc_get_page_permalink('shop'); }
    return home_url('/');
}

function overview_fashion_messenger_url() {
    $url = get_theme_mod('of_messenger', '');
    if (!$url || untrailingslashit($url) === 'https://m.me') { return 'https://www.facebook.com/Sajalonline/'; }
    return $url;
}

function overview_fashion_render_free_delivery_notice() {
    $enabled = (bool) get_theme_mod('of_free_delivery_notice_enabled', true);
    $message = get_theme_mod('of_free_delivery_notice_text', 'একাধিক পণ্য অর্ডার করলে ডেলিভারি চার্জ সম্পূর্ণ ফ্রি।');
    if (!$enabled || trim((string) $message) === '') { return; }
    echo '<p class="of-free-delivery-note"><span aria-hidden="true">🚚</span> ' . esc_html($message) . '</p>';
}

function overview_fashion_render_contact_cta($context_class = '') {
    $class = 'of-call-to-order' . ($context_class ? ' ' . sanitize_html_class($context_class) : '');
    echo '<div class="' . esc_attr($class) . '"><span class="of-call-icon" aria-hidden="true">☎</span><strong class="of-call-number">01969905799</strong><a class="of-call-messenger" href="' . esc_url(overview_fashion_messenger_url()) . '" target="_blank" rel="noopener" aria-label="Messenger"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2C6.4 2 2 6.1 2 11.3c0 2.9 1.4 5.5 3.7 7.2V22l3.4-1.9c.9.2 1.9.4 2.9.4 5.6 0 10-4.1 10-9.2S17.6 2 12 2Zm1 12.4-2.6-2.8-5 2.8 5.5-5.8 2.7 2.8 4.9-2.8-5.5 5.8Z"/></svg></a></div>';
}

function overview_fashion_customize_register($wp_customize) {
    $wp_customize->add_section('overview_fashion_colors', array('title' => __('Theme colors', 'overview-fashion'), 'priority' => 30));
    $wp_customize->add_setting('of_theme_color', array('default' => '#176b57', 'sanitize_callback' => 'sanitize_hex_color', 'transport' => 'refresh'));
    $wp_customize->add_control(new WP_Customize_Color_Control($wp_customize, 'of_theme_color', array('label' => __('Primary theme color', 'overview-fashion'), 'section' => 'overview_fashion_colors')));
    $wp_customize->add_section('overview_fashion_contact', array('title' => __('Store contact details', 'overview-fashion'), 'priority' => 35));
    $fields = array(
        'phone' => array(__('Phone number', 'overview-fashion'), '+880 1XXX-XXXXXX'),
        'email' => array(__('Contact email', 'overview-fashion'), 'info@overviewfashion.com'),
        'address' => array(__('Store location', 'overview-fashion'), 'Dhaka, Bangladesh'),
        'facebook' => array(__('Facebook URL', 'overview-fashion'), ''),
        'instagram' => array(__('Instagram URL', 'overview-fashion'), ''),
        'messenger' => array(__('Messenger URL', 'overview-fashion'), 'https://www.facebook.com/Sajalonline/'),
        'whatsapp' => array(__('WhatsApp URL', 'overview-fashion'), 'https://wa.me/'),
    );
    foreach ($fields as $key => $data) {
        $wp_customize->add_setting('of_' . $key, array('default' => $data[1], 'sanitize_callback' => $key === 'email' ? 'sanitize_email' : ($key === 'phone' || $key === 'address' ? 'sanitize_text_field' : 'esc_url_raw')));
        $wp_customize->add_control('of_' . $key, array('label' => $data[0], 'section' => 'overview_fashion_contact', 'type' => 'text'));
    }
    $wp_customize->add_section('overview_fashion_fbt_text', array('title' => __('Frequently bought together text', 'overview-fashion'), 'priority' => 36));
    $fbt_texts = array(
        'of_fbt_heading' => array(__('Section heading', 'overview-fashion'), 'আপনি কি এখান থেকে কিছু নিতে চান?'),
        'of_fbt_total_label' => array(__('Total label', 'overview-fashion'), 'Total:'),
        'of_fbt_buy_label' => array(__('Buy button text', 'overview-fashion'), 'পণ্যগুলো কিনুন'),
        'of_fbt_size_placeholder' => array(__('Size placeholder', 'overview-fashion'), 'Choose a size'),
        'of_fbt_size_required_label' => array(__('Select size button text', 'overview-fashion'), 'সাইজ নির্বাচন করুন'),
    );
    foreach ($fbt_texts as $setting => $data) {
        $wp_customize->add_setting($setting, array('default' => $data[1], 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'refresh'));
        $wp_customize->add_control($setting, array('label' => $data[0], 'section' => 'overview_fashion_fbt_text', 'type' => 'text'));
    }
    $wp_customize->add_section('overview_fashion_delivery', array('title' => __('Delivery offer', 'overview-fashion'), 'priority' => 37));
    $wp_customize->add_setting('of_multi_item_free_shipping', array('default' => true, 'sanitize_callback' => 'rest_sanitize_boolean', 'transport' => 'refresh'));
    $wp_customize->add_control('of_multi_item_free_shipping', array('label' => __('Free delivery when cart has 2 or more items', 'overview-fashion'), 'section' => 'overview_fashion_delivery', 'type' => 'checkbox'));
    $wp_customize->add_setting('of_free_shipping_min_items', array('default' => 2, 'sanitize_callback' => 'absint', 'transport' => 'refresh'));
    $wp_customize->add_control('of_free_shipping_min_items', array('label' => __('Minimum item quantity for free delivery', 'overview-fashion'), 'section' => 'overview_fashion_delivery', 'type' => 'number', 'input_attrs' => array('min' => 2, 'step' => 1)));
    $wp_customize->add_setting('of_free_delivery_notice_enabled', array('default' => true, 'sanitize_callback' => 'rest_sanitize_boolean', 'transport' => 'refresh'));
    $wp_customize->add_control('of_free_delivery_notice_enabled', array('label' => __('Show free delivery notice', 'overview-fashion'), 'section' => 'overview_fashion_delivery', 'type' => 'checkbox'));
    $wp_customize->add_setting('of_free_delivery_notice_text', array('default' => 'একাধিক পণ্য অর্ডার করলে ডেলিভারি চার্জ সম্পূর্ণ ফ্রি।', 'sanitize_callback' => 'sanitize_text_field', 'transport' => 'refresh'));
    $wp_customize->add_control('of_free_delivery_notice_text', array('label' => __('Free delivery notice text', 'overview-fashion'), 'section' => 'overview_fashion_delivery', 'type' => 'text'));
    $wp_customize->add_setting('of_cover_banner', array('default' => '', 'sanitize_callback' => 'esc_url_raw'));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'of_cover_banner', array('label' => __('Homepage banner image', 'overview-fashion'), 'section' => 'static_front_page')));
}
add_action('customize_register', 'overview_fashion_customize_register');

function overview_fashion_mix_hex_colors($base, $mix, $amount) {
    $base = ltrim((string) $base, '#');
    $mix = ltrim((string) $mix, '#');
    if (strlen($base) === 3) { $base = $base[0] . $base[0] . $base[1] . $base[1] . $base[2] . $base[2]; }
    if (strlen($mix) === 3) { $mix = $mix[0] . $mix[0] . $mix[1] . $mix[1] . $mix[2] . $mix[2]; }
    if (strlen($base) !== 6 || strlen($mix) !== 6) { return '#ffffff'; }
    $channels = array();
    for ($i = 0; $i < 3; $i++) {
        $from = hexdec(substr($base, $i * 2, 2));
        $to = hexdec(substr($mix, $i * 2, 2));
        $channels[] = (int) round($from * (1 - $amount) + $to * $amount);
    }
    return sprintf('#%02x%02x%02x', $channels[0], $channels[1], $channels[2]);
}

function overview_fashion_dynamic_theme_colors() {
    $primary = sanitize_hex_color(get_theme_mod('of_theme_color', '#176b57')) ?: '#176b57';
    $dark = overview_fashion_mix_hex_colors($primary, '#000000', 0.2);
    $soft = overview_fashion_mix_hex_colors($primary, '#ffffff', 0.90);
    $background = overview_fashion_mix_hex_colors($primary, '#ffffff', 0.965);
    $checkout_background = overview_fashion_mix_hex_colors($primary, '#ffffff', 0.94);
    $border = overview_fashion_mix_hex_colors($primary, '#ffffff', 0.48);
    $rgb_hex = ltrim($primary, '#');
    if (strlen($rgb_hex) === 3) { $rgb_hex = $rgb_hex[0] . $rgb_hex[0] . $rgb_hex[1] . $rgb_hex[1] . $rgb_hex[2] . $rgb_hex[2]; }
    $shadow = 'rgba(' . hexdec(substr($rgb_hex, 0, 2)) . ',' . hexdec(substr($rgb_hex, 2, 2)) . ',' . hexdec(substr($rgb_hex, 4, 2)) . ',.3)';
    $css = ':root{--of-primary:' . $primary . ';--of-primary-dark:' . $dark . ';--of-primary-border:' . $border . ';--of-theme-soft:' . $soft . ';--of-bg:' . $background . ';--of-checkout-bg:' . $checkout_background . ';--of-primary-shadow:' . $shadow . '}';
    wp_add_inline_style('overview-fashion-style', $css);
}
add_action('wp_enqueue_scripts', 'overview_fashion_dynamic_theme_colors', 20);

/** Skip the standalone cart screen and take shoppers straight to checkout. */
function overview_fashion_skip_cart_page() {
    if (!function_exists('is_cart') || !is_cart()) { return; }
    $destination = function_exists('WC') && WC()->cart && !WC()->cart->is_empty() ? wc_get_checkout_url() : home_url('/');
    wp_safe_redirect($destination);
    exit;
}
add_action('template_redirect', 'overview_fashion_skip_cart_page', 20);

/** Finish single-product form submissions with a redirect, so refreshing cannot re-add the product. */
function overview_fashion_redirect_after_single_product_add() {
    if (is_admin() || !is_product() || strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST' || empty($_REQUEST['add-to-cart'])) { return; }
    $product_id = absint(wp_unslash($_REQUEST['add-to-cart']));
    if (!$product_id) { return; }
    wp_safe_redirect(add_query_arg('added-to-cart', $product_id, get_permalink($product_id)), 303);
    exit;
}
add_action('template_redirect', 'overview_fashion_redirect_after_single_product_add', 30);

function overview_fashion_ajax_add_product_to_cart() {
    if (!function_exists('WC') || !WC()->cart || !check_ajax_referer('overview_fashion_add_to_cart', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Please refresh the page and try again.', 'overview-fashion')), 403);
    }
    $product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
    $quantity = isset($_POST['quantity']) ? max(1, absint(wp_unslash($_POST['quantity']))) : 1;
    $product = wc_get_product($product_id);
    if (!$product || !$product->is_purchasable()) {
        wp_send_json_error(array('message' => __('This product cannot be added to the cart.', 'overview-fashion')), 400);
    }
    $variation_id = isset($_POST['variation_id']) ? absint(wp_unslash($_POST['variation_id'])) : 0;
    $variation = array();
    foreach ($_POST as $key => $value) {
        if (strpos((string) $key, 'attribute_') === 0) { $variation[sanitize_text_field($key)] = sanitize_text_field(wp_unslash($value)); }
    }
    // WooCommerce applies `woocommerce_add_cart_item_data` inside add_to_cart().
    // Passing pre-filtered data here would run extension callbacks twice.
    $added = WC()->cart->add_to_cart($product_id, $quantity, $variation_id, $variation, array());
    if (!$added) { wp_send_json_error(array('message' => __('Could not add this product to the cart.', 'overview-fashion')), 400); }
    WC()->cart->calculate_totals();
    $fragments = apply_filters('woocommerce_add_to_cart_fragments', array());
    wp_send_json_success(array('fragments' => $fragments, 'cart_hash' => WC()->cart->get_cart_hash()));
}
add_action('wp_ajax_overview_fashion_add_product_to_cart', 'overview_fashion_ajax_add_product_to_cart');
add_action('wp_ajax_nopriv_overview_fashion_add_product_to_cart', 'overview_fashion_ajax_add_product_to_cart');

function overview_fashion_ajax_add_fbt_products() {
    if (!function_exists('WC') || !WC()->cart || !check_ajax_referer('overview_fashion_add_to_cart', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Please refresh the page and try again.', 'overview-fashion')), 403);
    }
    $current_id = isset($_POST['current_product']) ? absint(wp_unslash($_POST['current_product'])) : 0;
    $current = wc_get_product($current_id);
    $allowed_ids = $current ? overview_fashion_auto_related_product_ids($current_id, 20) : array();
    $items = isset($_POST['items']) ? json_decode(wp_unslash($_POST['items']), true) : array();
    if (!$current || !is_array($items) || !$items) { wp_send_json_error(array('message' => __('Choose at least one product.', 'overview-fashion')), 400); }
    $original_size = $_POST['of_size'] ?? null;
    $current_size = isset($_POST['current_size']) ? sanitize_text_field(wp_unslash($_POST['current_size'])) : 'M';
    if (!in_array($current_size, array('M', 'L', 'XL'), true)) { $current_size = 'M'; }
    $added_any = false;
    foreach ($items as $entry) {
        $id = isset($entry['id']) ? absint($entry['id']) : 0;
        $size = isset($entry['size']) ? sanitize_text_field($entry['size']) : 'M';
        $item_product = wc_get_product($id);
        if (!$item_product || !in_array($id, $allowed_ids, true) || !$item_product->is_type('simple') || !$item_product->is_purchasable() || !in_array($size, array('M', 'L', 'XL'), true)) { continue; }
        $_POST['of_size'] = $size;
        $added_any = WC()->cart->add_to_cart($id, 1, 0, array(), array('of_size' => $size)) || $added_any;
    }
    if (!$added_any) { wp_send_json_error(array('message' => __('Could not add the selected products.', 'overview-fashion')), 400); }
    WC()->cart->calculate_totals();
    $_POST['of_size'] = $current_size;
    $_POST['product_id'] = $current_id;
    $fragments = apply_filters('woocommerce_add_to_cart_fragments', array());
    if ($original_size === null) { unset($_POST['of_size']); } else { $_POST['of_size'] = $original_size; }
    unset($_POST['product_id']);
    wp_send_json_success(array('fragments' => $fragments, 'cart_hash' => WC()->cart->get_cart_hash()));
}
add_action('wp_ajax_overview_fashion_add_fbt_products', 'overview_fashion_ajax_add_fbt_products');
add_action('wp_ajax_nopriv_overview_fashion_add_fbt_products', 'overview_fashion_ajax_add_fbt_products');

function overview_fashion_ajax_refresh_product_cart_controls() {
    if (!check_ajax_referer('overview_fashion_add_to_cart', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Please refresh the page and try again.', 'overview-fashion')), 403);
    }
    $product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
    $product = wc_get_product($product_id);
    if (!$product) { wp_send_json_error(array('message' => __('Product not found.', 'overview-fashion')), 404); }
    ob_start();
    overview_fashion_single_product_cart_controls($product);
    wp_send_json_success(array('html' => ob_get_clean()));
}
add_action('wp_ajax_overview_fashion_refresh_product_cart_controls', 'overview_fashion_ajax_refresh_product_cart_controls');
add_action('wp_ajax_nopriv_overview_fashion_refresh_product_cart_controls', 'overview_fashion_ajax_refresh_product_cart_controls');

function overview_fashion_ajax_product_search() {
    if (!check_ajax_referer('overview_fashion_product_search', 'nonce', false)) {
        wp_send_json_error(array('message' => __('Please refresh the page and try again.', 'overview-fashion')), 403);
    }
    if (!function_exists('wc_get_products')) {
        wp_send_json_success(array('items' => array()));
    }
    $query = isset($_POST['query']) ? sanitize_text_field(wp_unslash($_POST['query'])) : '';
    if (strlen($query) < 2) {
        wp_send_json_success(array('items' => array()));
    }
    $products = wc_get_products(array('status' => 'publish', 'limit' => 6, 'orderby' => 'relevance', 'order' => 'DESC', 's' => $query));
    $items = array();
    foreach ($products as $product) {
        if (!$product instanceof WC_Product) { continue; }
        $image_id = $product->get_image_id();
        $image = $image_id ? wp_get_attachment_image_src($image_id, 'woocommerce_thumbnail') : false;
        $terms = get_the_terms($product->get_id(), 'product_cat');
        $items[] = array(
            'url' => esc_url_raw(get_permalink($product->get_id())),
            'image' => $image ? esc_url_raw($image[0]) : esc_url_raw(wc_placeholder_img_src('woocommerce_thumbnail')),
            'name' => $product->get_name(),
            'price' => wp_kses_post($product->get_price_html()),
            'category' => !is_wp_error($terms) && !empty($terms) ? $terms[0]->name : '',
        );
    }
    wp_send_json_success(array('items' => $items));
}
add_action('wp_ajax_overview_fashion_product_search', 'overview_fashion_ajax_product_search');
add_action('wp_ajax_nopriv_overview_fashion_product_search', 'overview_fashion_ajax_product_search');

// Use this theme's responsive product grid while keeping WooCommerce's product data and cart behavior.
add_filter('loop_shop_columns', function () { return 4; });

function overview_fashion_remove_woocommerce_sidebar() {
    remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
    if (function_exists('is_product') && is_product()) {
        remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);
        remove_action('woocommerce_before_single_product', 'woocommerce_output_all_notices', 10);
    }
}
add_action('wp', 'overview_fashion_remove_woocommerce_sidebar');

function overview_fashion_cart_fragments($fragments) {
    ob_start(); ?><span class="of-count" data-of-cart-count><?php echo esc_html(overview_fashion_cart_count()); ?></span><?php
    $fragments['[data-of-cart-count]'] = ob_get_clean();
    ob_start(); overview_fashion_mini_cart_contents();
    $fragments['[data-of-cart-panel-content]'] = ob_get_clean();
    ob_start(); ?><span class="of-mobile-cart-total"><?php echo wp_kses_post(WC()->cart->get_cart_subtotal()); ?></span><?php
    $fragments['[data-of-mobile-cart-total]'] = ob_get_clean();
    ob_start(); ?><strong class="of-cart-total-value"><?php echo wp_kses_post(WC()->cart->get_cart_subtotal()); ?></strong><?php
    $fragments['.of-cart-total-value'] = ob_get_clean();
    ob_start(); overview_fashion_cart_footer(false);
    $fragments['[data-of-cart-sidebar-footer]'] = ob_get_clean();
    ob_start(); overview_fashion_cart_footer(true);
    $fragments['[data-of-cart-drawer-footer]'] = ob_get_clean();
    ob_start(); overview_fashion_cart_drawer_counts();
    $fragments['[data-of-cart-drawer-counts]'] = ob_get_clean();
    $detail_product_id = isset($_POST['product_id']) ? absint(wp_unslash($_POST['product_id'])) : 0;
    if (!$detail_product_id) { $detail_product_id = url_to_postid(wp_get_referer()); }
    if ($detail_product_id && get_post_type($detail_product_id) === 'product') {
        $detail_product = wc_get_product($detail_product_id);
        ob_start(); overview_fashion_single_product_cart_controls($detail_product);
        $fragments['[data-of-single-product-cart-controls]'] = ob_get_clean();
        $size_totals = overview_fashion_product_cart_size_totals($detail_product_id);
        $selected_size = isset($_POST['of_size']) ? sanitize_text_field(wp_unslash($_POST['of_size'])) : 'M';
        if (!isset($size_totals[$selected_size])) { $selected_size = 'M'; }
        ob_start();
        echo '<span data-of-fbt-total data-of-fbt-current-total data-base-m="' . esc_attr($size_totals['M']) . '" data-base-l="' . esc_attr($size_totals['L']) . '" data-base-xl="' . esc_attr($size_totals['XL']) . '">' . wp_kses_post(wc_price($size_totals[$selected_size])) . '</span>';
        $fragments['[data-of-fbt-current-total]'] = ob_get_clean();
    }
    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'overview_fashion_cart_fragments');

function overview_fashion_cart_footer($drawer = false) {
    $empty = !function_exists('WC') || !WC()->cart || WC()->cart->is_empty();
    $type = $drawer ? 'drawer' : 'sidebar';
    $class = $drawer ? 'of-cart-drawer-footer' : 'of-cart-sidebar-footer';
    echo '<div class="' . esc_attr($class) . ($empty ? ' is-empty' : '') . '" data-of-cart-' . esc_attr($type) . '-footer>';
    if (!$empty) {
        if ($drawer) {
            echo '<a class="of-button" href="' . esc_url(wc_get_checkout_url()) . '"><span class="of-drawer-checkout-total">' . wp_kses_post(WC()->cart->get_cart_subtotal()) . '</span><span class="of-drawer-checkout-label">' . esc_html__('অর্ডার কনফার্ম করুন', 'overview-fashion') . '</span><span aria-hidden="true">→</span></a>';
        } else {
            echo '<div><span>' . esc_html__('TOTAL:', 'overview-fashion') . '</span><strong class="of-cart-total-value">' . wp_kses_post(WC()->cart->get_cart_subtotal()) . '</strong></div><a class="of-button" href="' . esc_url(wc_get_checkout_url()) . '">' . esc_html__('অর্ডার কনফার্ম করুন', 'overview-fashion') . ' <span>→</span></a>';
        }
    }
    echo '</div>';
}

function overview_fashion_mini_cart_contents() {
    $is_empty = !function_exists('WC') || !WC()->cart || WC()->cart->is_empty();
    echo '<div class="of-cart-panel-content' . ($is_empty ? ' is-empty' : '') . '" data-of-cart-panel-content>';
    if ($is_empty) {
        echo '<div class="of-cart-empty-state"><span class="of-cart-empty-icon"><svg viewBox="0 0 64 64" aria-hidden="true"><path d="M7 9a2.5 2.5 0 0 0 0 5h5l6.8 29.4a7.5 7.5 0 0 0 7.3 5.8h26a2.5 2.5 0 0 0 0-5h-26a2.5 2.5 0 0 1-2.4-1.9l-.4-1.8h28.1a5.5 5.5 0 0 0 5.3-4l5.1-17.8a3.2 3.2 0 0 0-3.1-4.1H16.3l-1.1-4.8A2.5 2.5 0 0 0 12.8 9H7Zm18.7 45a5 5 0 1 0 0 10 5 5 0 0 0 0-10Zm23 0a5 5 0 1 0 0 10 5 5 0 0 0 0-10Z"/></svg></span><span>' . esc_html__('Add some items', 'overview-fashion') . '</span></div>';
    } else {
        echo '<ul class="of-cart-list">';
        foreach (WC()->cart->get_cart() as $cart_key => $cart_item) {
            $product = $cart_item['data'];
            if (!$product || !$product->exists()) { continue; }
            $image = $product->get_image('woocommerce_thumbnail', array('loading' => 'lazy'));
            $size = !empty($cart_item['of_size']) ? (string) $cart_item['of_size'] : '';
            foreach (($cart_item['variation'] ?? array()) as $attribute => $value) {
                if (strpos($attribute, 'size') !== false && $value !== '') { $size = strtoupper((string) $value); break; }
            }
            if (!$size) { $size = 'M'; }
            $qty = (int) $cart_item['quantity'];
            $minus = overview_fashion_cart_quantity_url($cart_key, max(0, $qty - 1));
            $plus = overview_fashion_cart_quantity_url($cart_key, $qty + 1);
            echo '<li class="of-cart-item" data-product-id="' . esc_attr($cart_item['product_id']) . '" data-product-size="' . esc_attr($size) . '"><a class="of-cart-item-image" href="' . esc_url(get_permalink($product->get_id())) . '">' . $image . '</a><div class="of-cart-item-info">';
            echo '<a class="of-cart-item-name" href="' . esc_url(get_permalink($product->get_id())) . '">' . esc_html($product->get_name()) . '</a>';
            echo '<span class="of-size-pill">' . esc_html($size) . '</span><span class="of-cart-unit">' . wp_kses_post(wc_price((float) $product->get_price())) . ' × ' . esc_html($qty) . ' pc</span>';
            $of_qty_nonce = wp_create_nonce('overview_fashion_cart_quantity');
            $of_ajax_url = admin_url('admin-ajax.php');
            echo '<div class="of-cart-item-bottom"><strong>' . wp_kses_post(wc_price((float) $product->get_price() * $qty)) . '</strong><a class="of-remove-item" href="' . esc_url(wc_get_cart_remove_url($cart_key)) . '" aria-label="' . esc_attr__('Remove item', 'overview-fashion') . '"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m-9 0 1 13h10l1-13M10 11v5m4-5v5"></path></svg></a><div class="of-qty-control"><a href="' . esc_url($minus) . '" data-of-cart-quantity data-cart-key="' . esc_attr($cart_key) . '" data-quantity="' . esc_attr(max(0, $qty - 1)) . '" data-nonce="' . esc_attr($of_qty_nonce) . '" data-ajax-url="' . esc_url($of_ajax_url) . '" aria-label="' . esc_attr__('Decrease quantity', 'overview-fashion') . '">−</a><span>' . esc_html($qty) . '</span><a href="' . esc_url($plus) . '" data-of-cart-quantity data-cart-key="' . esc_attr($cart_key) . '" data-quantity="' . esc_attr($qty + 1) . '" data-nonce="' . esc_attr($of_qty_nonce) . '" data-ajax-url="' . esc_url($of_ajax_url) . '" aria-label="' . esc_attr__('Increase quantity', 'overview-fashion') . '">+</a></div></div>';
            echo '</div></li>';
        }
        echo '</ul>';
    }
    echo '</div>';
}

function overview_fashion_cart_quantity_url($cart_key, $quantity) {
    return wp_nonce_url(add_query_arg(array('of_cart_action' => 'quantity', 'of_cart_key' => $cart_key, 'of_cart_qty' => absint($quantity)), home_url('/')), 'of_cart_qty_' . $cart_key);
}

function overview_fashion_handle_cart_quantity() {
    if (!isset($_GET['of_cart_action'], $_GET['of_cart_key'], $_GET['of_cart_qty']) || $_GET['of_cart_action'] !== 'quantity' || !function_exists('WC') || !WC()->cart) { return; }
    $key = sanitize_text_field(wp_unslash($_GET['of_cart_key']));
    $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
    if (!$nonce || !wp_verify_nonce($nonce, 'of_cart_qty_' . $key)) { return; }
    if (isset(WC()->cart->get_cart()[$key])) {
        WC()->cart->set_quantity($key, absint(wp_unslash($_GET['of_cart_qty'])), true);
    }
    wp_safe_redirect(remove_query_arg(array('of_cart_action', 'of_cart_key', 'of_cart_qty', '_wpnonce')));
    exit;
}
add_action('template_redirect', 'overview_fashion_handle_cart_quantity', 1);

function overview_fashion_ajax_cart_quantity() {
    check_ajax_referer('overview_fashion_cart_quantity', 'nonce');
    if (!function_exists('WC') || !WC()->cart) { wp_send_json_error(array('message' => 'Cart is unavailable.'), 400); }
    $key = isset($_POST['cart_key']) ? sanitize_text_field(wp_unslash($_POST['cart_key'])) : '';
    $quantity = isset($_POST['quantity']) ? absint(wp_unslash($_POST['quantity'])) : 0;
    if (!$key || !isset(WC()->cart->get_cart()[$key])) { wp_send_json_error(array('message' => 'Cart item not found.'), 404); }
    WC()->cart->set_quantity($key, $quantity, true);
    $fragments = apply_filters('woocommerce_add_to_cart_fragments', array());
    ob_start(); ?><strong class="of-cart-total-value"><?php echo wp_kses_post(WC()->cart->get_cart_subtotal()); ?></strong><?php
    $fragments['.of-cart-total-value'] = ob_get_clean();
    wp_send_json_success(array('fragments' => $fragments));
}
add_action('wp_ajax_overview_fashion_cart_quantity', 'overview_fashion_ajax_cart_quantity');
add_action('wp_ajax_nopriv_overview_fashion_cart_quantity', 'overview_fashion_ajax_cart_quantity');

function overview_fashion_checkout_privacy_notice($text, $type = '') {
    return $type === 'checkout' ? '' : $text;
}
add_filter('woocommerce_get_privacy_policy_text', 'overview_fashion_checkout_privacy_notice', 10, 2);

function overview_fashion_cod_description($description, $gateway_id) {
    if ($gateway_id === 'cod') {
        return '১০০% নিশ্চিত হয়ে অর্ডার করুন। আপনার একটি ফেইক অর্ডারে হালাল ব্যবসায় লোকসান হয়।';
    }
    return $description;
}
add_filter('woocommerce_gateway_description', 'overview_fashion_cod_description', 10, 2);

/** Simplify checkout for Bangladesh orders and keep location fields available to WooCommerce. */
function overview_fashion_checkout_fields($fields) {
    $fields['billing']['billing_first_name']['label'] = __('নাম', 'overview-fashion');
    $fields['billing']['billing_first_name']['placeholder'] = __('আপনার নাম লিখুন', 'overview-fashion');
    $fields['billing']['billing_first_name']['priority'] = 10;
    $fields['billing']['billing_first_name']['required'] = true;
    $fields['billing']['billing_first_name']['autocomplete'] = 'name';
    $fields['billing']['billing_phone']['label'] = __('মোবাইল নম্বর', 'overview-fashion');
    $fields['billing']['billing_phone']['placeholder'] = __('01XXXXXXXXX', 'overview-fashion');
    $fields['billing']['billing_phone']['priority'] = 20;
    $fields['billing']['billing_phone']['required'] = true;
    $fields['billing']['billing_phone']['custom_attributes'] = array('maxlength' => '11', 'inputmode' => 'numeric', 'pattern' => '01[3-9][0-9]{8}', 'autocomplete' => 'tel');
    $fields['billing']['billing_address_1']['label'] = __('আপনার সম্পূর্ণ ঠিকানা', 'overview-fashion');
    $fields['billing']['billing_address_1']['placeholder'] = __('বাসা, সড়ক, এলাকা ও জেলা লিখুন', 'overview-fashion');
    $fields['billing']['billing_address_1']['priority'] = 30;
    $fields['billing']['billing_address_1']['required'] = true;
    $fields['billing']['billing_address_1']['custom_attributes'] = array('maxlength' => '255');
    foreach (array('billing_last_name', 'billing_company', 'billing_address_2', 'billing_city', 'billing_state', 'billing_postcode', 'billing_email') as $field) {
        unset($fields['billing'][$field]);
    }
    if (isset($fields['billing']['billing_country'])) {
        $fields['billing']['billing_country']['type'] = 'hidden';
        $fields['billing']['billing_country']['default'] = 'BD';
        $fields['billing']['billing_country']['required'] = false;
    }
    return $fields;
}
add_filter('woocommerce_checkout_fields', 'overview_fashion_checkout_fields', 20);

function overview_fashion_delivery_charge($cart) {
    if (is_admin() && !defined('DOING_AJAX')) { return; }
    if (!$cart || $cart->is_empty() || !function_exists('WC') || !WC()->session) { return; }
    $checkout_ajax = isset($_GET['wc-ajax']) && sanitize_key(wp_unslash($_GET['wc-ajax'])) === 'update_order_review';
    if ((!function_exists('is_checkout') || !is_checkout()) && !$checkout_ajax) { return; }
    $area = WC()->session->get('of_delivery_area', 'dhaka');
    if (isset($_POST['post_data'])) {
        parse_str(wp_unslash($_POST['post_data']), $posted);
        if (isset($posted['of_delivery_area'])) { $area = sanitize_key($posted['of_delivery_area']); }
    } elseif (isset($_POST['of_delivery_area'])) {
        $area = sanitize_key(wp_unslash($_POST['of_delivery_area']));
    }
    if (!in_array($area, array('dhaka', 'outside'), true)) { $area = 'dhaka'; }
    WC()->session->set('of_delivery_area', $area);
    $free_shipping_for_multiple = (bool) get_theme_mod('of_multi_item_free_shipping', true);
    $free_shipping_min_items = max(2, absint(get_theme_mod('of_free_shipping_min_items', 2)));
    if ($free_shipping_for_multiple && (int) $cart->get_cart_contents_count() >= $free_shipping_min_items) { return; }
    $cart->add_fee(__('Delivery Charge', 'overview-fashion'), $area === 'outside' ? 130 : 80, false);
}
add_action('woocommerce_cart_calculate_fees', 'overview_fashion_delivery_charge', 20);

/** Delivery is charged by the checkout's Dhaka/outside-Dhaka selector, not WooCommerce shipping zones. */
add_filter('woocommerce_cart_needs_shipping', '__return_false', 100);

/** Ignore the obsolete flat-rate left by the original local preview setup. */
function overview_fashion_remove_preview_shipping_rate($rates, $package) {
    foreach ($rates as $rate_id => $rate) {
        if ($rate->method_id === 'flat_rate' && strtolower((string) $rate->label) === 'bangladesh delivery') {
            unset($rates[$rate_id]);
        }
    }
    return $rates;
}
add_filter('woocommerce_package_rates', 'overview_fashion_remove_preview_shipping_rate', 100, 2);

function overview_fashion_save_delivery_area($order, $data) {
    $area = isset($_POST['of_delivery_area']) ? sanitize_key(wp_unslash($_POST['of_delivery_area'])) : (function_exists('WC') && WC()->session ? WC()->session->get('of_delivery_area', 'dhaka') : 'dhaka');
    if (!in_array($area, array('dhaka', 'outside'), true)) { $area = 'dhaka'; }
    $order->update_meta_data('_of_delivery_area', $area === 'outside' ? __('Outside Dhaka', 'overview-fashion') : __('Dhaka', 'overview-fashion'));
}
add_action('woocommerce_checkout_create_order', 'overview_fashion_save_delivery_area', 20, 2);

function overview_fashion_validate_delivery_area($data, $errors) {
    $phone = isset($data['billing_phone']) ? preg_replace('/\D+/', '', (string) $data['billing_phone']) : '';
    if (!preg_match('/^01[3-9][0-9]{8}$/', $phone)) {
        $errors->add('billing_phone', __('Enter a valid 11-digit Bangladesh mobile number.', 'overview-fashion'));
    }
    $area = isset($_POST['of_delivery_area']) ? sanitize_key(wp_unslash($_POST['of_delivery_area'])) : '';
    if (!in_array($area, array('dhaka', 'outside'), true)) {
        $errors->add('of_delivery_area', __('Please choose a delivery area.', 'overview-fashion'));
    }
}
add_action('woocommerce_after_checkout_validation', 'overview_fashion_validate_delivery_area', 10, 2);

/** Render the classic WooCommerce checkout so the theme's custom checkout template is used. */
function overview_fashion_classic_checkout_content($content) {
    if (!function_exists('is_checkout') || !is_checkout() || is_wc_endpoint_url() || !is_main_query() || !in_the_loop()) { return $content; }
    if (has_shortcode($content, 'woocommerce_checkout')) { return $content; }
    return do_shortcode('[woocommerce_checkout]');
}
add_filter('the_content', 'overview_fashion_classic_checkout_content', 9);

function overview_fashion_checkout_price_decimals($decimals) {
    $checkout_ajax = isset($_GET['wc-ajax']) && sanitize_key(wp_unslash($_GET['wc-ajax'])) === 'update_order_review';
    return (function_exists('is_checkout') && is_checkout()) || $checkout_ajax ? 0 : $decimals;
}
add_filter('wc_get_price_decimals', 'overview_fashion_checkout_price_decimals');

/** This storefront does not use coupon codes. */
add_filter('woocommerce_coupons_enabled', '__return_false');
add_filter('woocommerce_order_button_text', function () { return __('অর্ডার কনফার্ম করুন', 'overview-fashion'); });
