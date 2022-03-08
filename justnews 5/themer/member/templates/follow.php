<?php $url = get_author_posts_url( $follow->ID );?>
<li class="follow-item">
    <div class="follow-item-avatar">
        <a href="<?php echo $url;?>" target="_blank"><?php echo get_avatar( $follow->ID, 120, '', $follow->display_name );?></a>
    </div>
    <div class="follow-item-text">
        <h2 class="follow-item-name"><a href="<?php echo $url;?>" target="_blank"><?php echo $follow->display_name;?></a></h2>
        <div class="follow-item-desc"><?php echo $follow->description;?></div>
        <div class="follow-item-meta"><?php do_action('wpcom_user_data_stats', $follow, false);?></div>
    </div>
    <div class="follow-item-action">
        <?php do_action('wpcom_follow_item_action', $follow->ID);?>
    </div>
</li>