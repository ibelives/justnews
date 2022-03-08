<?php
// TEMPLATE NAME: 评论模板
global $options, $post;
wp_enqueue_script( 'comment-reply' );
$sidebar = get_post_meta( $post->ID, 'wpcom_sidebar', true );
$sidebar = !(!$sidebar && $sidebar!=='');
$class = $sidebar ? 'main' : 'main main-full';
$show_indent = isset($options['show_indent']) ? $options['show_indent'] : get_post_meta($post->ID, 'wpcom_show_indent', true);
get_header();
$banner = get_post_meta( $post->ID, 'wpcom_banner', true );
if($banner){
    $banner_height = get_post_meta( $post->ID, 'wpcom_banner_height', true );
    $text_color = get_post_meta( $post->ID, 'wpcom_text_color', true );
    $bHeight = intval($banner_height ?: 300);
    $bColor = ($text_color ? $text_color : 0) ? ' banner-white' : '';
    $description = term_description(); ?>
    <div <?php echo wpcom_lazybg($banner, 'banner'.$bColor, 'height:'.$bHeight.'px;');?>>
        <div class="banner-inner container">
            <h1><?php the_title(); ?></h1>
        </div>
    </div>
<?php } ?>
    <div class="wrap container">
        <?php if( isset($options['breadcrumb']) && $options['breadcrumb']=='1' ) wpcom_breadcrumb('breadcrumb'); ?>
        <div class="<?php echo esc_attr($class);?>">
            <?php while( have_posts() ) : the_post();?>
                <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                    <div class="entry-main">
                        <?php if(!$banner){ ?>
                            <div class="entry-head">
                                <h1 class="entry-title"><?php the_title();?></h1>
                            </div>
                        <?php } ?>
                        <div class="entry-content<?php echo $show_indent?' text-indent':''?>">
                            <?php the_content();?>
                            <?php wpcom_pagination();?>
                        </div>
                    </div>
                    <?php comments_template();?>
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