<?php
global $options;
if(is_singular( 'product' ) && isset($options['related_col']) && $options['related_col']==3) {
    add_filter('body_class', 'wpcom_woo_body_class');
}else if(!is_singular( 'product' ) && isset($options['shop_list_col']) && $options['shop_list_col']==3){
    add_filter('body_class', 'wpcom_woo_body_class');
}

get_header();
if( !is_singular( 'product' ) ) {
    $is_sidebar = isset($options['shop_list_sidebar']) ? $options['shop_list_sidebar'] : 0;
    if( is_tax() ){
        $term = get_queried_object();
        $banner = get_term_meta( $term->term_id, 'wpcom_banner', true );
        $banner = !$banner && isset($options['shop_banner']) ? $options['shop_banner'] : $banner;
    }else{
        $shop_id = get_option( 'woocommerce_shop_page_id' );
        $banner = get_post_meta( $shop_id, 'wpcom_banner', true );
    }

    if( $banner ) {
        $banner_height = isset($term) ? get_term_meta( $term->term_id, 'wpcom_banner_height', true ) : '';
        $text_color = isset($term) ? get_term_meta( $term->term_id, 'wpcom_text_color', true ) : '';
        if(isset($shop_id)){
            $bHeight = intval(get_post_meta( $shop_id, 'wpcom_banner_height', true )?:300);
            $bColor = (get_post_meta( $shop_id, 'wpcom_text_color', true )?:0) ? ' banner-white' : '';
        }else{
            $banner_height = isset($term) ? get_term_meta( $term->term_id, 'wpcom_banner_height', true ) : '';
            $text_color = isset($term) ? get_term_meta( $term->term_id, 'wpcom_text_color', true ) : '';
            $bHeight = intval($banner_height ?: 300);
            $bColor = ($text_color ?: 0) ? ' banner-white' : '';
        }
        ?>
        <div class="banner<?php echo $bColor;?>" style="height:<?php echo $bHeight;?>px;background-image: url(<?php echo $banner ?>)">
            <div class="banner-inner container">
                <h1><?php woocommerce_page_title(); ?></h1>
                <?php do_action( 'woocommerce_archive_description' ); ?>
            </div>
        </div>
    <?php } else { ?>
        <div class="banner banner-2">
            <div class="banner-inner container">
                <h1><?php woocommerce_page_title(); ?></h1>
                <?php do_action( 'woocommerce_archive_description' ); ?>
            </div>
        </div>
    <?php }
} else {
    $is_sidebar = isset($options['shop_single_sidebar']) ? $options['shop_single_sidebar'] : 0;
} ?>
    <div class="wrap container">

        <div class="main<?php echo $is_sidebar?'':'-full';?> main-woo">
            <?php
            if ( is_singular( 'product' ) ) {

                while ( have_posts() ) : the_post();

                    wc_get_template_part( 'content', 'single-product' );

                endwhile;

            } else { ?>

                <?php if ( have_posts() ) : ?>

                    <?php do_action( 'woocommerce_before_shop_loop' ); ?>

                    <?php woocommerce_product_loop_start(); ?>

                    <?php woocommerce_product_subcategories(); ?>

                    <?php while ( have_posts() ) : the_post(); ?>

                        <?php wc_get_template_part( 'content', 'product' ); ?>

                    <?php endwhile; // end of the loop. ?>

                    <?php woocommerce_product_loop_end(); ?>

                    <?php do_action( 'woocommerce_after_shop_loop' ); ?>

                <?php elseif ( ! woocommerce_product_subcategories( array( 'before' => woocommerce_product_loop_start( false ), 'after' => woocommerce_product_loop_end( false ) ) ) ) : ?>

                    <?php do_action( 'woocommerce_no_products_found' ); ?>

                <?php endif;

            }
            ?>
        </div>
        <?php if($is_sidebar){ ?>
            <aside class="sidebar">
                <?php get_sidebar();?>
            </aside>
        <?php } ?>
    </div>
<?php get_footer();?>