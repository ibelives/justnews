<?php
defined( 'ABSPATH' ) || exit;

if( !class_exists('WPCOM_lastest_news_widget') ) :
class WPCOM_lastest_news_widget extends WPCOM_Widget {
    public function __construct() {
        $this->widget_cssclass = 'widget_lastest_news';
        $this->widget_description = '选择指定分类文章，适合用于显示文章列表、新闻动态';
        $this->widget_id = 'lastest-news';
        $this->widget_name = '#文章列表';
        $this->settings = array(
            'title'       => array(
                'name' => '标题',
            ),
            'number'      => array(
                'value'   => 10,
                'name' => '显示数量',
            ),
            'category'    => array(
                'type'  => 'cat-single',
                'std'   => '0',
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
        $orderby_id = empty( $instance['orderby'] ) ? $this->settings['orderby']['value'] :  $instance['orderby'];
        $number = empty( $instance['number'] ) ? $this->settings['number']['value'] : absint( $instance['number'] );

        $orderby = 'date';
        if($orderby_id==1){
            $orderby = 'comment_count';
        }else if($orderby_id==2){
            $orderby = 'meta_value_num';
        }else if($orderby_id==3){
            $orderby = 'rand';
        }

        $parg = array(
            'cat' => $category,
            'showposts' => $number,
            'orderby' => $orderby,
            'post_status' => 'publish',
            'ignore_sticky_posts' => 1
        );
        if($orderby=='meta_value_num'){
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

        if ( $posts->have_posts() ) : ?>
            <ul>
                <?php while ( $posts->have_posts() ) : $posts->the_post(); ?>
                    <li><a href="<?php echo esc_url( get_permalink() )?>" title="<?php echo esc_attr(get_the_title());?>"><?php the_title();?></a></li>
                <?php endwhile; wp_reset_postdata();?>
            </ul>
        <?php
        endif;

        $this->widget_end( $args );
        echo $this->cache_widget( $args, ob_get_clean() );
    }
}

// register widget
function register_wpcom_lastest_news_widget() {
    register_widget( 'WPCOM_lastest_news_widget' );
}
add_action( 'widgets_init', 'register_wpcom_lastest_news_widget' );
endif;