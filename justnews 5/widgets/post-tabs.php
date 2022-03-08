<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_post_tabs_widget extends WPCOM_Widget {
    public function __construct() {
        $this->widget_cssclass = 'widget_post_tabs';
        $this->widget_description = '支持Tab切换的文章展示小工具';
        $this->widget_id = 'post-tabs';
        $this->widget_name = '#文章Tab切换';
        $this->settings = array(
            'title' => array(
                'name' => '标题',
                'desc' => '可选，不填写效果更佳，填写后不显示图标'
            ),
            'number' => array(
                'value' => 10,
                'name' => '显示数量'
            ),
            'tabs' => array(
                'type' => 'rp',
                'max' => 4,
                'name' => 'Tab选项',
                'o' => array(
                    'label' => array(
                        'name' => 'Tab标题'
                    ),
                    'icon' => array(
                        'name' => '图标',
                        't' => 'ic',
                        'f' => 'title:',
                        'desc' => '可选，Tab标题前的图标'
                    ),
                    'category'    => array(
                        'type'  => 'cat-single',
                        'name' => '分类',
                        'desc' => '可选，不选择则不限定分类'
                    ),
                    'order' => array(
                        'name' => '排序方式',
                        't' => 's',
                        'o' => array(
                            '0' => '发布时间',
                            '1' => '评论数',
                            '2' => '浏览数(需安装WP-PostViews插件)',
                            '3' => '随机排序'
                        )
                    ),
                    'days' => array(
                        'name' => '时间范围',
                        'f' => 'order:2',
                        'desc' => '限制时间范围，以天为单位，例如填写365，则表示仅获取1年内的文章，可避免获取太久之前的文章，留空或0则不限制'
                    )
                )
            )
        );
        parent::__construct();
    }

    public function widget( $args, $instance ) {
        if ( $this->get_cached_widget( $args ) ) return;
        ob_start();

        $title  = empty( $instance['title'] ) ? '' : esc_attr( $instance['title'] );
        $instance['title'] = '';
        $number = empty( $instance['number'] ) ? $this->settings['number']['value'] : absint( $instance['number'] );

        $this->widget_start( $args, $instance ); ?>

        <div class="post-tabs-hd<?php echo $title ? ' tab-has-title' : '' ?>">
            <?php if($title){ ?>
                <h3 class="widget-title"><?php echo $title;?></h3>
            <?php } ?>
            <div class="post-tabs-hd-inner post-tabs-<?php echo isset($instance['label']) && $instance['label'] ? count($instance['label']) : 0;?>">
                <?php if(isset($instance['label']) && $instance['label']) { foreach ($instance['label'] as $i => $tab){ ?>
                    <div class="post-tabs-item j-post-tab<?php echo $i===0 ? ' active' : '';?>">
                        <?php if($instance['icon'][$i]) WPCOM::icon($instance['icon'][$i]); echo $tab;?>
                    </div>
                <?php } } ?>
            </div>
        </div>
        <?php if(isset($instance['label']) && $instance['label']) { foreach ($instance['label'] as $i => $tab){
            $order = $instance['order'][$i];
            $category = $instance['category'][$i];
            $orderby = 'date';
            if($order==1){
                $orderby = 'comment_count';
            }else if($order==2){
                $orderby = 'meta_value_num';
            }else if($order==3){
                $orderby = 'rand';
            }
            $_args = array(
                'cat' => $category,
                'post_status' => 'publish',
                'showposts' => $number,
                'orderby' => $orderby,
                'ignore_sticky_posts' => 1
            );
            if($orderby=='meta_value_num') {
                $_args['meta_key'] = 'views';
                $days = isset($instance['days']) && $instance['days'][$i] ? intval($instance['days'][$i]) : 0;
                if($days){
                    $_args['date_query'] = array(
                        array(
                            'column' => 'post_date',
                            'after' => date('Y-m-d H:i:s',current_time('timestamp')-3600*24*$days)
                        )
                    );
                }
            }

            $posts = new WP_Query( $_args ); ?>
            <ul class="post-tabs-list j-post-tab-wrap<?php echo $i===0 ? ' active' : '';?>">
                <?php if ( $posts->have_posts() ) :
                    global $post;
                    while ($posts->have_posts()) :
                        $posts->the_post();
                        $this->loop_item();
                    endwhile; wp_reset_postdata();
                else:
                    echo '<li class="post-tabs-empty">' . __('No Posts', 'wpcom') . '</li>';
                endif;?>
            </ul>
        <?php } }

        $this->widget_end( $args );
        echo $this->cache_widget( $args, ob_get_clean() );
    }

    function loop_item(){
        global $post;?>
        <li class="item">
            <?php $has_thumb = get_the_post_thumbnail(null, 'default'); if ($has_thumb) {
                $video = get_post_meta($post->ID, 'wpcom_video', true); ?>
                <div class="item-img<?php echo $video?' item-video':''; ?>">
                    <a class="item-img-inner" href="<?php the_permalink(); ?>" title="<?php echo esc_attr(get_the_title()); ?>">
                        <?php echo $has_thumb; ?>
                    </a>
                </div>
            <?php } ?>
            <div class="item-content<?php echo ($has_thumb?'':' item-no-thumb');?>">
                <p class="item-title"><a href="<?php the_permalink();?>" title="<?php echo esc_attr(get_the_title());?>"><?php the_title();?></a></p>
                <p class="item-date"><?php the_time(get_option('date_format'));?></p>
            </div>
        </li>
    <?php }
}

// register widget
function register_wpcom_post_tabs_widget() {
    register_widget( 'WPCOM_post_tabs_widget' );
}
add_action( 'widgets_init', 'register_wpcom_post_tabs_widget' );