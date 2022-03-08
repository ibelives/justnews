<ul class="notify-list wp-block-wpcom-accordion panel-group">
    <?php if( is_array($list) && $list) {
        foreach ($list as $item) { ?>
            <li class="notify-item panel panel-default j-notification status-<?php echo $item->status2 ?: $item->status;?>" data-id="<?php echo $item->ID;?>">
                <div class="notify-item-head panel-heading" role="tab" id="heading-notify-<?php echo $item->ID; ?>">
                    <div class="notify-item-title panel-title">
                        <a role="button" data-toggle="collapse" data-parent="#accordion-notify" href="#accordion-notify-<?php echo $item->ID; ?>" aria-expanded="false" aria-controls="#accordion-notify-<?php echo $item->ID; ?>">
                            <?php echo $item->title; ?>
                            <span class="notify-item-time"><?php echo get_date_from_gmt($item->time, 'Y-m-d H:i');?></span>
                        </a>
                    </div>
                </div>
                <div class="notify-item-text panel-collapse collapse" id="accordion-notify-<?php echo $item->ID; ?>" role="tabpanel" aria-labelledby="heading-notify-<?php echo $item->ID; ?>" aria-expanded="false">
                    <div class="panel-body">
                        <?php echo $item->content; ?>
                    </div>
                </div>
            </li>
        <?php } }else{ ?>
        <li class="member-account-empty notify-empty"><?php echo wpcom_empty_icon('notification');?><?php _e('No notification', 'wpcom');?></li>
    <?php } ?>
</ul>
<?php if($pages>1){ wpcom_pagination(5, array('numpages' => $pages, 'paged' => $paged, 'url' => wpcom_subpage_url('notifications'), 'paged_arg' => 'pageid')); } ?>