<div class="hidden-content-wrap" id="<?php echo esc_attr($id);?>">
    <svg class="hidden-content-bg" width="879" height="205" viewBox="0 45 820 160" xmlns="http://www.w3.org/2000/svg">
        <defs>
            <linearGradient x1="100%" y1="23.013%" x2="9.11%" y2="68.305%" id="la"><stop stop-color="var(--theme-color)" stop-opacity=".01" offset=".533%"/><stop stop-color="var(--theme-color)" stop-opacity=".15" offset="100%"/></linearGradient>
            <linearGradient x1="81.006%" y1="27.662%" x2=".899%" y2="69.383%" id="lb"><stop stop-color="var(--theme-color)" stop-opacity=".15" offset="0%"/><stop stop-color="var(--theme-color)" stop-opacity=".01" offset="100%"/></linearGradient>
        </defs>
        <g fill="none" fill-rule="evenodd"><path d="M9.871 124.063c7.9 9.12 28.19 21.598 46.66 5.41 19.19-16.818 27.986-42.87 15.531-51.971-12.454-9.102-37.594-6.819-59.32 1.62-21.727 8.44-10.77 35.822-2.87 44.941z" fill="url(#la)" transform="translate(67.938 .937)"/><path d="M610.783 44.063c-25.145 39.42-47.054 78.134-30.12 105.532 16.932 27.398 74.377 30.672 171.4 6.468 97.021-24.203 52.5-112.016 17.794-141.793-34.705-29.777-133.929-9.626-159.074 29.793z" fill="url(#lb)" transform="translate(67.938 .937)"/><path fill-opacity=".06" fill="var(--theme-color)" d="M325.477 101.51l-11.132 16.084-1.86-1.118L323.96 100.2z"/><path fill-opacity=".06" fill="var(--theme-color)" d="M363.904 94.28l-1.494 1.24 8.566 10.255 1.487-1.383z"/><path fill-opacity=".06" fill="var(--theme-color)" d="M215.386 150.719v.88l14.355 2.179v-.821z"/><path fill-opacity=".06" fill="var(--theme-color)" d="M144.006 125.22l.63.83 11.67-6.978-.569-.758-11.38 6.686"/><path fill-opacity=".06" fill="var(--theme-color)" d="M530.724 87.128l-.41.92 13.227 4.995.396-.942z"/><path fill-opacity=".06" fill="var(--theme-color)" d="M613.697 99.184l.65.711 13.93-15.484-.8-.593z"/><path fill-opacity=".06" fill="var(--theme-color)" d="M605.186 140.762l-.794.433 6.098 17.285.821-.419z"/></g>
    </svg>
    <div class="hidden-content">
        <?php if($type == '0'){ ?>
            <div class="hidden-content-icon"><?php WPCOM::icon('comment-lock'); ?></div>
            <p class="hidden-content-desc"><?php echo $tips ?: '您需要回复本文后才能查看完整内容';?></p>
            <a class="btn btn-primary hidden-content-btn hidden-content-btn-comment" href="#comments">立即回复</a>
            <div class="hidden-content-refresh">已经回复？<a class="refresh-url" href="javascript:;">立即刷新<?php WPCOM::icon('refresh')?></a></div>
        <?php }else if($type == '1'){ ?>
            <div class="hidden-content-icon"><?php WPCOM::icon('user-lock'); ?></div>
            <p class="hidden-content-desc"><?php echo $tips ?: '您需要登录才能查看完整内容';?></p>
            <a class="btn btn-primary hidden-content-btn hidden-content-btn-login" href="<?php echo wp_login_url();?>">立即登录</a>
            <div class="hidden-content-refresh">已经登录？<a class="refresh-url" href="javascript:;">立即刷新<?php WPCOM::icon('refresh')?></a></div>
        <?php } else if($type == '2'){
            global $current_user;
            $user_group = isset($user_group) && $user_group ? explode(',', $user_group) : array();
            $groups = $user_group ? get_terms(array(
                'taxonomy' => 'user-groups',
                'include' => $user_group,
                'hide_empty' => false
            )) : array();
            ?>
            <div class="hidden-content-icon"><?php WPCOM::icon('group-lock'); ?></div>
            <?php if($groups){
                $group_html = '<div class="hidden-content-groupname">';
                foreach ($groups as $group){
                    if($group->name) {
                        $group_html .= '<b>'.$group->name.'</b>';
                    }
                }
                $group_html .= '</div>';
                ?>
                <div class="hidden-content-desc"><?php echo $tips ?: '此内容仅对以下用户分组的用户可见：';?><?php echo $group_html;?></div>
            <?php } else { ?>
                <p class="hidden-content-desc"><?php echo $tips ?: '此内容仅对指定用户分组的用户可见';?></p>
            <?php }
            if($current_user && isset($current_user->ID) && $current_user->ID){ ?>
                <p class="hidden-content-forbidden">抱歉，您暂无查看权限</p>
            <?php }else{ ?>
                <a class="btn btn-primary hidden-content-btn hidden-content-btn-login" href="<?php echo wp_login_url();?>">立即登录</a>
            <?php }
            ?>
            <div class="hidden-content-refresh">已有权限？<a class="refresh-url" href="javascript:;">立即刷新<?php WPCOM::icon('refresh')?></a></div>
        <?php } ?>
    </div>
</div>