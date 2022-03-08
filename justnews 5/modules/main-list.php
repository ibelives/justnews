<?php
class WPCOM_Module_main_list extends WPCOM_Module {
    function __construct(){
        $options = array(
            array(
                'tab-name' => '常规设置',
                'latest' => array(
                    'name' => '默认Tab',
                    'desc' => '开启后第一个Tab会显示最新文章',
                    'value'  => '1',
                    'type' => 't'
                ),
                'latest-title' => array(
                    'name' => '默认Tab标题',
                    'f' => 'latest:1',
                    'desc' => '第一个默认Tab标题显示文案',
                    'value'  => '最新文章'
                ),
                'exclude' => array(
                    'name' => '排除分类',
                    'f' => 'latest:1',
                    'type' => 'cat-multi',
                    'desc' => '默认Tab文章列表排除的分类，排除分类的文章将不显示'
                ),
                'cats' => array(
                    'name' => 'Tab切换分类',
                    'type' => 'cat-multi-sort',
                    'desc' => '列表切换栏展示的文章分类，按勾选顺序排序'
                ),
                'type' => array(
                    'name' => '显示方式',
                    'type' => 'r',
                    'ux' => 2,
                    'o' => array(
                        '' => '默认列表||/justnews/list-tpl-default.png',
                        'image' => '图文列表||/justnews/list-tpl-image.png',
                        'card' => '卡片列表||/justnews/list-tpl-card.png',
                        'masonry' => '瀑布流||/justnews/list-tpl-masonry.png',
                        'list' => '文章列表||/justnews/list-tpl-list.png',
                    )
                ),
                'cols' => array(
                    'name' => '每行显示',
                    'type' => 'r',
                    'ux' => 1,
                    'value'  => '3',
                    'filter' => 'type:image,type:card,type:masonry',
                    'o' => array(
                        '2' => '2篇',
                        '3' => '3篇',
                        '4' => '4篇',
                        '5' => '5篇'
                    )
                ),
                'hide-excerpt' => array(
                    'filter' => 'type:',
                    'name' => '隐藏摘要',
                    'd' => '如果使用栅格分栏显示，则可能过于拥挤显示不下太多内容，此时建议开启此选项',
                    'type'  => 't'
                ),
                'hide-date' => array(
                    'filter' => 'type:list',
                    'name' => '隐藏时间',
                    'type'  => 't'
                ),
                'per_page' => array(
                    'name' => '显示数量',
                    'desc' => '需要显示的文章数量'
                ),
                'load_more' => array(
                    'name' => '显示加载更多按钮',
                    'value' => 1,
                    'type' => 't',
                    'desc' => '是否支持点击加载更多'
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
        parent::__construct('main-list', '文章主列表', $options, 'mti:view_list', '/justnews/mod-main-list.png');
    }

    function template( $atts, $depth ){
        global $is_sticky;
        $is_sticky = 1;
        $cats = $this->value('cats', array());
        $type = $this->value('type', 'default');
        $per_page = $this->value('per_page', get_option('posts_per_page'));
        $default = $this->value('latest', 1);
        $hide_date = $type==='list' && $this->value('hide-date') ? ' hide-date' : '';
        $hide_excerpt = $type==='default' && $this->value('hide-excerpt') ? ' hide-excerpt' : '';
        $load_more = $this->value('load_more');
        ?>
        <div class="sec-panel main-list main-list-<?php echo $type; echo ($load_more ? '' : ' hide-more');?>" data-type="<?php echo $type;?>">
            <?php if(!empty($cats)) { ?>
                <div class="sec-panel-head">
                    <ul class="list tabs j-newslist" data-type="<?php echo $type;?>" data-per_page="<?php echo $per_page;?>">
                        <?php if($default){ ?>
                            <li class="tab active">
                                <a data-id="0" href="javascript:;">
                                    <?php echo $this->value('latest-title', __('Latest Posts', 'wpcom'));?>
                                </a>
                                <i class="tab-underscore"></i>
                            </li>
                        <?php }
                        foreach($cats as $i => $cat){ ?>
                            <li class="tab<?php echo !$default && $i===0 ? ' active' : '';?>">
                                <a data-id="<?php echo $cat;?>" href="javascript:;"><?php echo get_cat_name($cat);?></a>
                                <?php if(!$default && $i===0) { ?><i class="tab-underscore"></i><?php } ?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            <?php }
            if($default){ ?>
                <div class="tab-wrap active">
                    <ul class="post-loop post-loop-<?php echo $type;?> cols-<?php echo $this->value('cols'); echo $hide_date; echo $hide_excerpt;?>">
                        <?php
                        $exclude = $this->value('exclude', array());
                        $arg = array(
                            'posts_per_page' => $per_page,
                            'ignore_sticky_posts' => 0,
                            'post_type' => 'post',
                            'post_status' => array( 'publish' ),
                            'category__not_in' => $exclude
                        );
                        global $wp_posts;
                        $wp_posts = new WP_Query($arg);
                        if( $wp_posts->have_posts() ) { while ( $wp_posts->have_posts() ) { $wp_posts->the_post(); ?>
                            <?php get_template_part( 'templates/loop' , $type ); ?>
                        <?php } } wp_reset_postdata(); ?>
                    </ul>
                    <?php if($load_more && $wp_posts->max_num_pages>1){ ?>
                        <div class="load-more-wrap">
                            <div class="btn load-more j-load-more" data-exclude="<?php echo empty($exclude) ? '' : implode(',', $exclude);?>"><?php _e('Load more posts', 'wpcom');?></div>
                        </div>
                    <?php } ?>
                </div>
            <?php }
            if($cats){ foreach($cats as $i => $cat){ ?>
                <div class="tab-wrap<?php echo !$default && $i===0 ? ' active' : '';?>">
                    <ul class="post-loop post-loop-<?php echo $type;?> cols-<?php echo $this->value('cols'); echo $hide_date; echo $hide_excerpt;?>">
                        <?php if(!$default && $i===0){
                            $wp_posts = new WP_Query(array(
                                'posts_per_page' => $per_page,
                                'cat' => $cat,
                                'post_type' => 'post',
                                'post_status' => array( 'publish' ),
                                'ignore_sticky_posts' => 0
                            ));
                            if( $wp_posts->have_posts() ) {
                                while ( $wp_posts->have_posts() ) {
                                    $wp_posts->the_post();
                                    get_template_part( 'templates/loop' , $type );
                                }
                            }
                            wp_reset_postdata();
                        } ?>
                    </ul>
                    <?php if($load_more && !$default && $i===0 && $wp_posts->max_num_pages>1){ ?>
                        <div class="load-more-wrap">
                            <div class="btn load-more j-load-more" data-id="<?php echo $cat;?>"><?php _e('Load more posts', 'wpcom');?></div>
                        </div>
                    <?php } ?>
                </div>
            <?php } } ?>
        </div>
    <?php }
}
register_module( 'WPCOM_Module_main_list' );