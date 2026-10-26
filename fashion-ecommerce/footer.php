<?php if (!defined('ABSPATH')) { exit; } ?>
    </main>
    <?php if (!is_front_page() && !(function_exists('is_woocommerce') && is_woocommerce())) : ?><footer class="of-footer">
      <div class="of-footer-inner">
        <div><div class="of-footer-brand"><?php overview_fashion_logo(); ?></div><p><?php esc_html_e('Premium polos, shirts and shoes delivered across Bangladesh.', 'overview-fashion'); ?></p>
          <div><?php foreach (array('facebook', 'instagram', 'messenger') as $of_social) : if (get_theme_mod('of_' . $of_social)) : ?><a href="<?php echo esc_url(get_theme_mod('of_' . $of_social)); ?>" target="_blank" rel="noopener"><?php echo esc_html(ucfirst($of_social)); ?></a> &nbsp;<?php endif; endforeach; ?></div>
        </div>
        <div><h3><?php esc_html_e('Categories', 'overview-fashion'); ?></h3><ul><?php foreach (array_slice(overview_fashion_categories(6), 0, 6) as $of_cat) : ?><li><a href="<?php echo esc_url(get_term_link($of_cat)); ?>"><?php echo esc_html($of_cat->name); ?></a></li><?php endforeach; ?></ul></div>
        <div><h3><?php esc_html_e('Help', 'overview-fashion'); ?></h3><ul>
          <?php if (function_exists('wc_get_page_permalink')) : ?><li><a href="<?php echo esc_url(wc_get_cart_url()); ?>"><?php esc_html_e('Cart', 'overview-fashion'); ?></a></li><li><a href="<?php echo esc_url(wc_get_checkout_url()); ?>"><?php esc_html_e('Checkout', 'overview-fashion'); ?></a></li><li><a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>"><?php esc_html_e('Order history / account', 'overview-fashion'); ?></a></li><?php endif; ?>
          <li><?php esc_html_e('Delivery: 2–4 days', 'overview-fashion'); ?></li><li><?php esc_html_e('Easy exchange within 3 days', 'overview-fashion'); ?></li>
        </ul></div>
        <div><h3><?php esc_html_e('Contact', 'overview-fashion'); ?></h3><ul>
          <li><?php echo esc_html(get_theme_mod('of_phone', '+880 1XXX-XXXXXX')); ?></li><li><?php echo esc_html(get_theme_mod('of_email', 'info@overviewfashion.com')); ?></li><li><?php echo esc_html(get_theme_mod('of_address', 'Dhaka, Bangladesh')); ?></li>
        </ul></div>
      </div>
      <div class="of-copyright">© <?php echo esc_html(wp_date('Y')); ?> <?php bloginfo('name'); ?>. <?php esc_html_e('All rights reserved.', 'overview-fashion'); ?></div>
    </footer><?php endif; ?>
  </div>
  <?php if (function_exists('WC')) : ?>
    <aside class="of-cart-sidebar">
      <div class="of-cart-tabs"><a href="<?php echo esc_url(wc_get_page_permalink('myaccount')); ?>"><?php esc_html_e('History', 'overview-fashion'); ?></a><button type="button" class="active"><?php esc_html_e('Cart', 'overview-fashion'); ?></button></div>
      <div class="of-cart-sidebar-content"><?php overview_fashion_mini_cart_contents(); ?></div>
      <div class="of-cart-sidebar-footer"><div><span><?php esc_html_e('TOTAL:', 'overview-fashion'); ?></span><strong><?php echo wp_kses_post(WC()->cart->get_total()); ?></strong></div><a class="of-button" href="<?php echo esc_url(wc_get_checkout_url()); ?>"><?php esc_html_e('Checkout', 'overview-fashion'); ?> <span>→</span></a></div>
    </aside>
    <button class="of-mobile-cart-bar<?php echo overview_fashion_cart_count() > 0 ? ' is-visible' : ''; ?>" data-of-cart-open data-of-cart-bar type="button"><span class="of-mobile-cart-bag">♧ <b class="of-count" data-of-cart-count><?php echo esc_html(overview_fashion_cart_count()); ?></b></span><span><?php esc_html_e('View cart', 'overview-fashion'); ?></span><span data-of-mobile-cart-total class="of-mobile-cart-total"><?php echo wp_kses_post(WC()->cart->get_cart_subtotal()); ?></span></button>
  <?php endif; ?>
</div>
<?php wp_footer(); ?>
</body></html>
