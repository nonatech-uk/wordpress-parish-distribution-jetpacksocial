<?php
/**
 * Plugin Name: Distribution - Jetpack Social
 * Plugin URI: https://github.com/nonatech-uk/wp-dist-jetpack-social
 * Description: Integrates Jetpack Social (Publicize) with Distribution for controlled Facebook sharing
 * Version: 1.0.0
 * Author: NonaTech Services Ltd
 * License: GPL v2 or later
 * Text Domain: dist-jetpack-social
 * Requires Plugins: distribution
 */

if (!defined('ABSPATH')) {
    exit;
}

define('PARISH_DIST_JETPACK_SOCIAL_VERSION', '1.0.0');
define('PARISH_DIST_JETPACK_SOCIAL_DIR', plugin_dir_path(__FILE__));
define('PARISH_DIST_JETPACK_SOCIAL_URL', plugin_dir_url(__FILE__));

// Check for core plugin
add_action('plugins_loaded', function() {
    if (!class_exists('Parish_Distribution')) {
        add_action('admin_notices', function() {
            echo '<div class="notice notice-error"><p>';
            echo esc_html__('Parish Distribution - Jetpack Social requires the Parish Distribution plugin to be installed and activated.', 'parish-dist-jetpack-social');
            echo '</p></div>';
        });
        return;
    }

    require_once PARISH_DIST_JETPACK_SOCIAL_DIR . 'includes/class-dist-jetpack-social.php';
    require_once PARISH_DIST_JETPACK_SOCIAL_DIR . 'includes/class-github-updater.php';

    $jetpack_social = Dist_Jetpack_Social::get_instance();
    $jetpack_social->init();

    // Initialize GitHub updater
    if (is_admin()) {
        new Dist_Jetpack_Social_GitHub_Updater(
            __FILE__,
            'nonatech-uk/wp-dist-jetpack-social',
            PARISH_DIST_JETPACK_SOCIAL_VERSION
        );
    }
});

function parish_dist_jetpack_social_activate() {
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'parish_dist_jetpack_social_activate');

function parish_dist_jetpack_social_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'parish_dist_jetpack_social_deactivate');
