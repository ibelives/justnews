<?php
defined( 'ABSPATH' ) || exit;

function wpcom_account_url(){
    global $options;
    if( isset($options['member_page_account']) && $options['member_page_account'] ){
        return get_permalink( $options['member_page_account'] );
    }else{
        return get_edit_user_link();
    }
}

function wpcom_subpage_url( $subpage = '', $page = '' ){
    global $options;
    $page_id = 0;

    if( $page && is_numeric($page) ) {
        $page_id = $page;
    }else if( isset($options['member_page_account']) && $options['member_page_account'] ){
        $page_id = $options['member_page_account'];
    }

    if($page_id){
        $permalink_structure = get_option('permalink_structure');

        $page_url = get_permalink( $page_id );
        if( $permalink_structure ) {
            $url = trailingslashit($page_url) . $subpage;
        } else {
            $url =  add_query_arg( 'subpage', $subpage, $page_url );
        }
        return $url;
    }
}

function wpcom_profile_url( $user, $subpage='' ){
    global $options;
    if( $user && isset($options['member_page_profile']) && $options['member_page_profile'] ){
        $page_url = wpcom_author_url( $user->ID, $user->user_nicename );
        $permalink_structure = get_option('permalink_structure');

        if( $permalink_structure ) {
            $url = $subpage!='' ? trailingslashit($page_url) . $subpage : $page_url;
        } else {
            $url =  add_query_arg( 'subpage', $subpage, $page_url );
        }
        return $url;
    }
}

function wpcom_login_url( $redirect = '', $modal=1 ){
    global $options;
    if( isset($options['member_page_login']) && $options['member_page_login'] ){
        $login_url = get_permalink( $options['member_page_login'] );
        if ( !empty($redirect) ){
            $redirect = preg_replace('/^(http|https):\/\/[^\/]+\//i', '/', $redirect);
            $login_url = add_query_arg('redirect_to', urlencode($redirect), $login_url);
        }
        // 用户弹框
        if($modal && isset($options['login_modal']) && $options['login_modal']=='1') $login_url = add_query_arg('modal-type', 'login', $login_url);
        return $login_url;
    }
}

function wpcom_social_login_url( $type, $action = 'login' ){
    $login_url = wpcom_login_url('', 0);
    $args = array(
        'type' => $type,
        'action' => $action
    );
    if(isset($_GET['redirect_to']) && $_GET['redirect_to']){
        $args['redirect_to'] = urlencode($_GET['redirect_to']);
    }else if(wp_doing_ajax()){
        $redirect_url = isset( $_SERVER['HTTP_REFERER'] ) && $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : '';
        if( $redirect_url ){
            $site_domain = parse_url(get_bloginfo('url'), PHP_URL_HOST);
            $red_domain = parse_url($redirect_url, PHP_URL_HOST);
            if( $site_domain == $red_domain ) {
                // 去除域名，改成路径，防止某些服务器安全配置导致无法识别的问题
                $redirect_url = preg_replace('/^(http|https):\/\/[^\/]+\//i', '/', $redirect_url);
                $args['redirect_to'] = urlencode($redirect_url);
            }
        }
    }

    return add_query_arg( $args, $login_url );
}

function wpcom_logout_url( $redirect = '' ){
    if( $logout_url = wpcom_subpage_url( 'logout' ) ){
        if ( !empty($redirect) ){
            $redirect = preg_replace('/^(http|https):\/\/[^\/]+\//i', '/', $redirect);
            $logout_url = add_query_arg('redirect_to', urlencode($redirect), $logout_url);
        }

        return $logout_url;
    }
}

function wpcom_lostpassword_url( $redirect = '' ){
    global $options;
    if( isset($options['member_page_lostpassword']) && $options['member_page_lostpassword'] ){
        $lostpassword_url = get_permalink( $options['member_page_lostpassword'] );
        if ( !empty($redirect) ) {
            $redirect = preg_replace('/^(http|https):\/\/[^\/]+\//i', '/', $redirect);
            $lostpassword_url = add_query_arg('redirect_to', urlencode($redirect), $lostpassword_url);
        }
        return $lostpassword_url;
    }
}

function wpcom_register_url(){
    global $options;
    if( isset($options['member_page_register']) && $options['member_page_register'] ){
        $url = get_permalink( $options['member_page_register'] );
        // 用户弹框
        if(isset($options['login_modal']) && $options['login_modal']=='1') $url = add_query_arg('modal-type', 'register', $url);
        return $url;
    }
}

function wpcom_author_url( $author_id, $author_nicename ){
    global $options;
    if( isset($options['member_page_profile']) && $options['member_page_profile'] ){
        $author_url = get_permalink( $options['member_page_profile'] );
        $permalink_structure = get_option('permalink_structure');

        if( isset($options['member_user_slug']) && $options['member_user_slug']=='2' ) {
            $user_slug = $author_id;
        }else if ( '' == $author_nicename ) {
            $user = get_userdata($author_id);
            if ( !empty($user->user_nicename) ){
                $user_slug = $user->user_nicename;
            }else{
                $user_slug = wpcom_set_user_nicename($user);
            }
        } else {
            $user_slug = $author_nicename;
        }

        if( $permalink_structure ) {
            $url = trailingslashit( $author_url ) . $user_slug;
        } else {
            $url =  add_query_arg( 'user', $user_slug, $author_url );
        }
        return $url;
    }
}

function wpcom_set_user_nicename($user){
    if(isset($user->user_login)){
        global $wpdb;
        $user_nicename = mb_substr( $user->user_login, 0, 50 );
        $user_nicename = sanitize_title( $user_nicename );
        $user_nicename = apply_filters( 'pre_user_nicename', $user_nicename );
        $user_nicename_check = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->users WHERE user_nicename = %s AND user_login != %s LIMIT 1", $user_nicename, $user->user_login ) );
        if ( $user_nicename_check ) {
            $suffix = 2;
            while ( $user_nicename_check ) {
                // user_nicename allows 50 chars. Subtract one for a hyphen, plus the length of the suffix.
                $base_length         = 49 - mb_strlen( $suffix );
                $alt_user_nicename   = mb_substr( $user_nicename, 0, $base_length ) . "-$suffix";
                $user_nicename_check = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->users WHERE user_nicename = %s AND user_login != %s LIMIT 1", $alt_user_nicename, $user->user_login ) );
                    $suffix++;
            }
            $user_nicename = $alt_user_nicename;
        }
        $res = wp_update_user(array('ID' => $user->ID, 'user_nicename' => $user_nicename));
        if(!is_wp_error($res)) return $user_nicename;
    }
    return '';
}