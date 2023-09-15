<?php 

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Plugin Name: Example CPT Plugin
 * Description: Registers a custom post type with meta field support.
 * Version: 1.0
 * Author: Your Name
 */

class Example_CPT_Plugin {
    public function __construct() {
        add_action('init', array($this, 'register_cpt'));
        add_action('rest_api_init', array($this, 'add_meta_to_rest_api'));
    }

    public function register_cpt() {
        $labels = array(
            'name' => 'Example CPT',
            'singular_name' => 'Example CPT',
        );

        $args = array(
            'labels' => $labels,
            'public' => true,
            'capability_type' => 'post',
            'supports' => array('title', 'editor', 'custom-fields'),
            'show_in_rest' => true,
        );

        register_post_type('example_cpt', $args);

        register_meta('post', 'example_meta', array(
            'show_in_rest' => true,
            'type' => 'string',
            'single' => true,
            'sanitize_callback' => 'sanitize_text_field',
            'auth_callback' => array($this, 'check_meta_permissions'),
        ));
    }

    public function add_meta_to_rest_api() {
        register_rest_field('example_cpt', 'example_meta', array(
            'get_callback' => array($this, 'get_example_meta'),
            'update_callback' => array($this, 'update_example_meta'),
            'schema' => array(
                'type' => 'string',
                'description' => 'Example Meta Field',
                'context' => array('view', 'edit'),
            ),
        ));
    }

    public function get_example_meta($object) {
        return get_post_meta($object['id'], 'example_meta', true);
    }

    public function update_example_meta($value, $object, $field_name) {
        if (current_user_can('edit_post', $object['id'])) {
            return update_post_meta($object['id'], 'example_meta', $value);
        }
        return new WP_Error('rest_forbidden', esc_html__('You cannot update this field.'), array('status' => 403));
    }

    public function check_meta_permissions($value, $request, $param) {
        if (current_user_can('edit_post', $request->get_param('id'))) {
            return true;
        }
        return new WP_Error('rest_forbidden', esc_html__('You cannot edit this field.'), array('status' => 403));
    }
}

$example_cpt_plugin = new Example_CPT_Plugin();
