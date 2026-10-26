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
    wp_enqueue_style('overview-fashion-style', get_stylesheet_uri(), array(), wp_get_theme()->get('Version'));
    wp_enqueue_script('overview-fashion-script', get_template_directory_uri() . '/assets/theme.js', array(), wp_get_theme()->get('Version'), true);
}
add_action('wp_enqueue_scripts', 'overview_fashion_assets');

function overview_fashion_logo() {
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

function overview_fashion_cart_count() {
    return function_exists('WC') && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
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

function overview_fashion_add_size_to_cart_item($cart_item_data, $product_id) {
    if (!isset($_POST['of_size'])) { return $cart_item_data; }
    $size = sanitize_text_field(wp_unslash($_POST['of_size']));
    if (in_array($size, array('M', 'L', 'XL'), true)) {
        $cart_item_data['of_size'] = $size;
        $cart_item_data['of_size_key'] = wp_hash($size . '|' . wp_rand());
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'overview_fashion_add_size_to_cart_item', 10, 2);

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
    $button_class = 'button product_type_' . sanitize_html_class($product->get_type()) . ' add_to_cart_button';
    if ($product->supports('ajax_add_to_cart')) { $button_class .= ' ajax_add_to_cart'; }
    echo '<a href="' . esc_url($product->add_to_cart_url()) . '" data-quantity="1" data-product_id="' . esc_attr($product->get_id()) . '" data-product_sku="' . esc_attr($product->get_sku()) . '" aria-label="' . esc_attr($product->add_to_cart_description()) . '" rel="nofollow" class="' . esc_attr($button_class) . '">' . esc_html($product->add_to_cart_text()) . '</a>';
    echo '</article>';
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

function overview_fashion_customize_register($wp_customize) {
    $wp_customize->add_section('overview_fashion_contact', array('title' => __('Store contact details', 'overview-fashion'), 'priority' => 35));
    $fields = array(
        'phone' => array(__('Phone number', 'overview-fashion'), '+880 1XXX-XXXXXX'),
        'email' => array(__('Contact email', 'overview-fashion'), 'info@overviewfashion.com'),
        'address' => array(__('Store location', 'overview-fashion'), 'Dhaka, Bangladesh'),
        'facebook' => array(__('Facebook URL', 'overview-fashion'), ''),
        'instagram' => array(__('Instagram URL', 'overview-fashion'), ''),
        'messenger' => array(__('Messenger URL', 'overview-fashion'), 'https://m.me/'),
        'whatsapp' => array(__('WhatsApp URL', 'overview-fashion'), 'https://wa.me/'),
    );
    foreach ($fields as $key => $data) {
        $wp_customize->add_setting('of_' . $key, array('default' => $data[1], 'sanitize_callback' => $key === 'email' ? 'sanitize_email' : ($key === 'phone' || $key === 'address' ? 'sanitize_text_field' : 'esc_url_raw')));
        $wp_customize->add_control('of_' . $key, array('label' => $data[0], 'section' => 'overview_fashion_contact', 'type' => 'text'));
    }
    $home_categories = array('' => __('All Products', 'overview-fashion'));
    foreach (overview_fashion_categories() as $category) { $home_categories[$category->slug] = $category->name; }
    $wp_customize->add_setting('of_home_category', array('default' => '', 'sanitize_callback' => 'sanitize_title'));
    $wp_customize->add_control('of_home_category', array('label' => __('Homepage product category', 'overview-fashion'), 'section' => 'static_front_page', 'type' => 'select', 'choices' => $home_categories));
    $wp_customize->add_setting('of_cover_banner', array('default' => '', 'sanitize_callback' => 'esc_url_raw'));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'of_cover_banner', array('label' => __('Homepage cover banner', 'overview-fashion'), 'section' => 'static_front_page')));
}
add_action('customize_register', 'overview_fashion_customize_register');

// Use this theme's responsive product grid while keeping WooCommerce's product data and cart behavior.
add_filter('loop_shop_columns', function () { return 4; });

function overview_fashion_cart_fragments($fragments) {
    ob_start(); ?><span class="of-count" data-of-cart-count><?php echo esc_html(overview_fashion_cart_count()); ?></span><?php
    $fragments['[data-of-cart-count]'] = ob_get_clean();
    ob_start(); overview_fashion_mini_cart_contents();
    $fragments['[data-of-cart-panel-content]'] = ob_get_clean();
    ob_start(); ?><span class="of-mobile-cart-total"><?php echo wp_kses_post(WC()->cart->get_cart_subtotal()); ?></span><?php
    $fragments['[data-of-mobile-cart-total]'] = ob_get_clean();
    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'overview_fashion_cart_fragments');

function overview_fashion_mini_cart_contents() {
    echo '<div data-of-cart-panel-content>';
    if (!function_exists('WC') || !WC()->cart || WC()->cart->is_empty()) {
        echo '<p class="of-cart-empty">' . esc_html__('Add some items', 'overview-fashion') . '</p>';
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
            echo '<li class="of-cart-item"><a class="of-cart-item-image" href="' . esc_url(get_permalink($product->get_id())) . '">' . $image . '</a><div class="of-cart-item-info">';
            echo '<a class="of-cart-item-name" href="' . esc_url(get_permalink($product->get_id())) . '">' . esc_html($product->get_name()) . '</a>';
            echo '<span class="of-size-pill">' . esc_html($size) . '</span><span class="of-cart-unit">' . wp_kses_post(wc_price((float) $product->get_price())) . ' × ' . esc_html($qty) . ' pc</span>';
            echo '<div class="of-cart-item-bottom"><strong>' . wp_kses_post(wc_price((float) $product->get_price() * $qty)) . '</strong><a class="of-remove-item" href="' . esc_url(wc_get_cart_remove_url($cart_key)) . '" aria-label="' . esc_attr__('Remove item', 'overview-fashion') . '"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M9 7V4h6v3m-9 0 1 13h10l1-13M10 11v5m4-5v5"></path></svg></a><div class="of-qty-control"><a href="' . esc_url($minus) . '" aria-label="' . esc_attr__('Decrease quantity', 'overview-fashion') . '">−</a><span>' . esc_html($qty) . '</span><a href="' . esc_url($plus) . '" aria-label="' . esc_attr__('Increase quantity', 'overview-fashion') . '">+</a></div></div>';
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
