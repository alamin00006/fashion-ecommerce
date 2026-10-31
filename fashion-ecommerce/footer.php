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
      <?php overview_fashion_cart_footer(false); ?>
    </aside>
    <button class="of-mobile-cart-bar<?php echo overview_fashion_cart_count() > 0 ? ' is-visible' : ''; ?>" data-of-cart-open data-of-cart-bar type="button"><span class="of-mobile-cart-bag"><svg viewBox="0 0 448 512" aria-hidden="true"><path fill="currentColor" d="M160 112c0-35.3 28.7-64 64-64s64 28.7 64 64v48H160v-48zm-48 48H48c-26.5 0-48 21.5-48 48v208c0 53 43 96 96 96h256c53 0 96-43 96-96V208c0-26.5-21.5-48-48-48h-64v-48C336 50.1 285.9 0 224 0S112 50.1 112 112v48zm24 48a24 24 0 1 1 0 48 24 24 0 1 1 0-48zm152 24a24 24 0 1 1 48 0 24 24 0 1 1-48 0z"/></svg><b class="of-count" data-of-cart-count><?php echo esc_html(overview_fashion_cart_count()); ?></b></span><span><?php esc_html_e('কার্ট দেখুন', 'overview-fashion'); ?></span><span data-of-mobile-cart-total class="of-mobile-cart-total"><?php echo wp_kses_post(WC()->cart->get_cart_subtotal()); ?></span></button>
  <?php endif; ?>
</div>
<?php wp_footer(); ?>
</body></html>
