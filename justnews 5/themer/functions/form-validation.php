<?php
defined( 'ABSPATH' ) || exit;

add_action( 'wpcom_account_general_post', 'wpcom_account_form_general', 20 );
if( !function_exists( 'wpcom_account_form_general' ) ){
    function wpcom_account_form_general(){
        $res = array();
        $res['result'] = 1;
        $res['error'] = array();
        $res['value'] = array();

        $res = wpcom_form_validate( $res, 'member_form_general', 'wpcom_account_tabs_general_metas' );

        $res = apply_filters( 'wpcom_account_form_general_validate', $res );

        // 全部验证通过
        if( empty($res['error']) ){
            $user = wp_get_current_user();
            if($user->ID){
                $res['value']['ID'] = $user->ID;
                $user_id = wp_update_user( $res['value'] );
                if( is_wp_error( $user_id ) ){
                    $res['error'][$user_id->get_error_code()] = $user_id->get_error_message();
                }
            }
        } else {
            $res['result'] = 0;
        }

        $GLOBALS['validation'] = $res;
    }
}

add_action( 'wpcom_account_bind_post', 'wpcom_account_form_bind', 20 );
if( !function_exists( 'wpcom_account_form_bind' ) ){
    function wpcom_account_form_bind(){
        $res = array();
        $res['result'] = 1;
        $res['error'] = array();
        $res['value'] = array();

        if(isset($_POST['by']) && $_POST['by']){
            $user = wp_get_current_user();
            $by = $_POST['by'];
            if($by == 'phone' && !$user->mobile_phone){
                $res['error'] = __('No phone number added, phone number verification cannot be used', 'wpcom');
            }else if($by == 'email' && (!$user->user_email || wpcom_is_empty_mail($user->user_email)) ){
                $res['error'] = __('No email address added, email verification cannot be used', 'wpcom');
            }

            // 全部验证通过
            if( empty($res['error']) ){
                $url = add_query_arg( array(
                    'type' => $_GET['type'],
                    'action' => $_GET['action'],
                    'by' => $by
                ), wpcom_subpage_url('bind') );
                wp_safe_redirect($url);
                exit;
            }else{
                $res['result'] = 0;
            }
        }

        $GLOBALS['validation'] = $res;
    }
}

add_action( 'wpcom_account_password_post', 'wpcom_account_form_password', 20 );
if( !function_exists( 'wpcom_account_form_password' ) ){
    function wpcom_account_form_password(){
        $res = array();
        $res['result'] = 1;
        $res['error'] = array();
        $res['value'] = array();

        $res = wpcom_form_validate( $res, 'member_form_password', 'wpcom_account_tabs_password_metas' );

        $res = apply_filters( 'wpcom_account_form_password_validate', $res );

        // 全部验证通过
        if( empty($res['error']) ){
            $user = wp_get_current_user();
            if( $user->ID && wp_check_password($res['value']['old-password'], $user->user_pass, $user->ID ) ){
                //wp_set_password( $res['value']['password'], $user->ID );
                reset_password( $user, $res['value']['password'] );
                $res['value']['old-password'] = '';
                $res['value']['password'] = '';
                $res['value']['password2'] = '';

                // 更新cookie，避免重新登录
                wp_set_auth_cookie($user->ID);
                wp_set_current_user($user->ID);
            }else{
                $res['error']['old-password'] = __( 'The password is incorrect', 'wpcom' );
            }
        }else{
            $res['result'] = 0;
        }

        $GLOBALS['validation'] = $res;
    }
}

add_action( 'wp_ajax_nopriv_wpcom_login', 'wpcom_ajax_login' );
if( !function_exists( 'wpcom_ajax_login' ) ) {
    function wpcom_ajax_login(){
        global $options;
        $res = array();
        $res['result'] = 1; // 0：帐号密码错误；1：登录成功；-1：nonce校验失败；-2：滑动解锁验证失败；-3：请先滑动解锁
        $res['error'] = '';

        $errors = apply_filters( 'wpcom_member_errors', array() );

        $msg = array(
            '0' => __( 'The username or password is incorrect', 'wpcom' ),
            '1' => __('Login successfully', 'wpcom'),
            '-1' => $errors['nonce'],
            '-2' => $errors['captcha_fail'],
            '-3' => $errors['captcha_verify']
        );

        $filter = 'wpcom_login_form_items';
        $sms_login = 0;
        if(isset($_POST['user_phone']) && $_POST['user_phone'] && !isset($_POST['user_login']) && !isset($_POST['user_password']) && is_wpcom_enable_phone()){
            $filter = 'wpcom_sms_code_items';
            $sms_login = 1;
        }
        $res = wpcom_form_validate( $res, 'member_form_login', $filter );

        $res = apply_filters( 'wpcom_login_form_validate', $res );

        if ($res['result'] == 1) {
            if($sms_login){ // 手机快捷登录
                $args = array(
                    'meta_key'     => 'mobile_phone',
                    'meta_value'   => $_POST['user_phone'],
                );
                $users = get_users($args);
                if($users && $users[0]->ID ) { // 用户存在
                    $user = $users[0];
                    $approve = get_user_meta( $user->ID, 'wpcom_approve', true );
                    $member_reg_active = isset($options['member_reg_active']) && $options['member_reg_active'] ? $options['member_reg_active']: '0';
                    if( $approve=='0' && $member_reg_active!='0' ){ // 用户未通过审核
                        $err = '';
                        if($member_reg_active=='1'){
                            $resend_url = add_query_arg( 'approve', 'resend', wp_registration_url() );
                            $err = sprintf( __( 'Please activate your account. <a href="%s" target="_blank">Resend activation email</a>', 'wpcom' ), $resend_url );
                        }else if($member_reg_active=='2'){
                            $err = __( 'Account awaiting approval.', 'wpcom' );
                        }
                        if($err) $login = new WP_Error( 'not_approve', $err );
                    }else{
                        wp_set_auth_cookie($user->ID);
                        wp_set_current_user($user->ID, isset($_POST['remember']) ? $_POST['remember'] : false);
                        $login = $user;
                    }
                }else{ // 用户不存在
                    $phone = $_POST['user_phone'];
                    $username = wpcom_generate_unique_username(substr($phone,-4));
                    // 补充邮箱
                    $email = $username . '@email.empty';
                    $user_id = wp_insert_user(array(
                        'user_login' => $username,
                        'user_email' => $email,
                        'user_pass' => wp_generate_password()
                    ));

                    if(is_wp_error($user_id)){
                        $res['result'] = 0;
                        $errors = apply_filters( 'wpcom_member_errors', array() );
                        $res['error'] = $errors['sms_code'];
                        $login = new WP_Error( 'sms_code_error', $errors['sms_code'] );
                    }else{
                        // 保存用户手机号码，并登录
                        update_user_meta($user_id, 'mobile_phone', $phone);
                        WPCOM_Session::delete('', 'code_' . $phone);
                        wp_set_auth_cookie($user_id);
                        wp_set_current_user($user_id, isset($_POST['remember']) ? $_POST['remember'] : false);
                        $login = $user_id;
                    }
                }
            }else{
                $login = wp_signon($_POST);
            }
            if (is_wp_error($login)){
                $res['result'] = 0;
                if( $login->get_error_code() == 'not_approve' ){
                    $res['error'] = $login->get_error_message();
                }
            }else if( !preg_match('/redirect_to=[^\s&]/i', $_SERVER['HTTP_REFERER']) && isset($options['login_redirect']) && $options['login_redirect'] != '' ){
                $res['redirect_to'] = $options['login_redirect'];
            }
        }

        if ( $res['error'] == '' && isset($msg[$res['result']]) ) $res['error'] = $msg[$res['result']];

        echo json_encode($res);
        exit;
    }
}

add_action( 'wp_ajax_nopriv_wpcom_register', 'wpcom_ajax_register' );
if( !function_exists( 'wpcom_ajax_register' ) ) {
    function wpcom_ajax_register(){
        global $options;
        $res = array();
        $res['result'] = 1; // 0：插入失败；1：登录成功；-1：nonce校验失败；-2：滑动解锁验证失败；-3：请先滑动解锁
        $res['error'] = '';

        $errors = apply_filters( 'wpcom_member_errors', array() );

        $msg = array(
            //'0' => '',
            '1' => __('Registered successfully', 'wpcom'),
            '-1' => $errors['nonce'],
            '-2' => $errors['captcha_fail'],
            '-3' => $errors['captcha_verify'],
            '-4' => $errors['email'],
            '-5' => $errors['password'],
            '-6' => $errors['passcheck']
        );

        if( !get_option('users_can_register') ){ // 未开启注册
            $res['result'] = 0;
            $res['error'] = __('User registration is currently not allowed.', 'wpcom');
        }else{
            $res = wpcom_form_validate( $res, 'member_form_register', 'wpcom_register_form_items' );
            $res = apply_filters( 'wpcom_register_form_validate', $res );
        }

        if ($res['result'] == 1) {
            // 手机、邮箱注册生成随机用户名
            $items = apply_filters( 'wpcom_register_form_items', array() );
            $name = $items[10]['name'];
            $login = trim($_POST[$name]);
            if(is_wpcom_enable_phone()){ // 手机注册
                $username = wpcom_generate_unique_username(substr($login,-4));
                // 补充邮箱
                $_POST['user_email'] = $username . '@email.empty';
            }else{
                $strs = explode('@', $login);
                $username = wpcom_generate_unique_username($strs[0]);
            }
            $_POST['user_login'] = $username;

            if ( is_wpcom_enable_phone() && wpcom_mobile_phone_exists($login) ) { // 手机号是否注册
                $user_id = new WP_Error( 'existing_user_login', __( 'Sorry, that mobile phone number already exists!', 'wpcom' ) );
            }else{
                $user_id = wp_insert_user($_POST);
            }
            if ( is_wp_error( $user_id ) ){
                $res['error'] = $user_id->get_error_message();
                $res['result'] = 0;
            }else{
                if( !is_wpcom_enable_phone() && isset($options['member_reg_active']) && $options['member_reg_active'] ){
                    // 邮箱注册用户需要验证
                    $url = wpcom_register_url();
                    $url = add_query_arg( 'approve', 'false', $url );
                    $res['redirect_to'] = $url;
                } else {
                    if(is_wpcom_enable_phone()){ // 保存用户手机号码
                        update_user_meta($user_id, 'mobile_phone', $login);
                        WPCOM_Session::delete('', 'code_'.$login);
                    }
                    wp_set_auth_cookie($user_id);
                    wp_set_current_user($user_id);
                }
            }
        }

        if ( $res['error'] == '' && isset($msg[$res['result']]) ) $res['error'] = $msg[$res['result']];

        echo json_encode($res);
        exit;
    }
}

add_action( 'wp_ajax_wpcom_approve_resend', 'wpcom_ajax_approve_resend' );
add_action( 'wp_ajax_nopriv_wpcom_approve_resend', 'wpcom_ajax_approve_resend' );
function wpcom_ajax_approve_resend(){
    global $options;
    if( !(isset($options['member_reg_active']) && $options['member_reg_active']=='1') ){
        return 0; // 未开启邮件认证直接推出
    }

    $res = array();
    $res['result'] = 1; // 0：帐号密码错误；1：登录成功；-1：nonce校验失败；-2：滑动解锁验证失败；-3：请先滑动解锁
    $res['error'] = '';

    $errors = apply_filters( 'wpcom_member_errors', array() );

    $msg = array(
        '0' => __( 'The username does not exist', 'wpcom' ),
        '1' => __('Resend successfully', 'wpcom'),
        '-1' => $errors['nonce'],
        '-2' => $errors['captcha_fail'],
        '-3' => $errors['captcha_verify']
    );

    $res = wpcom_form_validate( $res, 'member_form_approve_resend', 'wpcom_approve_resend_form_items' );

    $res = apply_filters( 'wpcom_approve_resend_form_validate', $res );

    if ($res['result'] == 1) {

        if( isset( $_POST['user_login'] ) && is_string( $_POST['user_login'] ) ){
            $user_name = wp_unslash( $_POST['user_login'] );
            $user = get_user_by( 'login', $user_name );
            if ( ! $user && strpos( $user_name, '@' ) ) {
                $user = get_user_by( 'email', $user_name );
            }

            if( $user ) {
                $approve = get_user_meta( $user->ID, 'wpcom_approve', true );
                if( $approve=='0' ){
                    $resend = wpcom_send_active_email($user->ID);
                    if ($resend !== true) {
                        $res['result'] = 0;
                        $res['error'] = $resend ? $resend : __( 'Error occurs when resend email.', 'wpcom' );
                    } else {
                        $url = wpcom_register_url();
                        $url = add_query_arg( 'approve', 'false', $url );
                        $res['redirect_to'] = $url;
                    }
                } else {
                    $res['result'] = 0;
                    $res['error'] = __( 'You have already activated your account.', 'wpcom' );
                }
            } else {
                $res['result'] = 0;
            }
        }else{
            $res['result'] = 0;
        }
    }

    if ( $res['error'] == '' && isset($msg[$res['result']]) ) $res['error'] = $msg[$res['result']];

    echo json_encode($res);
    exit;
}

add_action( 'wp_ajax_wpcom_lostpassword', 'wpcom_ajax_lostpassword' );
add_action( 'wp_ajax_nopriv_wpcom_lostpassword', 'wpcom_ajax_lostpassword' );
function wpcom_ajax_lostpassword(){
    $res = array();
    $res['result'] = 1; // 0：帐号密码错误；1：登录成功；-1：nonce校验失败；-2：滑动解锁验证失败；-3：请先滑动解锁
    $res['error'] = '';

    $errors = apply_filters( 'wpcom_member_errors', array() );

    $msg = array(
        '0' => __( 'The username does not exist', 'wpcom' ),
        '1' => __('Submitted successfully', 'wpcom'),
        '-1' => $errors['nonce'],
        '-2' => $errors['captcha_fail'],
        '-3' => $errors['captcha_verify']
    );

    $res = wpcom_form_validate( $res, 'member_form_lostpassword', 'wpcom_lostpassword_form_items' );

    $res = apply_filters( 'wpcom_lostpassword_form_validate', $res );

    if ($res['result'] == 1) {
        if( isset( $_POST['user_login'] ) && is_string( $_POST['user_login'] ) ){
            $user_name = wp_unslash( $_POST['user_login'] );
            $user = get_user_by( 'login', $user_name );
            $is_mobile_phone = 0;
            if ( !$user && strpos( $user_name, '@' ) ) {
                $user = get_user_by( 'email', $user_name );
            }else if( !$user && is_wpcom_enable_phone() && preg_match("/^1[3-9]{1}\d{9}$/", $user_name) ){
                $args = array(
                    'meta_key'     => 'mobile_phone',
                    'meta_value'   => $user_name,
                );
                $users = get_users($args);
                if($users && $users[0]->ID) {
                    $is_mobile_phone = 1;
                    WPCOM_Session::set('lost_password_phone', $user_name);
                    $user = $users[0];
                }
            }

            if( $user && $user->ID ) {
                if(!$is_mobile_phone) { // 非手机找回，则发送邮件
                    $phone = $user->mobile_phone;
                    if(!$user->user_email || wpcom_is_empty_mail($user->user_email)){ // 未设置邮箱
                        if($phone){// 使用手机找回
                            $is_mobile_phone = 1;
                            WPCOM_Session::set('lost_password_phone', $phone);
                        }else{
                            $res['result'] = 0;
                            $res['error'] = __('No email address or phone number added, you should add first', 'wpcom');//'未绑定邮箱或者手机，社交登录用户请绑定后再使用找回密码功能';
                        }
                    }else{
                        $reset = wpcom_retrieve_password($user);
                        if ($reset !== true) {
                            $res['result'] = 0;
                            $res['error'] = $reset;
                        }
                    }
                }

                $args = array('subpage' => 'send_success');
                if($is_mobile_phone) $args['phone'] = 'true';
                $res['redirect_to'] = add_query_arg($args, $_POST['_wp_http_referer']);
            } else {
                $res['result'] = 0;
            }
        }else{
            $res['result'] = 0;
        }
    }

    if ( $res['error'] == '' && isset($msg[$res['result']]) ) $res['error'] = $msg[$res['result']];

    echo json_encode($res);
    exit;
}

add_action( 'wp_ajax_wpcom_resetpassword', 'wpcom_ajax_resetpassword' );
add_action( 'wp_ajax_nopriv_wpcom_resetpassword', 'wpcom_ajax_resetpassword' );
function wpcom_ajax_resetpassword(){
    $res = array();
    $res['result'] = 1; // 0：帐号密码错误；1：登录成功；-1：nonce校验失败；-2：滑动解锁验证失败；-3：请先滑动解锁
    $res['error'] = '';

    $errors = apply_filters( 'wpcom_member_errors', array() );

    $msg = array(
        '0' => __('Reset failed, please retry!', 'wpcom'),
        '1' => __('Reset successfully', 'wpcom'),
        '-1' => $errors['nonce'],
        '-2' => $errors['captcha_fail'],
        '-3' => $errors['captcha_verify']
    );

    $res = wpcom_form_validate( $res, 'member_form_resetpassword', 'wpcom_resetpassword_form_items' );

    $res = apply_filters( 'wpcom_resetpassword_form_validate', $res );

    if ($res['result'] == 1) {
        $rp_cookie = 'wp-resetpass-' . COOKIEHASH;
        if ( isset( $_COOKIE[ $rp_cookie ] ) && 0 < strpos( $_COOKIE[ $rp_cookie ], ':' ) ) {
            list( $rp_login, $rp_key ) = explode( ':', wp_unslash( $_COOKIE[ $rp_cookie ] ), 2 );
            $user = check_password_reset_key( $rp_key, $rp_login );
        } else {
            $user = false;
        }

        if ( ! $user || is_wp_error( $user ) ) {
            $res['result'] = 0;
        }else{
            reset_password($user, $_POST['password']);
            $res['redirect_to'] = add_query_arg('subpage', 'finished', $_POST['_wp_http_referer']);
        }
    }

    if ( $res['error'] == '' && isset($msg[$res['result']]) ) $res['error'] = $msg[$res['result']];

    echo json_encode($res);
    exit;
}

function wpcom_retrieve_password( $user ) {
    $user_login = $user->user_login;
    $user_email = $user->user_email;
    $key = get_password_reset_key( $user );

    if ( is_wp_error( $key ) ) {
        return __('Generate reset key error.', 'wpcom');
    }

    if ( is_multisite() ) {
        $site_name = get_network()->site_name;
    } else {
        /*
         * The blogname option is escaped with esc_html on the way into the database
         * in sanitize_option we want to reverse this for the plain text arena of emails.
         */
        $site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
    }

    $url = add_query_arg( array(
        'subpage' => 'reset',
        'key' => $key,
        'login' => rawurlencode( $user_login )
    ), wpcom_lostpassword_url() );

    $message = __( 'Someone has requested a password reset for the following account:', 'wpcom' ) . "<br><br>";
    /* translators: %s: site name */
    $message .= sprintf( __( 'Site Name: %s'), $site_name ) . "<br>";
    /* translators: %s: user login */
    $message .= sprintf( __( 'Username: %s'), $user_login ) . "<br><br>";
    $message .= __( 'If this was a mistake, ignore this email and nothing will happen.', 'wpcom' ) . "<br><br>";
    $message .= __( 'To reset your password, visit the following address:', 'wpcom' ) . "<br>";
    $message .= '<a href="'.$url.'">'.$url.'</a>' . "<br>";

    /* translators: Password reset email subject. %s: Site name */
    $title = sprintf( __( '[%s] Password Reset' ), $site_name );

    /**
     * Filters the subject of the password reset email.
     *
     * @since 2.8.0
     * @since 4.4.0 Added the `$user_login` and `$user_data` parameters.
     *
     * @param string  $title      Default email title.
     * @param string  $user_login The username for the user.
     * @param WP_User $user_data  WP_User object.
     */
    $title = apply_filters( 'retrieve_password_title', $title, $user_login, $user );

    /**
     * Filters the message body of the password reset mail.
     *
     * If the filtered message is empty, the password reset email will not be sent.
     *
     * @since 2.8.0
     * @since 4.1.0 Added `$user_login` and `$user_data` parameters.
     *
     * @param string  $message    Default mail message.
     * @param string  $key        The activation key.
     * @param string  $user_login The username for the user.
     * @param WP_User $user_data  WP_User object.
     */
    $message = apply_filters( 'retrieve_password_message', $message, $key, $user_login, $user );
    $headers = array('Content-Type: text/html; charset=UTF-8');

    if ( $message && !wp_mail( $user_email, wp_specialchars_decode( $title ), $message, $headers ) )
        return __('The email could not be sent.', 'wpcom');

    return true;
}


add_action( 'wp_ajax_wpcom_send_sms_code', 'wpcom_send_sms_code' );
add_action( 'wp_ajax_nopriv_wpcom_send_sms_code', 'wpcom_send_sms_code' );
function wpcom_send_sms_code(){
    $res = array();
    $res['result'] = 1; // 0：发送失败；1：发送成功；-1：nonce校验失败；-2：滑动解锁验证失败；-3：请先滑动解锁
    $res['error'] = '';

    $errors = apply_filters( 'wpcom_member_errors', array() );

    $msg = array(
        '0' => __( 'Failed to send', 'wpcom' ),
        '1' => __('Send success', 'wpcom'),
        '-1' => $errors['nonce'],
        '-2' => $errors['captcha_fail'],
        '-3' => $errors['captcha_verify']
    );

    if( (isset($_POST['member_form_accountbind_nonce']) && isset($_POST['user_email'])) ||
        (isset($_POST['member_form_account_change_bind_nonce']) && isset($_POST['type']) && $_POST['type']=='email') ){
        $filter = 'wpcom_email_code_items';
    }else{
        $filter = 'wpcom_sms_code_items';
    }
    $items = apply_filters($filter, array());
    $target = 'user_phone';
    if($items){
        foreach ($items as $item){
            if($item['type']==='smsCode'){
                $target = $item['target'];
                break;
            }
        }
    }

    if( isset($_POST['member_form_smscode_nonce'])){ // 找回密码的验证短信
        $_POST[$target] = WPCOM_Session::get('lost_password_phone');
    }

    if( isset($_POST['member_form_account_change_bind_nonce'])){ // 更换绑定安全验证短信
        $user = wp_get_current_user();
        $_POST[$target] = isset($_POST['type']) && $_POST['type']=='phone' ? $user->mobile_phone : $user->user_email;
    }

    $res = wpcom_form_validate( $res, 'send_sms_code', $filter );

    if ($res['result'] == 1) {
        if(is_email($_POST[$target])){
            if(!wpcom_send_email_code($_POST[$target])){
                $res['result'] = 0;
                $res['error'] = __('Failed to send email', 'wpcom');
            }
        }else{
            $send = wpcom_sms_code_sender($_POST[$target], isset($_POST['user_phone_country']) ? $_POST['user_phone_country'] : '');
            if($send->result!==0){ // 发送失败
                $res['result'] = 0;
                $res['error'] = $send->errmsg;
            }
        }
        if($res['result'] == 1){
            if(isset($_POST['ticket'])){
                $ticket = $_POST['ticket'];
                $randstr = $_POST['randstr'];
                $last_ticket = $ticket . '+' . $randstr;
            }else if(isset($_POST['csessionid'])){
                $csessionid = $_POST['csessionid'];
                $token = $_POST['token'];
                $sig = $_POST['sig'];
                $scene = $_POST['scene'];
                $last_ticket = $csessionid . '+' . $token . '+' . $sig . '+' . $scene;
            }else if(isset($_POST['h-captcha-response'])){
                $last_ticket = $_POST['h-captcha-response'];
            }else if(isset($_POST['g-recaptcha-response'])){
                $last_ticket = $_POST['g-recaptcha-response'];
            }
            if(isset($last_ticket)) WPCOM_Session::set('last_ticket', $last_ticket);
        }
    }

    if ( $res['error'] == '' && isset($msg[$res['result']]) ) $res['error'] = $msg[$res['result']];

    echo json_encode($res);
    exit;
}

// 找回密码的短信验证码验证
add_action( 'wp_ajax_wpcom_smscode', 'wpcom_smscode' );
add_action( 'wp_ajax_nopriv_wpcom_smscode', 'wpcom_smscode' );
function wpcom_smscode(){
    $res = array();
    $res['result'] = 1; // 0：验证失败；1：验证成功；-1：nonce校验失败；-2：滑动解锁验证失败；-3：请先滑动解锁
    $res['error'] = '';

    $errors = apply_filters( 'wpcom_member_errors', array() );

    $msg = array(
        '0' => __('verification failed', 'wpcom'),
        '1' => __('Verified successfully', 'wpcom'),
        '-1' => $errors['nonce'],
        '-2' => $errors['captcha_fail'],
        '-3' => $errors['captcha_verify']
    );

    $items = apply_filters('wpcom_sms_code_items', array());
    $target = 'user_phone';
    if($items){
        foreach ($items as $item){
            if($item['type']==='smsCode'){
                $target = $item['target'];
                break;
            }
        }
    }

    if( isset($_POST['member_form_smscode_nonce'])){ // 找回密码的验证短信
        $_POST[$target] = WPCOM_Session::get('lost_password_phone');
    }

    $res = wpcom_form_validate( $res, 'member_form_smscode', 'wpcom_sms_code_items' );
    $res = apply_filters( 'wpcom_smscode_form_validate', $res );

    if ($res['result'] == 1) {
        WPCOM_Session::delete('', 'code_'.$_POST[$target]);
        WPCOM_Session::delete('', 'lost_password_phone');

        $args = array(
            'meta_key'     => 'mobile_phone',
            'meta_value'   => $_POST[$target],
        );
        $users = get_users($args);
        $user = $users[0];
        $user_login = $user->user_login;
        $key = get_password_reset_key( $user );

        $url = add_query_arg( array(
            'subpage' => 'reset',
            'key' => $key,
            'login' => rawurlencode( $user_login )
        ), wpcom_lostpassword_url() );

        $res['redirect_to'] = $url;
    }

    if ( $res['error'] == '' && isset($msg[$res['result']]) ) $res['error'] = $msg[$res['result']];

    echo json_encode($res);
    exit;
}


add_action( 'wp_ajax_wpcom_accountbind', 'wpcom_accountbind' );
add_action( 'wp_ajax_nopriv_wpcom_accountbind', 'wpcom_accountbind' );
function wpcom_accountbind(){
    $res = array();
    $res['result'] = 1; // 0：绑定失败；1：绑定成功；-1：nonce校验失败；-2：滑动解锁验证失败；-3：请先滑动解锁
    $res['error'] = '';

    $errors = apply_filters( 'wpcom_member_errors', array() );

    $msg = array(
        '0' => isset($_POST['member_form_accountbind_nonce']) ? __('Add failed', 'wpcom') : __('verification failed', 'wpcom'),
        '1' => isset($_POST['member_form_accountbind_nonce']) ? __('Added successfully', 'wpcom') : __('Verified successfully', 'wpcom'),
        '-1' => $errors['nonce'],
        '-2' => $errors['captcha_fail'],
        '-3' => $errors['captcha_verify']
    );

    $type = $_POST['type'];
    $filter = $type=='phone' ? 'wpcom_sms_code_items' : 'wpcom_email_code_items';
    $nonce = isset($_POST['member_form_accountbind_nonce']) ? 'member_form_accountbind' : 'member_form_account_change_bind';

    $items = apply_filters($filter, array());
    $target = 'user_phone';
    if($items){
        foreach ($items as $item){
            if($item['type']==='smsCode'){
                $target = $item['target'];
                break;
            }
        }
    }

    if(isset($_POST['member_form_account_change_bind_nonce'])) {
        $user = wp_get_current_user();
        $_POST[$target] = $type=='phone' ? $user->mobile_phone : $user->user_email;
    }
    $res = wpcom_form_validate( $res, $nonce, $filter );


    if ($res['result'] == 1) {
        if(isset($_POST['member_form_accountbind_nonce'])) {
            $user = wp_get_current_user();
            if ($type == 'phone') {
                // 判断手机号是否已经绑定过了
                $user_id = wpcom_mobile_phone_exists($_POST[$target]);
                if ($user_id && $user_id != $user->ID) { // 已注册，并且注册用户非当前用户
                    $res['result'] = 0;
                    $res['error'] = __('The phone number has been registered', 'wpcom');
                } else {
                    WPCOM_Session::delete('', 'code_' . $_POST[$target]);
                    update_user_meta($user->ID, 'mobile_phone', $_POST[$target]);
                }
            } else {
                $id = wp_update_user(array('ID' => $user->ID, 'user_email' => $_POST[$target]));
                if (is_wp_error($id)) {
                    $res['result'] = 0;
                    $res['error'] = $id->get_error_message();
                } else {
                    WPCOM_Session::delete('', 'code_' . sanitize_user($_POST[$target], true));
                }
            }
        }

        if(isset($_POST['member_form_account_change_bind_nonce'])) {
            $res['redirect_to'] = add_query_arg( array(
                'type' => $_POST['change'],
                'action' => 'change',
                'token' => get_password_reset_key( $user )
            ), wpcom_subpage_url('bind') );
        }else{
            $res['redirect_to'] = wpcom_subpage_url('bind');
        }
    }

    if ( $res['error'] == '' && isset($msg[$res['result']]) ) $res['error'] = $msg[$res['result']];

    echo json_encode($res);
    exit;
}

function wpcom_form_validate( $res, $nonce, $filter ){
    global $options;
    if (!check_ajax_referer($nonce, $nonce . '_nonce', false)) {
        $res['result'] = -1;
    } else {
        // 非空验证
        $items = apply_filters( $filter, array() );
        $captcha = '';
        foreach( $items as $item ){
            if( $item['type'] == 'noCaptcha' ) {
                $captcha = 'noCaptcha';
            }else if( $item['type'] == 'TCaptcha' ) {
                $captcha = 'TCaptcha';
            }else if( $item['type'] == 'hCaptcha' ) {
                $captcha = 'hCaptcha';
            }else if( $item['type'] == 'reCAPTCHA' ) {
                $captcha = 'reCAPTCHA';
            }
            // 发送验证码操作无需检查验证码
            if($nonce === 'send_sms_code' && $item['type'] === 'smsCode') continue;

            if( ! ( isset($item['disabled']) && $item['disabled'] ) && !preg_match("/Captcha$/i", $item['type']) ) {
                $val = isset($item['name']) && isset($_POST[$item['name']]) ? $_POST[$item['name']] : '';

                if (isset($item['require']) && $item['require']) {
                    $item['validate'] = 'require' . (isset($item['validate']) ? ' ' . $item['validate'] : '');
                }

                if (isset($item['validate']) && $item['validate']) {
                    $validate = wpcom_form_item_validate($item['validate'], $val, $item);

                    if (isset($validate['result']) && !$validate['result']) {
                        if( isset($res['value']) ){
                            // account 页面需要返回所有错误和提交的内容
                            $res['error'][$item['name']] = $validate['error'];
                        } else {
                            // 注册登录等页面有错误则返回第一条错误信息
                            $res['result'] = 0;
                            $res['error'] = $validate['error'];
                        }
                    }
                }

                if( isset($res['value']) )
                    $res['value'][$item['name']] = $val;
                else
                    if ($res['result'] != 1) break;
            }
        }

        // 验证阿里云滑动验证码
        if ($captcha == 'noCaptcha' && $res['result'] ==1 && isset($options['nc_appkey']) && $options['nc_appkey'] ) {
            $csessionid = $_POST['csessionid'];
            $token = $_POST['token'];
            $sig = $_POST['sig'];
            $scene = $_POST['scene'];

            $last_ticket = WPCOM_Session::get('last_ticket');
            if($last_ticket != ($csessionid . '+' . $token . '+' . $sig . '+' . $scene)){
                if ($csessionid != '' && $token != '' && $sig != '' && $scene != '') {
                    $check = wpcom_aliyun_sdk( $csessionid, $token, $sig, $scene );
                    if ($check->Code == '100') {
                        // 验证通过
                    } else {
                        $res['result'] = -2;
                    }
                } else {
                    $res['result'] = -3;
                }
            }
            WPCOM_Session::delete('', 'last_ticket');
        }else if($captcha == 'TCaptcha' && $res['result'] ==1 && isset($options['tc_appkey']) && $options['tc_appkey'] ){
            // 腾讯防水墙验证
            $ticket = $_POST['ticket'];
            $randstr = $_POST['randstr'];
            $last_ticket = WPCOM_Session::get('last_ticket');
            if($last_ticket != ($ticket . '+' . $randstr)){
                if ($ticket != '' && $randstr != '') {
                    // 获取 IP 地址
                    if(!empty($_SERVER["HTTP_CLIENT_IP"])){
                        $cip = $_SERVER["HTTP_CLIENT_IP"];
                    } elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
                        $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
                    } elseif (!empty($_SERVER["REMOTE_ADDR"])){
                        $cip = $_SERVER["REMOTE_ADDR"];
                    } else {
                        $cip = "none";
                    }
                    $result = wp_remote_request('https://ssl.captcha.qq.com/ticket/verify',
                        array(
                            'method' => 'GET',
                            'timeout' => 10,
                            'body' => array(
                                'aid' => $options['tc_appid'],
                                'AppSecretKey' => $options['tc_appkey'],
                                'Ticket' => $ticket,
                                'Randstr' => $randstr,
                                'UserIP' => $cip
                            )
                        )
                    );

                    if( is_wp_error( $result ) ){
                        $res['error'] = $result->get_error_message();
                        $res['result'] = -2;
                    }else{
                        $result = isset($result['body']) ? json_decode($result['body']) : '';

                        if (isset($result->response) && $result->response == 1) {
                            // 验证通过
                        } else {
                            $res['result'] = -2;
                        }
                    }
                } else {
                    $res['result'] = -3;
                }
            }
            WPCOM_Session::delete('', 'last_ticket');
        }else if($res['result'] ==1 && ($captcha == 'hCaptcha' || $captcha == 'reCAPTCHA') ){
            // hCaptcha 和 reCAPTCHA 接口相似，可以一起处理
            $response = '';
            if($captcha == 'hCaptcha' && isset($options['hc_sitekey']) && $options['hc_secret']){
                $response = $_POST['h-captcha-response'];
                $api = 'https://hcaptcha.com/siteverify';
            }else if($captcha == 'reCAPTCHA' && isset($options['gc_sitekey']) && $options['gc_secret']){
                $response = $_POST['g-recaptcha-response'];
                $api = 'https://www.google.com/recaptcha/api/siteverify';
            }

            $last_ticket = WPCOM_Session::get('last_ticket');
            if($last_ticket != $response){
                if ($response != '') {
                    $result = wp_remote_request($api,
                        array(
                            'method' => 'POST',
                            'timeout' => 10,
                            'body' => array(
                                'response' => $response,
                                'secret' => $options['hc_secret']
                            )
                        )
                    );

                    if( is_wp_error( $result ) ){
                        $res['error'] = $result->get_error_message();
                        $res['result'] = -2;
                    }else{
                        $result = isset($result['body']) ? json_decode($result['body']) : '';
                        if(isset($result->success) && $result->success == true) {
                            // 验证通过
                        } else {
                            $res['result'] = -2;
                        }
                    }
                } else {
                    $res['result'] = -3;
                }
            }
            WPCOM_Session::delete('', 'last_ticket');
        }
    }
    return $res;
}

function wpcom_form_item_validate( $validate_type, $val, $meta ){
    $types = explode(" ", $validate_type );
    $types = array_filter($types);  // 删除空元素

    $res = array();

    if($types){
        $errors = apply_filters( 'wpcom_member_errors', array() );

        foreach ( $types as $type ) {
            $type_array = explode(":", $type );
            $type = $type_array[0];
            $filter = isset($type_array[1]) ? $type_array[1] : '';

            switch ($type) {
                case 'require':
                    if (trim($val) === '') {
                        $res['result'] = 0;
                        $res['error'] = $meta['label'] . $errors['require'];
                    } else {
                        $res['result'] = 1;
                    }
                    break;
                case 'email':
                    $res['result'] = is_email($val) ? 1 : 0;
                    if (!$res['result']) {
                        $res['error'] = $errors['email'];
                    }
                    break;
                case 'phone':
                    $res['result'] = 1;
                    if(!preg_match("/^1[3-9]{1}\d{9}$/", $val)){
                        $res['result'] = 0;
                        $res['error'] = $errors['phone'];
                    }
                    break;
                case 'sms_code':
                    $res['result'] = $filter && wpcom_check_sms_code($_POST[$filter], $val) ? 1 : 0;
                    if (!$res['result']) {
                        $res['error'] = $errors['sms_code'];
                    }
                    break;
                case 'password':
                    $res['result'] = 1;
                    if( $filter ){
                        $pre = $_POST[$filter];
                        if( $pre!==$val ){
                            $res['result'] = 0;
                            $res['error'] = $errors['passcheck'];
                        }
                    }else{
                        if( isset($meta['maxlength']) && $meta['maxlength'] && strlen($val) > $meta['maxlength'] ) {
                            $res['result'] = 0;
                        }else if( isset($meta['minlength']) && $meta['minlength'] && strlen($val) < $meta['minlength'] ){
                            $res['result'] = 0;
                        }
                        if( ! $res['result'] ) $res['error'] = $errors['password'];
                    }

                    break;
            }

            if( isset($res['result']) && !$res['result'] )
                break;
        }
    }

    return $res;
}

add_action( 'wp_ajax_wpcom_social_unbind', 'wpcom_social_unbind' );
add_action( 'wp_ajax_nopriv_wpcom_social_unbind', 'wpcom_social_unbind' );
function wpcom_social_unbind(){
    global $wpdb;
    $can_unbind = false;
    $res = array(
        'result' => 1,
        'error' => __('Successfully deleted!', 'wpcom')
    );
    if(isset($_POST['name']) && $_POST['name']){
        $user = wp_get_current_user();
        if($user->mobile_phone) $can_unbind = true; // 是否绑定手机
        if(!$can_unbind && $user->user_email && !wpcom_is_empty_mail($user->user_email)) $can_unbind = true; // 是否绑定邮箱

        if( !$can_unbind ) { // 未绑定手机和邮箱，检查社交帐号，至少要绑定了其他帐号才可以解绑当前帐号，不然帐号无法登录
            $socials = apply_filters( 'wpcom_socials', array() );
            if($socials) {
                foreach ($socials as $social) {
                    $social['name'] = $social['name'] === 'wechat2' ? 'wechat' : $social['name'];
                    if ($_POST['name']!=$social['name'] && $social['id'] && $social['key']) {
                        $openid = get_user_meta($user->ID, $wpdb->get_blog_prefix() . 'social_type_' . $social['name'], true);
                        if($openid) {
                            $can_unbind = true;
                            break;
                        }
                    }
                }
            }else{
                $res['result'] = 0;
                $res['error'] = __('Social login is not turned on', 'wpcom');
            }
        }
        if($can_unbind){
            update_user_option($user->ID, 'social_type_'.($_POST['name'] === 'wechat2' ? 'wechat' : $_POST['name']), '');
            $url = add_query_arg(array('from' => 'bind'), wpcom_social_login_url($_POST['name']));
            $res['error'] = __('Not set', 'wpcom') . '<a class="member-bind-url j-social-bind '.$_POST['name'].'" href="'.$url.'">'.__('Connect', 'wpcom').'</a>';
        }else{
            $res['result'] = 0;
            $res['error'] = __('The current account only has this login method, please bind other login methods before unbinding!', 'wpcom');
        }
    }else{
        $res['result'] = 0;
        $res['error'] = __('Parameter error', 'wpcom');
    }
    echo json_encode($res);
    exit;
}