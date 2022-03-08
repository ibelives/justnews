<?php
defined( 'ABSPATH' ) || exit;

global $options;
$social_login_on = isset($options['social_login_on']) && $options['social_login_on']=='1' ? 1 : 0;
$classes = apply_filters('wpcom_register_form_classes', 'member-form-wrap member-form-register');
$logo = isset($options['login_logo']) && $options['login_logo'] ? wp_get_attachment_url( $options['login_logo'] ) : wpcom_logo();
?>
<div class="<?php echo $classes;?>">
    <div class="member-form-inner">
        <?php if ( !get_option('users_can_register') ) { ?>
        <div class="alert alert-warning text-center"><?php _e('User registration is currently not allowed.', 'wpcom');?></div>
        <?php } ?>
        <div class="member-form-head">
            <a class="member-form-logo" href="<?php bloginfo('url');?>" rel="home"><img class="j-lazy" src="<?php echo $logo; ?>" alt="<?php echo esc_attr(get_bloginfo( 'name' ));?>"></a>
        </div>
        <div class="member-form-title">
            <h3><?php _e('Sign Up', 'wpcom');?></h3>
            <span class="member-switch pull-right"><?php _e('Already have an account?', 'wpcom');?> <a href="<?php echo wp_login_url();?>"><?php echo _x('Sign in', 'sign', 'wpcom');?></a></span>
        </div>
        <?php do_action( 'wpcom_register_form' ); ?>
        <?php if( $social_login_on ){ ?>
        <div class="member-form-footer">
            <div class="member-form-social">
                <span><?php _e('Sign up with', 'wpcom');?></span>
                <?php do_action( 'wpcom_social_login' );?>
            </div>
        </div>
        <?php } ?>
    </div>
</div>
<a href="<?php bloginfo('url'); ?>" class="btn btn-primary btn-home"><?php WPCOM::icon('home-fill'); _e('Go back to home', 'wpcom');?></a>
