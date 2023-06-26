<?php
/**
 * Plugin Name: Revision Control System
 * Plugin URI: https://www.wppoland.com/en/revision-control-system
 * Description: This plugin introduces revisions for all post types and keeps the last 400 revisions.
 * Version: 1.0.4
 * Requires at least: 5.8
 * Requires PHP: 7.3
 * Code Name: RCS
 * Author: WPPoland
 * Author URI: https://www.wppoland.com
 * Licence: GPLv2 or later
 *
 * Text Domain: wppoland
 * Domain Path: languages
 *
 * Copyright 2013-2023 WPPoland
 *
 *
 * Revision Control System is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 2 of the License, or
* any later version.
*
* Revision Control System is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Revision Control System.
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
add_action('init', 'add_revisions_to_all_post_types', 99999);

add_filter('wp_revisions_to_keep', 'limit_revisions', 10, 2);

function limit_revisions($num, $post) {
    $revisions_limit = get_option('revision_control_' . $post->post_type, 400);
    return empty($revisions_limit) ? 400 : intval($revisions_limit);
}

function revision_control_admin_menu() {
    add_options_page('Revision Control', 'Revision Control', 'manage_options', 'revision-control', 'revision_control_options_page');
}
add_action('admin_menu', 'revision_control_admin_menu');

function revision_control_options_page() {
    $post_types = get_post_types();
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            foreach ($post_types as $post_type) {
                settings_fields('revision_control');
                do_settings_sections('revision_control_' . $post_type);
            }
            submit_button('Save Settings');
            ?>
        </form>
    </div>
    <?php
}

function revision_control_settings_init() {
    $post_types = get_post_types();
    foreach ($post_types as $post_type) {
        register_setting('revision_control', 'revision_control_' . $post_type);

        add_settings_section(
            'revision_control_section_' . $post_type,
            __('Revision Limit for ' . $post_type, 'revision_control'),
            'revision_control_section_callback',
            'revision_control_' . $post_type
        );

        add_settings_field(
            'revision_control_field_' . $post_type,
            __('Number of Revisions', 'revision_control'),
            'revision_control_field_callback',
            'revision_control_' . $post_type,
            'revision_control_section_' . $post_type,
            [
                'label_for' => 'revision_control_' . $post_type,
                'class' => 'revision_control_row',
                'revision_control_custom_data' => 'custom',
            ]
        );
    }
}
add_action('admin_init', 'revision_control_settings_init');

function revision_control_section_callback($args) {
    ?>
    <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e('Set the maximum number of revisions for this post type.', 'revision_control'); ?></p>
    <?php
}

function revision_control_field_callback($args) {
    $options = get_option('revision_control_' . $args['label_for'], 400);
    ?>
    <input type="text" id="<?php echo esc_attr($args['label_for']); ?>" data-custom="<?php echo esc_attr($args['revision_control_custom_data']); ?>" name="revision_control_<?php echo $args['label_for']; ?>" value="<?php echo $options; ?>">
    <?php
}