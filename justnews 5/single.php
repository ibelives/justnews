<?php
global $options,$current_user;
$dashang_display = isset($options['dashang_display']) ? $options['dashang_display'] : 0;
$show_author = isset($options['show_author']) && $options['show_author']=='0' ? 0 : 1;
$video = get_post_meta( $post->ID, 'wpcom_video', true );
$video = $video ?: '';
$sidebar = wpcom_post_sidebar( $post->ID );
$sidebar = !(!$sidebar && $sidebar!=='');
$class = $sidebar ? 'main' : 'main main-full';
$show_indent = isset($options['show_indent']) ? $options['show_indent'] : get_post_meta($post->ID, 'wpcom_show_indent', true);

if( $video!='' && preg_match('/^(http:\/\/|https:\/\/|\/\/).*/i', $video) ){
    $vthumb = get_the_post_thumbnail_url( $post->ID,'large' );
    $video = '<video id="wpcom-video" width="860" preload="none" src="'.$video.'" poster="'.$vthumb.'" playsinline controls></video>';
}
get_header();?>
    <div class="wrap container<?php echo $video!=='' ? ' has-video' : '';?>">
        <?php if( isset($options['breadcrumb']) && $options['breadcrumb']=='1' ) wpcom_breadcrumb('breadcrumb'); ?>
        <main class="<?php echo esc_attr($class);?>">
            <?php while( have_posts() ) : the_post();?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <div class="entry-main">
                        <?php
                        if( $video!='' ){ ?>
                            <div class="entry-video">
                                <?php echo do_shortcode($video); ?>
                            </div>
                        <?php } ?>
                        <?php do_action('wpcom_echo_ad', 'ad_single_0');?>
                        <div class="entry-head">
                            <h1 class="entry-title"><?php the_title();?></h1>
                            <div class="entry-info">
                                <?php
                                if($show_author) {
                                    $author = get_the_author_meta( 'ID' );
                                    $author_url = get_author_posts_url( $author );
                                    $author_name = get_the_author();
                                    ?>
                                    <span class="vcard">
                                        <a class="nickname url fn j-user-card" data-user="<?php echo $author;?>" href="<?php echo $author_url; ?>"><?php echo $author_name;?></a>
                                    </span>
                                    <span class="dot">•</span>
                                <?php }
                                if(isset($options['show_origin']) && $options['show_origin'] && $ori_title = get_post_meta($post->ID, 'wpcom_original_name', true)){
                                    $ori_url = get_post_meta($post->ID, 'wpcom_original_url', true);
                                    $ori_html = $ori_url ? '<a href="'.esc_url($ori_url).'" target="_blank">'.$ori_title.'</a>' : $ori_title;
                                    $pre_txt = $options['origin_title'] ?: '文章来源: ';?>
                                    <span class="origin"><?php echo $pre_txt . $ori_html;?></span>
                                    <span class="dot">•</span>
                                <?php } ?>
                                <time class="entry-date published" datetime="<?php echo get_post_time( 'c', false, $post );?>" pubdate>
                                    <?php echo wpcom_format_date(get_post_time( 'U', false, $post ));?>
                                </time>
                                <span class="dot">•</span>
                                <?php the_category( ', ', '', false ); ?>
                                <?php if(function_exists('the_views')) {
                                    $views = intval(get_post_meta($post->ID, 'views', true));
                                    $views_options = get_option('views_options');
                                    if(function_exists('should_views_be_displayed') && should_views_be_displayed($views_options)){ ?>
                                        <span class="dot">•</span>
                                        <span><?php echo sprintf( __('%s views', 'wpcom'), $views); ?></span>
                                    <?php }} ?>
                            </div>
                        </div>
                        <?php do_action('wpcom_echo_ad', 'ad_single_1');?>

                        <?php if($post->post_excerpt){ ?>
                            <div class="entry-excerpt entry-summary">
                                <?php the_excerpt(); ?>
                            </div>
                        <?php } ?>
                        <div class="entry-content<?php echo $show_indent?' text-indent':''?>">
                            <?php the_content();?>
                            <?php wpcom_pagination();?>
                            <?php wpcom_post_copyright();?>
                        </div>

                        <div class="entry-tag"><?php the_tags('', '');?></div>
                        <div class="entry-action">
                            <div class="btn-zan" data-id="<?php the_ID(); ?>"><?php WPCOM::icon('thumb-up-fill'); ?> <?php _e( 'Like', 'wpcom' );?> <span class="entry-action-num">(<?php $likes = get_post_meta($post->ID, 'wpcom_likes', true); echo $likes?$likes:0;?>)</span></div>

                            <?php if($dashang_display==1 && isset($options['dashang_1_img']) && ($options['dashang_1_img'] || $options['dashang_2_img'])){ ?>
                                <div class="btn-dashang">
                                    <?php WPCOM::icon('cny-circle-fill'); ?> <?php _e('Donate', 'wpcom');?>
                                    <span class="dashang-img<?php if($options['dashang_1_img']&&$options['dashang_2_img']){echo ' dashang-img2';}?>">
                                                    <?php if($options['dashang_1_img']){ ?>
                                                        <span>
                                                        <img src="<?php echo esc_url($options['dashang_1_img'])?>" alt="<?php echo esc_attr($options['dashang_1_title'])?>"/>
                                                            <?php echo $options['dashang_1_title'];?>
                                                    </span>
                                                    <?php } ?>
                                        <?php if($options['dashang_2_img']){ ?>
                                            <span>
                                                        <img src="<?php echo esc_url($options['dashang_2_img'])?>" alt="<?php echo esc_attr($options['dashang_2_title'])?>"/>
                                                            <?php echo $options['dashang_2_title'];?>
                                                    </span>
                                        <?php } ?>
                                                </span>
                                </div>
                            <?php } ?>
                        </div>

                        <div class="entry-bar">
                            <div class="entry-bar-inner clearfix">
                                <?php if($show_author) { ?>
                                    <div class="author pull-left">
                                        <?php
                                        $display_name = get_avatar( $author, 60, '',  $author_name) . '<span class="author-name">' . $author_name . '</span>';
                                        $display_name = apply_filters('wpcom_user_display_name', $display_name, $author, 'full');
                                        ?>
                                        <a data-user="<?php echo $author;?>" target="_blank" href="<?php echo $author_url; ?>" class="avatar j-user-card">
                                            <?php echo $display_name; ?>
                                        </a>
                                    </div>
                                <?php } ?>
                                <div class="info <?php echo $show_author?'pull-right':'text-center';?>">
                                    <div class="info-item meta">
                                        <?php if(isset($options['member_enable']) && $options['member_enable']=='1'){ ?>
                                            <a class="meta-item j-heart" href="javascript:;" data-id="<?php the_ID(); ?>"><?php WPCOM::icon('star'); ?> <span class="data"><?php $favorites = get_post_meta($post->ID, 'wpcom_favorites', true); echo $favorites?$favorites:0;?></span></a><?php } ?>
                                        <?php if ( isset($options['comments_open']) && $options['comments_open']=='1' ) { ?><a class="meta-item" href="#comments"><?php WPCOM::icon('comment'); ?> <span class="data"><?php echo get_comments_number();?></span></a><?php } ?>
                                        <?php if($dashang_display==0 && isset($options['dashang_1_img']) && ($options['dashang_1_img'] || $options['dashang_2_img'])){ ?>
                                            <a class="meta-item dashang" href="javascript:;">
                                                <?php WPCOM::icon('cny-circle-fill'); ?> <?php _e('Donate', 'wpcom');?>
                                                <span class="dashang-img<?php if($options['dashang_1_img']&&$options['dashang_2_img']){echo ' dashang-img2';}?>">
                                                    <?php if($options['dashang_1_img']){ ?>
                                                        <span>
                                                        <img src="<?php echo esc_url($options['dashang_1_img'])?>" alt="<?php echo esc_attr($options['dashang_1_title'])?>"/>
                                                            <?php echo $options['dashang_1_title'];?>
                                                    </span>
                                                    <?php } ?>
                                                    <?php if($options['dashang_2_img']){ ?>
                                                        <span>
                                                        <img src="<?php echo esc_url($options['dashang_2_img'])?>" alt="<?php echo esc_attr($options['dashang_2_title'])?>"/>
                                                            <?php echo $options['dashang_2_title'];?>
                                                    </span>
                                                    <?php } ?>
                                                </span>
                                            </a>
                                        <?php } ?>
                                    </div>
                                    <div class="info-item share">
                                        <a class="meta-item mobile j-mobile-share" href="javascript:;" data-id="<?php the_ID();?>" data-qrcode="<?php the_permalink();?>"><?php WPCOM::icon('share'); ?> <?php _e('Generate poster', 'wpcom');?></a>
                                        <?php if(isset($options['post_shares'])){ if($options['post_shares']){ foreach ($options['post_shares'] as $share){ ?>
                                            <a class="meta-item <?php echo $share;?>" data-share="<?php echo $share;?>" target="_blank" rel="nofollow" href="#">
                                                <?php WPCOM::icon($share); ?>
                                            </a>
                                        <?php } } }else{ ?>
                                            <a class="meta-item wechat" data-share="wechat" href="#"><?php WPCOM::icon('wechat'); ?></a>
                                            <a class="meta-item weibo" data-share="weibo" target="_blank" rel="nofollow" href="#"><?php WPCOM::icon('weibo'); ?></a>
                                            <a class="meta-item qq" data-share="qq" target="_blank" rel="nofollow" href="#"><?php WPCOM::icon('qq'); ?></a>
                                        <?php } ?>
                                    </div>
                                    <div class="info-item act">
                                        <a href="javascript:;" id="j-reading"><?php WPCOM::icon('article'); ?></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php if(!isset($options['post_nextprev']) || (isset($options['post_nextprev']) && $options['post_nextprev'])){ ?>
                        <div class="entry-page">
                            <?php
                            $in_same_term = isset($options['post_nextprev']) && $options['post_nextprev'] == '2';
                            $pre = get_previous_post($in_same_term);
                            $next = get_next_post($in_same_term);
                            if($pre){ $pbg = get_the_post_thumbnail_url($pre); ?>
                                <div <?php echo wpcom_lazybg($pbg, 'entry-page-prev'.($pbg?'':' entry-page-nobg')); ?>>
                                    <a href="<?php echo get_the_permalink($pre);?>" title="<?php echo esc_attr(get_the_title($pre));?>" rel="prev">
                                        <span><?php echo get_the_title($pre);?></span>
                                    </a>
                                    <div class="entry-page-info">
                                        <span class="pull-left"><?php wpcom::icon('arrow-left-double');?> <?php echo _x( 'Previous', 'single', 'wpcom' );?></span>
                                        <span class="pull-right"><?php echo wpcom_format_date(get_post_time( 'U', false, $pre ));?></span>
                                    </div>
                                </div>
                            <?php } ?>
                            <?php if($next){ $nbg = get_the_post_thumbnail_url($next); ?>
                                <div <?php echo wpcom_lazybg($nbg, 'entry-page-next'.($nbg?'':' entry-page-nobg')); ?>>
                                    <a href="<?php echo get_the_permalink($next);?>" title="<?php echo esc_attr(get_the_title($next));?>" rel="next">
                                        <span><?php echo get_the_title($next);?></span>
                                    </a>
                                    <div class="entry-page-info">
                                        <span class="pull-right"><?php echo _x( 'Next', 'single', 'wpcom' );?> <?php wpcom::icon('arrow-right-double');?></span>
                                        <span class="pull-left"><?php echo wpcom_format_date(get_post_time( 'U', false, $next ));?></span>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    <?php do_action('wpcom_echo_ad', 'ad_single_2');?>
                    <?php
                    $type = isset($options['related_show_type']) && $options['related_show_type'] ? $options['related_show_type'] : 'default';
                    if($type=='1') {
                        $type = 'image';
                    } else if($type=='0'){
                        $type = 'list';
                    }
                    $related = wpcom_get_related_post( (isset($options['related_num'])?$options['related_num']:6), ($related_news=$options['related_news'])?$related_news:__('Related posts', 'wpcom'), 'templates/loop-'.$type, 'cols-3 post-loop post-loop-'.$type, $type=='image' || $type=='card');
                    if($related){ ?>
                        <div class="entry-related-posts">
                            <?php echo $related;?>
                        </div>
                    <?php }
                    if ( isset($options['comments_open']) && $options['comments_open']=='1' ) { comments_template(); } ?>
                </article>
            <?php endwhile; ?>
        </main>
        <?php if( $sidebar ){ ?>
            <aside class="sidebar">
                <?php get_sidebar();?>
            </aside>
        <?php } ?>
    </div>
<?php get_footer();?>