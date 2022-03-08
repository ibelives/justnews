<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_special_widget extends WPCOM_Widget {
    public function __construct() {
        $this->widget_cssclass = 'widget_special';
        $this->widget_description = '在页面边栏展示指定专题信息';
        $this->widget_id = 'special';
        $this->widget_name = '#专题推荐';
        $this->settings = array(
            'title'       => array(
                'name' => '标题'
            ),
            'special' => array(
                'name' => '显示专题',
                'type' => 'cat-multi-sort',
                'tax' => 'special',
                'desc' => '选择需要展示的专题，按勾选顺序排序'
            ),
            'desc' => array(
                'name' => '简介内容',
                'value' => '1',
                't' => 'r',
                'ux' => 1,
                'o' => array(
                    '1' => '专题描述',
                    '2' => '最近更新'
                )
            ),
        );
        parent::__construct();
    }

    public function widget( $args, $instance ) {
        if ( $this->get_cached_widget( $args ) ) return;
        ob_start();
        $desc = empty( $instance['desc'] ) ? $this->settings['desc']['value'] : $instance['desc'];
        $this->widget_start( $args, $instance );?>

        <ul class="speial-recommend">
            <?php if($instance['special']){ foreach( $instance['special'] as $sp) {
                $term = get_term($sp, 'special');
                if(is_wp_error($term)) continue;
                $thumb = get_term_meta($term->term_id, 'wpcom_thumb', true);
                $link = get_term_link( $term->term_id );
                if(is_wp_error($link)) continue;
                ?>
                <li class="speial-item">
                    <a class="speial-item-img" href="<?php echo $link ?>" target="_blank">
                        <?php echo wpcom_lazyimg($thumb, $term->name);?>
                    </a>
                    <div class="speial-item-text">
                        <a class="speial-item-title" href="<?php echo $link ?>"><?php echo $term->name ?></a>
                        <div class="speial-item-desc">
                            <?php if($desc==1){
                                echo $term->description;
                            } else {
                                $_args = array(
                                    'posts_per_page' => 1,
                                    'tax_query' => array(
                                        array(
                                            'taxonomy' => 'special',
                                            'field' => 'term_id',
                                            'terms' => $sp
                                        )
                                    )
                                );
                                $posts = get_posts( $_args );
                                if(isset($posts[0]) && isset($posts[0]->ID)){ ?>
                                <span class="speial-item-last"><?php _x('Recent Posts: ', 'speial', 'wpcom');?></span>
                                <a href="<?php echo get_the_permalink($posts[0]->ID);?>" target="_blank"><?php echo $posts[0]->post_title;?></a>
                            <?php } } ?>
                        </div>
                    </div>
                </li>
            <?php }} ?>
        </ul>

        <?php
        $this->widget_end( $args );
        echo $this->cache_widget( $args, ob_get_clean() );
    }
}

// register widget
function register_wpcom_special_widget() {
    register_widget( 'WPCOM_special_widget' );
}
add_action( 'widgets_init', 'register_wpcom_special_widget' );