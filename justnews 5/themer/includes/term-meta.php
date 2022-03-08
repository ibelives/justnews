<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_Term_Meta{
    public function __construct( $tax ) {
        $this->tax = $tax;
        add_action( $tax . '_add_form_fields', array($this, 'add'), 10, 2 );
        add_action( $tax . '_edit_form_fields', array($this, 'edit'), 10, 2 );
        add_action( 'created_' . $tax, array($this, 'save'), 10, 2 );
        add_action( 'edited_' . $tax, array($this, 'save'), 10, 2 );
    }

    function add(){
        WPCOM::panel_script();
        ?>

        <div id="wpcom-panel" class="wpcom-term-wrap"><term-panel :ready="ready"/></div>
        <script>_panel_options = <?php echo $this->get_term_metas(0);?>;</script>
        <div style="display: none;"><?php wp_editor( 'EDITOR', 'WPCOM-EDITOR', WPCOM::editor_settings(array('textarea_name'=>'EDITOR-NAME')) );?></div>
    <?php }

    function edit($term){
        WPCOM::panel_script();
        ?>

        <tr id="wpcom-panel" class="wpcom-term-wrap"><td colspan="2"><term-panel :ready="ready"/></td></tr>
        <tr style="display: none;"><th></th><td><script>_panel_options = <?php echo $this->get_term_metas($term->term_id);?></script>
                <div style="display: none;"><?php wp_editor( 'EDITOR', 'WPCOM-EDITOR', WPCOM::editor_settings(array('textarea_name'=>'EDITOR-NAME')) );?></div></td></tr>
    <?php }

    function save($term_id){
        $values = array();
        $_post = $_POST;
        foreach($_post as $key => $value) {
            if (preg_match('/^wpcom_/i', $key)) {
                $name = preg_replace('/^wpcom_/i', '', $key);
                $values[$name] = $value;
            }
        }
        if(!empty($values)){
            update_term_meta( $term_id, '_wpcom_metas', $values );
        }
    }

    function get_term_metas($term_id){
        global $options;
        $res = array('type' => 'taxonomy', 'tax' => $this->tax);
        if($term_id){
            $res['options'] = get_term_meta( $term_id, '_wpcom_metas', true );
        }
        $res['filters'] = apply_filters('wpcom_tax_metas', array());
        $res['ver'] = THEME_VERSION;
        $res['theme-id'] = THEME_ID;
        $res['theme-settings'] = $options;
        $res['framework_url'] = FRAMEWORK_URI;
        $res['framework_ver'] = FRAMEWORK_VERSION;
        $res['assets_ver'] = defined('ASSETS_VERSION')?ASSETS_VERSION:'';
        $res['seo'] = !isset($options['seo']) || $options['seo']=='1' ? true : false;
        $res = apply_filters('wpcom_term_panel_options', $res);
        return json_encode($res);
    }
}

add_action('admin_init', 'wpcom_tax_meta');
function wpcom_tax_meta(){
    global $pagenow;
    if( ($pagenow == 'edit-tags.php' || $pagenow == 'term.php' || (isset($_POST['action']) && $_POST['action']=='add-tag'))  ) {
        $exclude_taxonomies = array('nav_menu', 'link_category', 'post_format');
        $taxonomies = get_taxonomies();
        foreach ($taxonomies as $key => $taxonomy) {
            if (!in_array($key, $exclude_taxonomies)) {
                new WPCOM_Term_Meta($key);
            }
        }
    }
}

add_action('admin_menu', 'wpcom_reading_per_page');
function wpcom_reading_per_page(){
    global $wpcom_panel;
    $tpls = $wpcom_panel->get_term_tpls();
    if($tpls){
        add_settings_section(
            'wpcom',
            '列表分页显示数量',
            'wpcom_reading_section_callback',
            'reading'
        );
        register_setting( 'reading', 'wpcom' );
        foreach ($tpls as $key => $tpl){
            foreach ($tpl as $name => $title) {
                $_title = explode('||', $title);
                $title = $_title[0];
                if($name) {
                    $id = 'per_page_for_' . $name;
                    add_settings_field(
                        $id,
                        $title,
                        'wpcom_reading_per_page_callback',
                        'reading',
                        'wpcom',
                        array( 'id' => $id )
                    );
                    add_settings_field(
                        $id . '_full',
                        $title . '（无边栏）',
                        'wpcom_reading_per_page_callback',
                        'reading',
                        'wpcom',
                        array( 'id' => $id . '_full' )
                    );
                    if( isset($_POST['option_page']) && $_POST['option_page'] == 'reading' ){
                        update_option( $id,  $_POST[$id] );
                        update_option( $id . '_full',  $_POST[$id . '_full'] );
                    }
                }
            }
        }
    }
}
function wpcom_reading_section_callback() {
    echo '<p>文章列表每页显示数量设置</p>';
}
function wpcom_reading_per_page_callback($args){
    echo '<input name="'.esc_attr($args['id']).'" type="number" step="1" min="1" id="'.esc_attr($args['id']).'" value="'.esc_attr( get_option($args['id']) ).'" class="small-text" /> ' . __( 'posts' );
}

add_action( 'wp_ajax_wpcom_get_taxs', 'wpcom_get_taxs' );
function wpcom_get_taxs(){
    $taxs = $_REQUEST['taxs'];
    $res = array();
    if( current_user_can( 'edit_posts' ) ){
        foreach ($taxs as $tax){
            if($tax) $res[$tax] = WPCOM::category($tax);
        }
    }
    echo json_encode($res);
    exit;
}