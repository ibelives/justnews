<?php
defined( 'ABSPATH' ) || exit;

global $options;
$classes = 'member-form-wrap member-reg-notice';
?>
<div class="<?php echo $classes;?>">
    <div class="status-icon status-icon-success"><?php WPCOM::icon('clock');?></div>
    <?php echo wpautop($notice);?>
</div>
