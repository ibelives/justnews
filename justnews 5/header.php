<?php global $options, $is_submit_page; ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="renderer" content="webkit">
    <meta name="viewport" content="initial-scale=1.0,maximum-scale=5,width=device-width,viewport-fit=cover">
    <title><?php wp_title( isset($options['title_sep']) && $options['title_sep'] ? $options['title_sep'] : ' | ', true, 'right' ); ?></title>
    <?php wp_head();?>
    <!--[if lte IE 9]><script src="<?php echo get_template_directory_uri()?>/js/update.js"></script><![endif]-->
</head>
<body <?php body_class()?>>
<?php $header_class = isset($options['header_style']) && $options['header_style'] ? ' header-style-2' : '';?>
<header class="header<?php echo $header_class;?>">
    <div class="container clearfix">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target=".navbar-collapse" aria-label="menu">
                <span class="icon-bar icon-bar-1"></span>
                <span class="icon-bar icon-bar-2"></span>
                <span class="icon-bar icon-bar-3"></span>
            </button>
            <?php $h1_tag = 'div'; if(is_home()||is_front_page()) $h1_tag = 'h1'; ?>
            <<?php echo $h1_tag;?> class="logo">
                <a href="<?php bloginfo('url');?>" rel="home"><img src="<?php echo wpcom_logo()?>" alt="<?php echo esc_attr(get_bloginfo( 'name' ));?>"></a>
            </<?php echo $h1_tag;?>>
        </div>
        <div class="collapse navbar-collapse">
            <?php
            wp_nav_menu( array(
                    'theme_location'    => 'primary',
                    'depth'             => 3,
                    'container'         => 'nav',
                    'container_class'   => 'navbar-left primary-menu',
                    'menu_class'        => 'nav navbar-nav',
                    'advanced_menu'     => true,
                    'fallback_cb'       => 'WPCOM_Nav_Walker::fallback',
                    'walker'            => new WPCOM_Nav_Walker())
            ); ?>
            <div class="navbar-action pull-right">
                <div class="navbar-search-icon j-navbar-search"><?php WPCOM::icon('search');?></div>
                <?php do_action('wpcom_woo_cart_icon');?>
                <?php if( isset($options['member_enable']) && $options['member_enable']=='1' ) { ?>
                    <div id="j-user-wrap">
                        <a class="login" href="<?php echo wp_login_url(); ?>"><?php _e('Sign in', 'wpcom');?></a>
                        <a class="login register" href="<?php echo wp_registration_url(); ?>"><?php _e('Sign up', 'wpcom');?></a>
                    </div>
                    <?php if( !isset($is_submit_page) && isset($options['tougao_on']) && $options['tougao_on']=='1' ){ ?><a class="btn btn-primary btn-xs publish" href="<?php echo esc_url(wpcom_addpost_url());?>">
                        <?php echo (isset($options['tougao_btn']) && $options['tougao_btn'] ? $options['tougao_btn'] : __('Submit Post', 'wpcom'));?></a>
                    <?php } ?>
                <?php } ?>
            </div>
            <form class="navbar-search" action="<?php echo get_bloginfo('url');?>" method="get" role="search">
                <div class="navbar-search-inner">
                    <?php WPCOM::icon('close', true, 'navbar-search-close');?>
                    <input type="text" name="s" class="navbar-search-input" autocomplete="off" placeholder="<?php _e('Type your search here ...', 'wpcom');?>" value="<?php echo get_search_query(); ?>">
                    <button class="navbar-search-btn" type="submit"><?php WPCOM::icon('search');?></button>
                </div>
            </form>
        </div><!-- /.navbar-collapse -->
    </div><!-- /.container -->
</header>
<div id="wrap">