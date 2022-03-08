<?php
class WPCOM_Module_tjnr extends WPCOM_Module {
    function __construct(){
        $options = array(
            array(
                'tab-name' => '常规设置',
                't1' => array(
                    'name' => '幻灯图片',
                    'd' => '幻灯图片设置',
                    'type' => 'title'
                ),
                'from' => array(
                    'name' => '文章来源',
                    'type' => 'r',
                    'ux' => 1,
                    'value'  => '0',
                    'o' => array(
                        '0' => '使用文章推送',
                        '1' => '手动添加'
                    )
                ),
                'posts_num' => array(
                    "name" => '显示数量',
                    'filter' => 'from:0',
                    "desc" => '调用文章数量',
                    "value" => '5',
                ),
                'wrap' => array(
                    'filter' => 'from:1',
                    'type' => 'wrapper',
                    'o' => array(
                        'slides' => array(
                            'type' => 'rp',
                            'o' => array(
                                'title' => array(
                                    'name' => '标题'
                                ),
                                'img' => array(
                                    'name' => '图片',
                                    'type' => 'u',
                                    'desc' => '图片尺寸620 * 320 px，无边栏/无右侧推荐 860 * 400px，，无边栏及右侧推荐 1180 * 400px',
                                ),
                                'url' => array(
                                    'type' => 'url',
                                    'name' => '链接'
                                )
                            )
                        )
                    )
                ),
                't2' => array(
                    'name' => '头条推荐',
                    'desc' => '幻灯图片旁边的推荐位',
                    'type' => 'title'
                ),
                'fea' => array(
                    'type' => 'rp',
                    'o' => array(
                        'fea_img' => array(
                            'l' => '图片',
                            'd' => '推荐尺寸226*153px；如果无边栏推荐尺寸300*190px',
                            't' => 'u'
                        ),
                        'fea_title' => array(
                            'l' => '标题'
                        ),
                        'fea_url' => array(
                            'type' => 'url',
                            'l' => '链接'
                        )
                    )
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
        parent::__construct('tjnr', '推荐内容', $options, 'star', '/justnews/mod-tjnr.png');
    }

    function template( $atts, $depth ){
        $from = isset($atts['from']) && $atts['from']=='1' ? 1 : 0;
        if($from=='0'){
            $num = isset($atts['posts_num']) && $atts['posts_num'] ? $atts['posts_num'] : 5;
            $posts = get_posts('posts_per_page='.$num.'&meta_key=_show_as_slide&meta_value=1&post_type=post');
            if($posts){
                global $post;
                $atts['slides'] = array();
                foreach ( $posts as $post ) { setup_postdata( $post );
                    $atts['slides'][] = array(
                        'url' => get_permalink() . ', _blank',
                        'img' => get_the_post_thumbnail_url( $post->ID, 'large' ),
                        'title' => get_the_title()
                    );
                }
                wp_reset_postdata();
            }
        }
        $is_fea_img = isset($atts['fea']) && $atts['fea'] && $atts['fea'][0] && $atts['fea'][0]['fea_img'];
        if(isset($atts['slides']) && $atts['slides']){ ?>
            <div class="slider-wrap clearfix">
                <div class="main-slider wpcom-slider swiper-container<?php echo $is_fea_img ? ' pull-left' : ' slider-full';?>">
                    <ul class="swiper-wrapper">
                        <?php foreach($atts['slides'] as $slide){ ?>
                            <li class="swiper-slide">
                                <?php if($slide['url']){ ?>
                                    <a <?php echo WPCOM::url($slide['url']);?>>
                                        <img src="<?php echo esc_url($slide['img']); ?>" alt="<?php echo esc_attr($slide['title']); ?>">
                                    </a>
                                    <?php if($slide['title']){ ?>
                                        <p class="slide-title">
                                            <a <?php echo WPCOM::url($slide['url']);?>><?php echo $slide['title'];?></a>
                                        </p>
                                    <?php } ?>
                                <?php } else { ?>
                                    <img src="<?php echo esc_url($slide['img']); ?>" alt="<?php echo esc_attr($slide['title']); ?>">
                                    <?php if($slide['title']){ ?>
                                        <p class="slide-title">
                                            <?php echo $slide['title'];?>
                                        </p>
                                    <?php } ?>
                                <?php } ?>
                            </li>
                        <?php } ?>
                    </ul>
                    <?php if($atts['slides'] && count($atts['slides'])>1){ ?><!-- Add Pagination -->
                    <div class="swiper-pagination"></div>
                    <!-- Add Navigation -->
                    <div class="swiper-button-prev"><?php WPCOM::icon('arrow-left-3');?></div>
                    <div class="swiper-button-next"><?php WPCOM::icon('arrow-right-3');?></div>
                    <?php } ?>
                </div>

                <?php if($is_fea_img){ ?>
                    <ul class="feature-post pull-right">
                        <?php $i=0;foreach($atts['fea'] as $fea){ if($i<2){ ?>
                            <li>
                                <?php if($fea['fea_url']){ ?>
                                    <a <?php echo WPCOM::url($fea['fea_url']);?>>
                                        <?php echo wpcom_lazyimg($fea['fea_img'], $fea['fea_title']);?>
                                    </a>
                                    <?php if($fea['fea_title']){ ?>
                                        <span><?php echo $fea['fea_title'];?></span>
                                    <?php } ?>
                                <?php } else {
                                    echo wpcom_lazyimg($fea['fea_img'], $fea['fea_title']);
                                    if($fea['fea_title']){ ?>
                                        <span><?php echo $fea['fea_title'];?></span>
                                    <?php } ?>
                                <?php } ?>
                            </li>
                        <?php }$i++;} ?>
                    </ul>
                <?php } ?>
            </div>
        <?php } ?>
    <?php }
}
register_module( 'WPCOM_Module_tjnr' );