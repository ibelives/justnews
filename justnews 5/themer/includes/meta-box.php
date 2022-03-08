<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_Meta {
    public function __construct() {
        add_action( 'load-post.php', array( $this, 'register_scripts' ) );
        add_action( 'load-post-new.php', array( $this, 'register_scripts' ) );
        add_action( 'add_meta_boxes', array( $this, 'set_metabox' ) );
        add_action( 'save_post', array( $this, 'save_metabox' ) );
        add_action( 'wp_ajax_wpcom_get_keys_value', array( $this, 'get_keys_value' ) );
        add_action( 'wp_ajax_wpcom_get_attachments', array( $this, 'get_attachments' ) );
    }

    public function register_scripts() {
        add_action('admin_enqueue_scripts', array($this, 'load_scripts'));
    }

    public function load_scripts(){
        WPCOM::panel_script();
    }

    public function set_metabox(){
        global $wp_post_types;
        $exclude_types = array( 'attachment', 'revision', 'nav_menu_item', 'custom_css', 'customize_changeset', 'um_form', 'um_role', 'um_directory', 'shop_order', 'shop_coupon', 'page_module', 'gutenberg_content' );

        foreach( $wp_post_types as $type => $args ){
            if( ! in_array( $type , $exclude_types ) ){
                add_meta_box('wpcom-metas', '设置选项', array($this, 'metabox_html'), $type, 'normal', 'high', array());
            }
        }
    }

    public function metabox_html( $post ){
        // Add an nonce field
        wp_nonce_field( 'wpcom_meta_box', 'wpcom_meta_box_nonce' );
        $editor = post_type_supports($post->post_type, 'editor');
        ?>
        <div id="wpcom-panel" class="wpcom-post-metas"><post-panel :ready="ready" /></div>
        <script>_panel_options = <?php echo $this->get_post_metas($post);?>;</script>
        <?php if(!$editor){ ?><div style="display: none;"><?php wp_editor( 'EDITOR', 'WPCOM-EDITOR', WPCOM::editor_settings(array('textarea_name'=>'EDITOR-NAME')) );?></div><?php } ?>
    <?php }

    private function get_post_metas( $post ){
        global $options;
        $res = array('type' => 'post', 'post_type' => $post->post_type);
        // 向下兼容
        $ometas = get_post_meta($post->ID);
        $metas = get_post_meta($post->ID, '_wpcom_metas', true);
        if($ometas && !$metas){
            $metas = $metas ?: array();
            foreach ($ometas as $key => $val){
                if(preg_match('/^wpcom_/i', $key)){
                    $key = preg_replace('/^wpcom_/i', '', $key);
                    if(!isset($metas[$key]) && isset($val[0])) $metas[$key] = maybe_unserialize($val[0]);
                }
            }
        }
        $res['options'] = $metas;
        $res['theme-settings'] = $options;
        $res['filters'] = apply_filters( 'wpcom_post_metas', array() );
        $res['post_id'] = $post->ID;
        $res['ver'] = THEME_VERSION;
        $res['theme-id'] = THEME_ID;
        $res['framework_url'] = FRAMEWORK_URI;
        $res['framework_ver'] = FRAMEWORK_VERSION;
        $res['assets_ver'] = defined('ASSETS_VERSION')?ASSETS_VERSION:'';
        $res['seo'] = !isset($options['seo']) || $options['seo']=='1' ? true : false;
        $res = apply_filters('wpcom_post_panel_options', $res);
        return json_encode($res);
    }

    public function get_keys_value(){
        $post_id = $_REQUEST['id'];
        $keys = $_REQUEST['keys'];
        $res = array();
        if( current_user_can( 'edit_posts', $post_id ) ){
            foreach ($keys as $key){
                $res[$key] = get_post_meta($post_id, $key, true);
            }
        }
        echo json_encode($res);
        exit;
    }

    public function get_attachments(){
        $ids = $_REQUEST['ids'];
        $res = array();
        if( current_user_can( 'edit_posts' ) ){
            foreach ($ids as $id){
                $img = wp_get_attachment_url( $id );
                if($img) $res[$id] = $img;
            }
        }
        echo json_encode($res);
        exit;
    }

    /**
     * Save the meta when the post is saved.
     */
    public function save_metabox($post_id){
        global $post;
        if($post && $post->ID!=$post_id) return false;

        if(isset($_POST['post_type'])){
            foreach($_POST as $key => $value) {
                if (preg_match('/^_wpcom_/i', $key)) {
                    $meta_boxes = isset($meta_boxes) ? $meta_boxes : array();
                    $meta_boxes[] = preg_replace('/^_wpcom_/i', '', $key);
                }
            }
        }

        if(!isset($meta_boxes)||!$meta_boxes) return false;

        // Check if our nonce is set.
        if ( ! isset( $_POST['wpcom_meta_box_nonce'] ) )
            return $post_id;

        $nonce = $_POST['wpcom_meta_box_nonce'];

        // Verify that the nonce is valid.
        if ( ! wp_verify_nonce( $nonce, 'wpcom_meta_box' ) )
            return $post_id;

        // If this is an autosave, our form has not been submitted,
        // so we don't want to do anything.
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
            return $post_id;

        // Check the user's permissions.
        if ( 'page' == $_POST['post_type'] ) {
            if ( ! current_user_can( 'edit_page', $post_id ) )
                return $post_id;
        } else {
            if ( ! current_user_can( 'edit_post', $post_id ) )
                return $post_id;
        }

        $metas = get_post_meta( $post_id, '_wpcom_metas', true);
        $metas = is_array($metas) ? $metas : array();
        foreach ($meta_boxes as $meta) {
            if(preg_match('/^_/', $meta)){
                update_post_meta($post_id, $meta, stripslashes_deep( $_POST['_wpcom_'.$meta] ) );
            }else{
                $value = stripslashes_deep( $_POST['_wpcom_'.$meta] );

                if ( $value!=='' )
                    $metas[$meta] = $value;
                else if ( isset($metas[$meta]) )
                    unset($metas[$meta]);
            }
        }
        update_post_meta($post_id, '_wpcom_metas', $metas );
    }
}