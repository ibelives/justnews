<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_post_thumb_widget extends WPCOM_Widget {
    public function __construct() {
        $this->widget_cssclass = 'widget_post_thumb';
        $this->widget_description = '带缩略图的文章列表';
        $this->widget_id = 'post-thumb';
        $this->widget_name = '#图文列表';
        $this->settings = array(
            'title'       => array(
                'name' => '标题',
            ),
            'number'      => array(
                'name' => '显示数量',
                'value'   => 10
            ),
            'category'    => array(
                'type'  => 'cat-single',
                'value'   => '0',
                'name' => '分类'
            ),
            'orderby'    => array(
                'type'  => 'select',
                'value'   => '0',
                'name' => '排序',
                'options' => array(
                    '0' => '发布时间',
                    '1' => '评论数',
                    '2' => '浏览数(需安装WP-PostViews插件)',
                    '3' => '随机排序'
                )
            ),
            'days' => array(
                'name' => '时间范围',
                'f' => 'orderby:2',
                'desc' => '限制时间范围，以天为单位，例如填写365，则表示仅获取1年内的文章，可避免获取太久之前的文章，留空或0则不限制'
            )
        );
        parent::__construct();
    }

    public function widget( $args, $instance ) {
        if ( $this->get_cached_widget( $args ) ) return;
        ob_start();

        $category = $instance['category'];
        $_orderby = empty( $instance['orderby'] ) ? $this->settings['orderby']['value'] :  $instance['orderby'];
        $number = empty( $instance['number'] ) ? $this->settings['number']['value'] : absint( $instance['number'] );

        $orderby = 'date';
        if($_orderby==1){
            $orderby = 'comment_count';
        }else if($_orderby==2){
            $orderby = 'meta_value_num';
        }else if($_orderby==3){
            $orderby = 'rand';
        }

        $parg = array(
            'cat' => $category,
            'post_status' => 'publish',
            'showposts' => $number,
            'orderby' => $orderby,
            'ignore_sticky_posts' => 1
        );
        if($orderby=='meta_value_num') {
            $parg['meta_key'] = 'views';
            $days = isset($instance['days']) && $instance['days'] ? intval($instance['days']) : 0;
            if($days){
                $parg['date_query'] = array(
                    array(
                        'column' => 'post_date',
                        'after' => date('Y-m-d H:i:s',current_time('timestamp')-3600*24*$days)
                    )
                );
            }
        }

        $posts = new WP_Query( $parg );

        $this->widget_start( $args, $instance );

        if ( $posts->have_posts() ) : global $post;?>
            <ul>
                <?php while ( $posts->have_posts() ) : $posts->the_post(); ?>
                    <li class="item">
                        <?php $has_thumb = get_the_post_thumbnail(null, 'default'); if($has_thumb){
                            $video = get_post_meta( $post->ID, 'wpcom_video', true );?>
                            <div class="item-img<?php echo $video?' item-video':'';?>">
                                <a class="item-img-inner" href="<?php the_permalink();?>" title="<?php echo esc_attr(get_the_title());?>">
                                    <?php echo $has_thumb; ?>
                                </a>
                            </div>
                        <?php } ?>
                        <div class="item-content<?php echo ($has_thumb?'':' item-no-thumb');?>">
                            <p class="item-title"><a href="<?php the_permalink();?>" title="<?php echo esc_attr(get_the_title());?>"><?php the_title();?></a></p>
                            <p class="item-date"><?php the_time(get_option('date_format'));?></p>
                        </div>
                    </li>
                <?php endwhile; wp_reset_postdata();?>
            </ul>
        <?php
        else:
            echo '<p style="color:#999;font-size: 12px;text-align: center;padding: 10px 0;margin:0;">' . __('No Posts', 'wpcom') . '</p>';
        endif;
        $this->widget_end( $args );
        echo $this->cache_widget( $args, ob_get_clean() );
    }
}

// register widget
function register_wpcom_post_thumb_widget() {
    register_widget( 'WPCOM_post_thumb_widget' );
}
add_action( 'widgets_init', 'register_wpcom_post_thumb_widget' );