<?php
class WPCOM_Module_mix_tabs extends WPCOM_Module {
    function __construct(){
        $options = array(
            array(
                'tab-name' => '常规设置',
                'tabs' => array(
                    'type' => 'rp',
                    'name' => 'Tab选项卡',
                    'o' => array(
                        'type' => array(
                            'name' => '展示内容',
                            'type' => 's',
                            'o' => array(
                                '0' => '文章列表',
                                '1' => '专题列表',
                                '2' => '快讯',
                                '3' => '问答（需安装QAPress插件）'
                            )
                        ),
                        'title' => array(
                            'name' => '标题'
                        ),
                        'wrap0' => array(
                            'filter' => 'type:0',
                            'type' => 'wrapper',
                            'o' => array(
                                'cat' => array(
                                    'name' => '文章分类',
                                    'type' => 'cs',
                                    'desc' => '可选，留空则显示全部文章'
                                ),
                                'tpl' => array(
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
                                    'filter' => 'tpl:image,tpl:card,tpl:masonry',
                                    'o' => array(
                                        '2' => '2篇',
                                        '3' => '3篇',
                                        '4' => '4篇',
                                        '5' => '5篇'
                                    )
                                ),
                                'hide-excerpt' => array(
                                    'filter' => 'tpl:',
                                    'name' => '隐藏摘要',
                                    'd' => '如果使用栅格分栏显示，则可能过于拥挤显示不下太多内容，此时建议开启此选项',
                                    'type'  => 't'
                                ),
                                'hide-date' => array(
                                    'filter' => 'tpl:list',
                                    'name' => '隐藏时间',
                                    'type'  => 't'
                                )
                            )
                        ),
                        'wrap1' => array(
                            'filter' => 'type:1',
                            'type' => 'wrapper',
                            'o' => array(
                                'style' => array(
                                    'name' => '显示风格',
                                    'value' => '1',
                                    't' => 'r',
                                    'ux' => 2,
                                    'o' => array(
                                        '1' => '风格1（默认）||/justnews/special-1.png',
                                        '2' => '风格2||/justnews/special-2.png',
                                        '3' => '风格1||/justnews/special-3.png',
                                    )
                                ),
                                'cols' => array(
                                    'name' => '每行显示',
                                    'type' => 'r',
                                    'ux' => 1,
                                    'value'  => '3',
                                    'o' => array(
                                        '2' => '2个',
                                        '3' => '3个',
                                        '4' => '4个',
                                        '5' => '5个'
                                    )
                                )
                            )
                        ),
                        'wrap3' => array(
                            'filter' => 'type:3',
                            'type' => 'wrapper',
                            'o' => array(
                                'cat' => array(
                                    'name' => '问答分类',
                                    'type' => 'cs',
                                    'tax' => 'qa_cat',
                                    'desc' => '可选，留空则显示全部问题'
                                )
                            )
                        ),
                        'per_page' => array(
                            'name' => '显示数量',
                            'desc' => '需要显示的文章/内容数量'
                        )
                    )
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
        parent::__construct('mix-tabs', '混合内容Tab', $options, 'mti:style', '/justnews/mod-main-list.png');

        add_action('wp_ajax_wpcom_load_mix_tabs', array($this, 'load_mix_tabs'));
        add_action('wp_ajax_nopriv_wpcom_load_mix_tabs', array($this, 'load_mix_tabs'));
    }

    function template( $atts, $depth ){
        $tabs = $this->value('tabs');
        $load_more = $this->value('load_more');
        ?>
        <script type="text/json"><?php echo json_encode($tabs);?></script>
        <div class="sec-panel mix-tabs<?php echo ($load_more ? '' : ' hide-more');?>">
            <?php if(!empty($tabs)) { ?>
                <div class="sec-panel-head">
                    <ul class="list tabs j-mix-tabs">
                        <?php foreach($tabs as $i => $tab){ ?>
                            <li class="tab<?php echo $i===0 ? ' active' : '';?>">
                                <a href="javascript:;" data-type="<?php echo esc_attr($tab['type']);?>" data-id="<?php echo esc_attr(isset($tab['cat'])?$tab['cat']:'');?>">
                                    <?php echo $tab['title'];?>
                                </a>
                                <?php if($i===0) { ?><i class="tab-underscore"></i><?php } ?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            <?php }

            if(!empty($tabs)){ foreach($tabs as $i => $tab){ ?>
                <div class="tab-wrap<?php echo $i===0 ? ' active' : '';?>" data-index="<?php echo $i;?>">
                    <?php if($i===0){
                        switch ($tab['type']){
                            case '1':
                                $this->special($tab);
                                break;
                            case '2':
                                $this->kuaixun($tab);
                                break;
                            case '3':
                                $this->qapress($tab);
                                break;
                            case '0':
                                $this->posts($tab);
                                break;
                        } } ?>
                </div>
            <?php } } ?>
        </div>
    <?php }
    function load_mix_tabs(){
        $tab = $_POST;
        unset($tab['action']);
        switch ($tab['type']){
            case '1':
                $this->special($tab);
                break;
            case '2':
                $this->kuaixun($tab);
                break;
            case '3':
                $this->qapress($tab);
                break;
            case '0':
                $this->posts($tab);
                break;
        }
        exit;
    }
    function posts($tab){
        $type = isset($tab['tpl']) && $tab['tpl'] ? $tab['tpl'] : 'default';
        $cols = isset($tab['cols']) && $tab['cols'] ? $tab['cols'] : '3';
        $per_page = isset($tab['per_page']) && $tab['per_page'] ? $tab['per_page'] : 10;
        $page = isset($tab['page']) && $tab['page'] ? $tab['page'] : 1;
        $hide_date = $type==='list' && $tab['hide-date'] ? ' hide-date' : '';
        $hide_excerpt = $type==='default' && $tab['hide-excerpt'] ? ' hide-excerpt' : '';

        $arg = array(
            'ignore_sticky_posts' => 0,
            'post_type' => 'post',
            'paged' => $page,
            'posts_per_page' => $per_page,
            'post_status' => array( 'publish' )
        );
        if(isset($tab['cat']) && $tab['cat']) $arg['cat'] = $tab['cat'];
        global $wp_posts;
        $wp_posts = new WP_Query($arg);
        if( $wp_posts->have_posts() ) { ?>
            <ul class="post-loop post-loop-<?php echo $type;?> cols-<?php echo $cols; echo $hide_date; echo $hide_excerpt;?>">
                <?php
                while ( $wp_posts->have_posts() ) { $wp_posts->the_post(); ?>
                    <?php get_template_part( 'templates/loop' , $type ); ?>
                <?php } wp_reset_postdata(); ?>
            </ul>
            <?php if($page==1 && $wp_posts->max_num_pages>1){ ?>
                <div class="load-more-wrap">
                    <div class="btn load-more j-mix-tabs-more"><?php _e('Load more posts', 'wpcom');?></div>
                </div>
            <?php } ?>
        <?php } else if($page == 1){
            get_template_part( 'templates/loop' , 'none' );
        }
    }
    function special($tab){
        $per_page = isset($tab['per_page']) && $tab['per_page'] ? $tab['per_page'] : 10;
        $page = isset($tab['page']) && $tab['page'] ? $tab['page'] : 1;
        $style = isset($tab['style']) && $tab['style'] ? $tab['style'] : 1;
        $cols = isset($tab['cols']) && $tab['cols'] ? $tab['cols'] : 3;
        $special = get_special_list($per_page, $page);
        if($special){ ?>
            <ul class="list topic-list topic-list-<?php echo $style;?> topic-col-<?php echo $cols;?>">
                <?php foreach($special as $term){
                    if(isset($term->term_id) && $term->term_id){
                        $thumb = get_term_meta( $term->term_id, 'wpcom_thumb', true ); ?>
                        <li class="topic">
                            <a class="topic-wrap" href="<?php echo get_term_link($term->term_id);?>" target="_blank">
                                <div class="cover-container">
                                    <?php echo wpcom_lazyimg($thumb, $term->name);?>
                                </div>
                                <span><?php echo $term->name;?></span>
                            </a>
                        </li>
                    <?php }
                }?>
            </ul>
            <?php if($page==1){
                $terms = get_terms(array('taxonomy' => 'special', 'hide_empty' => false));
                if($terms && is_array($terms) && $per_page<count($terms)){ ?>
                    <div class="load-more-wrap">
                        <div class="btn load-more j-mix-tabs-more"><?php _e('Load more topics', 'wpcom');?></div>
                    </div>
                <?php }
            }
        }else if($page == 1){
            get_template_part( 'templates/loop' , 'none' );
        }?>
    <?php }
    function kuaixun($tab){
        $per_page = isset($tab['per_page']) && $tab['per_page'] ? $tab['per_page'] : 10;
        $page = isset($tab['page']) && $tab['page'] ? $tab['page'] : 1;
        $arg = array(
            'post_status' => array( 'publish' ),
            'post_type' => 'kuaixun',
            'paged' => $page,
            'posts_per_page' => $per_page,
        );
        $posts = new WP_Query($arg);
        $cur_day = '';
        if( $posts->have_posts() ) { ?>
            <div class="kx-list">
                <?php  while ( $posts->have_posts() ) { $posts->the_post();
                    if($cur_day != $date = get_the_date(get_option('date_format'))){
                        $pre_day = '';
                        $week = date_i18n('D', get_the_date('U'));
                        if(date(get_option('date_format'), current_time('timestamp')) == $date) {
                            $pre_day = __('Today', 'wpcom') . ' • ';
                        }else if(date(get_option('date_format'), current_time('timestamp')-86400) == $date){
                            $pre_day = __('Yesterday', 'wpcom') . ' • ';
                        }
                        echo '<div class="kx-date">'. $pre_day .$date . ' • ' . $week.'</div>';
                        if($cur_day=='') echo '<div class="kx-new"></div>';
                        $cur_day = $date;
                    } ?>
                    <div class="kx-item" data-id="<?php the_ID();?>">
                        <span class="kx-time"><?php the_time('H:i');?></span>
                        <div class="kx-content">
                            <h2><?php if(isset($options['kx_url_enable']) &&  $options['kx_url_enable'] == '1'){ ?>
                                    <a href="<?php the_permalink();?>" target="_blank"><?php the_title();?></a>
                                <?php } else{ the_title(); } ?></h2>
                            <?php the_excerpt();?>
                            <?php if(get_the_post_thumbnail()){ ?>
                                <?php if(isset($options['kx_url_enable']) &&  $options['kx_url_enable'] == '1'){ ?>
                                    <a class="kx-img" href="<?php the_permalink();?>" title="<?php echo esc_attr(get_the_title());?>" target="_blank"><?php the_post_thumbnail('full'); ?></a>
                                <?php }else{ ?>
                                    <div class="kx-img"><?php the_post_thumbnail('full'); ?></div>
                                <?php } ?>
                            <?php } ?>
                        </div>
                        <div class="kx-meta clearfix" data-url="<?php the_permalink();?>">
                                    <span class="j-mobile-share" data-id="<?php the_ID();?>" data-qrcode="<?php the_permalink();?>">
                                        <?php WPCOM::icon('share');?> <?php _e('Generate poster', 'wpcom');?>
                                    </span>
                            <span class="hidden-xs"><?php _e('Share to: ', 'wpcom');?></span>
                            <?php if(isset($options['post_shares'])){ if($options['post_shares']){ foreach ($options['post_shares'] as $share){ ?>
                                <a class="share-icon <?php echo $share;?> hidden-xs" target="_blank" data-share="<?php echo $share;?>" data-share-callback="kx_share" rel="noopener">
                                    <?php WPCOM::icon($share);?>
                                </a>
                            <?php } } }else{ ?>
                                <a class="share-icon wechat hidden-xs" data-share="wechat" data-share-callback="kx_share" rel="noopener"><?php WPCOM::icon('wechat');?></a>
                                <a class="share-icon weibo hidden-xs" target="_blank" data-share="weibo" data-share-callback="kx_share" rel="noopener"><?php WPCOM::icon('weibo');?></a>
                                <a class="share-icon qq hidden-xs" target="_blank" data-share="qq" data-share-callback="kx_share" rel="noopener"><?php WPCOM::icon('qq');?></a>
                            <?php } ?>
                            <span class="share-icon copy hidden-xs"><?php WPCOM::icon('copy');?></span>
                        </div>
                    </div>
                <?php } ?>
            </div>
            <?php if($page==1 && $posts->max_num_pages>1){ ?>
                <div class="load-more-wrap">
                    <div class="btn load-more j-mix-tabs-more"><?php _e('Load more topics', 'wpcom');?></div>
                </div>
            <?php } ?>
        <?php wp_reset_postdata(); } else if($page==1){
            get_template_part( 'templates/loop' , 'none' );
        } ?>
    <?php }
    function qapress($tab){
        if(!defined('QAPress_VERSION')) echo '请先安装并启用QAPress插件';
        global $wpcomqadb;
        $per_page = isset($tab['per_page']) && $tab['per_page'] ? $tab['per_page'] : 10;
        $page = isset($tab['page']) && $tab['page'] ? $tab['page'] : 1;
        $cat = isset($tab['cat']) && $tab['cat'] ? $tab['cat'] : 0;
        $list = $wpcomqadb->get_questions($per_page, $page, $cat);
        ?>
        <div class="q-content">
            <?php if($list){
                global $post;
                foreach ($list as $post) {
                    echo QAPress_template('list-item', array('post'=> $post));
                }
            }else if($page == 1){
                get_template_part( 'templates/loop' , 'none' );
            } ?>
        </div>
        <?php
        if($page == 1){
            $total_q = $wpcomqadb->get_questions_total($cat);
            $numpages = ceil($total_q/$per_page);
            if($numpages>1){ ?>
                <div class="load-more-wrap">
                    <div class="btn load-more j-mix-tabs-more"><?php _e('Load more topics', 'wpcom');?></div>
                </div>
            <?php }
        }
    }
}
register_module( 'WPCOM_Module_mix_tabs' );