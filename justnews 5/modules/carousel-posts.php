<?php
class WPCOM_Module_carousel_posts extends WPCOM_Module {
    function __construct() {
        $options = array(
            array(
                'tab-name' => '常规设置',
                'title' => array(
                    'name' => '模块标题'
                ),
                'sub-title' => array(
                    'name' => '模块副标题'
                ),
                'cat' => array(
                    'name' => '文章分类',
                    'type' => 'cat-single'
                ),
                'per-view' => array(
                    'name' => '每栏显示',
                    'type' => 'r',
                    'ux' => 1,
                    'value'  => '4',
                    'o' => array(
                        '3' => '3篇',
                        '4' => '4篇',
                        '5' => '5篇'
                    )
                ),
                'number' => array(
                    'name' => '显示数量',
                    'value'  => '12'
                )
            ),
            array(
                'tab-name' => '风格样式',
                'margin' => array(
                    'name' => '外边距',
                    'type' => 'trbl',
                    'use' => 'tb',
                    'mobile' => 1,
                    'desc' => '和上下模块/元素的间距',
                    'units' => 'px, %',
                    'value'  => '20px'
                )
            )
        );
        parent::__construct('carousel-posts', '轮播文章', $options, 'file-text', '/justnews/mod-carousel-posts.png');
    }

    function template( $atts, $depth ){
        global $is_sticky;
        $is_sticky = 0;?>
        <div class="sec-panel">
            <?php if(isset($atts['title']) && $atts['title']){ ?>
                <div class="sec-panel-head">
                    <div class="sec-panel-more">
                        <div class="swiper-button-prev"><?php WPCOM::icon('arrow-left-3');?></div>
                        <div class="swiper-button-next"><?php WPCOM::icon('arrow-right-3');?></div>
                    </div>
                    <h3><span><?php echo $atts['title']; ?></span> <small><?php echo $atts['sub-title']; ?></small></h3>
                </div>
            <?php } ?>
            <div class="sec-panel-body carousel-slider">
                <div class="j-slider-<?php echo $atts['modules-id'];?> cs-inner">
                    <ul class="swiper-wrapper post-loop post-loop-image cols-<?php echo isset($atts['per-view']) && $atts['per-view'] ? $atts['per-view'] : 4;?>">
                        <?php
                        $i = 0;
                        $posts = get_posts('posts_per_page='.(isset($atts['number'])&&$atts['number']?$atts['number']:12).'&cat='.(isset($atts['cat'])?$atts['cat']:''));
                        if($posts){ global $post;foreach ( $posts as $post ) { setup_postdata( $post );?>
                            <?php get_template_part( 'templates/loop' , 'image' ); ?>
                        <?php $i++;} wp_reset_postdata(); } ?>
                    </ul>
                </div>
            </div>
        </div>
        <script>
            <?php $per_view = $this->value('per-view');?>
            jQuery(document).ready(function(){
                new Swiper('.j-slider-<?php echo $atts['modules-id'];?>', {
                    onInit: function(el){
                        el.slides.addClass('swiper-slide');
                        jQuery(el.container[0]).closest('.wpcom-modules').on('click', '.swiper-button-next', function () {
                            el.slideNext();
                        }).on('click', '.swiper-button-prev', function () {
                            el.slidePrev();
                        });
                    },
                    paginationClickable: true,
                    autoplay: _wpcom_js.slide_speed ? _wpcom_js.slide_speed : 5000,
                    loop: true,
                    effect: 'slide',
                    slidesPerView: <?php echo $per_view?>,
                    spaceBetween: 0,
                    slidesPerGroup: <?php echo $i%2 === 0 ? 2 : 1;?>,
                    slideClass: 'item',
                    simulateTouch: false,
                    // Responsive breakpoints
                    breakpoints: {
                        480: {
                            slidesPerView: 2,
                            slidesPerGroup: 1
                        },
                        767: {
                            slidesPerView: 2,
                            slidesPerGroup: 1
                        },
                        1024: {
                            slidesPerView: 2,
                            slidesPerGroup: 1
                        }
                    },
                    onSlideChangeEnd: function(){
                        jQuery(window).trigger('scroll');
                    }
                });
            })
        </script>
    <?php }
}

register_module( 'WPCOM_Module_carousel_posts' );