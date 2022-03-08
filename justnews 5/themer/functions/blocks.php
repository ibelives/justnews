<?php
defined( 'ABSPATH' ) || exit;

if ( function_exists('register_block_type') ) {
    add_action('admin_init', 'wpcom_gutenberg_blocks');
    function wpcom_gutenberg_blocks() {
        global $wp_version;
        wp_register_script('wpcom-blocks', FRAMEWORK_URI . '/assets/js/blocks.js', array('wp-blocks', 'wp-element'), FRAMEWORK_VERSION, true);
        wp_register_style('wpcom-blocks', FRAMEWORK_URI . '/assets/css/blocks.css', array('wp-edit-blocks'), FRAMEWORK_VERSION);
        wp_localize_script( 'wpcom-blocks', '_wpcom_blocks', apply_filters('wpcom_blocks_script', array('exclude' => array())) );

        register_block_type('wpcom/blocks', array(
            'editor_script' => 'wpcom-blocks',
            'editor_style' => 'wpcom-blocks'
        ));

        if(version_compare($wp_version,'5.8.0') >= 0){
            add_filter( 'block_categories_all', 'wpcom_gutenberg_block_categories', 5 );
        }else{
            add_filter( 'block_categories', 'wpcom_gutenberg_block_categories', 5 );
        }
    }

    function wpcom_gutenberg_block_categories( $categories ) {
        return array_merge(
            $categories,
            array(
                array(
                    'slug' => 'wpcom',
                    'title' => __( 'WPCOM扩展区块', 'wpcom' )
                ),
            )
        );
    }
}