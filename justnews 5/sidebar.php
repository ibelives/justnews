<?php
if(is_home()){
    $sidebar = 'home';
}else if(is_page()){
    global $wp_query;
    $sidebar = get_post_meta($wp_query->queried_object_id, 'wpcom_sidebar', true);
}else if(is_category()){
    $sidebar = get_term_meta( $cat, 'wpcom_sidebar', true );
}else if(is_singular('post')){
    global $wp_query;
    $sidebar = wpcom_post_sidebar($wp_query->queried_object_id);
}else if(function_exists('is_woocommerce') && (is_post_type_archive( 'product' ) || is_woocommerce()) ){
    global $wp_query;
    if(is_tax('product_cat')){
        $term = get_queried_object();
        $sidebar = get_term_meta( $term->term_id, 'wpcom_sidebar', true );
    }else if(is_singular( 'product' )){
        $sidebar = wpcom_post_sidebar($wp_query->queried_object_id, 'product_cat');
    }else{
        $sidebar = get_post_meta(wc_get_page_id( 'shop' ), 'wpcom_sidebar', true);
    }
}else if(is_tag() || is_tax()){
    $term = get_queried_object();
    $sidebar = get_term_meta( $term->term_id, 'wpcom_sidebar', true );
}
$sidebar = isset($sidebar) && $sidebar ? $sidebar : 'primary';
dynamic_sidebar($sidebar);