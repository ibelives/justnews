<?php
defined( 'ABSPATH' ) || exit;

if( !class_exists('WPCOM_icon_url_widget') ) :
class WPCOM_icon_url_widget extends WPCOM_Widget {
    public function __construct() {
        $this->widget_cssclass = 'widget_icon_url';
        $this->widget_description = '可以添加图标快捷跳转链接';
        $this->widget_id = 'icon-url';
        $this->widget_name = '#图标链接';
        $this->settings = array(
            'urls' => array(
                'type' => 'repeat',
                'options' => array(
                    'name' => array(
                        'name' => '标题'
                    ),
                    'icon' => array(
                        'name' => '图标',
                        'type' => 'icon',
                        'img' => 1,
                        'desc' => '如果使用图片，尺寸建议为90*90px'
                    ),
                    'url' => array(
                        'name' => '链接',
                        'type' => 'url'
                    )
                )
            )
        );
        parent::__construct();
    }

    public function widget( $args, $instance ) {
        if ( $this->get_cached_widget( $args ) ) return;
        ob_start();
        $this->widget_start( $args, $instance ); ?>
        <div class="icon-list">
            <?php if($instance['icon']) { foreach($instance['icon'] as $i => $icon){ ?>
                <a class="icon-list-item" <?php echo WPCOM::url($instance['url'][$i]); ?>>
                    <?php WPCOM::icon($instance['icon'][$i]);?>
                    <span class="list-item-title"><?php echo $instance['name'][$i] ?></span>
                </a>
                <?php } } ?>
            </div>
        <?php
        $this->widget_end( $args );
        echo $this->cache_widget( $args, ob_get_clean() );
    }
}

// register widget
function wpcom_icon_url_widget() {
    register_widget( 'WPCOM_icon_url_widget' );
}
add_action( 'widgets_init', 'wpcom_icon_url_widget' );
endif;