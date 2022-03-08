<?php
defined( 'ABSPATH' ) || exit;

add_filter( 'wpcom_login_form_items', 'wpcom_login_form_items' );
function wpcom_login_form_items( $items = array() ){
    $items += array(
        10 => array(
            'type' => 'text',
            'label' => _x('Username', 'label', 'wpcom'),
            'icon' => 'user',
            'name' => 'user_login',
            'require' => true,
            'placeholder' =>  is_wpcom_enable_phone() ? __('Phone number / E-mail / Username', 'wpcom') : __('Username or email address', 'wpcom')
        ),
        20 => array(
            'type' => 'password',
            'label' => _x('Password', 'label', 'wpcom'),
            'icon' => 'lock',
            'name' => 'user_password',
            'require' => true,
            'placeholder' => _x('Password', 'placeholder', 'wpcom'),
        ),
        30 => array(
            'type' => wpcom_member_captcha_type()
        )
    );
    return $items;
}

add_filter( 'wpcom_register_form_items', 'wpcom_register_form_items' );
function wpcom_register_form_items( $items = array() ){
    if(is_wpcom_enable_phone()) {
        $items += apply_filters( 'wpcom_sms_code_items', array() );
        $items += array(
            40 => array(
                'type' => 'password',
                'label' => _x('Password', 'label', 'wpcom'),
                'icon' => 'lock',
                'name' => 'user_pass',
                'require' => true,
                'validate' => 'password',
                'placeholder' => _x('Password', 'placeholder', 'wpcom'),
            )
        );
    }else{
        $items += array(
            10 => array(
                'type' => 'text',
                'label' => _x('Email address', 'label', 'wpcom'),
                'icon' => 'mail',
                'name' => 'user_email',
                'require' => true,
                'validate' => 'email',
                'placeholder' => _x('Email address', 'placeholder', 'wpcom'),
            ),
            20 => array(
                'type' => 'password',
                'label' => _x('Password', 'label', 'wpcom'),
                'icon' => 'lock',
                'name' => 'user_pass',
                'require' => true,
                'validate' => 'password',
                'placeholder' => _x('Password', 'placeholder', 'wpcom'),
            ),
            30 => array(
                'type' => 'password',
                'label' => _x('Password', 'label', 'wpcom'),
                'icon' => 'lock',
                'name' => 'user_pass2',
                'require' => true,
                'validate' => 'password:user_pass',
                'placeholder' => _x('Confirm password', 'placeholder', 'wpcom'),
            ),
            40 => array(
                'type' => wpcom_member_captcha_type()
            ),
        );
    }
    return $items;
}

add_filter( 'wpcom_sms_code_items', 'wpcom_sms_code_items' );
function wpcom_sms_code_items($items){
    $items += array(
        10 => array(
            'type' => 'text',
            'label' => _x('Phone number', 'label', 'wpcom'),
            'icon' => 'phone',
            'name' => 'user_phone',
            'require' => true,
            'validate' => 'phone',
            'placeholder' => _x('Phone number', 'placeholder', 'wpcom'),
        ),
        20 => array(
            'type' => wpcom_member_captcha_type()
        ),
        30 => array(
            'type' => 'smsCode',
            'label' => _x('Verification code', 'label', 'wpcom'),
            'name' => 'sms_code',
            'icon' => 'shield-check',
            'validate' => 'sms_code:user_phone',
            'target' => 'user_phone',
            'require' => true,
            'placeholder' => _x('Please enter your verification code', 'placeholder', 'wpcom')
        )
    );
    return $items;
}

add_filter( 'wpcom_email_code_items', 'wpcom_email_code_items' );
function wpcom_email_code_items($items){
    global $options;
    $items += array(
        10 => array(
            'type' => 'text',
            'label' => _x('Email address', 'label', 'wpcom'),
            'icon' => 'envelope',
            'name' => 'user_email',
            'require' => true,
            'validate' => 'email',
            'placeholder' => _x('Email address', 'placeholder', 'wpcom'),
        ),
        20 => array(
            'type' => wpcom_member_captcha_type()
        ),
        30 => array(
            'type' => 'smsCode',
            'label' => _x('Verification code', 'label', 'wpcom'),
            'name' => 'sms_code',
            'icon' => FRAMEWORK_URI . '/assets/images/yzm.svg',
            'validate' => 'sms_code:user_email',
            'target' => 'user_email',
            'require' => true,
            'placeholder' => _x('Please enter your verification code', 'placeholder', 'wpcom')
        )
    );
    return $items;
}

// 插入默认的配置数据
add_filter( 'wpcom_account_tabs', 'wpcom_account_default_tabs' );
function wpcom_account_default_tabs( $tabs = array() ){
    global $options;
    $tabs += array(
        10 => array(
            'slug' => 'general',
            'title' => __('General', 'wpcom'),
            'icon' => 'personal-circle'
        ),
        20 => array(
            'slug' => 'password',
            'title' => __('Password', 'wpcom'),
            'icon' => 'lock-circle'
        ),
        30 => array(
            'slug' => 'logout',
            'title' => __('Logout', 'wpcom'),
            'icon' => 'out-circle'
        )
    );
    if(!is_wpcom_enable_phone() && !(isset($options['social_login_on']) && $options['social_login_on']=='1')){
        $tabs[98989] = array(
            'slug' => 'bind',
            'title' => _x('Connect', 'title', 'wpcom'),
            'icon' => 'bind-circle',
            'parent' => 'general'
        );
    }else{
        $tabs[11] = array(
            'slug' => 'bind',
            'title' => _x('Connect', 'title', 'wpcom'),
            'icon' => 'bind-circle'
        );
    }
    return $tabs;
}

add_filter( 'wpcom_account_tabs_general_metas', 'wpcom_account_tabs_general_metas' );
function wpcom_account_tabs_general_metas( $metas ){
    $user = wp_get_current_user();
    if( !$user->ID ) return $metas;

    if(is_wpcom_enable_phone()) {
        $phone = $user->mobile_phone;
        if($phone){
            $url = add_query_arg(array('type' => 'phone', 'action' => 'change'), wpcom_subpage_url('bind'));
            $phone .= '<a class="member-bind-url" href="'.$url.'">'.__('Edit', 'wpcom').'</a><span class="member-bind-tip">'.__('Private', 'wpcom').'</span>';
        }else{
            $url = add_query_arg(array('type' => 'phone', 'action' => 'bind'), wpcom_subpage_url('bind'));
            $phone = __('Not set', 'wpcom') . '<a class="member-bind-url" href="'.$url.'">'.__('Add phone number', 'wpcom').'</a><span class="member-bind-tip">'.__('Private', 'wpcom').'</span>';
        }

        $metas += array(
            10 => array(
                'type' => 'text',
                'label' => _x('Phone number', 'label', 'wpcom'),
                'name' => 'mobile_phone',
                'value' => $phone,
                'disabled' => true
            )
        );
    }
    $email = $user->user_email;
    if($email && !wpcom_is_empty_mail($email)){
        $url = add_query_arg(array('type' => 'email', 'action' => 'change'), wpcom_subpage_url('bind'));
        $email .= '<a class="member-bind-url" href="'.$url.'">'.__('Edit', 'wpcom').'</a><span class="member-bind-tip">'.__('Private', 'wpcom').'</span>';
    }else{
        $url = add_query_arg(array('type' => 'email', 'action' => 'bind'), wpcom_subpage_url('bind'));
        $email = __('Not set', 'wpcom') . '<a class="member-bind-url" href="'.$url.'">'.__('Add email address', 'wpcom').'</a><span class="member-bind-tip">'.__('Private', 'wpcom').'</span>';
    }

    $metas += array(
        20 => array(
            'type' => 'text',
            'label' => _x('Email address', 'label', 'wpcom'),
            'name' => 'user_email',
            'maxlength' => 64,
            'require' => true,
            'validate' => 'email',
            'value' => $email,
            'disabled' => true
        ),
        30 => array(
            'type' => 'text',
            'label' => __('Nickname', 'wpcom'),
            'name' => 'display_name',
            'maxlength' => 20,
            'require' => true,
            'value' => $user->display_name
        ),
        40 => array(
            'type' => 'textarea',
            'label' => __('Description', 'wpcom'),
            'maxlength' => 200,
            'rows' => 3,
            'name' => 'description',
            'desc' => __('Optional, description can not exceed 200 characters', 'wpcom'),
            'value' => $user->description
        )
    );

    return $metas;
}

add_filter( 'wpcom_account_tabs_bind_metas', 'wpcom_account_tabs_bind_metas' );
function wpcom_account_tabs_bind_metas( $metas ){
    global $options, $wpdb;
    $user = wp_get_current_user();
    if( !$user->ID ) return $metas;

    if(is_wpcom_enable_phone()) {
        $phone = $user->mobile_phone;
        if($phone){
            $url = add_query_arg(array('type' => 'phone', 'action' => 'change'), wpcom_subpage_url('bind'));
            $phone .= '<a class="member-bind-url" href="'.$url.'">'.__('Edit', 'wpcom').'</a><span class="member-bind-tip">'.__('Private', 'wpcom').'</span>';
        }else{
            $url = add_query_arg(array('type' => 'phone', 'action' => 'bind'), wpcom_subpage_url('bind'));
            $phone = __('Not set', 'wpcom') . '<a class="member-bind-url" href="'.$url.'">'.__('Add phone number', 'wpcom').'</a><span class="member-bind-tip">'.__('Private', 'wpcom').'</span>';
        }
        $metas += array(
            10 => array(
                'type' => 'text',
                'label' => _x('Phone number', 'label', 'wpcom'),
                'name' => 'mobile_phone',
                'value' => $phone,
                'disabled' => true
            )
        );
    }
    $email = $user->user_email;
    if($email && !wpcom_is_empty_mail($email)){
        $url = add_query_arg(array('type' => 'email', 'action' => 'change'), wpcom_subpage_url('bind'));
        $email .= '<a class="member-bind-url" href="'.$url.'">'.__('Edit', 'wpcom').'</a><span class="member-bind-tip">'.__('Private', 'wpcom').'</span>';
    }else{
        $url = add_query_arg(array('type' => 'email', 'action' => 'bind'), wpcom_subpage_url('bind'));
        $email = __('Not set', 'wpcom') . '<a class="member-bind-url" href="'.$url.'">'.__('Add email address', 'wpcom').'</a><span class="member-bind-tip">'.__('Private', 'wpcom').'</span>';
    }
    $metas += array(
        20 => array(
            'type' => 'text',
            'label' => _x('Email address', 'label', 'wpcom'),
            'name' => 'user_email',
            'maxlength' => 64,
            'require' => true,
            'validate' => 'email',
            'value' => $email,
            'disabled' => true
        )
    );
    if(isset($options['social_login_on']) && $options['social_login_on']=='1'){
        $key = 20;
        $socials = apply_filters( 'wpcom_socials', array() );
        ksort($socials);
        if( $socials ){
            foreach ( $socials as $social ){
                if( $social['id'] && $social['key'] ) {
                    $key += 10;
                    $url = add_query_arg(array('from' => 'bind'), wpcom_social_login_url($social['name']));
                    $value = __('Not set', 'wpcom') . '<a class="member-bind-url j-social-bind '.$social['name'].'" href="'.$url.'">'.__('Connect', 'wpcom').'</a>';
                    $social_name = $social['name'];
                    $social['name'] = $social['name'] === 'wechat2' ? 'wechat' : $social['name'];
                    $openid = get_user_meta($user->ID, $wpdb->get_blog_prefix() . 'social_type_'.$social['name'], true);
                    if($openid){
                        $value = __('Connected', 'wpcom');
                        $name = get_user_meta($user->ID, $wpdb->get_blog_prefix() . 'social_type_'.$social['name'].'_name', true);
                        $value = $name ?: $value;
                        $value = $value . '<a class="member-bind-url j-social-unbind" href="javascript:;" data-name="'.$social_name.'">'.__('Delete', 'wpcom').'</a>';
                    }
                    $metas += array(
                        $key => array(
                            'type' => 'text',
                            'label' => $social['title'],
                            'name' =>  $social['name'],
                            'value' => $value,
                            'disabled' => true
                        )
                    );
                }
            }
        }
    }
    return $metas;
}

add_filter( 'wpcom_account_tabs_password_metas', 'wpcom_account_tabs_password_metas' );
function wpcom_account_tabs_password_metas( $metas ){
    $metas += array(
        10 => array(
            'type' => 'password',
            'label' => _x('Old password', 'label', 'wpcom'),
            'name' => 'old-password',
            'require' => true,
            'value' => '',
            'placeholder' => _x('Please enter your old password', 'placeholder', 'wpcom')
        ),
        20 => array(
            'type' => 'password',
            'label' => _x('New password', 'label', 'wpcom'),
            'name' => 'password',
            'require' => true,
            'validate' => 'password',
            'maxlength' => 32,
            'minlength' => 6,
            'desc' => __('Password must be 6-32 characters', 'wpcom'),
            'value' => '',
            'placeholder' => _x('Please enter your new password', 'placeholder', 'wpcom')
        ),
        30 => array(
            'type' => 'password',
            'label' => _x('New password', 'label2', 'wpcom'),
            'name' => 'password2',
            'require' => true,
            'validate' => 'password:password',
            'value' => '',
            'placeholder' => _x('Please confirm your new password', 'placeholder', 'wpcom')
        )
    );

    return $metas;
}

add_filter( 'wpcom_lostpassword_form_items', 'wpcom_lostpassword_form_items' );
function wpcom_lostpassword_form_items( $items = array() ){
    global $options;
    $items += array(
        10 => array(
            'type' => 'text',
            'label' => _x('Username', 'label', 'wpcom'),
            'icon' => 'user',
            'name' => 'user_login',
            'require' => true,
            'placeholder' =>  is_wpcom_enable_phone() ? __('Phone number / E-mail / Username', 'wpcom') : __('Username or email address', 'wpcom')
        ),
        30 => array(
            'type' => wpcom_member_captcha_type()
        )
    );
    return $items;
}

add_filter( 'wpcom_resetpassword_form_items', 'wpcom_resetpassword_form_items' );
function wpcom_resetpassword_form_items( $items = array() ){
    global $options;
    $items += array(
        10 => array(
            'type' => 'password',
            'label' => _x('New password', 'label', 'wpcom'),
            'name' => 'password',
            'icon' => 'lock',
            'require' => true,
            'validate' => 'password',
            'maxlength' => 32,
            'minlength' => 6,
            'desc' => __('Password must be 6-32 characters', 'wpcom'),
            'value' => '',
            'placeholder' => _x('Please enter your new password', 'placeholder', 'wpcom')
        ),
        20 => array(
            'type' => 'password',
            'label' => _x('New password', 'label2', 'wpcom'),
            'name' => 'password2',
            'icon' => 'lock',
            'require' => true,
            'validate' => 'password:password',
            'value' => '',
            'placeholder' => _x('Please confirm your new password', 'placeholder', 'wpcom')
        ),
        30 => array(
            'type' => wpcom_member_captcha_type()
        )
    );
    return $items;
}

add_filter( 'wpcom_member_errors', 'wpcom_member_errors' );
function wpcom_member_errors( $errors ){
    $captcha = wpcom_member_captcha_type();
    $errors += array(
        'require' => __( ' is required', 'wpcom' ),
        'email' => __( 'This is not a valid email', 'wpcom' ),
        'pls_enter' => __( 'Please enter your ', 'wpcom' ),
        'password' => __( 'Your password must be 6-32 characters', 'wpcom' ),
        'passcheck' => __( 'Your passwords do not match', 'wpcom' ),
        'phone' => __( 'Please enter a valid phone number', 'wpcom' ),
        'terms' => __( 'Please read and agree with the terms', 'wpcom' ),
        'sms_code' => __( 'Your verification code error', 'wpcom' ),
        'captcha_verify' => $captcha == 'noCaptcha' ? __( 'Please slide to verify', 'wpcom' ) : __( 'Please click to verify', 'wpcom' ),
        'captcha_fail' => $captcha == 'noCaptcha' ? __( 'Slide verify failed, please try again', 'wpcom' ) : __( 'Click verify failed, please try again', 'wpcom' ),
        'nonce' => __( 'The nonce check failed', 'wpcom' ),
        'req_error' => __( 'Request Error!', 'wpcom' )
    );
    return $errors;
}

// 插入默认的配置数据
add_filter( 'wpcom_profile_tabs', 'wpcom_profile_default_tabs' );
function wpcom_profile_default_tabs( $tabs = array() ){
    global $options;
    $tabs[10] = array(
        'slug' => 'posts',
        'title' => __( 'Posts', 'wpcom' )
    );
    if ( isset($options['comments_open']) && $options['comments_open']=='1' ) {
        $tabs[20] = array(
            'slug' => 'comments',
            'title' => __( 'Comments', 'wpcom' )
        );
    }
    return $tabs;
}

add_filter( 'wpcom_socials', 'wpcom_socials' );
function wpcom_socials( $social ){
    global $options;
    $types = array(
        'qq' => array(
            'title' => _x('QQ', 'social login', 'wpcom'),
            'icon' => 'qq'
        ),
        'weibo' => array(
            'title' => _x('Weibo', 'social login', 'wpcom'),
            'icon' => 'weibo'
        ),
        'wechat' => array(
            'title' => _x('Wechat', 'social login', 'wpcom'),
            'icon' => 'wechat'
        ),
        'wechat2' => array(
            'title' => _x('Wechat', 'social login', 'wpcom'),
            'icon' => 'wechat'
        ),
        'weapp' => array(
            'title' => _x('Wechat', 'social login', 'wpcom'),
            'icon' => 'wechat'
        ),
        'google' => array(
            'title' => _x('Google', 'social login', 'wpcom'),
            'icon' => 'google-logo'
        ),
        'facebook' => array(
            'title' => _x('Facebook', 'social login', 'wpcom'),
            'icon' => 'facebook-circle'
        ),
        'twitter' => array(
            'title' => _x('Twitter', 'social login', 'wpcom'),
            'icon' => 'twitter'
        ),
        'github' => array(
            'title' => _x('Github', 'social login', 'wpcom'),
            'icon' => 'github-circle'
        )
    );

    $has_wechat = -1;
    $has_wechat2 = -1;
    $has_weapp = -1;
    if(isset($options['sl_type']) && is_array($options['sl_type']) && !empty($options['sl_type'])){
        foreach ($options['sl_type'] as $i => $type){
            if(isset($types[$type]) && isset($options['sl_id'][$i]) && $options['sl_id'][$i] && isset($options['sl_key'][$i]) && $options['sl_key'][$i]){
                $item = $types[$type];
                $item['name'] = $type;
                $item['id'] = $options['sl_id'][$i];
                $item['key'] = $options['sl_key'][$i];
                if($type === 'wechat') $has_wechat = $i*10;
                if($type === 'wechat2') {
                    $has_wechat2 = $i*10;
                    if(isset($options['sl_wechat_follow']) && isset($options['sl_wechat_follow'][$i]) && $options['sl_wechat_follow'][$i]){
                        $item['follow'] = 1;
                        $item['aeskey'] = $options['sl_wechat2_aeskey'][$i];
                        $item['welcome'] = isset($options['sl_wechat2_welc'][$i]) ? $options['sl_wechat2_welc'][$i] : '';
                    }
                }
                if($type === 'weapp') {
                    $has_weapp = $i*10;
                    $item['mobile'] = 0;
                    if(isset($options['sl_weapp_type']) && isset($options['sl_weapp_type'][$i]) && $options['sl_weapp_type'][$i]){
                        $item['mobile'] = 1;
                    }
                }
                $social[$i*10] = $item;
            }
        }
    }

    if( $has_wechat > -1 && wp_is_mobile() ) {
        unset($social[$has_wechat]);
    }else if($has_wechat > -1 && $has_wechat2 > -1 && !wp_is_mobile()){
        unset($social[$has_wechat2]);
    }else if($has_wechat2 > -1 && $has_weapp > -1 && wp_is_mobile()){
        // 移动端，开启了公众号登录和小程序登录的情况，如果是勾选认证小程序，则使用小程序，否则使用公众号
        if($social[$has_weapp]['mobile']){
            unset($social[$has_wechat2]);
        }else{
            unset($social[$has_weapp]);
        }
    }

    return $social;
}

// 社交登录配置信息更新
add_filter( 'option_izt_theme_options', 'wpcom_update_social_login', 20 );
function wpcom_update_social_login( $value ){
    if(!$value) return $value;
    if($value && is_string($value)) $value = json_decode($value, true);

    if(isset($value['sl_qq_id']) && $value['sl_qq_id'] && $value['sl_qq_key']){
        $value['sl_type'] = isset($value['sl_type']) ? $value['sl_type'] : array();
        $value['sl_id'] = isset($value['sl_id']) ? $value['sl_id'] : array();
        $value['sl_key'] = isset($value['sl_key']) ? $value['sl_key'] : array();
        $value['sl_type'][] = 'qq';
        $value['sl_id'][] = $value['sl_qq_id'];
        $value['sl_key'][] = $value['sl_qq_key'];
        unset($value['sl_qq_id']);
        unset($value['sl_qq_key']);
    }
    if(isset($value['sl_weibo_id']) && $value['sl_weibo_id'] && $value['sl_weibo_key']){
        $value['sl_type'] = isset($value['sl_type']) ? $value['sl_type'] : array();
        $value['sl_id'] = isset($value['sl_id']) ? $value['sl_id'] : array();
        $value['sl_key'] = isset($value['sl_key']) ? $value['sl_key'] : array();
        $value['sl_type'][] = 'weibo';
        $value['sl_id'][] = $value['sl_weibo_id'];
        $value['sl_key'][] = $value['sl_weibo_key'];
        unset($value['sl_weibo_id']);
        unset($value['sl_weibo_key']);
    }
    if(isset($value['sl_wechat_id']) && $value['sl_wechat_id'] && $value['sl_wechat_key']){
        $value['sl_type'] = isset($value['sl_type']) ? $value['sl_type'] : array();
        $value['sl_id'] = isset($value['sl_id']) ? $value['sl_id'] : array();
        $value['sl_key'] = isset($value['sl_key']) ? $value['sl_key'] : array();
        $value['sl_type'][] = 'wechat';
        $value['sl_id'][] = $value['sl_wechat_id'];
        $value['sl_key'][] = $value['sl_wechat_key'];
        unset($value['sl_wechat_id']);
        unset($value['sl_wechat_key']);
    }
    if(isset($value['sl_wechat2_id']) && $value['sl_wechat2_id'] && $value['sl_wechat2_key']){
        $value['sl_type'] = isset($value['sl_type']) ? $value['sl_type'] : array();
        $value['sl_id'] = isset($value['sl_id']) ? $value['sl_id'] : array();
        $value['sl_key'] = isset($value['sl_key']) ? $value['sl_key'] : array();
        $value['sl_type'][] = 'wechat2';
        $value['sl_id'][] = $value['sl_wechat2_id'];
        $value['sl_key'][] = $value['sl_wechat2_key'];
        unset($value['sl_wechat2_id']);
        unset($value['sl_wechat2_key']);
    }
    return $value;
}

add_filter( 'wpcom_approve_resend_form_items', 'wpcom_approve_resend_form_items' );
function wpcom_approve_resend_form_items( $items = array() ){
    $value = isset($_REQUEST['login']) ? $_REQUEST['login'] : '';
    $items += array(
        10 => array(
            'type' => 'text',
            'label' => _x('Username', 'label', 'wpcom'),
            'icon' => 'user',
            'name' => 'user_login',
            'require' => true,
            'placeholder' => __('Username or email address', 'wpcom'),
            'disabled' => true,
            'value' => $value
        ),
        20 => array(
            'type' => wpcom_member_captcha_type()
        ),
    );
    if($value){
        $items += array(
            30 => array(
                'type' => 'hidden',
                'name' => 'user_login',
                'value' => $value
            )
        );
    }
    return $items;
}

if( ! function_exists('is_wpcom_member_page') ){
    function is_wpcom_member_page( $page = 'account' ){
        global $options;
        if ( isset($options['member_page_' . $page]) && $options['member_page_' . $page] && is_page($options['member_page_' . $page]) ) {
            return true;
        }
        return false;
    }
}

function wpcom_get_cover_url( $user_id ){
    $cover_img = FRAMEWORK_URI . '/assets/images/lazy.png';
    if ( $user_id && $url = get_user_meta( $user_id, 'wpcom_cover', 1) ) {
        if(preg_match('/^(http|https|\/\/)/i', $url)){
            $cover_img = $url;
        }else{
            $uploads = wp_upload_dir();
            $cover_img = $uploads['baseurl'] . $url;
        }
    } else {
        global $options;
        if( isset($options['member_cover']) && $options['member_cover'] )
            $cover_img = esc_url($options['member_cover']);
    }
    $cover_img = apply_filters( 'wpcom_member_user_cover', $cover_img, $user_id );

    $cover_img = preg_replace('/^(http|https):/i', '', $cover_img);
    return $cover_img;
}

function wpcom_get_user_group($user_id){
    if($user_id) {
        $group = wp_get_object_terms($user_id, 'user-groups');
        if (!is_wp_error($group) && isset($group[0])) return $group[0];
    }
}

function wpcom_send_active_email( $user_id ){
    $user = get_user_by( 'ID', $user_id );
    if(!$user->ID) return false;

    $key = get_password_reset_key( $user );
    $url = add_query_arg( array(
        'approve' => 'pending',
        'key' => $key,
        'login' => rawurlencode( $user->user_login )
    ), wp_registration_url() );

    if ( is_multisite() ) {
        $site_name = get_network()->site_name;
    } else {
        $site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
    }

    $message = '<p>' . sprintf( __( 'Hi, %s!', 'wpcom' ), $user->display_name ) . '</p>';
    $message .= '<p>' . sprintf( __( 'Welcome to %s. To activate your account and verify your email address, please click the following link:', 'wpcom' ), $site_name ) . '</p>';
    $message .= '<p><a href="'.$url.'">'.$url.'</a></p><p></p>';
    $message .= '<p>' . __( 'If this was a mistake, ignore this email and nothing will happen.', 'wpcom' ) . "</p>";

    $title = sprintf( __( '[%s] Please verify your email address', 'wpcom' ), $site_name );

    $headers = array('Content-Type: text/html; charset=UTF-8');

    if ( $message && !wp_mail( $user->user_email, wp_specialchars_decode( $title ), $message, $headers ) )
        return __('The email could not be sent.', 'wpcom');

    return true;
}

function wpcom_send_active_to_admin( $user_id ){
    $user = get_user_by( 'ID', $user_id );
    if(!$user->ID) return __( 'The user does not exist', 'wpcom' );

    $admin_email = get_option('admin_email');

    if ( is_multisite() ) {
        $site_name = get_network()->site_name;
    } else {
        $site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
    }

    $message = '<p>' . sprintf( __( '%s has just created an account on %s!', 'wpcom' ), $user->display_name, $site_name ) . '</p>';
    $message .= '<p>' .sprintf( __( 'Username: %s', 'wpcom' ), $user->user_login ) . '</p>';
    $message .= '<p>' .sprintf( __( 'E-Mail: %s', 'wpcom' ), $user->user_email ) . '</p><p></p>';

    $message .= '<p>' . __( 'If you want to approve the new user, please go to wp-admin page.', 'wpcom' ) . '</p>';

    $title = sprintf( __( '[%s] New user account', 'wpcom' ), $site_name );

    $headers = array('Content-Type: text/html; charset=UTF-8');

    if ( $message && !wp_mail( $admin_email, wp_specialchars_decode( $title ), $message, $headers ) )
        return __('The email could not be sent.', 'wpcom');

    return true;
}

function wpcom_send_actived_email( $user_id ){
    $user = get_user_by( 'ID', $user_id );
    if(!$user->ID) return false;

    if ( is_multisite() ) {
        $site_name = get_network()->site_name;
    } else {
        $site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
    }

    $login_url = wpcom_login_url();

    $message = '<p>' . sprintf( __( 'Hi, %s!', 'wpcom' ), $user->display_name ) . '</p>';
    $message .= '<p>' . sprintf( __( 'Congratulations, your account has been activated successfully, you can now login: <a href="%s">%s</a>', 'wpcom' ), $login_url, $login_url ) . '</p>';

    $title = sprintf( __( '[%s] Welcome to join us', 'wpcom' ), $site_name );

    $headers = array('Content-Type: text/html; charset=UTF-8');

    if ( $message && !wp_mail( $user->user_email, wp_specialchars_decode( $title ), $message, $headers ) )
        return __('The email could not be sent.', 'wpcom');

    return true;
}

function wpcom_send_email_code( $email ){
    $user = wp_get_current_user();
    if(!$user->ID) return false;

    if ( is_multisite() ) {
        $site_name = get_network()->site_name;
    } else {
        $site_name = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );
    }

    $code = wpcom_generate_sms_code(sanitize_user($email, true));
    $message = '<p>' . sprintf( __( 'Hi, %s!', 'wpcom' ), $user->display_name ) . '</p>';
    $message .= '<p>' . sprintf( __( 'Your verification code is <b style="color:red;">%s</b>, please enter in 10 minutes.', 'wpcom' ), $code ) . '</p>';
    $message .= '<p></p>';
    $message .= '<p>' . __( 'If this was a mistake, ignore this email and nothing will happen.', 'wpcom' ) . "</p>";

    $title = sprintf( __( '[%s] Your verification code', 'wpcom' ), $site_name );

    $headers = array('Content-Type: text/html; charset=UTF-8');

    if ( $message && !wp_mail( $email, wp_specialchars_decode( $title ), $message, $headers ) )
        return __('The email could not be sent.', 'wpcom');

    return true;
}

add_action('wp_ajax_wpcom_is_login', 'wpcom_is_login');
add_action('wp_ajax_nopriv_wpcom_is_login', 'wpcom_is_login');
// 登录状态
function wpcom_is_login(){
    $res = array();
    $current_user = wp_get_current_user();
    if($current_user->ID){
        global $options;
        $res['result'] = 0;
        $res['avatar'] = get_avatar( $current_user->ID, 60 );
        $res['url'] = get_author_posts_url( $current_user->ID );
        if( function_exists('wpcom_account_url') ) $res['account'] = wpcom_account_url();
        $res['display_name'] = $current_user->display_name;

        $menus = array();

        $show_profile = apply_filters( 'wpcom_member_show_profile' , true );
        if($show_profile) {
            $menus[] = array(
                'url' => $res['url'],
                'title' => __('Profile', 'wpcom')
            );
        }

        if(isset($options['profile_menu_url']) && isset($options['profile_menu_title']) && $options['profile_menu_url']){
            $i=1;
            foreach($options['profile_menu_url'] as $menu){
                if($menu && $options['profile_menu_title'][$i-1]) {
                    $menus[] = array(
                        'url' => esc_url($menu),
                        'title' => $options['profile_menu_title'][$i-1]
                    );
                }
                $i++;
            }
        }

        if( isset($options['member_messages']) && $options['member_messages']=='1' && file_exists(FRAMEWORK_PATH . '/includes/messages.php') ) {
            $unread_messages = apply_filters('wpcom_unread_messages_count', 0, $current_user->ID);
            $menus[] = array(
                'url' => wpcom_subpage_url('messages'),
                'title' => __('Messages', 'wpcom') . ($unread_messages ? '<span class="num-count">'.$unread_messages.'</span>' : '')
            );

            $res['messages'] = $unread_messages;
        }

        if( isset($options['member_notify']) && $options['member_notify']=='1' && file_exists(FRAMEWORK_PATH . '/includes/notifications.php') ) {
            $unread_messages = apply_filters('wpcom_unread_notifications_count', 0, $current_user->ID);
            $menus[] = array(
                'url' => wpcom_subpage_url('notifications'),
                'title' => __('Notifications', 'wpcom') . ($unread_messages ? '<span class="num-count">'.$unread_messages.'</span>' : '')
            );

            $res['notifications'] = $unread_messages;
        }

        $menus[] = array(
            'url' => isset( $res['account'] ) ? $res['account'] : $res['url'],
            'title' => __('Account', 'wpcom')
        );
        $menus[] = array(
            'url' => wp_logout_url(),
            'title' => __( 'Logout', 'wpcom' )
        );
        $res['menus'] = apply_filters('wpcom_profile_menus', $menus);
    }else{
        $res['result'] = -1;
    }

    if ( function_exists('is_woocommerce') ) {
        ob_start();

        woocommerce_mini_cart();

        $mini_cart = ob_get_clean();

        $data = array(
            'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array(
                    'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
                )
            ),
            'cart_hash' => apply_filters( 'woocommerce_add_to_cart_hash', WC()->cart->get_cart_for_session() ? md5( json_encode( WC()->cart->get_cart_for_session() ) ) : '', WC()->cart->get_cart_for_session() ),
        );

        $res['wc'] = $data;
    }
    echo wp_json_encode($res);
    die();
}

function is_wpcom_enable_phone(){
    global $options;
    return isset($options['enable_phone']) && $options['enable_phone'];
}

function wpcom_check_sms_code($phone, $val){
    // 检查session、验证码值
    $key = 'code_'.$phone;
    $code = WPCOM_Session::get($key);
    if($code && $code == $val ){
        return true;
    }
    return false;
}

function wpcom_generate_sms_code($phone){
    $code = '' . rand(0,9) . '' . rand(0,9) . '' . rand(0,9) . '' . rand(100,999);
    $key = 'code_'.$phone;
    WPCOM_Session::set($key, $code, 600);
    return $code;
}

function wpcom_generate_unique_username( $username ) {
    $username = sanitize_user( $username, true );
    static $i;
    if ( null === $i ) {
        $i = 1;
    } else {
        $i ++;
    }
    if ( ! username_exists( $username ) ) {
        return $username;
    }
    $new_username = sprintf( '%s%s', $username, $i );
    if ( ! username_exists( $new_username ) ) {
        return $new_username;
    } else {
        return call_user_func( __FUNCTION__, $username );
    }
}

function wpcom_mobile_phone_exists($phone){
    $args = array(
        'meta_key'     => 'mobile_phone',
        'meta_value'   => $phone,
    );
    $users = get_users($args);
    return isset($users[0]) && $users[0]->ID ? $users[0]->ID : false;
}

function wpcom_aliyun_sdk( $csessionid, $token, $sig, $scene ){
    include_once FRAMEWORK_PATH . '/member/aliyun-php-sdk/load.php';
    return AfsCheckRequest($csessionid, $token, $sig, $scene);
}

if(!function_exists('wpcom_sms_code_sender')){
    function wpcom_sms_code_sender( $phone, $nationCode='86' ){
        global $options;
        if($phone){
            $api = isset($options['sms_api']) && $options['sms_api'] ? $options['sms_api'] : 0;
            $code = wpcom_generate_sms_code($phone);
            $nationCode = preg_replace('/^\+/', '', $nationCode);

            /**
             * 可通过此 hook 自定义短信发送接口
             * @param string $phone 手机号码
             * @param string $code 验证码
             * @param string $nationCode 国家代码
             *
             * @return object {result:0, errmsg: '发送成功'} result = 0 表示发送成功，如发送失败可通过 errmsg 返回错误信息
             */
            $sender = apply_filters('wpcom_sms_code_sender', null, $phone, $code, $nationCode);
            if($sender !== null) return $sender;

            if($api=='0'){
                include_once FRAMEWORK_PATH . '/member/qcloudsms/index.php';
                $params = array($code, 10);
                return wpcom_qcloud_sms_sender($phone, $params, $nationCode);
            }else if($api=='1'){
                include_once FRAMEWORK_PATH . '/member/aliyun-php-sdk/dysmsapi.php';
                $sms = new Dysmsapi();
                return $sms->send($phone, $code, $nationCode);
            }
        }
    }
}

function wpcom_member_captcha_type(){
    global $options;
    $type = '';
    if(isset($options['member_captcha']) && $options['member_captcha']!==''){
        switch ($options['member_captcha']){
            case '0': // 防水墙
                $type = 'TCaptcha';
                break;
            case '1': // 阿里云
                $type = 'noCaptcha';
                break;
            case '2': // hCaptcha
                $type = 'hCaptcha';
                break;
            case '3': // reCAPTCHA
                $type = 'reCAPTCHA';
                break;
        }
    }
    return $type;
}

function wpcom_is_empty_mail($mail){
    if(preg_match('/@email\.empty$/i', $mail) || preg_match('/@weixin\.qq$/i', $mail) || preg_match('/@(weapp|swan|alipay|toutiao|qq)\.app$/i', $mail)){
        return true;
    }
    return false;
}

function wpcom_send_notification($to, $title, $content){
    if(isset($GLOBALS['_notification']) && $GLOBALS['_notification'] && $to && $title && $content){
        return $GLOBALS['_notification']->add_notification($to, $title, $content);
    }
    return false;
}

function wpcom_get_scan_login_info(){
    global $options;
    if(wp_is_mobile()) return false;
    $scan_login = isset($options['scan_login']) && $options['scan_login'] ? $options['scan_login'] : 0;
    if($scan_login){
        $socials = apply_filters( 'wpcom_socials', array() );
        $method = '';
        foreach ($socials as $s){
            if(isset($s['icon']) && $s['icon'] === 'wechat'){
                $method = $s;
                break;
            }
        }
        if($method){
            return array(
                'type' => $scan_login,
                'api' => $method
            );
        }
    }
    return false;
}