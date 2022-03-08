<?php
global $is_sticky, $options, $post;
$video = get_post_meta( $post->ID, 'wpcom_video', true );
$show_author = isset($options['show_author']) && $options['show_author']=='0' ? 0 : 1;
$show_author = $show_author && isset($options['member_enable']) && $options['member_enable']=='1';
?>
<li class="item<?php echo $is_sticky&&is_sticky()?' item-sticky':'';?>">
    <div class="item-inner">
        <div class="item-img">
            <a class="item-thumb<?php echo $video?' item-video':'';?>" href="<?php echo esc_url( get_permalink() )?>" title="<?php echo esc_attr(get_the_title());?>"<?php echo wpcom_post_target();?> rel="bookmark">
                <?php the_post_thumbnail('full');?>
            </a>
            <?php
            $category = get_the_category();
            $cat = $category?$category[0]:'';
            if($cat){
                ?>
                <a class="item-category" href="<?php echo get_category_link($cat->cat_ID);?>" target="_blank"><?php echo $cat->name;?></a><?php } ?>
        </div>
        <h2 class="item-title">
            <a href="<?php echo esc_url( get_permalink() )?>"<?php echo wpcom_post_target();?> rel="bookmark">
                <?php if($is_sticky&&is_sticky()){ ?><span class="sticky-post"><?php _ex('TOP', '置顶', 'wpcom');?></span><?php } ?> <?php the_title();?>
            </a>
        </h2>

        <?php if($show_author){ ?>
            <div class="item-meta-items">
                <?php
                $post_metas = isset($options['post_metas']) && is_array($options['post_metas']) ? $options['post_metas'] : array();
                foreach ( $post_metas as $meta ) echo wpcom_post_metas($meta);
                ?>
            </div>
            <div class="item-meta-author">
                <?php
                $author = get_the_author_meta( 'ID' );
                $author_url = get_author_posts_url( $author );
                ?>
                <a data-user="<?php echo $author;?>" target="_blank" href="<?php echo $author_url; ?>" class="avatar j-user-card">
                    <?php echo get_avatar( $author, 60, '',  get_the_author());?>
                    <span><?php echo get_the_author(); ?></span>
                </a>
                <div class="item-meta-right"><?php echo wpcom_format_date(get_post_time( 'U', false, $post ));?></div>
            </div>
        <?php }else{ ?>
            <div class="item-meta">
                <span class="item-meta-left"><?php echo wpcom_format_date(get_post_time( 'U', false, $post ));?></span>
                <span class="item-meta-right">
                <?php
                $post_metas = isset($options['post_metas']) && is_array($options['post_metas']) ? $options['post_metas'] : array();
                foreach ( $post_metas as $meta ) echo wpcom_post_metas($meta);
                ?>
            </span>
            </div>
        <?php } ?>
    </div>
</li>