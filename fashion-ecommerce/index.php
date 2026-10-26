<?php
if (!defined('ABSPATH')) { exit; }
get_header();
if (have_posts()) :
  while (have_posts()) : the_post(); ?>
    <article <?php post_class('of-section'); ?>><h1 class="of-page-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h1><?php the_excerpt(); ?></article>
  <?php endwhile;
  the_posts_pagination();
else : ?><h1 class="of-page-title"><?php esc_html_e('Nothing found', 'overview-fashion'); ?></h1><?php endif;
get_footer();
