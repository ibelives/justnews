<div class="profile-tab" data-user="<?php echo $user_id;?>">
    <div class="profile-tab-item active"><?php _e('Following', 'wpcom');?></div>
    <div class="profile-tab-item"><?php _e('Followers', 'wpcom');?></div>
</div>
<div class="profile-tab-content active">
    <?php
    global $wpcom_member;
    if($follows && is_array($follows)){ ?>
        <ul class="follow-items">
            <?php foreach ($follows as $follow) echo $wpcom_member->load_template('follow', array('follow' => $follow)); ?>
        </ul>
        <?php if($total>$number) { ?><div class="load-more-wrap"><div href="javascript:;" class="btn load-more j-user-follows"><?php _e( 'Load more posts', 'wpcom' );?></div></div><?php } ?>
    <?php }else{ ?>
        <div class="profile-no-content">
            <?php echo wpcom_empty_icon('follow'); if( get_current_user_id()==$user_id ){ _e( 'You have not followed any users.', 'wpcom' ); }else{ _e( 'This user has not followed any users.', 'wpcom' ); } ?>
        </div>
    <?php } ?>
</div>
<div class="profile-tab-content">
    <div class="profile-no-content follow-items-loading">
        <img class="loading" src="<?php echo FRAMEWORK_URI; ?>/assets/images/loading.gif" alt="loading"> <?php _e('Loading...', 'wpcom');?>
    </div>
    <ul class="follow-items" style="display: none;"></ul>
    <div class="load-more-wrap" style="display: none;"><div href="javascript:;" class="btn load-more j-user-followers" data-page="0"><?php _e( 'Load more posts', 'wpcom' );?></div></div>
    <div class="profile-no-content" style="display: none;">
        <?php echo wpcom_empty_icon('follow'); if( get_current_user_id()==$user_id ){ _e( 'You have not been followed by any users.', 'wpcom' ); }else{ _e( 'This user has not been followed by any users.', 'wpcom' ); } ?>
    </div>
</div>