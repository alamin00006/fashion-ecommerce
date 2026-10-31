<?php
if (!defined('ABSPATH')) { exit; }
$home_url = home_url('/');
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
    <div class="of-brand"><a class="of-side-brand" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Sajal Online', 'overview-fashion'); ?></a></div>
    <nav class="of-category-nav">
      <?php
        $of_active_category = '';
        if (function_exists('is_product_category') && is_product_category()) { $of_active_category = get_queried_object()->slug; }
        elseif (is_front_page()) { $of_active_category = ''; }
        elseif (function_exists('is_product') && is_product()) { $of_product_categories = wc_get_product_cat_ids(get_the_ID()); if ($of_product_categories) { $of_product_category = get_term($of_product_categories[0], 'product_cat'); if ($of_product_category && !is_wp_error($of_product_category)) { $of_active_category = $of_product_category->slug; } } }
      ?>
      <a href="<?php echo esc_url($home_url); ?>" class="<?php echo (function_exists('is_shop') && is_shop()) || (is_front_page() && !$of_active_category) ? 'current' : ''; ?>"><span class="of-category-dot">All</span><?php esc_html_e('All', 'overview-fashion'); ?></a>
      <?php foreach (overview_fashion_categories() as $of_cat) : ?>
        <a href="<?php echo esc_url(get_term_link($of_cat)); ?>" class="<?php echo (function_exists('is_product_category') && is_product_category($of_cat->slug)) || $of_active_category === $of_cat->slug ? 'current' : ''; ?>">
          <?php
            $of_thumb = get_term_meta($of_cat->term_id, 'thumbnail_id', true);
            if (!$of_thumb && function_exists('wc_get_products')) {
                $of_cat_products = wc_get_products(array('status' => 'publish', 'limit' => 1, 'category' => array($of_cat->slug)));
                if ($of_cat_products) { $of_thumb = $of_cat_products[0]->get_image_id(); }
            }
            if ($of_thumb) { echo wp_get_attachment_image($of_thumb, 'thumbnail', false, array('loading' => 'lazy')); } else { ?><span class="of-category-dot"><?php echo esc_html(substr($of_cat->name, 0, 1)); ?></span><?php }
          ?>
          <span><?php echo esc_html($of_cat->name); ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
    <p class="of-sidebar-note"><?php esc_html_e('Developed by', 'overview-fashion'); ?> <strong><?php esc_html_e('Legend Sabbir', 'overview-fashion'); ?></strong></p>
  </aside>
  <div class="of-main">
    <header class="of-header">
      <?php if (function_exists('is_product') && is_product()) : ?><a class="of-back-link" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('Back to home', 'overview-fashion'); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12H3m0 0 7-7m-7 7 7 7"/></svg></a><?php endif; ?>
      <button class="of-mobile-menu" type="button" aria-label="<?php esc_attr_e('Open menu', 'overview-fashion'); ?>" data-of-menu-open><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 6h18M3 12h12M3 18h18"/></svg></button>
      <div class="of-social-links">
        <a class="of-social-icon of-whatsapp" href="<?php echo esc_url(get_theme_mod('of_whatsapp', 'https://wa.me/')); ?>" target="_blank" rel="noopener" aria-label="WhatsApp"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2a9.7 9.7 0 0 0-8.3 14.7L2.4 22l5.5-1.4A9.8 9.8 0 1 0 12 2Zm0 17.7a7.9 7.9 0 0 1-4-1.1l-.3-.2-3.2.8.9-3.1-.2-.3A7.9 7.9 0 1 1 12 19.7Zm4.3-5.9c-.2-.1-1.4-.7-1.6-.8-.2-.1-.4-.1-.5.1-.2.2-.6.8-.8.9-.1.2-.3.2-.5.1-.2-.1-1-.4-1.9-1.2-.7-.6-1.2-1.4-1.3-1.6-.1-.2 0-.4.1-.5l.4-.4.2-.4c.1-.2 0-.3 0-.4l-.7-1.7c-.2-.5-.4-.4-.5-.4h-.5c-.2 0-.4.1-.6.3-.2.2-.8.8-.8 1.9s.8 2.2.9 2.4c.1.2 1.6 2.4 3.8 3.3.5.2.9.4 1.2.4.5.2 1 .1 1.4.1.4-.1 1.4-.6 1.6-1.1.2-.6.2-1 .1-1.1-.1-.1-.3-.2-.5-.3Z"/></svg></a>
        <a class="of-social-icon of-messenger" href="<?php echo esc_url(get_theme_mod('of_messenger', 'https://m.me/')); ?>" target="_blank" rel="noopener" aria-label="Messenger"><svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 2C6.4 2 2 6.1 2 11.3c0 2.9 1.4 5.5 3.7 7.2V22l3.4-1.9c.9.2 1.9.4 2.9.4 5.6 0 10-4.1 10-9.2S17.6 2 12 2Zm1 12.4-2.6-2.8-5 2.8 5.5-5.8 2.7 2.8 4.9-2.8-5.5 5.8Z"/></svg></a>
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
          <button class="of-mobile-cart" type="button" data-of-cart-open aria-label="<?php esc_attr_e('Open cart', 'overview-fashion'); ?>"><svg viewBox="0 0 448 512" aria-hidden="true"><path fill="currentColor" d="M160 112c0-35.3 28.7-64 64-64s64 28.7 64 64v48H160v-48zm-48 48H48c-26.5 0-48 21.5-48 48v208c0 53 43 96 96 96h256c53 0 96-43 96-96V208c0-26.5-21.5-48-48-48h-64v-48C336 50.1 285.9 0 224 0S112 50.1 112 112v48zm24 48a24 24 0 1 1 0 48 24 24 0 1 1 0-48zm152 24a24 24 0 1 1 48 0 24 24 0 1 1-48 0z"/></svg><span class="of-count" data-of-cart-count><?php echo esc_html(overview_fashion_cart_count()); ?></span></button>
        <?php endif; ?>
      </div>
    </header>
    <div class="of-search-overlay" data-of-search-overlay aria-hidden="true">
      <div class="of-search-modal" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e('Search products', 'overview-fashion'); ?>">
        <form class="of-search-modal-form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
          <input type="search" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php esc_attr_e('Search products...', 'overview-fashion'); ?>" data-of-search-modal-input autocomplete="off">
          <input type="hidden" name="post_type" value="product">
          <button type="button" data-of-search-close aria-label="<?php esc_attr_e('Close search', 'overview-fashion'); ?>">×</button>
        </form>
      </div>
    </div>
    <div class="of-mobile-drawer" data-of-drawer>
      <div class="of-drawer-panel"><button type="button" data-of-menu-close class="of-drawer-close" aria-label="<?php esc_attr_e('Close menu', 'overview-fashion'); ?>">×</button>
      <nav class="of-category-nav"><div class="of-drawer-brand"><a href="<?php echo esc_url($home_url); ?>"><?php esc_html_e('SAJAL ONLINE', 'overview-fashion'); ?></a></div><a href="<?php echo esc_url($home_url); ?>" class="<?php echo (function_exists('is_shop') && is_shop()) || (is_front_page() && !$of_active_category) ? 'current' : ''; ?>"><span class="of-category-dot">All</span><span><?php esc_html_e('All', 'overview-fashion'); ?></span></a>
        <?php foreach (overview_fashion_categories() as $of_cat) : ?><a href="<?php echo esc_url(get_term_link($of_cat)); ?>" class="<?php echo (function_exists('is_product_category') && is_product_category($of_cat->slug)) || $of_active_category === $of_cat->slug ? 'current' : ''; ?>"><?php $of_mobile_thumb = get_term_meta($of_cat->term_id, 'thumbnail_id', true); if (!$of_mobile_thumb && function_exists('wc_get_products')) { $of_mobile_products = wc_get_products(array('status' => 'publish', 'limit' => 1, 'category' => array($of_cat->slug))); if ($of_mobile_products) { $of_mobile_thumb = $of_mobile_products[0]->get_image_id(); } } if ($of_mobile_thumb) { echo wp_get_attachment_image($of_mobile_thumb, 'thumbnail', false, array('loading' => 'lazy')); } else { ?><span class="of-category-dot"><?php echo esc_html(substr($of_cat->name, 0, 1)); ?></span><?php } ?><span><?php echo esc_html($of_cat->name); ?></span></a><?php endforeach; ?>
      </nav></div><button class="of-drawer-backdrop" data-of-menu-close aria-label="<?php esc_attr_e('Close menu', 'overview-fashion'); ?>"></button>
    </div>
    <?php if (function_exists('WC')) : ?>
      <div class="of-cart-drawer" data-of-cart-drawer>
        <button class="of-cart-backdrop" data-of-cart-close aria-label="<?php esc_attr_e('Close cart', 'overview-fashion'); ?>"></button>
        <section class="of-cart-drawer-panel" aria-label="<?php esc_attr_e('Your cart', 'overview-fashion'); ?>"><div class="of-cart-drawer-heading"><strong class="of-cart-drawer-title"><?php esc_html_e('Your cart', 'overview-fashion'); ?></strong><?php overview_fashion_cart_drawer_counts(); ?><button type="button" data-of-cart-close aria-label="<?php esc_attr_e('Close cart', 'overview-fashion'); ?>">×</button></div><?php overview_fashion_mini_cart_contents(); overview_fashion_cart_footer(true); ?></section>
      </div>
    <?php endif; ?>
    <main class="of-content">
