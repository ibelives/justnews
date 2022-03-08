<?php global $options; get_header();?>
    <div class="wrap container">
        <main class="main">
            <?php $is_fea_img = isset($options['fea_img']) && $options['fea_img'] && $options['fea_img'][0];
            if(isset($options['slider_img']) && $options['slider_img'] && $options['slider_img'][0]){ ?>
                <section class="slider-wrap clearfix">
                    <div class="main-slider wpcom-slider swiper-container<?php echo $is_fea_img ? ' pull-left' : ' slider-full';?>">
                        <ul class="swiper-wrapper">
                            <?php foreach($options['slider_img'] as $k => $img){ ?>
                                <li class="swiper-slide">
                                    <?php if(isset($options['slider_url'][$k]) && $options['slider_url'][$k]){ ?>
                                        <a <?php echo WPCOM::url($options['slider_url'][$k]);?>>
                                            <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($options['slider_title'][$k]); ?>">
                                        </a>
                                        <?php if(isset($options['slider_title'][$k]) && $options['slider_title'][$k]){ ?>
                                            <p class="slide-title">
                                                <a <?php echo WPCOM::url($options['slider_url'][$k]);?>><?php echo $options['slider_title'][$k];?></a>
                                            </p>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <img src="<?php echo esc_url($img); ?>" alt="<?php echo esc_attr($options['slider_title'][$k]); ?>">
                                        <?php if(isset($options['slider_title'][$k]) && $options['slider_title'][$k]){ ?>
                                            <p class="slide-title">
                                                <?php echo $options['slider_title'][$k];?>
                                            </p>
                                        <?php } ?>
                                    <?php } ?>
                                </li>
                            <?php } ?>
                        </ul>
                        <!-- Add Pagination -->
                        <div class="swiper-pagination"></div>
                        <!-- Add Navigation -->
                        <div class="swiper-button-prev"><?php WPCOM::icon('arrow-left-3');?></div>
                        <div class="swiper-button-next"><?php WPCOM::icon('arrow-right-3');?></div>
                    </div>

                    <?php if($is_fea_img){ ?>
                        <ul class="feature-post pull-right">
                            <?php $i=0;foreach($options['fea_img'] as $k => $img){ if($i<2){ ?>
                                <li>
                                    <?php if(isset($options['fea_url'][$k]) && $options['fea_url'][$k]){ ?>
                                        <a <?php echo WPCOM::url($options['fea_url'][$k]);?>>
                                            <?php echo wpcom_lazyimg($img, $options['fea_title'][$k]);?>
                                        </a>
                                        <?php if(isset($options['fea_title'][$k]) && $options['fea_title'][$k]){ ?>
                                            <span><?php echo $options['fea_title'][$k];?></span>
                                        <?php } ?>
                                    <?php } else {
                                        echo wpcom_lazyimg($img, $options['fea_title'][$k]);
                                        if(isset($options['fea_title'][$k]) && $options['fea_title'][$k]){ ?>
                                            <span><?php echo $options['fea_title'][$k];?></span>
                                        <?php } ?>
                                    <?php } ?>
                                </li>
                            <?php }$i++;} ?>
                        </ul>
                    <?php } ?>
                </section>
            <?php } ?>
            <?php do_action('wpcom_echo_ad', 'ad_home_1');?>
            <?php
            if(isset($options['special_on']) && $options['special_on']=='1' && isset($options['special_home_num']) && $options['special_home_num']){
                $special = get_special_list($options['special_home_num']);
                $special_col = isset($options['special_home_col']) ? $options['special_home_col'] : 3;
                $special_style = isset($options['special_home_style']) ? $options['special_home_style'] : 1;
                if($special){ ?>
                <section class="sec-panel topic-recommend">
                    <?php if(isset($options['special_home_title']) && $options['special_home_title']){ ?>
                        <div class="sec-panel-head">
                            <h3><span><?php echo $options['special_home_title'];?></span> <small><?php echo $options['special_home_desc'];?></small> <?php if(isset($options['special_home_url']) && $options['special_home_url']){ ?><a <?php echo WPCOM::url($options['special_home_url']);?> class="more"><?php $more_special = isset($options['more_special']) && $options['more_special'] ? $options['more_special'] : __('All Topics', 'wpcom'); echo $more_special;?></a><?php } ?></h3>
                        </div>
                    <?php } ?>
                    <div class="sec-panel-body">
                        <ul class="list topic-list topic-list-<?php echo $special_style;?> topic-col-<?php echo $special_col;?>">
                            <?php foreach($special as $sp){
                                $thumb = get_term_meta( $sp->term_id, 'wpcom_thumb', true );
                                ?>
                                <li class="topic">
                                    <a class="topic-wrap" href="<?php echo get_term_link($sp->term_id);?>" target="_blank">
                                        <div class="cover-container">
                                            <?php echo wpcom_lazyimg($thumb, $sp->name);?>
                                        </div>
                                        <span><?php echo $sp->name;?></span>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </section>
            <?php } } ?>
            <?php do_action('wpcom_echo_ad', 'ad_home_2');?>
            <?php
            global $is_sticky;
            $is_sticky = 1;
            $cats = isset($options['cats_id']) && $options['cats_id'] ? $options['cats_id'] : array();
            ?>
            <section class="sec-panel main-list">
                <?php if(!empty($cats)){ ?>
                    <div class="sec-panel-head">
                        <ul class="list tabs j-newslist">
                            <li class="tab active"><a data-id="0" href="javascript:;"><?php $latest = isset($options['latest_title']) && $options['latest_title'] ? $options['latest_title'] : __('Latest Posts', 'wpcom'); echo $latest;?></a><i class="tab-underscore"></i></li>
                            <?php foreach($cats as $cat){ ?>
                                <li class="tab"><a data-id="<?php echo $cat;?>" href="javascript:;"><?php echo get_cat_name($cat);?></a></li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>
                <div class="tab-wrap active">
                    <ul class="post-loop post-loop-default">
                        <?php
                        $per_page = get_option('posts_per_page');
                        $exclude = isset($options['newest_exclude']) ? $options['newest_exclude'] : array();
                        $arg = array(
                            'posts_per_page' => $per_page,
                            'ignore_sticky_posts' => 0,
                            'post_type' => 'post',
                            'post_status' => array( 'publish' ),
                            'category__not_in' => $exclude
                        );
                        $posts = new WP_Query($arg);
                        if( $posts->have_posts() ) { while ( $posts->have_posts() ) { $posts->the_post(); ?>
                            <?php get_template_part( 'templates/loop' , 'default' ); ?>
                        <?php } } wp_reset_postdata(); ?>
                    </ul>
                    <?php if($posts->max_num_pages>1){ ?>
                        <div class="load-more-wrap">
                            <div class="btn load-more j-load-more" data-exclude="<?php echo empty($exclude) ? '' : implode(',', $exclude);?>"><?php _e('Load more posts', 'wpcom');?></div>
                        </div>
                    <?php } ?>
                </div>

                <?php if($cats){ foreach($cats as $cat){ ?>
                    <div class="tab-wrap"><ul class="post-loop post-loop-default"></ul></div>
                <?php } } ?>
            </section>
        </main>
        <aside class="sidebar">
            <?php get_sidebar();?>
        </aside>
    </div>

<?php
$partners = isset($options['pt_img']) && $options['pt_img'] ? $options['pt_img'] : array();
$link_cat = isset($options['link_cat']) && $options['link_cat'] ? $options['link_cat'] : '';
$bookmarks = get_bookmarks(array('limit' => -1, 'category' => $link_cat, 'category_name' => '', 'hide_invisible' => 1, 'show_updated' => 0, 'orderby' => 'rating' ));
if($partners && $partners[0] || $bookmarks){
    ?>
    <div class="container hidden-xs j-partner">
        <div class="sec-panel">
            <?php if($partners && $partners[0]){
                if(isset($options['partner_title']) && $options['partner_title']){
                    ?>
                    <div class="sec-panel-head">
                        <h3><span><?php echo $options['partner_title'];?></span> <small><?php echo $options['partner_desc'];?></small> <a <?php echo WPCOM::url($options['partner_more_url']);?> class="more"><?php echo $options['partner_more_title'];?></a></h3>
                    </div>
                <?php } ?>
                <div class="sec-panel-body">
                    <ul class="list list-partner">
                        <?php
                        $cols = isset($options['partner_img_cols']) && $options['partner_img_cols'] ? $options['partner_img_cols'] : 7;
                        $width = floor(10000/$cols)/100;
                        foreach($partners as $x =>$pt){
                            $url = $options['pt_url']&&$options['pt_url'][$x]?$options['pt_url'][$x]:'';
                            $alt = $options['pt_title'] && $options['pt_title'][$x] ? $options['pt_title'][$x] : '';
                            ?>
                            <li style="width:<?php echo $width;?>%">
                                <?php if($url){ ?><a title="<?php echo esc_attr($alt);?>" <?php echo WPCOM::url($url);?>><?php } ?><?php echo wpcom_lazyimg($pt, $alt);?><?php if($url){ ?></a><?php } ?>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
            <?php }
            if($bookmarks){
                if(isset($options['link_title']) && $options['link_title']){
                    ?>
                    <div class="sec-panel-head">
                        <h3><span><?php echo $options['link_title'];?></span> <small><?php echo $options['link_desc'];?></small> <a <?php echo WPCOM::url($options['link_more_url']);?> class="more"><?php echo $options['link_more_title'];?></a></h3>
                    </div>
                <?php } ?>

                <div class="sec-panel-body">
                    <div class="list list-links">
                        <?php foreach($bookmarks as $link){ if($link->link_visible=='Y'){ ?>
                            <a <?php if($link->link_target){?>target="<?php echo $link->link_target;?>" <?php } ?><?php if($link->link_description){?>title="<?php echo esc_attr($link->link_description);?>" <?php } ?>href="<?php echo $link->link_url?>"<?php if($link->link_rel){?> rel="<?php echo $link->link_rel;?>"<?php } ?>><?php echo $link->link_name?></a>
                        <?php }} ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>
<?php get_footer();?>