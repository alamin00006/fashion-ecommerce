<?php
if (!defined('ABSPATH')) { exit; }
$of_banner = get_theme_mod('of_cover_banner');
if (!$of_banner) {
    $of_banner = get_template_directory_uri() . '/assets/cover.jpeg';
}
?>
<section class="of-home-hero" aria-label="<?php esc_attr_e('Featured collection', 'overview-fashion'); ?>">
  <img class="of-home-hero-image" src="<?php echo esc_url($of_banner); ?>" alt="<?php esc_attr_e('SAJAL new collection', 'overview-fashion'); ?>" fetchpriority="high" decoding="async">
</section>
