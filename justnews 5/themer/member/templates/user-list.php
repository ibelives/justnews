<?php defined( 'ABSPATH' ) || exit;?>
<ul class="wpcom-user-list user-cols-<?php echo $cols;?>">
    <?php foreach ( $users as $user ){ ?>
        <li class="wpcom-user-item">
            <div class="user-item-inner">
                <?php echo $this->load_template('user-card', array('user' => $user));?>
            </div>
        </li>
    <?php } ?>
</ul>