<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_Module{
    private $options;
    function __construct($id, $name, $options = array(), $icon='', $thumb = '', $cache = true) {
        $this->id = $id;
        $this->name = $name;
        $this->options = apply_filters('wpcom_module_options', $options, $id);
        $this->icon = $icon;
        $this->thumb = $thumb;
        $this->is_cache = $cache;
        $this->settings = array();
        add_action( 'init', array( $this, '_register_module' ) );

        add_action( 'save_post', array( $this, 'flush_module_cache' ) );
        add_action( 'deleted_post', array( $this, 'flush_module_cache' ) );
        add_action( 'switch_theme', array( $this, 'flush_module_cache' ) );
    }

    function value($key, $defalut = ''){
        $val = '';
        if(preg_match('/\./', $key)) $keys = explode('.', $key);
        if(isset($keys) && is_array($keys) && count($keys)>1){
            $v = $this->settings;
            foreach ($keys as $k){
                $v = isset($v[$k]) && $v[$k]!=='' ? $v[$k] : '';
            }
            $val = $v;
        }else{
            $val = isset($this->settings[$key]) && $this->settings[$key]!=='' ? $this->settings[$key] : '';
        }
        if($val==='') {
            if($defalut!==''){
                $val = $defalut;
            }else{
                $_val = $this->get_option_attr($key, 'value');
                $val = $_val !=='' ? $_val : $val;
            }
        }
        return $val;
    }

    function el(){
        return  '#modules-' . $this->value('modules-id');
    }

    private function get_option_attr($key, $attr, $options=''){
        $options = $options !== '' ? $options : $this->options;
        if($options && is_array($options) && $key) {
            foreach ($options as $k => $v) {
                if ($k === $key && isset($v[$attr])) {
                    return $v[$attr];
                } else if (isset($v['o']) || isset($v['options']) || isset($v['items'])) {
                    $ops = isset($v['o']) ? $v['o'] : (isset($v['items']) ? $v['items'] : $v['options']);
                    $res = $this->get_option_attr($key, $attr, $ops);
                    if($res!=='') return $res;
                } else if(is_numeric($k) && is_array($v)) {
                    $res = $this->get_option_attr($key, $attr, $v);
                    if($res!=='') return $res;
                }
            }
        }
        return '';
    }

    function render( $atts, $depth = 0 ){
        if ( $this->get_cached_module( $atts ) ) return;
        $this->settings = $atts;
        ob_start();

        $classes = 'modules-'.$this->id;
        $more_classes = $this->classes( $atts, $depth );
        $classes .= $more_classes ? ' ' . $more_classes : '';
        ?>
        <section class="section wpcom-modules <?php echo $classes;?>" id="modules-<?php echo $atts['modules-id'];?>" <?php echo $this->_style_inline($atts); echo $this->animate($atts); ?>>
            <?php $this->template($atts, $depth);?>
        </section>
        <?php echo $this->cache_module( $atts, ob_get_clean() );
    }

    function animate($atts){
        $animate = '';
        if(isset($atts['animate']) && $atts['animate']){
            $_animate = json_decode($atts['animate']);
            if(isset($_animate->animate) && $_animate->animate){
                $animate = ' data-aos="'.esc_attr($_animate->animate).'"';
                if(isset($_animate->easing) && $_animate->easing)
                    $animate .= ' data-aos-easing="'.esc_attr($_animate->easing).'"';
                if(isset($_animate->duration) && $_animate->duration)
                    $animate .= ' data-aos-duration="'.esc_attr($_animate->duration).'"';
            }
        }
        return $animate;
    }

    function _style_inline( $atts ){
        $style = '';
        $style_inline = $this->style_inline( $atts );
        if($style_inline) $style = 'style="'.$style_inline.'"';
        return $style;
    }

    function classes( $atts, $depth ){
        $classes = $depth==0 ? 'container' : '';
        return $classes;
    }

    function module_options( $modules ){
        $modules->{$this->id} = array(
            'name'  => $this->name,
            'icon'  => $this->icon,
            'thumb' => $this->thumb,
            'options' => $this->options
        );
        return $modules;
    }

    function _register_module(){
        add_action('wpcom_modules_'.$this->id, array( $this, 'render' ), 10, 2);
        add_filter('wpcom_modules', array( $this, 'module_options' ));
    }

    function template($atts, $depth){}
    function register_script(){}
    function get_style($atts){
        $this->settings = $atts;
        $style = $this->style($atts);

        $default = array();
        if($this->value('margin')!==''){
            $default['margin'] = array(
                '' => WPCOM::trbl($this->value('margin'), 'margin', $this->get_option_attr('margin', 'use'))
            );
            $default['margin_mobile'] = array(
                '@[(max-width: 767px)]' => WPCOM::trbl($this->value('margin_mobile'), 'margin', $this->get_option_attr('margin', 'use'))
            );
        }
        if($this->value('padding')!==''){
            $default['padding'] = array(
                '' => WPCOM::trbl($this->value('padding'), 'padding', $this->get_option_attr('padding', 'use'))
            );
            $default['padding_mobile'] = array(
                '@[(max-width: 767px)]' => WPCOM::trbl($this->value('padding_mobile'), 'padding', $this->get_option_attr('padding', 'use'))
            );
        }
        $default = apply_filters('wpcom_module_' . $this->id . '_default_style', $default, $atts);
        $style = ($default ?: array()) + (is_array($style) ? $style : array());

        if($style && is_array($style)){
            $css = array();

            // 梳理移动端样式
            $n_style = array();
            foreach ($style as $key => $_style){
                $n_style[$key] = $_style;
                if($_style && !preg_match('/_mobile$/i', $key) && !isset($style[$key.'_mobile']) && isset($this->settings[$key.'_mobile']) && $this->settings[$key.'_mobile']!==''){
                    $_n_style = array();
                    foreach ($_style as $_k => $v){
                        if(!preg_match('/^@\[/i', $_k)) {
                            $_k = preg_replace('/,\s*/i', ', @[(max-width: 767px)] ', $_k);
                            $_n_style['@[(max-width: 767px)] ' . $_k] = $v;
                        }
                    }
                    $n_style[$key.'_mobile'] = $_n_style;
                }
            }
            foreach ($n_style as $key => $_style){
                $value = $this->value($key);
                if($_style){
                    foreach ($_style as $k => $v){
                        $ks = explode(',', $k);
                        if($ks){
                            foreach ($ks as $_k){
                                $_k = trim($_k);
                                $css[$_k] = isset($css[$_k]) ? $css[$_k] : array();
                                if(is_string($value) && $value!=='') {
                                    $_css = str_replace('{{value}}', $value, $v);
                                    $_css = str_replace('{{color}}', 'color: '.$value, $_css);
                                    $_css = str_replace('{{background-color}}', 'background-color: '.$value, $_css);
                                    $_css = str_replace('{{border-color}}', 'border-color: '.$value, $_css);
                                    $css[$_k][] = $_css;
                                }else if($value){
                                    $css[$_k][] = $v;
                                }
                            }
                        }
                    }
                }
            }

            if($css){
                $str = '';
                foreach ($css as $dom => $rules){
                    if($rules){
                        $_dom = preg_replace(array('/^([^@]+)/i', '/^@\[([^]]+)\]/i'), array('{{el}} $1', '@media $1{{{el}}'), trim($dom));
                        $_dom = $_dom ?: '{{el}} ';
                        $_dom = str_replace('{{el}}', $this->el(), trim($_dom));
                        $str .= $_dom . '{';
                        foreach ($rules as $rule) {
                            if($rule!=='') $str .= $rule;
                        }
                        if(preg_match('/^@\[/i', $dom)) $str .= '}';
                        $str .= '}' . "\r\n";
                    }
                }
            }
            echo $str;
        }
    }
    function style($atts){ return array();}
    function style_inline($atts){}

    public function get_cached_module( $args ) {
        global $options;
        if( !$this->is_cache || wp_script_is('themer-customizer') || (isset($options['enable_cache']) && $options['enable_cache']=='0') ) return false;
        $this->object_id = get_queried_object_id();
        if(!$this->object_id) return false;

        $cache = wp_cache_get( $this->get_cache_id( $this->object_id ), 'module' );

        if ( ! is_array( $cache ) ) {
            $cache = array();
        }

        if ( isset( $cache[ $args['modules-id'] ] ) ) {
            echo $cache[ $args['modules-id'] ];
            return true;
        }

        return false;
    }

    public function cache_module( $args, $content ) {
        if( !$this->is_cache || wp_script_is('themer-customizer') ) return $content;
        $this->object_id = get_queried_object_id();
        $cache = wp_cache_get( $this->get_cache_id( $this->object_id ), 'module' );
        if ( ! is_array( $cache ) ) {
            $cache = array();
        }
        $cache[ $args['modules-id'] ] = $content;
        wp_cache_set( $this->get_cache_id( $this->object_id ), $cache, 'module' );
        return $content;
    }

    public function flush_module_cache() {
        global $is_flush_module_cache;
        if($is_flush_module_cache) return false;

        $pages = get_pages(array(
            'meta_key' => '_wp_page_template',
            'meta_value' => 'page-home.php'
        ));

        if($pages){
            foreach ($pages as $page) {
                wp_cache_delete( $this->get_cache_id( $page->ID, 'http' ), 'module' );
                wp_cache_delete( $this->get_cache_id( $page->ID, 'https' ), 'module' );
            }
        }
        $is_flush_module_cache = 1;
    }

    protected function get_cache_id( $object_id, $scheme = '' ) {
        if ( $scheme ) {
            $cache_id = $object_id . '-' . $scheme;
        } else {
            $cache_id = $object_id . '-' . ( is_ssl() ? 'https' : 'http' );
        }

        return apply_filters( 'wpcom_module_cached_id', $cache_id );
    }
}

if( !function_exists('register_module') ){
    function register_module( $module_class ){
        global $_wpcom_modules;
        if(!isset($_wpcom_modules)) $_wpcom_modules = array();
        $_wpcom_modules[] = $module_class;
    }

    add_action('after_setup_theme', 'wpcom_init_module');
    function wpcom_init_module(){
        global $_wpcom_modules, $wpcom_modules;
        if(!isset($wpcom_modules)) $wpcom_modules = array();
        if($_wpcom_modules && is_array($_wpcom_modules)){
            foreach ($_wpcom_modules as $module){
                $_module = new $module();
                $wpcom_modules[$_module->id] = $_module;
            }
        }
    }
}

add_action('init', 'wpcom_my_module_list');
function wpcom_my_module_list(){
    $labels = array(
        'name' => '我的模块',
        'singular_name' => '我的模块',
        'edit_item' => '编辑模块',
        'search_items' => '搜索',
        'not_found' => '暂无模块',
        'not_found_in_trash' => '回收站为空'
    );
    $args = array(
        'labels' => $labels,
        'public' => current_user_can( 'customize' ) ? true : false,
        'show_ui' => true,
        'show_in_menu' => false,
        'show_in_nav_menus' => false,
        'capability_type' => 'post',
        'hierarchical' => false,
        'show_in_rest' => false,
        'exclude_from_search' => true,
        'supports' => array('title', 'excerpt')
    );
    register_post_type('page_module', $args);
}

add_action( 'init', 'wpcom_page_module_rewrite' );
function wpcom_page_module_rewrite() {
    global $wp_rewrite, $permalink_structure;
    if(!isset($permalink_structure)) $permalink_structure = get_option('permalink_structure');
    if($permalink_structure){
        $queryarg = 'post_type=page_module&p=';
        $wp_rewrite->add_rewrite_tag( '%mid%', '([^/]+)', $queryarg );
        $wp_rewrite->add_permastruct( 'page_module', 'page_module/%mid%', false );
    }
}

add_filter('post_type_link', 'wpcom_page_module_permalink', 5, 2);
function wpcom_page_module_permalink( $post_link, $id ) {
    global $wp_rewrite, $permalink_structure;
    if(!isset($permalink_structure)) $permalink_structure = get_option('permalink_structure');
    if($permalink_structure) {
        $post = get_post($id);
        if (!is_wp_error($post) && $post->post_type == 'page_module') {
            $newlink = $wp_rewrite->get_extra_permastruct('page_module');
            $newlink = str_replace('%mid%', $post->ID, $newlink);
            $newlink = home_url(untrailingslashit($newlink));
            return $newlink;
        }
    }
    return $post_link;
}

add_filter('template_include', 'wpcom_template_include');
function wpcom_template_include($template){
    if(is_singular('page_module')) $template = get_query_template('page-home');
    return $template;
}

add_action( 'admin_menu', 'wpcom_my_module_menu' );
function wpcom_my_module_menu(){
    global $wpcom_panel;
    $enable = apply_filters('wpcom_show_my_module', true);
    $config = $wpcom_panel->get_demo_config();
    if($enable && !empty($config)) {
        add_submenu_page('wpcom-panel', '我的模块', '我的模块', 'edit_theme_options', 'edit.php?post_type=page_module', null);
    }
}

add_filter( 'manage_page_module_posts_columns', 'wpcom_my_module_posts_columns' );
add_action( 'manage_page_module_posts_custom_column' , 'wpcom_my_module_posts_custom_column', 10, 2 );
function wpcom_my_module_posts_columns($columns){
    $columns = array(
        'cb' => '<input type="checkbox" />',
        'title' => '模块标题',
        'desc' => '模块简介',
        'date' => '添加时间'
    );
    return $columns;
}
function wpcom_my_module_posts_custom_column( $column, $post_id ){
    if($column == 'desc'){
        echo get_the_excerpt($post_id);
    }
}

add_filter( 'page_row_actions', 'wpcom_ve_row_actions', 10, 2 );
add_filter( 'post_row_actions', 'wpcom_ve_row_actions', 10, 2 );
function wpcom_ve_row_actions($actions, $post){
    if(!current_user_can( 'customize' )) return $actions;
    if($post->post_type == 'page_module') {
        $edit = preg_replace('/>([^<]+)<\/a>/i', '/>编辑模块</a>', $actions['edit']);
        $editor_url = add_query_arg(array('visual-editor' => 'true'), get_edit_post_link($post->ID));

        $actions = array(
            'edit' => $edit,
            'customize' => '<a href="' . $editor_url . '" target="_blank">可视化编辑</a>',
            'trash' => $actions['trash'],
        );
    }else if(get_page_template_slug($post->ID) == 'page-home.php'){
        $editor_url = add_query_arg(array('visual-editor' => 'true'), get_edit_post_link($post->ID));
        $_actions = array();
        foreach ($actions as $k => $v){
            $_actions[$k] = $v;
            if($k=='edit'){
                $_actions['customize'] = '<a style="font-weight: bold;" href="' . $editor_url . '" target="_blank">可视化编辑</a>';
            }
        }
        $actions = $_actions;
    }
    return $actions;
}

add_filter( 'parent_file', 'wpcom_my_module_parent_file' );
function wpcom_my_module_parent_file( $parent_file='' ){
    global $pagenow, $post;
    if ( $post && !empty($_GET['action']) && ($_GET['action'] == 'edit') && $pagenow == 'post.php' && $post->post_type == 'page_module' ) {
        $parent_file = 'wpcom-panel';
    }
    return $parent_file;
}