<?php
if (!defined('ABSPATH')) { exit; }
$shop_url = overview_fashion_shop_page_url();
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<div class="of-shell">
  <aside class="of-sidebar" aria-label="<?php esc_attr_e('Store navigation', 'overview-fashion'); ?>">
    <div class="of-brand"><a class="of-side-brand" href="<?php echo esc_url(home_url('/')); ?>"><?php echo esc_html(get_bloginfo('name') ?: 'Overview Fashion'); ?></a></div>
    <nav class="of-category-nav">
      <?php
        $of_active_category = '';
        if (function_exists('is_product_category') && is_product_category()) { $of_active_category = get_queried_object()->slug; }
        elseif (is_front_page()) { $of_active_category = sanitize_title(get_theme_mod('of_home_category', '')); }
        elseif (function_exists('is_product') && is_product()) { $of_product_categories = wc_get_product_cat_ids(get_the_ID()); if ($of_product_categories) { $of_product_category = get_term($of_product_categories[0], 'product_cat'); if ($of_product_category && !is_wp_error($of_product_category)) { $of_active_category = $of_product_category->slug; } } }
      ?>
      <a href="<?php echo esc_url($shop_url); ?>" class="<?php echo (function_exists('is_shop') && is_shop()) || (is_front_page() && !$of_active_category) ? 'current' : ''; ?>"><span class="of-category-dot">All</span><?php esc_html_e('All', 'overview-fashion'); ?></a>
      <?php foreach (overview_fashion_categories() as $of_cat) : ?>
        <a href="<?php echo esc_url(get_term_link($of_cat)); ?>" class="<?php echo (function_exists('is_product_category') && is_product_category($of_cat->slug)) || $of_active_category === $of_cat->slug ? 'current' : ''; ?>">
          <?php $of_thumb = get_term_meta($of_cat->term_id, 'thumbnail_id', true); if ($of_thumb) { echo wp_get_attachment_image($of_thumb, 'thumbnail', false, array('loading' => 'lazy')); } else { ?><span class="of-category-dot"><?php echo esc_html(substr($of_cat->name, 0, 1)); ?></span><?php } ?>
          <span><?php echo esc_html($of_cat->name); ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
    <p class="of-sidebar-note"><?php esc_html_e('Developed by', 'overview-fashion'); ?> <strong><?php esc_html_e('Legend Sabbir', 'overview-fashion'); ?></strong></p>
  </aside>
  <div class="of-main">
    <header class="of-header">
      <?php if (function_exists('is_product') && is_product()) : ?><a class="of-back-link" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('Back to home', 'overview-fashion'); ?>">←</a><?php endif; ?>
      <button class="of-mobile-menu" type="button" aria-label="<?php esc_attr_e('Open menu', 'overview-fashion'); ?>" data-of-menu-open>☰</button>
      <div class="of-social-links">
        <a class="of-social-icon of-whatsapp" href="<?php echo esc_url(get_theme_mod('of_whatsapp', 'https://wa.me/')); ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><span>☎</span></a>
        <a class="of-social-icon of-messenger" href="<?php echo esc_url(get_theme_mod('of_messenger', 'https://m.me/')); ?>" target="_blank" rel="noopener" aria-label="Messenger"><span>➤</span></a>
      </div>
      <div class="of-header-logo"><?php overview_fashion_logo(); ?></div>
      <div class="of-header-spacer"></div>
      <form class="of-search" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
        <button type="submit" aria-label="<?php esc_attr_e('Search', 'overview-fashion'); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="7.2"></circle><path d="m16.2 16.2 5 5"></path></svg></button>
        <input type="search" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php esc_attr_e('Search products…', 'overview-fashion'); ?>">
        <input type="hidden" name="post_type" value="product">
      </form>
      <div class="of-header-actions">
        <?php if (function_exists('wc_get_page_permalink')) : ?>
          <a class="of-cart-link" href="<?php echo esc_url(wc_get_cart_url()); ?>" aria-label="<?php esc_attr_e('Shopping cart', 'overview-fashion'); ?>"><?php esc_html_e('Cart', 'overview-fashion'); ?><span class="of-count" data-of-cart-count><?php echo esc_html(overview_fashion_cart_count()); ?></span></a>
          <button class="of-mobile-cart" type="button" data-of-cart-open aria-label="<?php esc_attr_e('Open cart', 'overview-fashion'); ?>">♧ <span class="of-count" data-of-cart-count><?php echo esc_html(overview_fashion_cart_count()); ?></span></button>
        <?php endif; ?>
      </div>
    </header>
    <div class="of-mobile-drawer" data-of-drawer>
      <div class="of-drawer-panel"><button type="button" data-of-menu-close class="of-drawer-close" aria-label="<?php esc_attr_e('Close menu', 'overview-fashion'); ?>">×</button>
      <nav class="of-category-nav"><a href="<?php echo esc_url($shop_url); ?>"><?php esc_html_e('All products', 'overview-fashion'); ?></a>
        <?php foreach (overview_fashion_categories() as $of_cat) : ?><a href="<?php echo esc_url(get_term_link($of_cat)); ?>"><?php echo esc_html($of_cat->name); ?></a><?php endforeach; ?>
      </nav></div><button class="of-drawer-backdrop" data-of-menu-close aria-label="<?php esc_attr_e('Close menu', 'overview-fashion'); ?>"></button>
    </div>
    <?php if (function_exists('WC')) : ?>
      <div class="of-cart-drawer" data-of-cart-drawer>
        <button class="of-cart-backdrop" data-of-cart-close aria-label="<?php esc_attr_e('Close cart', 'overview-fashion'); ?>"></button>
        <section class="of-cart-drawer-panel" aria-label="<?php esc_attr_e('Your cart', 'overview-fashion'); ?>"><div class="of-cart-drawer-heading"><strong><?php esc_html_e('Your cart', 'overview-fashion'); ?></strong><button type="button" data-of-cart-close aria-label="<?php esc_attr_e('Close cart', 'overview-fashion'); ?>">×</button></div><?php overview_fashion_mini_cart_contents(); ?><div class="of-cart-drawer-footer"><strong><?php esc_html_e('TOTAL:', 'overview-fashion'); ?> <?php echo wp_kses_post(WC()->cart->get_total()); ?></strong><a class="of-button" href="<?php echo esc_url(wc_get_checkout_url()); ?>"><?php esc_html_e('Checkout', 'overview-fashion'); ?> →</a></div></section>
      </div>
    <?php endif; ?>
    <main class="of-content">
