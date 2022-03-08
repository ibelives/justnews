<?php defined( 'ABSPATH' ) || exit; global $options;?>
<div class="member-account-wrap">
    <div class="member-account-nav">
        <div class="member-account-user">
            <div class="member-account-avatar">
                <?php echo get_avatar( $user->ID, 200 );?>
                <span class="edit-avatar" data-user="<?php echo $user->ID;?>"><?php WPCOM::icon('camera');?></span>
                <?php wp_nonce_field( 'wpcom_cropper', 'wpcom_cropper_nonce', 0 );?>
            </div>
            <?php $show_profile = apply_filters( 'wpcom_member_show_profile' , true );?>
            <h3 class="member-account-name">
                <?php if($show_profile){?><a href="<?php echo get_author_posts_url($user->ID); ?>" target="_blank"><?php echo $user->display_name;?></a>
                <?php }else { echo $user->display_name; } ?>
            </h3>
            <?php if($user->description){ ?><div class="member-account-dio"><?php echo $user->description;?></div><?php } ?>
            <?php if( $show_profile && isset($options['tougao_on']) && $options['tougao_on']=='1' ){ ?>
                <a class="btn btn-primary btn-block member-account-tg" href="<?php echo esc_url(wpcom_addpost_url());?>">
                <?php echo (isset($options['tougao_btn']) && $options['tougao_btn'] ? $options['tougao_btn'] : WPCOM::icon('quill-pen', false).__('Submit Post', 'wpcom'));?>
                </a>
            <?php } ?>
        </div>
        <ul class="member-account-menu">
            <?php $current_tab = null;
            foreach ($tabs as $t){
                if( $t['slug'] == $subpage && isset($t['parent']) && $t['parent'] ) {
                    $current_tab = $t;
                    $current_tab['slug'] = $t['parent'];
                }
            }
            foreach ( $tabs as $i => $tab ) { if( $i<999 ) {
                if( !$current_tab && $tab['slug'] == $subpage ) $current_tab = $tab; ?>
                <li class="member-nav-<?php echo $tab['slug']; if( $current_tab && $tab['slug']==$current_tab['slug'] ) echo ' active';?>">
                    <a href="<?php echo wpcom_subpage_url($tab['slug'])?>">
                        <?php WPCOM::icon($tab['icon'], true, 'member-nav-icon');?><?php echo $tab['title']?>
                    </a>
                </li>
            <?php } } ?>
        </ul>
    </div>
    <div class="member-account-content">
        <h2 class="member-account-title"><?php echo $current_tab['title'];?></h2>
        <?php if( isset($GLOBALS['validation']) && empty( $GLOBALS['validation']['error'] ) ) { ?>
        <div class="alert alert-success" role="alert">
            <div class="close" data-dismiss="alert"><?php WPCOM::icon('close');?></div>
            <?php _e( 'Updated successfully.', 'wpcom' ); ?>
        </div>
        <?php } ?>
        <?php do_action( 'wpcom_account_tabs_' . $subpage ); ?>
    </div>
</div>
