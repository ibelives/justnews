<?php
global $is_sticky, $options, $feature_post, $feature_style, $post;
$video = get_post_meta( $post->ID, 'wpcom_video', true );
$thumb = WPCOM::thumbnail_url($post->ID, isset($feature_post) && $feature_post ? 'full' : '');
if(!$thumb){
    $img_id = isset($options['post_thumb']) && $options['post_thumb'] ? $options['post_thumb'] : '';
    if($img_id) $thumb = wp_get_attachment_image_url( $img_id, 'full' );
}
$attr = $thumb ? wpcom_lazybg($thumb, 'item-img') : 'class="item-img"';
$li_class = 'item' . ($is_sticky&&is_sticky()?' item-sticky':'');
$div = 'li';
if(isset($feature_style) && $feature_style && ($feature_style==3||$feature_style==4)) $div = 'div'; ?>
<<?php echo $div;?> class="<?php echo $li_class;?>">
<div class="item-inner">
    <?php if(isset($feature_style) && $feature_style==1){ ?>
    <div <?php echo($thumb ? wpcom_lazybg($thumb, 'item-wrap-bg') : 'class="item-wrap-bg"');?>></div>
    <div class="item-container">
        <?php } ?>
        <div <?php echo $attr;?>>
            <a class="item-wrap<?php echo $video?' item-video':'';?>" href="<?php echo esc_url( get_permalink() )?>" title="<?php echo esc_attr(get_the_title());?>"<?php echo wpcom_post_target();?> rel="bookmark">
            <span class="item-title">
                <?php if($is_sticky&&is_sticky()){ ?><span class="sticky-post"><?php _ex('TOP', '置顶', 'wpcom');?></span><?php } ?> <?php the_title();?>
            </span>
                <span class="item-meta">
                <span class="item-meta-left"><?php echo wpcom_format_date(get_post_time( 'U', false, $post ));?></span>
                <span class="item-meta-right">
                    <?php
                    $post_metas = isset($options['post_metas']) && is_array($options['post_metas']) ? $options['post_metas'] : array();
                    foreach ( $post_metas as $meta ) echo wpcom_post_metas($meta, false);
                    ?>
                </span>
            </span>
            </a>
            <?php
            $category = get_the_category();
            $cat = $category?$category[0]:'';
            if($cat){
                ?>
                <a class="item-category" href="<?php echo get_category_link($cat->cat_ID);?>" target="_blank"><?php echo $cat->name;?></a><?php } ?>
        </div>
        <?php if(isset($feature_style) && $feature_style==1){ ?>
    </div>
<?php } ?>
</div>
</<?php echo $div;?>>