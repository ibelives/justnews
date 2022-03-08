<?php
global $options, $is_author, $is_sticky, $post;
$show_author = isset($options['show_author']) && $options['show_author']=='0' ? 0 : 1;
$show_author = $show_author && isset($options['member_enable']) && $options['member_enable']=='1';
?>
    <li class="item item3<?php echo $is_sticky&&is_sticky()?' item-sticky':'';?>">
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
            <a class="item-images" href="<?php the_permalink();?>" title="<?php echo esc_attr(get_the_title());?>"<?php echo wpcom_post_target();?>>
                <?php
                preg_match_all('/<img[^>]*src=[\'"]([^\'"]+)[\'"].*>/iU', get_the_content(), $matches);
                $imgs = array_slice($matches[1], 0, 4);
                foreach($imgs as $img){
                    echo '<span><i '.wpcom_lazybg($img, 'item-images-el') .'></i></span>';
                }?>
            </a>
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
                <span class="item-meta-li date"><?php echo wpcom_format_date(get_post_time( 'U', false, $post ));?></span>
                <?php
                $category = get_the_category();
                $cat = $category?$category[0]:'';
                if($cat){ ?>
                    <span class="item-meta-li dot">•</span>
                    <a class="item-meta-li" href="<?php echo get_category_link($cat->cat_ID);?>" target="_blank"><?php echo $cat->name;?></a>
                <?php } ?>
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