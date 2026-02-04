<?php
/**
 * Jetpack Social Distribution Destination
 *
 * Integrates Jetpack Social (Publicize) with the Parish Distribution system.
 * This allows controlling Jetpack Social sharing via the Distribution sidebar checkbox.
 */

if (!defined('ABSPATH')) {
    exit;
}

class Dist_Jetpack_Social {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}

    public function init() {
        // Register with core distribution system
        add_action('parish_dist_register_destinations', array($this, 'register_destination'));

        // Register post meta
        add_action('init', array($this, 'register_meta'));

        // Hook into Jetpack's publicize decision - this is the key filter
        // that controls whether a post should be shared via Jetpack Social
        add_filter('publicize_should_publicize_published_post', array($this, 'filter_publicize'), 10, 2);

        // Also hook the wpcom variant (used by Jetpack connection)
        add_filter('wpas_should_send_to_publicize', array($this, 'filter_publicize'), 10, 2);
    }

    /**
     * Register as a distribution destination
     */
    public function register_destination($registry) {
        $registry->register('jetpack_social', array(
            'label' => __('Jetpack Social', 'parish-dist-jetpack-social'),
            'settings_callback' => null,
            'publish_callback' => null, // We don't publish directly - Jetpack handles it
            'has_options' => false,
        ));
    }

    /**
     * Register post meta for tracking distribution
     */
    public function register_meta() {
        register_post_meta('post', '_parish_dist_jetpack_social', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'boolean',
            'default' => false,
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            },
        ));

        // Track when Jetpack Social shared the post
        register_post_meta('post', '_parish_dist_jetpack_social_at', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
            'auth_callback' => function() {
                return current_user_can('edit_posts');
            },
        ));
    }

    /**
     * Filter whether Jetpack should publicize a post
     *
     * This is the key integration point. When Jetpack is about to share a post,
     * it runs this filter. We check our distribution meta and only allow sharing
     * if the checkbox was enabled.
     *
     * @param bool    $should_publicize Whether Jetpack wants to publicize
     * @param WP_Post $post             The post being published
     * @return bool Whether to allow publicizing
     */
    public function filter_publicize($should_publicize, $post) {
        // If Jetpack already decided not to publicize, respect that
        if (!$should_publicize) {
            return false;
        }

        // Only filter posts (not pages or other post types)
        if ($post->post_type !== 'post') {
            return $should_publicize;
        }

        // Check our distribution meta
        $enabled = get_post_meta($post->ID, '_parish_dist_jetpack_social', true);

        // If checkbox was checked (true or '1'), allow publicize
        if ($enabled === true || $enabled === '1' || $enabled === 1) {
            // Record that we allowed this share
            update_post_meta($post->ID, '_parish_dist_jetpack_social_at', current_time('mysql'));
            return true;
        }

        // Checkbox not checked - prevent Jetpack from sharing
        return false;
    }

    /**
     * Check if Jetpack Social is available
     *
     * @return bool
     */
    public static function is_jetpack_social_available() {
        // Check for Jetpack Publicize module or Jetpack Social standalone plugin
        if (class_exists('Automattic\Jetpack\Publicize\Publicize')) {
            return true;
        }

        // Check if Publicize module is active in Jetpack
        if (class_exists('Jetpack') && method_exists('Jetpack', 'is_module_active')) {
            return Jetpack::is_module_active('publicize');
        }

        return false;
    }
}
