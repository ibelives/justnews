<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_user_list_widget extends WPCOM_Widget {
    public function __construct() {
        $this->widget_cssclass = 'widget_user_list';
        $this->widget_description = '在页面边栏展示指定用户信息';
        $this->widget_id = 'user-list';
        $this->widget_name = '#推荐用户';
        $this->settings = array(
            'title' => array(
                'name' => '标题'
            ),
            'follow' => array(
                'name' => '关注',
                'type' => 'toggle',
                'desc' => '是否显示关注按钮，需要先开启关注功能（主题设置>用户中心>用户关注）'
            ),
            'ids' => array(
                'name' => '用户id',
                'desc' => '多个用户id请用逗号分隔'
            )
        );
        parent::__construct();
    }

    public function widget( $args, $instance ) {
        if ( $this->get_cached_widget( $args ) ) return;
        ob_start();
        $follow = empty( $instance['follow'] ) ? 0 : $instance['follow'];
        $this->widget_start( $args, $instance );
        ?>
        <ul class="user-list-wrap">
            <?php
            $_ids = str_replace("，",",", $instance['ids']);
            $ids = explode(',', $_ids);
            foreach ($ids as $id) {
                $id = trim($id);
                $user = get_user_by('ID', $id);
                if(isset($user->ID)){
                $author_url = get_author_posts_url($id); ?>
                <li class="user-list-item j-user-card" data-user="<?php echo $user->ID;?>">
                    <a href="<?php echo $author_url ?>" target="_blank"><?php echo get_avatar($id);?></a>
                    <div class="user-list-content">
                        <div class="user-list-hd">
                            <a class="user-list-name" href="<?php echo $author_url ?>" target="_blank">
                                <?php echo apply_filters('wpcom_user_display_name', '<span class="user-name-inner">'.$user->display_name.'</span>', $user->ID); ?>
                            </a>
                            <?php if(class_exists('WPCOM_Follow') && $follow) {?>
                                <a class="user-list-btn btn-follow j-follow" data-user="<?php echo $id ?>">
                                    <?php echo apply_filters('wpcom_follow_btn_html', '');?>
                                </a>
                            <?php } ?>
                        </div>
                        <a href="<?php echo $author_url ?>">
                            <p class="user-list-desc"><?php echo $user->description; ?></p>
                        </a>
                    </div>
                </li>
            <?php } } ?>
        </ul>

        <?php
        $this->widget_end( $args );
        echo $this->cache_widget( $args, ob_get_clean() );
    }
}

// register widget
function register_wpcom_user_list_widget() {
    register_widget( 'WPCOM_user_list_widget' );
}
add_action( 'widgets_init', 'register_wpcom_user_list_widget' );