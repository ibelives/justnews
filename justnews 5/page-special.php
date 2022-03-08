<?php
// TEMPLATE NAME: 专题列表
global $options, $post;
$num = isset($options['special_per_page']) && $options['special_per_page'] ? $options['special_per_page'] : 10;
if( isset($options['special_on']) && $options['special_on'] ) {
    $special = get_special_list($num, 1);
} else {
    $special = array();
}
$banner = get_post_meta( $post->ID, 'wpcom_banner', true );
get_header();
while( have_posts() ) : the_post();
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
        <?php if(!$banner){ ?>
            <div class="banner banner-2">
                <div class="banner-inner container">
                    <h1><?php the_title(); ?></h1>
                    <?php $content = get_the_content(); if($content!=='') {?><div class="page-description"><?php the_content();?></div><?php } ?>
                </div>
            </div>
        <?php } endwhile; ?>
        <div class="special-wrap">
            <div class="special-list row">
                <?php foreach( $special as $sp ) {
                    $thumb = get_term_meta( $sp->term_id, 'wpcom_thumb', true );
                    $link = get_term_link( $sp->term_id );
                    ?>
                    <div class="col-md-12 col-xs-24 special-item-wrap">
                        <div class="special-item">
                            <div class="special-item-top">
                                <div class="special-item-thumb">
                                    <a href="<?php echo $link;?>" target="_blank">
                                        <?php echo wpcom_lazyimg($thumb, $sp->name);?>
                                    </a>
                                </div>
                                <div class="special-item-info">
                                    <div class="special-item-title">
                                        <h2><a href="<?php echo $link;?>" target="_blank"><?php echo $sp->name;?></a></h2>
                                        <a class="special-item-more" href="<?php echo $link;?>"><?php echo _x('Read More', 'topic', 'wpcom'); WPCOM::icon('arrow-right')?></a>
                                    </div>
                                    <div class="special-item-desc">
                                        <?php echo term_description($sp->term_id, 'special');?>
                                    </div>
                                    <div class="special-item-meta">
                                        <span class="special-item-count"><?php echo sprintf(__('%s posts', 'wpcom'), $sp->count);?></span>
                                        <div class="special-item-share">
                                            <span class="hidden-xs"><?php _e('Share to: ', 'wpcom');?></span>
                                            <?php if(isset($options['post_shares'])){ if($options['post_shares']){ foreach ($options['post_shares'] as $share){ ?>
                                                <a class="share-icon <?php echo $share;?> hidden-xs" target="_blank" data-share="<?php echo $share;?>" data-share-callback="zt_share" rel="noopener">
                                                    <?php WPCOM::icon($share);?>
                                                </a>
                                            <?php } } }else{ ?>
                                                <a class="share-icon wechat hidden-xs" data-share="wechat" data-share-callback="zt_share" rel="noopener"><?php WPCOM::icon('wechat');?></a>
                                                <a class="share-icon weibo hidden-xs" target="_blank" data-share="weibo" data-share-callback="zt_share" rel="noopener"><?php WPCOM::icon('weibo');?></a>
                                                <a class="share-icon qq hidden-xs" target="_blank" data-share="qq" data-share-callback="zt_share" rel="noopener"><?php WPCOM::icon('qq');?></a>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <ul class="special-item-bottom">
                                <?php
                                $args = array(
                                    'posts_per_page' => 3,
                                    'tax_query' => array(
                                        array(
                                            'taxonomy' => 'special',
                                            'field' => 'term_id',
                                            'terms' => $sp->term_id
                                        )
                                    )
                                );
                                $postslist = get_posts( $args );
                                foreach($postslist as $post){ setup_postdata($post);?>
                                    <li><a title="<?php echo esc_attr(get_the_title());?>" href="<?php the_permalink();?>" target="_blank"><?php the_title();?></a></li>
                                <?php } wp_reset_postdata(); ?>
                            </ul>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <?php $terms = get_terms(array('taxonomy' => 'special', 'hide_empty' => false)); if($terms && is_array($terms) && $num<count($terms)){ ?>
            <div class="load-more-wrap">
                <div class="btn load-more"><?php _e('Load more topics', 'wpcom');?></div>
            </div>
            <?php } ?>
        </div>
    </div>
<?php get_footer();?>