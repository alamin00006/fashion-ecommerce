<?php
if (!defined('ABSPATH')) { exit; }
get_header();
while (have_posts()) : the_post(); ?>
  <article <?php post_class('of-section'); ?>><h1 class="of-page-title"><?php the_title(); ?></h1><div class="entry-content"><?php the_content(); ?></div></article>
<?php endwhile;
get_footer();
