<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_kuaixun_widget extends WPCOM_Widget {
    public function __construct() {
        $this->widget_cssclass = 'widget_kuaixun';
        $this->widget_description = '快讯展示';
        $this->widget_id = 'kuaixun';
        $this->widget_name = '#快讯';
        $this->settings = array(
            'title'       => array(
                'name' => '标题',
            ),
            'number'      => array(
                'value'   => 10,
                'name' => '显示数量',
            )
        );
        parent::__construct();
    }

    public function widget( $args, $instance ) {
        if ( $this->get_cached_widget( $args ) ) return;
        ob_start();
        global $options;
        $num = empty( $instance['number'] ) ? $this->settings['number']['value'] : absint( $instance['number'] );

        echo $args['before_widget'];
        $url = '';
        if( isset($options['kx_page']) && $options['kx_page'] && $kx = get_post($options['kx_page']) )
            $url = get_permalink($kx->ID);

        if ( ! empty( $instance['title'] ) ) {
            if($url){
                $url = '<a class="widget-title-more" href="'.$url.'" target="_blank">' . __('More', 'wpcom') . WPCOM::icon('arrow-right', false).'</a>';
            }
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $url . $args['after_title'];
        }

        $arg = array(
            'posts_per_page' => $num,
            'post_status' => array( 'publish' ),
            'post_type' => 'kuaixun'
        );
        $posts = new WP_Query($arg);
        global $post;
        if( $posts->have_posts() ) { ?>
            <ul class="widget-kx-list">
            <?php  while ( $posts->have_posts() ) { $posts->the_post(); ?>
                <li class="kx-item" data-id="<?php the_ID();?>">
                    <a class="kx-title" href="javascript:;"><?php the_title();?></a>
                    <div class="kx-meta clearfix" data-url="<?php the_permalink();?>">
                        <span class="kx-time"><?php echo wpcom_format_date(get_post_time( 'U', false, $post ));?></span>
                        <div class="kx-share">
                            <span><?php _e('Share to: ', 'wpcom');?></span>
                            <?php if(isset($options['post_shares'])){ $i=0;if($options['post_shares']){ foreach ($options['post_shares'] as $share){ if($i<4){ ?>
                                <a class="share-icon <?php echo $share;?>" target="_blank" data-share="<?php echo $share;?>" data-share-callback="kx_share" rel="noopener">
                                    <?php WPCOM::icon($share);?>
                                </a>
                            <?php $i++;}} } }else{ ?>
                                <a class="share-icon wechat" data-share="wechat" data-share-callback="kx_share" rel="noopener"><?php WPCOM::icon('wechat');?></a>
                                <a class="share-icon weibo" target="_blank" data-share="weibo" data-share-callback="kx_share" rel="noopener"><?php WPCOM::icon('weibo');?></a>
                                <a class="share-icon qq" target="_blank" data-share="qq" data-share-callback="kx_share" rel="noopener"><?php WPCOM::icon('qq');?></a>
                            <?php } ?>
                            <span class="share-icon copy"><?php WPCOM::icon('copy');?></span>
                        </div>
                    </div>
                    <div class="kx-content">
                        <?php the_excerpt();?>
                        <?php if(get_the_post_thumbnail()){ ?>
                            <?php the_post_thumbnail(); ?>
                        <?php } ?>
                    </div>
                </li>
            <?php }
            echo '</ul>';
        }
        $this->widget_end( $args );
        echo $this->cache_widget( $args, ob_get_clean() );
    }
}

// register widget
function register_wpcom_kuaixun_widget() {
    register_widget( 'WPCOM_kuaixun_widget' );
}
add_action( 'widgets_init', 'register_wpcom_kuaixun_widget' );