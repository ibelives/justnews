<?php
class WPCOM_Module_feature_posts extends WPCOM_Module {
    function __construct() {
        $options = array(
            array(
                'tab-name' => '常规设置',
                'from' => array(
                    'name' => '文章来源',
                    'type' => 'r',
                    'ux' => 1,
                    'value'  => '0',
                    'options' => array(
                        '0' => '使用文章推送',
                        '1' => '按文章分类'
                    )
                ),
                'cat' => array(
                    'name' => '文章分类',
                    'type' => 'cat-single',
                    'filter' => 'from:1',
                    'desc' => '如果文章来源选择的是[按文章分类]，请选择此项分类，否则可忽略'
                ),
                'posts_num' => array(
                    "name" => '文章数量',
                    "desc" => '调用的文章数量',
                    "value" => '5'
                ),
                'style' => array(
                    'name' => '显示风格',
                    'type' => 'r',
                    'ux' => 2,
                    'o' => array(
                        '0' => '默认风格||/justnews/feature-style-0.png',
                        '1' => '单篇+虚化背景||/justnews/feature-style-1.png',
                        '2' => '3篇一组轮播||/justnews/feature-style-2.png',
                        '3' => '4篇一组轮播||/justnews/feature-style-3.png',
                        '4' => '5篇一组轮播||/justnews/feature-style-4.png',
                    )
                ),
                'padding' => array(
                    'name' => '虚化背景上下内边距',
                    'f' => 'style:1',
                    'type' => 'trbl',
                    'use' => 'tb',
                    'mobile' => 1,
                    'desc' => '模块内容区域与边界的距离',
                    'units' => 'px, %',
                    'value'  => '30px'
                ),
                'ratio' => array(
                    'f' => 'style:0,style:1',
                    'name' => '显示宽高比',
                    'mobile' => 1,
                    'desc' => '固定格式：<b>宽度:高度</b>，例如<b>10:3</b>',
                    'value' => '10:3',
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
        add_filter('wpcom_module_feature-posts_default_style', array($this, 'default_style'));
        parent::__construct('feature-posts', '推荐文章', $options, 'mti:view_module', '/justnews/mod-feature-posts.png');
    }

    function default_style($style){
        if($style && isset($style['padding'])) {
            unset($style['padding']);
            unset($style['padding_mobile']);
        }
        return $style;
    }

    function classes( $atts, $depth = 0 ){
        $style = $this->value('style', 0);
        $classes = $depth==0 ? 'container' : '';
        $classes .= ' feature-posts-style-' . $style;
        return $classes;
    }

    function style( $atts ){
        $style = $this->value('style', 0);
        $ratio = '';
        if($style==0||$style==1){
            $ratio = $this->value('ratio');
            $ratio = trim(str_replace('：', ':', $ratio));
            $ratio = explode(':', $ratio);
            if(isset($ratio[1]) && is_numeric($ratio[0]) && is_numeric($ratio[1])) $ratio = ($ratio[1] / $ratio[0]) * 100;

            $ratio_m = $this->value('ratio_mobile');
            $ratio_m = trim(str_replace('：', ':', $ratio_m));
            $ratio_m = explode(':', $ratio_m);
            if(isset($ratio_m[1]) && is_numeric($ratio_m[0]) && is_numeric($ratio_m[1])) $ratio_m = ($ratio_m[1] / $ratio_m[0]) * 100;
        }
        return array(
            'ratio' => array(
                '.post-loop-card .item-inner:before' => $style==0 && is_numeric($ratio) ? 'padding-top: ' . $ratio . '%;' : '',
                '.item-container' => $style==1 && is_numeric($ratio) ? 'padding-top: ' . $ratio . '%;' : ''
            ),
            'ratio_mobile' => array(
                '@[(max-width: 767px)] .post-loop-card .item-inner:before' => $style==0 && is_numeric($ratio_m) ? 'padding-top: ' . $ratio_m . '%;' : '',
                '@[(max-width: 767px)] .item-container' => $style==1 && is_numeric($ratio_m) ? 'padding-top: ' . $ratio_m . '%;' : ''
            ),
            'padding' => array(
                '.item-inner' => $style==1 ? WPCOM::trbl($this->value('padding'), 'padding', 'tb') : ''
            ),
            'padding_mobile' => array(
                '@[(max-width: 767px)] .item-inner' => $style==1 ? WPCOM::trbl($this->value('padding_mobile'), 'padding', 'tb') : ''
            )
        );
    }

    function template( $atts, $depth ){
        global $feature_post, $feature_style;
        $feature_post= 1;
        $style = $this->value('style', 0);
        $feature_style = $style;
        $posts_num = $this->value('posts_num');
        if($this->value('from')=='1'){
            $cat = $this->value('cat', 0);
            $posts = get_posts('posts_per_page='.$posts_num.'&cat='.$cat.'&post_type=post');
        }else{
            $posts = get_posts('posts_per_page='.$posts_num.'&meta_key=_show_as_slide&meta_value=1&post_type=post');
        } ?>
        <div class="feature-posts-wrap wpcom-slider">
            <ul class="post-loop post-loop-card cols-3 swiper-wrapper">
                <?php if($posts){
                    global $post;
                    if($style==3||$style==4){
                        $post_array = array();
                        $per = $style==3 ? 4 : 5;
                        $i = 0;
                        foreach ($posts as $post) {
                            $key = intval($i/$per);
                            if(!isset($post_array[$key])) $post_array[$key] = array();
                            $post_array[$key][] = $post;
                            $i++;
                        }
                        if($post_array){
                            foreach ($post_array as $array){
                                echo '<li class="swiper-slide">';
                                foreach ($array as $post){ setup_postdata($post);
                                    get_template_part('templates/loop', 'card');
                                }
                                echo  '</li>';
                            }
                        }
                    }else {
                        foreach ($posts as $post) { setup_postdata($post);
                            get_template_part('templates/loop', 'card');
                        }
                    }
                } wp_reset_postdata(); ?>
            </ul>
            <!-- Add Pagination -->
            <div class="swiper-pagination"></div>
            <!-- Add Navigation -->
            <div class="swiper-button-prev"><?php WPCOM::icon('arrow-left-3');?></div>
            <div class="swiper-button-next"><?php WPCOM::icon('arrow-right-3');?></div>
        </div>
        <script>
            jQuery(document).ready(function() {
                var _swiper_<?php echo $atts['modules-id'];?> = {
                    onInit: function (el) {
                        if (el.slides.length < 4) {
                            this.autoplay = false;
                            this.touchRatio = 0;
                            el.stopAutoplay();
                        }
                        jQuery(el.container[0]).on('click', '.swiper-button-next', function () {
                            el.slideNext();
                        }).on('click', '.swiper-button-prev', function () {
                            el.slidePrev();
                        });
                        setTimeout(function () {
                            jQuery(window).trigger('scroll');
                        }, 800);
                    },
                    pagination: '.swiper-pagination',
                    slideClass: 'item',
                    paginationClickable: true,
                    simulateTouch: false,
                    loop: true,
                    autoplay: _wpcom_js.slide_speed ? _wpcom_js.slide_speed : 5000,
                    effect: 'slide',
                    onSlideChangeEnd: function(){
                        jQuery(window).trigger('scroll');
                    }
                };
                <?php if($style==2){?>
                _swiper_<?php echo $atts['modules-id'];?>.slidesPerView = 3;
                _swiper_<?php echo $atts['modules-id'];?>.spaceBetween = 0;
                _swiper_<?php echo $atts['modules-id'];?>.slidesPerGroup = 3;
                _swiper_<?php echo $atts['modules-id'];?>.breakpoints = {
                    767: {
                        slidesPerView: 1,
                        slidesPerGroup: 1,
                        spaceBetween: 1
                    }
                };
                <?php }else if($style==3||$style==4){ ?>
                _swiper_<?php echo $atts['modules-id'];?>.slideClass = 'swiper-slide';
                <?php } ?>
                new Swiper('#modules-<?php echo $atts['modules-id'];?> .feature-posts-wrap', _swiper_<?php echo $atts['modules-id'];?>);
            });
        </script>
    <?php $feature_style = '';}
}

register_module( 'WPCOM_Module_feature_posts' );