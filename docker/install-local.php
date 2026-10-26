<?php
// Runs once in the local setup container after the WordPress image creates wp-config.php.
$core_zip = '/tmp/wordpress-core.zip';
if (file_exists($core_zip)) {
    $archive = new ZipArchive();
    if ($archive->open($core_zip) === true) {
        $archive->extractTo('/tmp/overview-wordpress-core');
        $archive->close();
        $source = '/tmp/overview-wordpress-core/wordpress';
        $copy_core = function ($from, $to, $relative = '') use (&$copy_core) {
            if (!is_dir($to)) { mkdir($to, 0755, true); }
            foreach (scandir($from) as $name) {
                if ($name === '.' || $name === '..') { continue; }
                if ($relative === '' && ($name === 'wp-content' || $name === 'wp-config.php')) { continue; }
                $source_path = $from . '/' . $name;
                $target_path = $to . '/' . $name;
                if (is_dir($source_path)) { $copy_core($source_path, $target_path, $relative . '/' . $name); }
                else { copy($source_path, $target_path); }
            }
        };
        $copy_core($source, '/var/www/html');
        echo "Updated the local WordPress core from the bundled official release.\n";
    } else {
        echo "Could not open the bundled WordPress core archive.\n";
    }
}

define('WP_INSTALLING', true);
require '/var/www/html/wp-load.php';

if (!is_blog_installed()) {
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    wp_install('sajalonline', 'admin', 'admin@example.test', true, '', 'overview-local-only');
    echo "Installed WordPress.\n";
} else {
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    wp_upgrade();
}

update_option('blogname', 'sajalonline');
update_option('siteurl', 'http://localhost:8081');
update_option('home', 'http://localhost:8081');
update_option('woocommerce_currency', 'BDT');
update_option('permalink_structure', '/%postname%/');
switch_theme('overview-fashion');

require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

$plugin_file = WP_PLUGIN_DIR . '/woocommerce/woocommerce.php';
if (!file_exists($plugin_file)) {
    $zip = '/tmp/woocommerce.zip';
    if (!file_exists($zip)) {
        echo "Downloading WooCommerce from WordPress.org...\n";
        $zip = download_url('https://downloads.wordpress.org/plugin/woocommerce.latest-stable.zip', 120);
    }
    if (is_wp_error($zip)) {
        echo 'WooCommerce download skipped: ' . $zip->get_error_message() . "\n";
    } else {
        $archive = new ZipArchive();
        $unzipped = $archive->open($zip);
        if ($unzipped === true) {
            $archive->extractTo(WP_PLUGIN_DIR);
            $archive->close();
            $unzipped = true;
        }
        @unlink($zip);
        if ($unzipped !== true) {
            echo "WooCommerce extraction failed.\n";
        }
    }
}

if (file_exists($plugin_file) && !is_plugin_active('woocommerce/woocommerce.php')) {
    $activated = activate_plugin('woocommerce/woocommerce.php');
    if (is_wp_error($activated)) {
        echo 'WooCommerce activation failed: ' . $activated->get_error_message() . "\n";
    } else {
        echo "Activated WooCommerce.\n";
    }
}

flush_rewrite_rules();
echo "Overview Fashion local WordPress setup finished.\n";
