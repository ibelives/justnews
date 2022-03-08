<?php
global $options, $post, $is_author, $is_sticky;
preg_match_all('/<img[^>]*src=[\'"]([^\'"]+)[\'"].*>/iU', get_the_content(), $matches);
$is_multimage = is_multimage();
if(isset($matches[1]) && isset($matches[1][3]) && $is_multimage == 1) {
    get_template_part('templates/loop', 'multimage');
    return;
}else if($is_multimage == 2 || $is_multimage == 3){
    get_template_part('templates/loop', 'large-image');
    return;
}
$show_author = isset($options['show_author']) && $options['show_author']=='0' ? 0 : 1;
$show_author = $show_author && isset($options['member_enable']) && $options['member_enable']=='1';
$img_right = isset($options['list_img_right']) && $options['list_img_right']=='1' ? 1 : 0;
$has_thumb = get_the_post_thumbnail(null, 'default');
?>
<li class="item<?php echo $img_right ? ' item2':'';?><?php echo $is_sticky&&is_sticky()?' item-sticky':''; echo $has_thumb?'':' item-no-thumb';?>">
    <?php if($has_thumb){
        $video = get_post_meta( $post->ID, 'wpcom_video', true );?>
    <div class="item-img<?php echo $video?' item-video':'';?>">
        <a class="item-img-inner" href="<?php the_permalink();?>" title="<?php echo esc_attr(get_the_title());?>"<?php echo wpcom_post_target();?> rel="bookmark">
            <?php echo $has_thumb; ?>
        </a>
        <?php
        $category = get_the_category();
        $cat = $category?$category[0]:'';
        if($cat){
        ?>
        <a class="item-category" href="<?php echo get_category_link($cat->cat_ID);?>" target="_blank"><?php echo $cat->name;?></a>
        <?php } ?>
    </div>
    <?php } ?>
    <div class="item-content<?php echo isset($is_author) && $is_author && (current_user_can('edit_published_posts') || $post->post_status =='draft' || $post->post_status =='pending' ) ? ' item-edit' : '';?>">
        <?php if(isset($is_author) && $is_author && (current_user_can('edit_published_posts') || $post->post_status =='draft' || $post->post_status =='pending' )){?>
            <a class="btn btn-primary btn-xs edit-link" href="<?php echo get_edit_link($post->ID);?>" target="_blank">编辑</a>
        <?php } ?>
        <h2 class="item-title">
            <a href="<?php the_permalink();?>"<?php echo wpcom_post_target();?> rel="bookmark">
                <?php if(isset($is_author) && $post->post_status=='draft'){ echo '<span>【草稿】</span>'; }else if(isset($is_author) && $post->post_status=='pending'){ echo '<span>【待审核】</span>'; }?>
                <?php if($is_sticky&&is_sticky()){ ?><span class="sticky-post"><?php _ex('TOP', '置顶', 'wpcom');?></span><?php } ?> <?php the_title();?>
            </a>
        </h2>
        <div class="item-excerpt">
            <?php the_excerpt(); ?>
        </div>
        <div class="item-meta">
            <?php if( $show_author ){ ?>
            <div class="item-meta-li author">
                <?php
                $author = get_the_author_meta( 'ID' );
                $author_url = get_author_posts_url( $author );
                ?>
                <a data-user="<?php echo $author;?>" target="_blank" href="<?php echo $author_url; ?>" class="avatar j-user-card">
                    <?php echo get_avatar( $author, 60, '',  get_the_author());?>
                    <span><?php echo get_the_author(); ?></span>
                </a>
            </div>
            <?php } ?>
            <?php
            if(!$has_thumb){
                $category = get_the_category();
                $cat = $category?$category[0]:'';
                if($cat){ ?>
                    <a class="item-meta-li" href="<?php echo get_category_link($cat->cat_ID);?>" target="_blank"><?php echo $cat->name;?></a>
                <?php } } ?>
            <span class="item-meta-li date"><?php echo wpcom_format_date(get_post_time( 'U', false, $post ));?></span>
            <div class="item-meta-right">
                <?php
                $post_metas = isset($options['post_metas']) && is_array($options['post_metas']) ? $options['post_metas'] : array();
                foreach ( $post_metas as $meta ) echo wpcom_post_metas($meta);
                ?>
            </div>
        </div>
    </div>
</li>
<?php do_action('wpcom_echo_ad', 'ad_flow');?>