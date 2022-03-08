<?php
global $options, $current_user, $post;
get_header();?>
    <div class="wrap container">
        <?php if( isset($options['breadcrumb']) && $options['breadcrumb']=='1' ) { ?>
            <ol class="breadcrumb">
                <li class="home"><a href="<?php echo get_bloginfo('url')?>"><?php _e('Home', 'wpcom');?></a>
                    <?php if( isset($options['kx_page']) && $options['kx_page'] && $kx = get_post($options['kx_page']) ){ ?>
                <li><?php WPCOM::icon('arrow-right-3');?><a href="<?php echo get_permalink($kx->ID);?>"><?php echo $kx->post_title;?></a></li>
                <?php } ?>
                <li class="active"><?php WPCOM::icon('arrow-right-3');?><?php the_title();?></li>
            </ol>
        <?php } ?>
        <?php while( have_posts() ) : the_post();?>
            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <div class="entry-main">
                    <div class="entry-head">
                        <h1 class="entry-title"><?php the_title();?></h1>
                    </div>
                    <div class="entry-content clearfix">
                        <?php the_excerpt(); ?>
                        <?php if(get_the_post_thumbnail()){ ?>
                            <a class="kx-img" href="<?php the_permalink();?>" title="<?php echo esc_attr(get_the_title());?>" target="_blank">
                                <?php the_post_thumbnail('full'); ?>
                            </a>
                        <?php } ?>
                        <?php wpcom_pagination();?>
                    </div>
                    <div class="entry-footer kx-item" data-id="<?php the_ID();?>">
                        <div class="kx-meta clearfix">
                            <time class="entry-date published" datetime="<?php echo get_post_time( 'c', false, $post );?>" pubdate>
                                <?php echo wpcom_format_date(get_post_time( 'U', false, $post ));?>
                            </time>
                            <span class="j-mobile-share" data-id="<?php the_ID();?>" data-qrcode="<?php the_permalink();?>">
                                    <?php WPCOM::icon('share');?> <?php _e('Generate poster', 'wpcom');?>
                                </span>
                            <span class="hidden-xs"><?php _e('Share to: ', 'wpcom');?></span>
                            <?php if(isset($options['post_shares'])){ if($options['post_shares']){ foreach ($options['post_shares'] as $share){ ?>
                                <a class="share-icon <?php echo $share;?> hidden-xs" target="_blank" data-share="<?php echo $share;?>"><?php WPCOM::icon($share);?></a>
                            <?php } } }else{ ?>
                                <a class="share-icon wechat hidden-xs" data-share="wechat"><?php WPCOM::icon('wechat');?></a>
                                <a class="share-icon weibo hidden-xs" target="_blank" data-share="weibo"><?php WPCOM::icon('weibo');?></a>
                                <a class="share-icon qq hidden-xs" target="_blank" data-share="qq"><?php WPCOM::icon('qq');?></a>
                            <?php } ?>
                            <a class="share-icon copy hidden-xs"><?php WPCOM::icon('copy');?></a>
                        </div>
                    </div>
                </div>
                <div class="entry-page">
                    <p><?php previous_post_link(_x( 'Previous: %link', 'kx', 'wpcom' ), '%title'); ?></p>
                    <p><?php next_post_link(_x( 'Next: %link', 'kx', 'wpcom' ), '%title'); ?></p>
                </div>
                <?php if ( isset($options['comments_open']) && $options['comments_open']=='1' ) { comments_template(); } ?>
            </article>
        <?php endwhile; ?>
    </div>
<?php get_footer();?>