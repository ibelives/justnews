<?php
defined( 'ABSPATH' ) || exit;

global $options;
$sms_login = is_wpcom_enable_phone() && isset($options['sms_login']) && $options['sms_login'] ? $options['sms_login'] : '0';
$social_login_on = isset($options['social_login_on']) && $options['social_login_on']=='1' ? 1 : 0;
$classes = apply_filters('wpcom_login_form_classes', 'member-form-wrap member-form-login');
$logo = isset($options['login_logo']) && $options['login_logo'] ? wp_get_attachment_url( $options['login_logo'] ) : wpcom_logo();
$scan_login = wpcom_get_scan_login_info();
if($scan_login) $classes .= ' member-form-login-' . ($scan_login['type']=='1'?'pc':'qr');
?>
<div class="<?php echo $classes;?>">
    <div class="member-form-inner">
        <div class="member-form-head">
            <div class="member-form-head">
                <a class="member-form-logo" href="<?php bloginfo('url');?>" rel="home"><img class="j-lazy" src="<?php echo $logo; ?>" alt="<?php echo esc_attr(get_bloginfo( 'name' ));?>"></a>
            </div>
        </div>
        <?php if($sms_login){ ?>
            <ul class="member-form-tab">
                <li class="active"><a href="#" data-type="1"><?php $sms_login=='2' ? _e('Log in with SMS', 'wpcom') : _e('Log in with username', 'wpcom');?></a></li>
                <li><a href="#" data-type="2"><?php $sms_login!='2' ? _e('Log in with SMS', 'wpcom') : _e('Log in with username', 'wpcom');?></a></li>
            </ul>
        <?php }else{ ?>
            <div class="member-form-title">
                <h3><?php _e('Sign In', 'wpcom');?></h3>
                <span class="member-switch pull-right"><?php _e('No account?', 'wpcom');?> <a href="<?php echo wp_registration_url();?>"><?php _e('Create one!', 'wpcom');?></a></span>
            </div>
        <?php } ?>
        <?php
        // 默认登录表单
        do_action( 'wpcom_login_form' );
        // 开启扫码登录
        if($scan_login && isset($scan_login['api'])){ ?>
            <div class="member-form-qr" data-type="<?php echo $scan_login['api']['name'];?>">
                <div class="member-form-qr-img"><div class="wechat-qrcode-loading"><?php WPCOM::icon('loader');?></div></div>
                <div class="member-form-qr-text">请使用微信扫描二维码登录</div>
            </div>
        <?php } ?>
        <?php if( $social_login_on ){ ?>
            <div class="member-form-footer">
                <div class="member-form-social">
                    <span><?php _e('Sign in with', 'wpcom');?></span>
                    <?php do_action( 'wpcom_social_login' );?>
                </div>
            </div>
        <?php }
        if($sms_login || $scan_login){ ?>
            <div class="member-form-footer member-form-footer2">
                <span class="member-switch"><?php _e('No account?', 'wpcom');?> <a href="<?php echo wp_registration_url();?>"><?php _e('Create one!', 'wpcom');?></a></span>
            </div>
        <?php } ?>
    </div>
    <?php if($scan_login){ ?>
        <div class="member-form-switcher j-login-switcher" data-type="<?php echo ($scan_login['type']=='1'?'qr':'pc');?>">
            <?php WPCOM::icon($scan_login['type']=='1' ? 'login-qr' : 'login-pc');?>
        </div>
    <?php } ?>
</div>
<a href="<?php bloginfo('url'); ?>" class="btn btn-primary btn-home"><?php WPCOM::icon('home-fill'); _e('Go back to home', 'wpcom');?></a>