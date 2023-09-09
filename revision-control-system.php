<?php
/**
 * Plugin Name: Revision Control System
 * Plugin URI: https://www.wppoland.com/en/revision-control-system
 * Description: This plugin introduces revisions for all post types and keeps a user-defined number of revisions.
 * Version: 1.0.7
 * Requires at least: 5.8
 * Requires PHP: 7.3
 * Tested up to: 6.3.1
 * Stable tag: 1.0.7
 * Code Name: RCS
 * Author: WPPoland
 * Author URI: https://www.wppoland.com
 * Licence: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wppoland
 * Domain Path: languages
 *
 * Copyright 2013-2023 WPPoland
 */

namespace WPPoland\RCS;  // Add a namespace

// Prevent direct file access
defined('ABSPATH') || exit;

/**
 * Add revisions support to all post types.
 */
function add_revisions_to_all_post_types() {
    $post_types = get_post_types();
    foreach ($post_types as $post_type) {
        if (post_type_supports($post_type, 'revisions')) {
            continue;
        }
        add_post_type_support($post_type, 'revisions');
    }
}
add_action('init', __NAMESPACE__ . '\\add_revisions_to_all_post_types', 99999);

/**
 * Limit the number of revisions.
 */
function limit_revisions($num, $post) {
    $revisions_limit = get_option('rcs_' . $post->post_type, 400);
    return empty($revisions_limit) ? 400 : intval($revisions_limit);
}
add_filter('wp_revisions_to_keep', __NAMESPACE__ . '\\limit_revisions', 10, 2);

/**
 * Add the options page to the WP Admin menu.
 */
function revision_control_admin_menu() {
    add_options_page('Revision Control', 'Revision Control', 'manage_options', 'revision-control', __NAMESPACE__ . '\\revision_control_options_page');
}
add_action('admin_menu', __NAMESPACE__ . '\\revision_control_admin_menu');

/**
 * Display the options page content.
 */
function revision_control_options_page() {
    // Check user capabilities
    if (!current_user_can('manage_options')) {
        return;
    }

    // Add a nonce field
    wp_nonce_field('rcs_settings_nonce');

    $post_types = get_post_types();
    echo '<div class="wrap">';
    echo '<h1>' . esc_html(get_admin_page_title()) . '</h1>';
    echo '<form action="options.php" method="post">';

    foreach ($post_types as $post_type) {
        settings_fields('rcs_settings');
        do_settings_sections('rcs_settings_' . $post_type);
    }

    submit_button('Save Settings');

    echo '</form>';
    echo '</div>';
}

/**
 * Initialize settings.
 */
function revision_control_settings_init() {
    $post_types = get_post_types();
    foreach ($post_types as $post_type) {
        register_setting('rcs_settings', 'rcs_' . $post_type, [
            'sanitize_callback' => __NAMESPACE__ . '\\sanitize_input'
        ]);

        // Other code as it is
    }
}
add_action('admin_init', __NAMESPACE__ . '\\revision_control_settings_init');

/**
 * Sanitize user input.
 */
function sanitize_input($input) {
    return intval($input);
}
