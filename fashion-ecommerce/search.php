<?php
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<header class="of-section-heading of-search-heading">
  <h1 class="of-page-title"><?php printf(esc_html__('Results for “%s”', 'overview-fashion'), esc_html(get_search_query())); ?></h1>
</header>
<?php if (have_posts()) : ?>
  <div class="of-products">
    <?php while (have_posts()) : the_post(); $of_product = function_exists('wc_get_product') ? wc_get_product(get_the_ID()) : false; ?>
      <?php if ($of_product) : overview_fashion_product_card($of_product); else : ?>
        <article class="of-product"><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><?php the_excerpt(); ?></article>
      <?php endif; ?>
    <?php endwhile; ?>
  </div>
  <?php the_posts_pagination(); ?>
<?php else : ?>
  <p class="of-no-results"><?php esc_html_e('No products found. Try another search.', 'overview-fashion'); ?></p>
<?php endif; ?>
<?php get_footer(); ?>
