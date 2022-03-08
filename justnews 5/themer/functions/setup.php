<?php
defined( 'ABSPATH' ) || exit;

// wpcom setup
add_action('after_setup_theme', 'wpcom_setup');
if ( ! function_exists( 'wpcom_setup' ) ) :
    function wpcom_setup() {
        global $options;
        /**
         * Add text domain
         */
        load_theme_textdomain('wpcom', get_template_directory() . '/lang');
        if( is_child_theme() ) load_theme_textdomain('wpcom', get_stylesheet_directory() . '/lang');

        add_theme_support( 'woocommerce', array(
            'thumbnail_image_width' => 480,
            'single_image_width' => 800
        ) );

        add_theme_support( 'html5', array(
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
        ) );

        // gutenberg 兼容
        if( function_exists('gutenberg_init') ) {
            add_theme_support( 'wp-block-styles' );
        }
        WPCOM_Session::session_prefix();
        new WPCOM_Static_Cache();

        // 缩略图设置
        add_theme_support( 'post-thumbnails' );
        $sizes = apply_filters('wpcom_image_sizes', array());
        if( isset($sizes['post-thumbnail']) && $sizes['post-thumbnail'] )
            set_post_thumbnail_size( $sizes['post-thumbnail']['width'], $sizes['post-thumbnail']['height'], true );

        // 允许添加友情链接
        add_filter( 'pre_option_link_manager_enabled', '__return_true' );

        // This theme uses wp_nav_menu() in two locations.
        register_nav_menus( apply_filters( 'wpcom_menus', array() ));

        // 切换经典小工具
        if(isset($options['classic_widgets']) && $options['classic_widgets']) {
            add_filter('gutenberg_use_widgets_block_editor', '__return_false');
            add_filter('use_widgets_block_editor', '__return_false');
        }

        if(isset($options['enable_cache']) && $options['enable_cache']=='1'){
            require_once FRAMEWORK_PATH . '/includes/object-cache.php';
            new WPCOM_Object_Cache();
        }

        if(isset($options['filter_item_id']) && !empty($options['filter_item_id']) && file_exists(FRAMEWORK_PATH . '/includes/multi-filter.php')){
            require_once FRAMEWORK_PATH . '/includes/multi-filter.php';
            new WPCOM_Multi_Filter();
        }

        if(isset($options['wx_appid']) && $options['wx_appid'] && $options['wx_appsecret']) {
            require_once FRAMEWORK_PATH . '/includes/wx-share.php';
            new WX_share();
        }

        if( isset($options['member_enable']) && $options['member_enable']=='1' ) {
            include_once FRAMEWORK_PATH . '/member/init.php';

            if( isset($options['member_follow']) && $options['member_follow']=='1' && file_exists(FRAMEWORK_PATH . '/includes/follow.php') ) {
                require_once FRAMEWORK_PATH . '/includes/follow.php';
                new WPCOM_Follow();
            }

            if( isset($options['member_messages']) && $options['member_messages']=='1' && file_exists(FRAMEWORK_PATH . '/includes/messages.php') ) {
                require_once FRAMEWORK_PATH . '/includes/messages.php';
                new WPCOM_Messages();
            }

            if( isset($options['member_notify']) && $options['member_notify']=='1' && file_exists(FRAMEWORK_PATH . '/includes/notifications.php') ) {
                require_once FRAMEWORK_PATH . '/includes/notifications.php';
                $GLOBALS['_notification'] = new WPCOM_Notifications();
            }

            if( isset($options['user_card']) && $options['user_card']=='1' && file_exists(FRAMEWORK_PATH . '/includes/user-card.php') ) {
                require_once FRAMEWORK_PATH . '/includes/user-card.php';
                new WPCOM_User_Card();
            }
        }

        remove_action( 'wp_head', 'rel_canonical' );
        remove_action( 'wp_head', 'wp_generator' );
        remove_action( 'wp_head', 'wp_shortlink_wp_head' );
        remove_action( 'wp_head', 'feed_links_extra', 3 );
        remove_action( 'wp_head', 'feed_links', 2 );
        remove_filter( 'wp_robots', 'wp_robots_max_image_preview_large' );
        add_filter( 'revslider_meta_generator', '__return_false' );
        add_filter( 'wp_lazy_loading_enabled', '__return_false' );
        add_filter( 'wp_calculate_image_srcset', '__return_false', 99999 );
        add_filter( 'rss_widget_feed_link', '__return_false' );

        if( !isset($options['disable_rest']) || (isset($options['disable_rest']) && $options['disable_rest']=='1')) {
            remove_action('wp_head', 'rest_output_link_wp_head', 10);
            remove_action('wp_head', 'wp_oembed_add_discovery_links', 10);
            remove_action( 'template_redirect', 'rest_output_link_header', 11);
        }

        if( !isset($options['disable_emoji']) || (isset($options['disable_emoji']) && $options['disable_emoji']=='1')) {
            global $wpsmiliestrans;
            $wpsmiliestrans = array(); // 禁用系统表情转换
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('admin_print_scripts', 'print_emoji_detection_script');
            remove_action('wp_print_styles', 'print_emoji_styles');
            remove_action('admin_print_styles', 'print_emoji_styles');
            remove_filter('the_content_feed', 'wp_staticize_emoji');
            remove_filter('comment_text_rss', 'wp_staticize_emoji');
            remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
            add_filter( 'tiny_mce_plugins', 'wpcom_disable_emojis_tinymce' );
            add_filter( 'emoji_svg_url', '__return_false' );
        }

        if(is_admin()){
            require_once FRAMEWORK_PATH . '/includes/plugin-activation.php';
            require_once FRAMEWORK_PATH . '/includes/term-meta.php';
            require_once FRAMEWORK_PATH . '/includes/importer.php';
            new WPCOM_DEMO_Importer();
        }
    }
endif;

add_filter( 'upload_mimes', 'wpcom_mime_types' );
function wpcom_mime_types( $mimes = array() ){
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}

add_filter( 'wp_check_filetype_and_ext', 'wpcom_svgs_upload_check', 10, 4 );
function wpcom_svgs_upload_check( $checked, $file, $filename, $mimes ) {
    if ( ! $checked['type'] ) {
        $check_filetype		= wp_check_filetype( $filename, $mimes );
        $ext				= $check_filetype['ext'];
        $type				= $check_filetype['type'];
        $proper_filename	= $filename;

        if ( $type && 0 === strpos( $type, 'image/' ) && $ext !== 'svg' ) {
            $ext = $type = false;
        }

        $checked = compact( 'ext','type','proper_filename' );
    }
    return $checked;
}

add_action( 'admin_init', 'wpcom_admin_setup' );
function wpcom_admin_setup() {
    global $pagenow;
    if( $pagenow == 'post.php' || $pagenow == 'post-new.php' || $pagenow == 'admin-ajax.php' ){
        require_once FRAMEWORK_PATH . '/includes/shortcodes.php';
        new WPCOM_Shortcodes();
    }
    require_once FRAMEWORK_PATH . '/includes/meta-box.php';
    new WPCOM_Meta();
    if( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) {
        if( file_exists( get_template_directory() . '/css/editor-style.css' ) )
            add_editor_style( 'css/editor-style.css' );
    }
    if (!wp_next_scheduled ( 'wpcom_sessions_clear' )) wp_schedule_event(time(), 'hourly', 'wpcom_sessions_clear');
    if (!wp_next_scheduled ( 'wpcom_static_cache_clear' )) wp_schedule_event(time(), 'twicedaily', 'wpcom_static_cache_clear');
}

add_action( 'wpcom_sessions_clear', array( 'WPCOM_Session', 'cron') );

add_filter( 'wpcom_image_sizes', 'wpcom_image_sizes' );
function wpcom_image_sizes($image_sizes){
    global $options, $_wp_additional_image_sizes;

    if( !empty($_wp_additional_image_sizes) ) {
        foreach ($_wp_additional_image_sizes as $sk => $size) {
            if( $sk =='shop_single' || $sk =='woocommerce_single' ) $size['crop'] = 1;
            if (isset($size['crop']) && $size['crop'] == 1) {
                $image_sizes[$sk] = $size;
            }
        }
    }
    $image_sizes['post-thumbnail'] = array(
        'width' => intval(isset($options['thumb_width']) && $options['thumb_width'] ? $options['thumb_width'] : 480),
        'height' => intval(isset($options['thumb_height']) && $options['thumb_height'] ? $options['thumb_height'] : 320)
    );
    $image_sizes['default'] = array(
        'width' => intval(isset($options['thumb_default_width']) && $options['thumb_default_width'] ? $options['thumb_default_width'] : 480),
        'height' => intval(isset($options['thumb_default_height']) && $options['thumb_default_height'] ? $options['thumb_default_height'] : 320)
    );
    return $image_sizes;
}

// 加载静态资源
add_action('wp_enqueue_scripts', 'wpcom_register_scripts', 1);
add_action('admin_enqueue_scripts', 'wpcom_register_scripts', 1);
function wpcom_register_scripts(){
    global $options, $wp_version;
    $action = current_filter();
    $static_cdn = isset($options['static_cdn']) && $options['static_cdn'] == '1';
    if($action==='wp_enqueue_scripts' && !defined('IFRAME_REQUEST')){
        // WP 5.6及以上版本使用3.5.1版本jQuery
        $jquery_ver = version_compare($wp_version,'5.6', '>=') ? '3.6.0' : '1.12.4';
        if($static_cdn){
            $jquery = 'https://cdn.jsdelivr.net/npm/jquery@'.$jquery_ver;
        }else{
            $jquery = FRAMEWORK_URI . '/assets/js/jquery-' . $jquery_ver . '.min.js';
        }
        wp_deregister_script('jquery-core');
        wp_register_script('jquery-core', $jquery, array(), $jquery_ver);
    }
    if(isset($options['iconfont']) && $options['iconfont']) wp_register_script('iconfont', $options['iconfont'], array(), THEME_VERSION);
    wp_register_style('material-icons', FRAMEWORK_URI . '/assets/css/material-icons'.($static_cdn?'.cdn':'').'.css', false, THEME_VERSION);
    wp_register_style('font-awesome', FRAMEWORK_URI . '/assets/css/font-awesome'.($static_cdn?'.cdn':'').'.css', false, THEME_VERSION);
    $remixicon = $static_cdn ? 'https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.min.css' : FRAMEWORK_URI . '/assets/css/remixicon-2.5.0.min.css';
    wp_register_style('remixicon', $remixicon, false, '2.5.0');
    $icons = $static_cdn ? 'https://cdn.jsdelivr.net/gh/wpcom-cn/assets'.(defined('ASSETS_VERSION')?ASSETS_VERSION:'').'/fonts/icons-2.6.18.js' : FRAMEWORK_URI . '/assets/js/icons-2.6.18.js';
    wp_register_script('wpcom-icons', $icons, array(), THEME_VERSION);
}

if ( ! function_exists( 'wpcom_scripts' ) ) :
    function wpcom_scripts() {
        global $options;
        // 载入主样式
        $css = is_child_theme() ? '/style.css' : '/css/style.css';
        wp_register_style('stylesheet', get_stylesheet_directory_uri() . $css, array(), THEME_VERSION);
        wp_enqueue_style('stylesheet');
        if(isset($options['material_icons']) && $options['material_icons']) wp_enqueue_style('material-icons');
        if(isset($options['remixicon']) && $options['remixicon']) wp_enqueue_style('remixicon');
        if((isset($options['fontawesome']) && $options['fontawesome']) || !isset($options['fontawesome'])) wp_enqueue_style('font-awesome');
        // wp_enqueue_style('animate', '//s2.pstatp.com/cdn/expire-1-M/aos/3.0.0-beta.6/aos.css', array(), THEME_VERSION);

        // 载入js文件
        // wp_enqueue_script('aos', '//s0.pstatp.com/cdn/expire-1-M/aos/3.0.0-beta.6/aos.js', array(), THEME_VERSION, true);
        wp_enqueue_script('main', get_template_directory_uri() . '/js/main.js', array( 'jquery' ), THEME_VERSION, true);
        wp_enqueue_script('wpcom-icons');
        if(isset($options['iconfont']) && $options['iconfont']) wp_enqueue_script('iconfont');

        // wpcom_localize_script
        $webp = isset($options['webp_suffix']) && $options['webp_suffix'] ? $options['webp_suffix'] : '';
        $script = array(
            'webp' => $webp,
            'ajaxurl' => admin_url( 'admin-ajax.php'),
            'theme_url' => get_template_directory_uri(),
            'slide_speed' => isset($options['slide_speed']) ? $options['slide_speed']: '',
            'static_cdn' => isset($options['static_cdn']) ? $options['static_cdn']: 0,
            'is_admin' => current_user_can('manage_options') ? '1' : '0'
        );
        if(isset($options['sl_wechat_follow']) && $options['sl_wechat_follow']){
            foreach ($options['sl_wechat_follow'] as $f){
                if($f) {
                    $script['wechat_follow'] = 1;
                    break;
                }
            }
        }
        if(!is_dir(get_template_directory() . '/themer')) $script['framework_url'] = FRAMEWORK_URI;
        if( is_singular() && (!isset($options['post_img_lightbox']) || $options['post_img_lightbox']=='1') ) {
            $script['lightbox'] = 1;
        }
        if(is_singular()) $script['post_id'] = get_queried_object_id();
        if(isset($options['user_card']) && $options['user_card']=='1'){
            $script['user_card_height'] = 356;
            if(!$options['member_follow'] && !$options['member_messages']){
                $script['user_card_height'] = 308;
            }
        }
        $wpcom_js = apply_filters('wpcom_localize_script', $script);
        wp_localize_script( 'main', '_wpcom_js', $wpcom_js );

        if ( is_singular() && isset($options['comments_open']) && $options['comments_open']=='1' && comments_open() && get_option('thread_comments')) {
            wp_enqueue_script('comment-reply');
        }
    }
endif;
add_action('wp_enqueue_scripts', 'wpcom_scripts', 2);
/* 静态资源结束 */

// Excerpt more
add_filter('excerpt_more', 'wpcom_excerpt_more');
if ( ! function_exists( 'wpcom_excerpt_more' ) ) :
    function wpcom_excerpt_more( $more ) {
        return '...';
    }
endif;

add_filter('comment_excerpt_length', 'wpcom_comment_excerpt_length');
function wpcom_comment_excerpt_length(){
    return 150;
}

add_filter( 'body_class', 'wpcom_body_class', 10);
function wpcom_body_class( $classes ){
    if( is_page() ){
        global $post;
        $sidebar = get_post_meta( $post->ID, 'wpcom_sidebar', true );
        $sidebar = !(!$sidebar && $sidebar!=='');
        if(!$sidebar) $classes[] = 'page-no-sidebar';
    }
    $lang = get_locale();
    if($lang == 'zh_CN' || $lang == 'zh_TW' || $lang == 'zh_HK') {
        $classes[] = 'lang-cn';
    } else {
        $classes[] = 'lang-other';
    }
    return $classes;
}

if ( ! function_exists( 'wpcom_disable_emojis_tinymce' ) ) :
    function wpcom_disable_emojis_tinymce( $plugins ) {
        if ( is_array( $plugins ) ) {
            return array_diff( $plugins, array( 'wpemoji' ) );
        } else {
            return array();
        }
    }
endif;

if ( ! function_exists( 'utf8_excerpt' ) ) :
    function utf8_excerpt($str, $len){
        $str = strip_tags( str_replace( array( "\n", "\r" ), ' ', $str ) );
        if(function_exists('mb_substr')){
            $excerpt = mb_substr($str, 0, $len, 'utf-8');
        }else{
            preg_match_all("/[x01-x7f]|[xc2-xdf][x80-xbf]|xe0[xa0-xbf][x80-xbf]|[xe1-xef][x80-xbf][x80-xbf]|xf0[x90-xbf][x80-xbf][x80-xbf]|[xf1-xf7][x80-xbf][x80-xbf][x80-xbf]/", $str, $ar);
            $excerpt = join('', array_slice($ar[0], 0, $len));
        }

        if(trim($str)!=trim($excerpt)){
            $excerpt .= '...';
        }
        return $excerpt;
    }
endif;

// JSON_LD数据
add_action( 'wp_footer', 'wpcom_baidu_xzh', 50);
function wpcom_baidu_xzh(){
    if ( ! is_singular() || is_attachment() || is_front_page() ) return; ?>
    <script type="application/ld+json">
        {
            "@context": {
                "@context": {
                    "images": {
                      "@id": "http://schema.org/image",
                      "@type": "@id",
                      "@container": "@list"
                    },
                    "title": "http://schema.org/headline",
                    "description": "http://schema.org/description",
                    "pubDate": "http://schema.org/DateTime"
                }
            },
            "@id": "<?php the_permalink();?>",
            "title": "<?php the_title();?>",
            "images": <?php echo wpcom_bdxzh_imgs();?>,
            "description": "<?php echo utf8_excerpt(strip_tags(get_the_excerpt()), 120);?>",
            "pubDate": "<?php the_time('Y-m-d\TH:i:s');?>",
            "upDate": "<?php the_modified_time('Y-m-d\TH:i:s');?>"
        }
    </script>
<?php }

function wpcom_bdxzh_imgs(){
    global $post;
    $imgs = '[]';

    preg_match_all('/<img[^>]*src=[\'"]([^\'"]+)[\'"].*>/iU', $post->post_content, $matches, PREG_PATTERN_ORDER);

    if(isset($matches[1]) && isset($matches[1][2])){ // 有3张图片
        for($i=0;$i<3;$i++){
            if(preg_match('/^\/\//i', $matches[1][$i])) $matches[1][$i] = 'http:' . $matches[1][$i];
        }
        $imgs = '["'.$matches[1][0].'","'.$matches[1][1].'","'.$matches[1][2].'"]';
    }else if($img_url = (isset($GLOBALS['post-thumb']) ? $GLOBALS['post-thumb'] : WPCOM::thumbnail_url($post->ID)) ){
        if(preg_match('/^\/\//i', $img_url)) $img_url = 'http:' . $img_url;
        $imgs = '["'.$img_url.'"]';
    }
    return $imgs;
}

add_action( 'transition_post_status', 'wpcom_baidu_pre_submit', 10, 3 );
function wpcom_baidu_pre_submit( $new_status, $old_status, $post ){
    if( $new_status!='publish' || $new_status==$old_status || ($post->post_type!='post' && $post->post_type!='product') ) return false;
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return false;
    global $options;
    if(isset($post->ID) && ( (isset($options['zz-submit']) && $options['zz-submit']) || (isset($options['ks-submit']) && $options['ks-submit']) ) ){
        global $_pre_submit;
        $_pre_submit = $post->ID;
    }
}

add_action( 'wp_insert_post', 'wpcom_baidu_submit', 50, 2 );
function wpcom_baidu_submit($post_ID, $post){
    global $_pre_submit, $options;
    if(isset($_pre_submit) && $post->post_status=='publish' && $_pre_submit==$post_ID){
        $zz_url = isset($options['zz-submit']) && $options['zz-submit'] ? $options['zz-submit'] : '';
        $ks_url = isset($options['ks-submit']) && $options['ks-submit'] ? $options['ks-submit'] : '';
        $post_url = get_permalink($post_ID);
        if($zz_url){
            $post1 = wp_remote_post(str_replace(' ', '', $zz_url), array(
                    'method' => 'POST',
                    'timeout' => 15,
                    'headers' => array('Content-Type: text/plain'),
                    'body' => $post_url
                )
            );
            wpcom_add_log($post_url . ' - ' . wp_json_encode($post1));
        }
        if($ks_url){
            $post2 = wp_remote_post(str_replace(' ', '', $ks_url), array(
                    'method' => 'POST',
                    'timeout' => 15,
                    'headers' => array('Content-Type: text/plain'),
                    'body' => $post_url
                )
            );
            wpcom_add_log($post_url . ' - ' . wp_json_encode($post2));
        }
    }
}

function wpcom_add_log($msg){
    $_dir = _wp_upload_dir();
    $folder = apply_filters('wpcom_static_cache_path', 'wpcom');
    $dir = $_dir['basedir'] . '/' . $folder;
    if(wp_mkdir_p($dir)) {
        @file_put_contents($dir . '/log-' . date('Ym') . '.log', '['.date('Y-m-d H:i:s') . ']: ' . $msg . "\r\n", FILE_APPEND);
    }
}

add_filter( 'mce_buttons_2', 'wpcom_mce_wp_page' );
function wpcom_mce_wp_page( $buttons ) {
    $buttons[] = 'wp_page';
    return $buttons;
}

add_filter( 'mce_buttons', 'wpcom_mce_buttons', 20 );
function wpcom_mce_buttons( $buttons ) {
    $res = array();
    foreach( $buttons as $bt ) {
        $res[] = $bt;
        if( $bt=='formatselect' && !in_array( 'fontsizeselect', $buttons ) ){
            $res[] = 'fontsizeselect';
        } else if( $bt=='link' && !in_array( 'unlink', $buttons ) ){
            $res[] = 'unlink';
        }
    }
    return $res;
}

add_filter( 'tiny_mce_before_init', 'wpcom_mce_text_sizes' );
function wpcom_mce_text_sizes( $initArray ){
    $initArray['fontsize_formats'] = "10px 12px 14px 16px 18px 20px 24px 28px 32px 36px 42px";
    return $initArray;
}

// 控制边栏标签云
add_filter('widget_tag_cloud_args', 'wpcom_tag_cloud_filter', 10);
function wpcom_tag_cloud_filter($args = array()) {
    global $options;
    $args['number'] = isset($options['tag_cloud_num']) && $options['tag_cloud_num'] ? $options['tag_cloud_num'] : 30;
    // $args['orderby'] = 'count';
    // $args['order'] = 'RAND';
    return $args;
}

add_filter( 'pre_update_option_sticky_posts', 'wpcom_fix_sticky_posts' );
if ( ! function_exists( 'wpcom_fix_sticky_posts' ) ) :
    function wpcom_fix_sticky_posts( $stickies ) {
        if( !class_exists('SCPO_Engine') ) {
            global $wpdb;
            $menu_order = 1;
            $count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts WHERE `post_type` = 'post' AND `menu_order` not IN (0,1)" );
            if( $count>0 ) {
                // 先预处理防止插件设置的menu_order，主要是SCPOrder插件
                $wpdb->update($wpdb->posts, array('menu_order' => 0), array('post_type' => 'post'));
            }
        }else{
            $menu_order = -1;
        }

        $old_stickies = array_diff( get_option( 'sticky_posts' ), $stickies );
        foreach( $stickies as $sticky )
            wp_update_post( array( 'ID' => $sticky, 'menu_order' => $menu_order ) );
        foreach( $old_stickies as $sticky )
            wp_update_post( array( 'ID' => $sticky, 'menu_order' => 0 ) );

        return $stickies;
    }
endif;

if ( ! function_exists( 'wpcom_sticky_posts_query' ) && !class_exists('SCPO_Engine') ) :
    add_action( 'pre_get_posts', 'wpcom_sticky_posts_query', 20 );
    function wpcom_sticky_posts_query( $q ) {
        if( $q->get('post_type') != 'post' ) return $q;

        if( !isset( $q->query_vars[ 'ignore_sticky_posts' ] ) ){
            $q->query_vars[ 'ignore_sticky_posts' ] = 1;
        }
        if ( isset( $q->query_vars[ 'ignore_sticky_posts' ] ) && !$q->query_vars[ 'ignore_sticky_posts' ] ){
            $q->query_vars[ 'ignore_sticky_posts' ] = 1;
            if(isset($q->query_vars[ 'orderby' ]) && $q->query_vars[ 'orderby' ]) {
                $q->query_vars[ 'orderby' ] .= ' menu_order';
            }else{
                $q->query_vars[ 'orderby' ] = 'menu_order date';
            }
        }
        return $q;
    }
endif;

add_filter('wp_handle_upload_prefilter','wpcom_file_upload_rename', 10);
if ( ! function_exists( 'wpcom_file_upload_rename' ) ) :
function wpcom_file_upload_rename( $file ) {
    global $options;
    if(isset($options['file_upload_rename']) && $options['file_upload_rename']) {
        $file['name'] = preg_replace('/\s/', '-', $file['name']);
        if ($options['file_upload_rename']=='2' || ($options['file_upload_rename']=='1' && !preg_match('/^[0-9_a-zA-Z!@()+-.]+$/u', $file['name']))) {
            $ext = substr(strrchr($file['name'], '.'), 1);
            $file['name'] = date('YmdHis') . rand(10, 99) . '.' . $ext;
        }
    }
    return $file;
}
endif;

// 安装依赖插件
function wpcom_register_required_plugins() {
    $config = array(
        'id'           => 'wpcom',
        'default_path' => '',
        'menu'         => 'wpcom-install-plugins',
        'parent_slug'  => 'wpcom-panel',
        'capability'   => 'edit_theme_options',
        'has_notices'  => true,
        'dismissable'  => true,
        'dismiss_msg'  => '',
        'is_automatic' => false
    );

    tgmpa( $config );
}

add_action( 'tgmpa_register', 'wpcom_register_required_plugins' );

function wpcom_tgm_show_admin_notice_capability() {
    return 'edit_theme_options';
}
add_filter( 'tgmpa_show_admin_notice_capability', 'wpcom_tgm_show_admin_notice_capability' );

function wpcom_lazyimg( $img, $alt, $width='', $height='', $class='' ){
    global $options;
    $class_html = $class ? ' class="'.$class.'"' : '';
    $size = $width ? ' width="'.intval($width).'"' : '';
    $size .= $height ? ' height="'.intval($height).'"' : '';
    if( isset($options['thumb_img_lazyload']) && $options['thumb_img_lazyload']=='1' && !is_embed() && !preg_match('/^data:image\//i', $img)){
        $class_html = $class ? ' class="j-lazy '.$class.'"' : ' class="j-lazy"';
        $lazy_img = isset($options['lazyload_img']) && $options['lazyload_img'] ? (is_numeric($options['lazyload_img']) ? wp_get_attachment_url($options['lazyload_img']) : $options['lazyload_img']) : FRAMEWORK_URI.'/assets/images/lazy.png';
        $html = '<img'.$class_html.' src="'.$lazy_img.'" data-original="'.esc_url($img).'" alt="'.esc_attr($alt).'"'.$size.'>';
    }else{
        $html = '<img'.$class_html.' src="'.(preg_match('/^data:image\//i', $img) ? $img : esc_url($img)).'" alt="'.esc_attr($alt).'"'.$size.'>';
    }
    return $html;
}

function wpcom_lazybg( $img, $class='', $style='' ){
    global $options;
    if( isset($options['thumb_img_lazyload']) && $options['thumb_img_lazyload']=='1' && !is_embed() && !preg_match('/^data:image\//i', $img) ){
        $lazy_img = isset($options['lazyload_img']) && $options['lazyload_img'] ? (is_numeric($options['lazyload_img']) ? wp_get_attachment_url($options['lazyload_img']) : $options['lazyload_img']) : FRAMEWORK_URI.'/assets/images/lazy.png';
        $attr = 'class="'.$class.' j-lazy" style="background-image: url('.$lazy_img.');'.$style.'" data-original="'.esc_url($img).'"';
    }else{
        $attr = 'class="'.$class.'" style="background-image: url('.$img.');'.$style.'"';
    }
    return $attr;
}

add_filter( 'wpcom_sidebars', 'wp_no_sidebar' );
function wp_no_sidebar( $sidebar ){
    $sidebar['0'] = '不显示边栏';
    return $sidebar;
}

add_filter( 'wp_video_shortcode_class', 'wpcom_video_shortcode_class' );
function wpcom_video_shortcode_class($class){
    $class = str_replace('wp-video-shortcode', '', $class);
    $class .= ' j-wpcom-video';
    return trim($class);
}

add_action('wp_head', 'wpcom_head_code', 10);
function wpcom_head_code(){
    global $options;
    if(isset($options['head_code']) && $options['head_code']) echo $options['head_code'] . "\n";
}

add_action('wp_footer', 'wpcom_footer_code', 20);
function wpcom_footer_code(){
    global $options;
    if(isset($options['footer_code']) && $options['footer_code']) echo $options['footer_code'] . "\n";;
}

add_filter('get_site_icon_url', 'wpcom_get_site_icon_url', 10, 2);
function wpcom_get_site_icon_url($url, $size){
    global $options;
    if(isset($options['fav']) && $options['fav']) {
        if ( $size >= 512 ) {
            $size_data = 'full';
        } else {
            $size_data = array( $size, $size );
        }
        $url = is_numeric($options['fav']) ? wp_get_attachment_image_url( $options['fav'], $size_data ) : $options['fav'];
    }
    return $url;
}

add_action('pre_handle_404', 'wpcom_pre_handle_404');
function wpcom_pre_handle_404($res){
    global $wp_query, $wp_version;
    if ( $wp_query->posts && version_compare($wp_version,'5.5') >= 0) {
        $content_found = true;
        if ( is_singular() ) {
            $post = isset( $wp_query->post ) ? $wp_query->post : null;
            // Only set X-Pingback for single posts that allow pings.
            if ( $post && pings_open( $post ) && ! headers_sent() ) {
                header( 'X-Pingback: ' . get_bloginfo( 'pingback_url', 'display' ) );
            }
            $paged = get_query_var( 'page' );
            if ( $post && ! empty( $paged ) ) {
                $shortcode_tags = array('wpcom_tags', 'wpcom-member');
                preg_match_all( '@\[([^<>&/\[\]\x00-\x20=]++)@', $post->post_content, $matches );
                $tagnames = array_intersect( $shortcode_tags, $matches[1] );

                if ( empty($tagnames) ) {
                    $content_found = false;
                }else if(in_array('wpcom_tags', $tagnames)){
                    preg_match( '/\[wpcom_tags[^\]]*\]/i', $post->post_content, $matches2 );
                    if(isset($matches2[0])){
                        $text = ltrim($matches2[0], '[wpcom_tags');
                        $text = rtrim($text, ']');
                        $atts = shortcode_parse_atts($text);
                        if(isset($atts['per_page']) && $atts['per_page']){ // 分页
                            $max   = wp_count_terms( 'post_tag', array( 'hide_empty' => true ) );
                            $pages   = ceil( $max / $atts['per_page'] );
                            if($pages<$paged) $content_found = false; // 页数超过
                        }else{ // 未分页，则一页全部显示
                            $content_found = false;
                        }
                    }
                }
            }
        }

        if ( $content_found ) $res = true;
    }
    return $res;
}

function wpcom_empty_icon($type='post'){
    return '<img class="empty-icon j-lazy" src="'.FRAMEWORK_URI.'/assets/images/empty-'.$type.'.svg">';
}

function wpcom_logo(){
    global $options;
    $logo = isset($options['logo']) ? (is_numeric($options['logo']) ? wp_get_attachment_url( $options['logo'] ) : $options['logo']) : '';
    $logo = $logo ?: get_template_directory_uri().'/images/logo.png';
    return esc_url($logo);
}

// 记录Themer框架版本，方便后续更新升级
add_action('admin_menu', 'wpcom_themer_update');
function wpcom_themer_update() {
    global $wpdb;
    $dbuser     = defined( 'DB_USER' ) ? DB_USER : '';
    $dbpassword = defined( 'DB_PASSWORD' ) ? DB_PASSWORD : '';
    $dbname     = defined( 'DB_NAME' ) ? DB_NAME : '';
    $dbhost     = defined( 'DB_HOST' ) ? DB_HOST : '';

    $_wpdb = new wpdb( $dbuser, $dbpassword, $dbname, $dbhost );
    $version = $_wpdb->get_row("SELECT * FROM $wpdb->options WHERE option_name='themer_version'");
    if(!$version || $version->option_value!=FRAMEWORK_VERSION){
        do_action('themer_updated', ($version ? $version->option_value : ''));
        if($version===null){
            $_wpdb->insert($wpdb->options, array('option_name' => 'themer_version', 'option_value' => FRAMEWORK_VERSION));
            if(version_compare(FRAMEWORK_VERSION,'2.6.0','>=')){
                // 更新栅格系统
                $metas = $_wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE meta_key='_page_modules'");
                if($metas){
                    foreach ($metas as $meta){
                        $modules = maybe_unserialize($meta->meta_value);
                        $modules = is_string($modules) ? json_decode($modules, true) : $modules;
                        if(is_array($modules) && count($modules)>0) {
                            if(isset($modules['type'])) $modules = array($modules);
                            $data = wpcom_update_girds($modules);
                            if($data){
                                if(version_compare(PHP_VERSION,'5.4.0','<')){
                                    $data = wp_slash(wp_json_encode($data));
                                }else{
                                    $data = wp_slash(wp_json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
                                }
                                if($data) update_post_meta($meta->post_id, '_page_modules', $data);
                            }
                        }
                    }
                }
            }
        }else{
            $_wpdb->update($wpdb->options, array('option_value' => FRAMEWORK_VERSION), array('option_name' => 'themer_version'));
        }
    }
    return $version;
}

function wpcom_update_girds($modules){
    if($modules && is_array($modules)){
        foreach ($modules as $i => $module){
            if(isset($module['type']) && $module['type'] === 'gird' && isset($module['settings']) && isset($module['settings']['columns'])){
                foreach ($module['settings']['columns'] as $x => $col){
                    $modules[$i]['settings']['columns'][$x] = $col * 2;
                }
                if(isset($module['settings']['columns_mobile']) && $module['settings']['columns_mobile']){
                    foreach ($module['settings']['columns_mobile'] as $y => $col){
                        $modules[$i]['settings']['columns_mobile'][$y] = $col * 2;
                    }
                }
            }else if(isset($module['settings']) && isset($module['settings']['modules'])){
                $modules[$i]['settings']['modules'] = wpcom_update_girds($module['settings']['modules']);
            }else{
                $modules[$i] = $module;
            }
        }
    }
    return $modules;
}

// 评论class 移除用户名
add_filter( 'comment_class', 'wpcom_comment_class' );
function wpcom_comment_class($classes){
    if($classes){
        foreach($classes as $i => $class){
            if(preg_match('/^comment-author-/i', $class)){
                unset($classes[$i]);
                break;
            }
        }
    }
    return $classes;
}