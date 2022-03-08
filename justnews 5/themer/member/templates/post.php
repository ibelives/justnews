<?php
defined( 'ABSPATH' ) || exit;

global $options, $is_author;
?>
<li class="item">
    <?php $has_thumb = get_the_post_thumbnail(); if($has_thumb){ ?>
        <div class="item-img">
            <a class="item-img-inner" href="<?php the_permalink();?>" title="<?php echo esc_attr(get_the_title());?>" target="_blank">
                <?php the_post_thumbnail(); ?>
            </a>
            <?php
            $category = get_the_category();
            $cat = $category?$category[0]:'';
            if($cat){
                ?>
                <a class="item-category" href="<?php echo get_term_link($cat->cat_ID);?>" target="_blank"><?php echo $cat->name;?></a>
            <?php } ?>
        </div>
    <?php } ?>
    <div class="item-content<?php echo isset($is_author) && $is_author && (current_user_can('edit_published_posts') || $post->post_status =='draft' || $post->post_status =='pending' ) ? ' item-edit' : '';?>">
        <?php if(isset($is_author) && $is_author && (current_user_can('edit_published_posts') || $post->post_status =='draft' || $post->post_status =='pending' )){ ?>
            <a class="btn btn-primary btn-xs edit-link" href="<?php echo get_edit_link($post->ID);?>" target="_blank">编辑</a>
        <?php } ?>
        <h2 class="item-title">
            <a href="<?php the_permalink();?>" title="<?php echo esc_attr(get_the_title());?>" target="_blank">
                <?php if(isset($is_author) && $post->post_status=='draft'){ echo '<span>【草稿】</span>'; }else if(isset($is_author) && $post->post_status=='pending'){ echo '<span>【待审核】</span>'; }?>
                <?php the_title();?>
            </a>
        </h2>
        <div class="item-excerpt">
            <?php the_excerpt(); ?>
        </div>
        <div class="item-meta">
            <?php
            if(!$has_thumb){
                $category = get_the_category();
                $cat = $category?$category[0]:'';
                if($cat){ ?>
                    <a class="item-meta-li" href="<?php echo get_category_link($cat->cat_ID);?>" target="_blank"><?php echo $cat->name;?></a>
                <?php } } ?>
            <span class="item-meta-li date"><?php echo format_date(get_post_time( 'U', false, $post ));?></span>
            <?php
            $post_metas = isset($options['post_metas']) && is_array($options['post_metas']) ? $options['post_metas'] : array();
            foreach ( $post_metas as $meta ) echo wpcom_post_metas($meta);
            ?>
        </div>
    </div>
</li>