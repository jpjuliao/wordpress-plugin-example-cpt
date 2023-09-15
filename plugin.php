<?php

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Plugin Name: Example CPT Plugin
 * Description: Registers a custom post type with meta field support.
 * Version: 1.0
 * Author: Juan Pablo Juliao
 */

class Example_CPT_Plugin
{
  /**
   * Plugin name.
   * @var string
   */
  private $plugin_name;

  /**
   * Plugin version.
   * @var string
   */
  private $plugin_version;

  /**
   * Custom post type name.
   * @var string
   */
  private $cpt_name;

  /**
   * Custom meta field name.
   * @var string
   */
  private $meta_name;

  /**
   * Custom post type arguments.
   * @var array
   */
  private $cpt_args;

  /**
   * Custom meta field arguments.
   * @var array
   */
  private $meta_args;

  /**
   * Custom meta field arguments for the REST API.
   * @var array
   */
  private $meta_rest_args;

  /**
   * Constructor for the plugin class.
   */
  public function __construct()
  {
    // Set plugin name and version
    $this->plugin_name = 'Example CPT Plugin';
    $this->plugin_version = '1.0';

    // Set custom post type and meta field names
    $this->cpt_name = 'example_cpt';
    $this->cpt_args = array(
      'labels' => array(
        'name' => 'Example CPT',
        'singular_name' => 'Example CPT',
      ),
      'public' => true,
      'capability_type' => 'post',
      'supports' => array('title', 'editor', 'custom-fields'),
      'show_in_rest' => true,
    );

    // Set custom meta field name, arguments, and REST API arguments
    $this->meta_name = 'example_meta';
    $this->meta_args = array(
      'show_in_rest' => true,
      'type' => 'string',
      'single' => true,
      'sanitize_callback' => 'sanitize_text_field',
      'auth_callback' => array($this, 'check_meta_permissions'),
    );
    $this->meta_rest_args = array(
      'get_callback' => array($this, 'get_example_meta'),
      'update_callback' => array($this, 'update_example_meta'),
      'schema' => array(
        'description' => 'Meta description.',
        'type' => 'string',
        'context' => array('view', 'edit'),
      ),
    );

    // Register custom post type and meta field
    add_action('init', array($this, 'register_cpt'));
    add_action('rest_api_init', array($this, 'add_meta_to_rest_api'));

    // Add custom meta box
    add_action('add_meta_boxes', array($this, 'add_example_meta_box'));
    add_action('save_post', array($this, 'save_example_meta'));
  }

  /**
   * Register the custom post type.
   */
  public function register_cpt()
  {
    register_post_type($this->cpt_name, $this->cpt_args);
    register_meta('post', $this->meta_name, $this->meta_args);
  }

  /**
   * Add custom meta field to the REST API.
   */
  public function add_meta_to_rest_api()
  {
    register_rest_field($this->cpt_name, $this->meta_name, $this->meta_rest_args);
  }

  /**
   * Get the meta value.
   */
  public function get_example_meta($object)
  {
    return get_post_meta($object['id'], 'example_meta', true);
  }

  /**
   * Update the meta value.
   * @param  mixed $value
   * @param  object $object
   * @param  string $field_name
   */
  public function update_example_meta($value, $object, $field_name)
  {
    if (current_user_can('edit_post', $object['id'])) {
      return update_post_meta($object['id'], 'example_meta', $value);
    }
    return new WP_Error('rest_forbidden', esc_html__('You cannot update this field.'), array('status' => 403));
  }

  /**
   * Check permissions for the meta value.
   * @param  mixed $value
   * @param  object $request
   * @param  string $param
   */
  public function check_meta_permissions($value, $request, $param)
  {
    if (current_user_can('edit_post', $request->get_param('id'))) {
      return true;
    }
    return new WP_Error('rest_forbidden', esc_html__('You cannot edit this field.'), array('status' => 403));
  }

  /**
   * Add custom meta box.
   */
  public function add_example_meta_box()
  {
    add_meta_box(
      'example_meta_box',
      'Example Meta',
      array($this, 'render_example_meta_box'),
      'example_cpt',
      'normal',
      'default'
    );
  }

  /**
   * Render custom meta box.
   * @param  object $post
   */
  public function render_example_meta_box($post)
  {
    // Retrieve the current value of 'example_meta'
    $example_meta = get_post_meta($post->ID, 'example_meta', true);
    // Display the input field
    ?>
    <label for="example_meta">Example Meta:</label>
    <input type="text" id="example_meta" name="example_meta" value="<?php echo esc_attr($example_meta); ?>" style="width: 100%;" />
    <?php
  }

  /**
   * Save custom meta box.
   * @param  int $post_id
   */
  public function save_example_meta($post_id)
  {
    // Check if this is an autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    // Check user permissions
    if (!current_user_can('edit_post', $post_id)) return;

    // Save the meta field value
    if (isset($_POST['example_meta'])) {
      update_post_meta($post_id, 'example_meta', sanitize_text_field($_POST['example_meta']));
    }
  }
}

// Instantiate the plugin class
new Example_CPT_Plugin();
