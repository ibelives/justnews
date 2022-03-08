<?php
defined( 'ABSPATH' ) || exit;

add_filter( 'get_avatar_url', 'wpcom_replace_avatar_url', 20 );
function wpcom_replace_avatar_url($url){
    global $options;
    $gravatar = isset($options["wafc_gravatar"]) ? $options["wafc_gravatar"] : 0;
    $gravatar = !$gravatar ? 1 : $gravatar;
    if($gravatar && $gravatar > 0){
        $gravatars = array('https://secure.gravatar.com/avatar', '//cn.gravatar.com/avatar', '//cravatar.cn/avatar', '//fdn.geekzu.org/avatar');
            // 匹配头像链接
            if($gravatar=='1'){
                $patterns = '/(http:|https:)?\/\/[0-9a-zA-Z]+\.gravatar\.com\/avatar/';
            }else{
                $patterns = '/\/\/[0-9a-zA-Z]+\.gravatar\.com\/avatar/';
            }
            // 使用可以访问到头像图片替换
        $url = preg_replace($patterns, $gravatars[$gravatar-1], $url);
    }
    return $url;
}

add_filter('pre_http_request', 'wpcom_pre_http_request', 20, 3);
function wpcom_pre_http_request($pre, $parsed_args, $url){
    global $options;
    if( class_exists('WP_CHINA_YES') ) return $pre;
    if ( ! stristr($url, 'api.wordpress.org') && ! stristr($url, 'downloads.wordpress.org')) return $pre;
    if( (isset($options['wp-proxy']) && $options['wp-proxy']) || !isset($options['wp-proxy'])){
        $url = str_replace('api.wordpress.org', 'api.w.org.ibadboy.net', $url);
        $url = str_replace('downloads.wordpress.org', 'd.w.org.ibadboy.net', $url);
        $pre = wp_remote_request($url, $parsed_args);
    }
    return $pre;
}