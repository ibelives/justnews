<?php
defined( 'ABSPATH' ) || exit;

abstract class WPCOM_Widget extends WP_Widget {
    public $widget_cssclass;
    public $widget_description;
    public $widget_id;
    public $widget_name;
    public $settings;

    public function __construct() {
        $widget_ops = array(
            'classname'   => $this->widget_cssclass,
            'description' => $this->widget_description,
            'customize_selective_refresh' => true
        );

        parent::__construct( 'wpcom-'.$this->widget_id, $this->widget_name, $widget_ops );

        add_action( 'save_post', array( $this, 'flush_widget_cache' ) );
        add_action( 'deleted_post', array( $this, 'flush_widget_cache' ) );
        add_action( 'switch_theme', array( $this, 'flush_widget_cache' ) );
    }

    public function get_cached_widget( $args ) {
        $cache = wp_cache_get( $this->get_widget_id_for_cache( $this->widget_id ), 'widget' );

        if ( ! is_array( $cache ) ) {
            $cache = array();
        }

        if ( isset( $cache[ $this->get_widget_id_for_cache( $args['widget_id'] ) ] ) ) {
            echo $cache[ $this->get_widget_id_for_cache( $args['widget_id'] ) ];
            return true;
        }

        return false;
    }

    public function cache_widget( $args, $content ) {
        global $options;
        if(isset($options['enable_cache']) && $options['enable_cache']=='0') return $content;
        $cache = wp_cache_get( $this->get_widget_id_for_cache( $this->widget_id ), 'widget' );
        if ( ! is_array( $cache ) ) {
            $cache = array();
        }
        $cache[ $this->get_widget_id_for_cache( $args['widget_id'] ) ] = $content;
        wp_cache_set( $this->get_widget_id_for_cache( $this->widget_id ), $cache, 'widget' );
        return $content;
    }

    public function flush_widget_cache() {
        foreach ( array( 'https', 'http' ) as $scheme ) {
            wp_cache_delete( $this->get_widget_id_for_cache( $this->widget_id, $scheme ), 'widget' );
        }
    }

    public function widget_start( $args, $instance ) {
        echo $args['before_widget'];
        if ( $title = apply_filters( 'widget_title', isset($instance['title']) && is_string($instance['title']) ? $instance['title'] : '', $instance, $this->id_base ) ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
    }

    public function widget_end( $args ) {
        echo $args['after_widget'];
    }

    public function update( $new_instance, $instance ) {
        // Loop settings and get values to save.
        foreach ( $this->settings as $key => $setting ) {
            $new_instance[ $key ] = apply_filters( 'wpcom_widget_settings_sanitize_option', $new_instance[ $key ], $instance, $key, $setting );
        }
        $this->flush_widget_cache();
        return $new_instance;
    }

    public function form( $instance ) {
        if ( empty( $this->settings ) ) return; ?>
        <widget-panel base-id="<?php echo 'wpcom-'.$this->widget_id;?>"><?php echo base64_encode(json_encode($instance));?></widget-panel>
    <?php }

    protected function get_widget_id_for_cache( $widget_id, $scheme = '' ) {
        if ( defined( 'ICL_SITEPRESS_VERSION' ) ){ // WPML兼容
            $lang = defined( 'ICL_LANGUAGE_CODE' ) ? ICL_LANGUAGE_CODE : get_bloginfo( 'language' );
            $widget_id = $widget_id . '-' . $lang;
        }

        if ( $scheme ) {
            $widget_id_for_cache = $widget_id . '-' . $scheme;
        } else {
            $widget_id_for_cache = $widget_id . '-' . ( is_ssl() ? 'https' : 'http' );
        }

        return apply_filters( 'wpcom_widget_cached_id', $widget_id_for_cache );
    }
}

add_action('admin_menu', 'wpcom_widgets_add_prefix');
function wpcom_widgets_add_prefix() {
    global $_wp_sidebars_widgets, $pagenow;
    $sidebars_widgets = get_option('sidebars_widgets');
    $_widgets = $GLOBALS['wp_widget_factory']->widgets;
    if($sidebars_widgets){
        $_sidebars_widgets = array();
        foreach ($sidebars_widgets as $k => $widgets){
            if($widgets && is_array($widgets)) {
                $_sidebars_widgets[$k] = array();
                foreach ($widgets as $widget) {
                    $added = 0;
                    $_widget = $widget;
                    $widget = preg_replace('/-[\d]+$/i', '', $widget);
                    foreach ($_widgets as $w) {
                        if (isset($w->widget_id) && $w->widget_id == $widget && $w->id_base == 'wpcom-' . $w->widget_id) {
                            $_sidebars_widgets[$k][] = 'wpcom-' . $_widget;
                            preg_match('/(.*)-(\d+)$/i', $_widget, $matches);
                            update_option('widget_' . $w->id_base, get_option('widget_' . $matches[1]));
                            $added = 1;
                            break;
                        }
                    }
                    if (!$added) $_sidebars_widgets[$k][] = $_widget;
                }
            }else{
                $_sidebars_widgets[$k] = $widgets;
            }
        }

        if($sidebars_widgets != $_sidebars_widgets) {
            $_wp_sidebars_widgets = null;
            update_option( 'sidebars_widgets', $_sidebars_widgets );
        }
    }
}

add_action('customize_controls_init', 'wpcom_customize_widget_init');
add_action('admin_print_scripts-widgets.php', 'wpcom_customize_widget_init');
function wpcom_customize_widget_init(){
    WPCOM::panel_script();
}

add_action('sidebar_admin_page', 'wpcom_widget_panel_options');
add_action('customize_controls_head', 'wpcom_widget_panel_options');
function wpcom_widget_panel_options(){ ?>
    <script>_panel_options = <?php echo wpcom_init_widget_options();?>;</script>
    <div style="display: none;"><?php wp_editor( 'EDITOR', 'WPCOM-EDITOR', WPCOM::editor_settings(array('textarea_name'=>'EDITOR-NAME')) );?></div>
<?php }

function wpcom_init_widget_options(){
    global $wp_widget_factory, $wpcom_panel;
    $widgets = $wp_widget_factory->widgets;
    $settings = array();
    foreach ($widgets as $name => $widget){
        if(preg_match('/^wpcom_/i', $name)){
            $settings[$widget->id_base] = $widget->settings;
        }
    }
    $res = array('type' => 'widget');
    $res['ver'] = THEME_VERSION;
    $res['theme-id'] = THEME_ID;
    $res['settings'] = $settings;
    $res['pages'] = $wpcom_panel->get_all_pages();
    $res['framework_url'] = FRAMEWORK_URI;
    $res['framework_ver'] = FRAMEWORK_VERSION;
    $res['assets_ver'] = defined('ASSETS_VERSION')?ASSETS_VERSION:'';
    $res = apply_filters('wpcom_widget_panel_options', $res);
    return json_encode($res);
}

/**
 * Registers the 'core/legacy-widget' block.
 */
remove_action( 'init', 'register_block_core_legacy_widget' );
add_action( 'init', 'wpcom_register_block_core_legacy_widget' );
function wpcom_register_block_core_legacy_widget() {
    if(function_exists('render_block_core_legacy_widget')){
        register_block_type_from_metadata(
            ABSPATH . 'wp-includes/blocks/legacy-widget',
            array(
                'render_callback' => 'wpcom_render_block_core_legacy_widget',
            )
        );
    }
}

function wpcom_render_block_core_legacy_widget( $attributes ) {
    global $wp_widget_factory, $wp_registered_sidebars;

    if ( isset( $attributes['id'] ) ) {
        $sidebar_id = wp_find_widgets_sidebar( $attributes['id'] );
        return wp_render_widget( $attributes['id'], $sidebar_id );
    }

    if ( ! isset( $attributes['idBase'] ) ) {
        return '';
    }

    $id_base = $attributes['idBase'];
    if ( method_exists( $wp_widget_factory, 'get_widget_key' ) && method_exists( $wp_widget_factory, 'get_widget_object' ) ) {
        $widget_key    = $wp_widget_factory->get_widget_key( $id_base );
        $widget_object = $wp_widget_factory->get_widget_object( $id_base );
    } else {
        /*
         * This file is copied from the published @wordpress/widgets package when WordPress
         * Core is built. Because the package is a dependency of both WordPress Core and the
         * Gutenberg plugin where the block editor is developed, this fallback condition is
         * required until the minimum required version of WordPress for the plugin is raised
         * to 5.8.
         */
        $widget_key    = gutenberg_get_widget_key( $id_base );
        $widget_object = gutenberg_get_widget_object( $id_base );
    }

    if ( ! $widget_key || ! $widget_object ) {
        return '';
    }

    if ( isset( $attributes['instance']['encoded'], $attributes['instance']['hash'] ) ) {
        $serialized_instance = base64_decode( $attributes['instance']['encoded'] );
        if ( wp_hash( $serialized_instance ) !== $attributes['instance']['hash'] ) {
            return '';
        }
        $instance = unserialize( $serialized_instance );
    } else {
        $instance = array();
    }

    $args = array(
        'widget_id'   => $widget_object->id,
        'widget_name' => $widget_object->name,
    );

    // 主要基于系统默认代码增加了主题的标题标签支持
    if($wp_registered_sidebars && count($wp_registered_sidebars)){
        $_args = current($wp_registered_sidebars);
        $args = wp_parse_args( $args, array(
            'before_title' => isset($_args['before_title']) ? $_args['before_title'] : '',
            'after_title' => isset($_args['after_title']) ? $_args['after_title'] : ''
        ) );
    }

    ob_start();
    the_widget( $widget_key, $instance, $args );
    return ob_get_clean();
}

// 自定义预览页
remove_action( 'admin_init', 'handle_legacy_widget_preview_iframe', 20 );
add_action( 'admin_init', 'wpcom_widget_preview_iframe', 20 );
function wpcom_widget_preview_iframe() {
    if ( empty( $_GET['legacy-widget-preview'] ) ) {
        return;
    }

    if ( ! current_user_can( 'edit_theme_options' ) ) {
        return;
    }

    define( 'IFRAME_REQUEST', true );
    $priview_css = apply_filters('wpcom_widget_preview_style', '');
    ?>
    <!doctype html>
    <html <?php language_attributes(); ?>>
    <head>
        <meta charset="<?php bloginfo( 'charset' ); ?>" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link rel="profile" href="https://gmpg.org/xfn/11" />
        <?php wp_head(); ?>
        <style>
            /* Reset theme styles */
            html, body, #page, #content {
                padding: 0 !important;
                margin: 0 !important;
            }
            <?php echo $priview_css;?>
        </style>
    </head>
    <body <?php body_class('widget-priview'); ?>>
    <div id="wrap">
        <aside class="site-content">
            <?php
            $registry = WP_Block_Type_Registry::get_instance();
            $block    = $registry->get_registered( 'core/legacy-widget' );
            echo $block->render( $_GET['legacy-widget-preview'] );
            ?>
        </aside><!-- #sidebar -->
    </div><!-- #wrap -->
    <?php wp_footer(); ?>
    </body>
    </html>
    <?php

    exit;
}

add_filter('wpcom_show_action_tool', 'wpcom_hide_footer_bar');
function wpcom_hide_footer_bar($res){
    if(defined('IFRAME_REQUEST')){
        global $options;
        if(isset($options['footer_bar_icon'])) unset($options['footer_bar_icon']);
        if(isset($options['top_news'])) unset($options['top_news']);
        $res = false;
    }
    return $res;
}