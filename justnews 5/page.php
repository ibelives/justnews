<?php
get_header();
global $options, $post;
$sidebar = get_post_meta( $post->ID, 'wpcom_sidebar', true );
$sidebar = !(!$sidebar && $sidebar!=='');
$body_classes = implode(' ', apply_filters( 'body_class', array() ));
$show_indent = isset($options['show_indent']) ? $options['show_indent'] : get_post_meta($post->ID, 'wpcom_show_indent', true);
$hide_title = 0;
if(preg_match('/(qapress|member-profile|member-account|member-login|member-register|member-lostpassword)/i', $body_classes)) {
    $hide_title = 1;
}
if($sidebar && preg_match('/(member-account|member-login|member-register|member-lostpassword)/i', $body_classes)){
    $sidebar = 0;
    update_post_meta($post->ID, 'wpcom_sidebar', '0');
}
$class = $sidebar ? 'main' : 'main main-full';
$page_template = get_post_meta($post->ID, '_wp_page_template', true);
if($page_template == 'page-fullwidth.php' || $page_template == 'page-fullnotitle.php'){
    update_post_meta($post->ID, '_wp_page_template', 'default');
    update_post_meta($post->ID, 'wpcom_sidebar', '0');
}
if($page_template == 'page-notitle.php' || $page_template == 'page-fullnotitle.php'){
    update_post_meta($post->ID, '_wp_page_template', 'default');
}
$banner = get_post_meta( $post->ID, 'wpcom_banner', true );
if(!$hide_title && $banner){
    $banner_height = get_post_meta( $post->ID, 'wpcom_banner_height', true );
    $text_color = get_post_meta( $post->ID, 'wpcom_text_color', true );
    $bHeight = intval($banner_height ?: 300);
    $bColor = ($text_color ?: 0) ? ' banner-white' : '';
    $description = term_description(); ?>
    <div <?php echo wpcom_lazybg($banner, 'banner'.$bColor, 'height:'.$bHeight.'px;');?>>
        <div class="banner-inner container">
            <h1><?php the_title(); ?></h1>
        </div>
    </div>
<?php } ?>
    <div class="wrap container">
        <?php if( !$hide_title && isset($options['breadcrumb']) && $options['breadcrumb']=='1' ) wpcom_breadcrumb('breadcrumb'); ?>
        <div class="<?php echo esc_attr($class);?>">
            <?php while( have_posts() ) : the_post();?>
                <article id="post-<?php the_ID(); ?>" <?php post_class();?>>
                    <div class="entry-main">
                        <?php if(!$banner && !$hide_title){ ?>
                            <div class="entry-head">
                                <h1 class="entry-title"><?php the_title();?></h1>
                            </div>
                        <?php } ?>
                        <div class="entry-content<?php echo $show_indent?' text-indent':''?>">
                            <?php the_content();?>
                            <?php wpcom_pagination();?>
                        </div>
                    </div>
                </article>
            <?php endwhile; ?>
        </div>
        <?php if( $sidebar ){ ?>
            <aside class="sidebar">
                <?php get_sidebar();?>
            </aside>
        <?php } ?>
    </div>
<?php get_footer();?>