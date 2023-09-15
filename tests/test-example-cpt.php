<?php
/**
 * PHPUnit tests for Example CPT Plugin.
 *
 * @package Your_Plugin_Package
 */

/**
 * Class ExampleCPTPluginTest
 *
 * @package Your_Plugin_Package
 */
class ExampleCPTPluginTest extends WP_UnitTestCase {
    /**
     * Test if the custom post type is registered correctly.
     */
    public function test_cpt_registration() {
        $this->assertTrue(post_type_exists('example_cpt'));
    }

    /**
     * Test if the custom meta field is registered correctly.
     */
    public function test_meta_field_registration() {
        $this->assertTrue(metadata_exists('post', 1, 'example_meta'));
    }

    /**
     * Test if the custom meta field value is retrieved correctly.
     */
    public function test_meta_field_value() {
        $post_id = $this->factory->post->create(array('post_type' => 'example_cpt'));
        update_post_meta($post_id, 'example_meta', 'Test Value');
        $value = get_post_meta($post_id, 'example_meta', true);
        $this->assertEquals('Test Value', $value);
    }

}
