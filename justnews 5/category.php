<?php
global $options, $wp_query, $paged;
$term_id = get_queried_object_id();
$tpl = get_term_meta( $term_id, 'wpcom_tpl', true );
$sidebar = get_term_meta( $term_id, 'wpcom_sidebar', true );
$sidebar = !(!$sidebar && $sidebar!=='');
$banner = get_term_meta( $term_id, 'wpcom_banner', true );
if($tpl=='image-fullwidth') {
    $tpl = 'image';
    update_term_meta($term_id, 'wpcom_tpl', $tpl);
    update_term_meta($term_id, 'wpcom_sidebar', '0');
}
if ( ! ($tpl && locate_template('templates/loop-' . $tpl . '.php') != '' ) ) {
    $tpl = 'default';
}

$hide_date = $tpl === 'list' ? get_term_meta( $term_id, 'wpcom_hide_date', true ) : 0;

$cols = 0;
if(in_array($tpl, array('masonry', 'card', 'image'))) {
    $cols = get_term_meta($term_id, 'wpcom_cols', true);
    $cols = $cols ?: ($sidebar ? 3 : 4);
}
$class = $sidebar ? 'main' : 'main main-full';
get_header();
if($banner){
    $banner_height = get_term_meta( $term_id, 'wpcom_banner_height', true );
    $text_color = get_term_meta( $term_id, 'wpcom_text_color', true );
    $bHeight = intval($banner_height ?: 300);
    $bColor = ($text_color ?: 0) ? ' banner-white' : '';
    $description = term_description(); ?>
    <div <?php echo wpcom_lazybg($banner, 'banner'.$bColor, 'height:'.$bHeight.'px;');?>>
        <div class="banner-inner container">
            <h1><?php single_cat_title(); ?></h1>
            <?php if($description!=='') { ?><div class="page-description"><?php echo $description;?></div><?php } ?>
        </div>
    </div>
<?php } ?>
    <div class="container wrap">
        <?php if( isset($options['breadcrumb']) && $options['breadcrumb']=='1' ) wpcom_breadcrumb('breadcrumb'); ?>
        <main class="<?php echo esc_attr($class);?>">
            <?php do_action('category_before_list');?>
            <section class="sec-panel sec-panel-<?php echo esc_attr($tpl);?>">
                <?php if($banner==''){ ?>
                    <div class="sec-panel-head">
                        <h1><span><?php single_cat_title(); ?></span></h1>
                    </div>
                <?php } ?>
                <div class="sec-panel-body">
                    <?php if(have_posts()) : ?>
                        <ul class="post-loop post-loop-<?php echo esc_attr($tpl);?> cols-<?php echo $cols;echo ($hide_date?' hide-date':'');?>">
                            <?php while( have_posts() ) : the_post();?>
                                <?php get_template_part( 'templates/loop' , $tpl ); ?>
                            <?php endwhile; ?>
                        </ul>
                        <?php
                        $pagenavi = get_term_meta($term_id, 'wpcom_pagenavi', true);
                        $paged = $paged ?: 1;
                        if($pagenavi == '1' && $wp_query->max_num_pages){ // 点击加载 ?>
                            <div class="load-more-wrap">
                                <a class="btn load-more j-load-archive" href="<?php echo esc_url(get_next_posts_page_link($wp_query->max_num_pages));?>" data-tax="<?php echo $wp_query->queried_object->taxonomy;?>" data-id="<?php echo $term_id;?>" data-page="<?php echo $paged;?>"><?php _e('Load more posts', 'wpcom');?></a>
                            </div>
                        <?php }else if($pagenavi == '2'){ // 滚动加载 ?>
                            <div class="load-more-wrap">
                                <a class="scroll-loader" href="<?php echo esc_url(get_next_posts_page_link($wp_query->max_num_pages));?>" data-tax="<?php echo $wp_query->queried_object->taxonomy;?>" data-id="<?php echo $term_id;?>" data-page="<?php echo $paged;?>"><?php _e('Loading...', 'wpcom');?></a>
                            </div>
                        <?php }else{
                            wpcom_pagination(5);
                        } ?>
                    <?php else: ?>
                        <ul class="post-loop post-loop-default">
                            <?php get_template_part( 'templates/loop' , 'none' ); ?>
                        </ul>
                    <?php endif;?>
                </div>
            </section>
        </main>
        <?php if( $sidebar ){ ?>
            <aside class="sidebar">
                <?php get_sidebar();?>
            </aside>
        <?php } ?>
    </div>
<?php get_footer();?>