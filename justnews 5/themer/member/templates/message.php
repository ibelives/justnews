<?php
$sender = $message->from_user == $user;
?>
<div class="modal-message-item<?php echo $sender ? ' message-sender' : '';?>" data-id="<?php echo $message->ID;?>">
    <div class="modal-message-time"><?php echo get_date_from_gmt($message->time, 'Y-m-d H:i');?></div>
    <div class="modal-message-inner">
        <?php if($sender){ ?>
            <div class="modal-message-status"></div>
            <div class="modal-message-content"><div class="message-text"><?php echo convert_smilies($message->content);?></div></div>
            <div class="modal-message-avatar"><img src="<?php echo get_avatar_url( $message->from_user );?>"></div>
        <?php } else { ?>
            <div class="modal-message-avatar"><img src="<?php echo get_avatar_url( $message->from_user );?>"></div>
            <div class="modal-message-content"><div class="message-text"><?php echo convert_smilies($message->content);?></div></div>
            <div class="modal-message-status"></div>
        <?php } ?>
    </div>
</div>