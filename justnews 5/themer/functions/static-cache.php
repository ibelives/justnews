<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_Static_Cache{
    function __construct(){
        global $options;
        $this->folder = apply_filters('wpcom_static_cache_path', 'wpcom');
        add_action('wp_enqueue_scripts', array($this, 'enqueue_style'), 20);
        add_action('wpcom_static_cache_clear', array($this, 'cron'));
        $this->enable = false;
        if($this->folder && (!isset($options['css_cache']) || $options['css_cache']=='1')){
            $this->enable = true;
            add_action('wpcom_options_updated', array($this, 'rebuild'));
            add_action('wp_ajax_wpcom_ve_save', array($this, 'rebuild'), 1);
        }
    }
    function dir(){
        $_dir = _wp_upload_dir();
        if($this->folder){
            $dir = $_dir['basedir'] . '/' . $this->folder;
            if($a = wp_mkdir_p($dir)) {
                return $dir;
            }
        }
    }
    function url(){
        if($this->folder){
            $url = _wp_upload_dir();
            return $url['baseurl'] . '/' . $this->folder;
        }
    }
    function build_css(){
        $child = is_child_theme();
        $css = $child ? '/style.css' : '/css/style.css';
        $path = get_stylesheet_directory() . $css;
        $dir = $this->dir();
        $time = get_option('wpcom_static_time');
        if(!$time){
            $time = time();
            update_option('wpcom_static_time', $time);
        }
        $file = '/style.' . THEME_VERSION . '.' . $time . '.css';
        if( is_singular() && (is_page_template('page-home.php') || is_singular('page_module')) ) {
            global $post;
            $file = '/style.p' . $post->ID .'.'. THEME_VERSION . '.' . $time . '.css';
        }
        $build = 0;
        if(file_exists($dir . $file)){ // 缓存文件存在，比较下修改时间
            // css文件修改时间晚于缓存时间，则表示有修改，更新缓存文件
            if(filemtime($path) > filemtime($dir . $file)){
                $build = 1;
            }
        }else{ // 缓存文件不存在，则新建
            $build = 1;
        }
        if($build && file_exists($path)){
            $css_str = @file_get_contents($path);
            if($child){ // 处理子主题引用的父主题样式
                preg_match('/\@import\s+url\([\'"]?(\.\.\/([^\)\'"]+))[\'"]?/im', $css_str, $matches);
                if($matches && isset($matches[1])){
                    $_parent_theme = preg_replace('/^\.\./i', '', $matches[1]);
                    $parent_theme = get_theme_root() . $_parent_theme;
                    $parent_css_str = @file_get_contents($parent_theme);
                    preg_match('/\@import\s+url\([\'"]?\.\.\/[^\)\'"]+[\'"]?\);?/im', $css_str, $m);
                    if($m && isset($m[0]) && $m[0]){
                        $css_str = str_replace($m[0], $parent_css_str, $css_str);
                    }
                }
            }
            $css_str = $this->replace_images_path($css_str);
            $css_str .= apply_filters('wpcom_custom_css', '');
            if($dir) {
                $dest = $dir . $file;
                @file_put_contents($dest, $css_str);
                // 基于wp_handle_upload钩子，兼容云储存插件同步
                apply_filters(
                    'wp_handle_upload',
                    array(
                        'file'  => $dest,
                        'url'   => $this->url() . $file,
                        'type'  => 'text/css',
                        'error' => false,
                    ),
                    'sideload'
                );
            }
        }
        if(file_exists($dir . $file)){
            return $this->url() . $file;
        }
    }
    function replace_images_path($str){
        $url = get_theme_root_uri() . '/' . get_template();
        $str = str_replace('../images/', $url . '/images/', $str);
        return $str;
    }
    function enqueue_style(){
        if($this->enable && $this->dir() && $css = $this->build_css()){
            wp_deregister_style('stylesheet');
            $css = preg_replace('/^(http:|https:)/i', '', $css);
            wp_register_style('stylesheet', $css, array(), THEME_VERSION);
            do_action('wpcom_enqueue_cache_style');
        }else{
            add_action( 'wp_head', array($this, 'custom_css'), 20 );
        }
    }
    function custom_css(){
        $css = apply_filters('wpcom_custom_css', '');
        if($css) echo '<style>'.$css.'</style>' . "\r\n";
    }
    function rebuild(){
        delete_option('wpcom_static_time');
    }
    function cron(){
        require_once ABSPATH . 'wp-admin/includes/file.php';
        $dir = $this->dir();
        $files = list_files($dir, 1);
        if($files){
            foreach ($files as $file){
                // 删除超过30天的缓存文件
                if(time() - filemtime($file) > 2592000){
                    @unlink($file);
                }
            }
        }
    }
}