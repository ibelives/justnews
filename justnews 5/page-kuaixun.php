<?php
// TEMPLATE NAME: 快讯页面
global $options;
global $options, $post;
$sidebar = get_post_meta( $post->ID, 'wpcom_sidebar', true );
$sidebar = !(!$sidebar && $sidebar!=='');
$class = $sidebar ? 'main' : 'main main-full';
get_header();
$banner = get_post_meta( $post->ID, 'wpcom_banner', true );
if($banner){
    $banner_height = get_post_meta( $post->ID, 'wpcom_banner_height', true );
    $text_color = get_post_meta( $post->ID, 'wpcom_text_color', true );
    $bHeight = intval($banner_height ?: 300);
    $bColor = ($text_color ?: 0) ? ' banner-white' : '';
    $description = term_description(); ?>
    <div <?php echo wpcom_lazybg($banner, 'banner'.$bColor, 'height:'.$bHeight.'px;');?>>
        <div class="banner-inner container">
            <h1><?php the_title(); ?></h1>
            <?php $content = get_the_content(); if($content!=='') {?><div class="page-description"><?php the_content();?></div><?php } ?>
        </div>
    </div>
<?php } ?>
    <div class="wrap container">
        <?php if( isset($options['breadcrumb']) && $options['breadcrumb']=='1' ) wpcom_breadcrumb('breadcrumb'); ?>
        <div class="<?php echo esc_attr($class);?>">
            <div class="sec-panel sec-panel-kx">
                <?php
                $per_page = get_option('posts_per_page');
                $arg = array(
                    'posts_per_page' => $per_page,
                    'post_status' => array( 'publish' ),
                    'post_type' => 'kuaixun'
                );
                $posts = get_posts($arg);
                $cur_day = '';
                global $post;
                if( $posts ) { ?>
                    <div class="kx-list">
                        <?php  foreach ( $posts as $post ) { setup_postdata( $post );
                            if($cur_day != $date = get_the_date(get_option('date_format'))){
                                $pre_day = '';
                                $week = date_i18n('D', get_the_date('U'));
                                if(date_i18n(get_option('date_format'), current_time('timestamp')) == $date) {
                                    $pre_day = __('Today', 'wpcom') . ' • ';
                                }else if(date_i18n(get_option('date_format'), current_time('timestamp')-86400) == $date){
                                    $pre_day = __('Yesterday', 'wpcom') . ' • ';
                                }
                                echo '<div class="kx-date">'. $pre_day .$date . ' • ' . $week.'</div>';
                                if($cur_day=='') echo '<div class="kx-new"></div>';
                                $cur_day = $date;
                            } ?>
                            <div class="kx-item" data-id="<?php the_ID();?>">
                                <span class="kx-time"><?php the_time('H:i');?></span>
                                <div class="kx-content">
                                    <h2><?php if(isset($options['kx_url_enable']) &&  $options['kx_url_enable'] == '1'){ ?>
                                            <a href="<?php the_permalink();?>" target="_blank"><?php the_title();?></a>
                                        <?php } else{ the_title(); } ?></h2>
                                    <?php the_excerpt();?>
                                    <?php if(get_the_post_thumbnail()){ ?>
                                        <?php if(isset($options['kx_url_enable']) &&  $options['kx_url_enable'] == '1'){ ?>
                                            <a class="kx-img" href="<?php the_permalink();?>" title="<?php echo esc_attr(get_the_title());?>" target="_blank"><?php the_post_thumbnail('full'); ?></a>
                                        <?php }else{ ?>
                                            <div class="kx-img"><?php the_post_thumbnail('full'); ?></div>
                                        <?php } ?>
                                    <?php } ?>
                                </div>
                                <div class="kx-meta clearfix" data-url="<?php the_permalink();?>">
                                    <span class="j-mobile-share" data-id="<?php the_ID();?>" data-qrcode="<?php the_permalink();?>">
                                        <?php WPCOM::icon('share');?> <?php _e('Generate poster', 'wpcom');?>
                                    </span>
                                    <span class="hidden-xs"><?php _e('Share to: ', 'wpcom');?></span>
                                    <?php if(isset($options['post_shares'])){ if($options['post_shares']){ foreach ($options['post_shares'] as $share){ ?>
                                        <a class="share-icon <?php echo $share;?> hidden-xs" target="_blank" data-share="<?php echo $share;?>" data-share-callback="kx_share" rel="noopener">
                                            <?php WPCOM::icon($share);?>
                                        </a>
                                    <?php } } }else{ ?>
                                        <a class="share-icon wechat hidden-xs" data-share="wechat" data-share-callback="kx_share" rel="noopener"><?php WPCOM::icon('wechat');?></a>
                                        <a class="share-icon weibo hidden-xs" target="_blank" data-share="weibo" data-share-callback="kx_share" rel="noopener"><?php WPCOM::icon('weibo');?></a>
                                        <a class="share-icon qq hidden-xs" target="_blank" data-share="qq" data-share-callback="kx_share" rel="noopener"><?php WPCOM::icon('qq');?></a>
                                    <?php } ?>
                                    <span class="share-icon copy hidden-xs"><?php WPCOM::icon('copy');?></span>
                                </div>
                            </div>
                        <?php } ?>
                        <?php if(count($posts)==$per_page){ ?>
                            <div class="load-more-wrap">
                                <div class="btn load-more j-load-kx"><?php _e('Load more topics', 'wpcom');?></div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } wp_reset_postdata(); ?>
            </div>
        </div>
        <?php if( $sidebar ){ ?>
            <aside class="sidebar">
                <?php get_sidebar();?>
            </aside>
        <?php } ?>
    </div>
<?php get_footer();?>