<?php
/**
 * 面包屑导航功能
 */
defined( 'ABSPATH' ) || exit;

// breadcrumb
function wpcom_breadcrumb( $cls='breadcrumb' ) {
    global $post, $wp_query, $breadcrumb_index;
    $breadcrumb_index = 1;
    $dot = WPCOM::icon('arrow-right-3', false);
    echo '<ol class="'.$cls.'" vocab="https://schema.org/" typeof="BreadcrumbList"><li class="home" property="itemListElement" typeof="ListItem"><a href="'.get_bloginfo('url').'" property="item" typeof="WebPage"><span property="name" class="hide">'.get_bloginfo('name').'</span>'.__('Home', 'wpcom').'</a><meta property="position" content="'.$breadcrumb_index.'"></li>';
    $breadcrumb_index++;
    if ( is_category() ) {
        $cat_obj = $wp_query->get_queried_object();
        $thisCat = $cat_obj->term_id;
        $thisCat = get_category($thisCat);
        $parentCat = get_category($thisCat->parent);

        if ($thisCat->parent != 0) echo wpcom_get_category_parents($parentCat);
        echo '<li class="active" property="itemListElement" typeof="ListItem">'.$dot;
        echo '<a href="'.get_category_link($thisCat).'" property="item" typeof="WebPage"><span property="name">'.single_cat_title('', false).'</span></a>';
        echo '<meta property="position" content="'.$breadcrumb_index.'"></li>';
        $breadcrumb_index++;
    } else if(function_exists('is_woocommerce') && is_shop() ) {
        echo '<li class="active" property="itemListElement" typeof="ListItem">'.$dot;
        echo '<span property="name">'.get_the_title(wc_get_page_id( 'shop' )).'</span>';
        echo '<meta property="position" content="'.$breadcrumb_index.'"></li>';
        $breadcrumb_index++;
    } elseif ( is_single() ) {
        $post_type = get_post_type();
        if($post_type == 'post'){
            $cat = get_the_category();
            $cat = isset($cat[0]) ? $cat[0] : 0;
            echo wpcom_get_category_parents($cat);
        }else if($post_type == 'product'){
            global $post;
            $taxonomy = 'product_cat';
            $terms = get_the_terms( $post->ID, $taxonomy );
            $term = isset($terms[0]) ? $terms[0] : 0;
            echo wpcom_get_category_parents( $term->term_id, $taxonomy);
        }else{
            $obj = get_post_type_object( $post_type );
            echo '<li class="active">'.$dot;
            echo $obj->labels->singular_name;
            echo '</li>';
        }
    } elseif ( is_page() && !$post->post_parent ) {
        echo '<li class="active" property="itemListElement" typeof="ListItem">'.$dot;
        echo '<a href="'.get_permalink().'" property="item" typeof="WebPage"><span property="name">'.get_the_title().'</span></a>';
        echo '<meta property="position" content="'.$breadcrumb_index.'"></li>';
        $breadcrumb_index++;
    } elseif ( is_page() && $post->post_parent ) {
        $parent_id  = $post->post_parent;
        $breadcrumbs = array();
        while ($parent_id) {
            $page = get_post($parent_id);
            $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '" property="item" typeof="WebPage"><span property="name">' . get_the_title($page->ID) . '</span></a>';
            $parent_id  = $page->post_parent;
        }
        $breadcrumbs = array_reverse($breadcrumbs);
        foreach ($breadcrumbs as $crumb) {
            echo '<li property="itemListElement" typeof="ListItem">'.$dot.$crumb.'<meta property="position" content="'.$breadcrumb_index.'"></li>';
            $breadcrumb_index++;
        }
        echo '<li class="active" property="itemListElement" typeof="ListItem">'.$dot;
        echo '<a href="'.get_permalink().'" property="item" typeof="WebPage"><span property="name">'.get_the_title().'</span></a>';
        echo '<meta property="position" content="'.$breadcrumb_index.'"></li>';
        $breadcrumb_index++;
    } elseif ( is_search() ) {
        $kw = get_search_query();
        $kw = !empty($kw) ? $kw : __('None', 'wpcom');
        echo '<li class="active">' .$dot. sprintf( __('Search for: %s', 'wpcom'), $kw) . '</li>';
    } elseif ( is_tag() || is_tax() ) {
        $tag_id = $wp_query->get_queried_object_id();
        echo '<li class="active" property="itemListElement" typeof="ListItem">'.$dot;
        echo '<a href="'.get_term_link($tag_id).'" property="item" typeof="WebPage"><span property="name">'.single_term_title('', false).'</span></a>';
        echo '<meta property="position" content="'.$breadcrumb_index.'"></li>';
        $breadcrumb_index++;
    } elseif( is_post_type_archive() ) {
        echo '<li class="active" property="itemListElement" typeof="ListItem">'.$dot;
        echo '<span property="name">'.post_type_archive_title('', 0).'</span>';
        echo '<meta property="position" content="'.$breadcrumb_index.'"></li>';
        $breadcrumb_index++;
    } elseif ( is_author() ) {
        global $author;
        $userdata = get_userdata($author);
        echo '<li class="active">' .$dot. $userdata->display_name . '</li>';
    } elseif ( is_day() ) {
        echo '<li property="itemListElement" typeof="ListItem">'.$dot.'<a href="' . get_year_link(get_the_time('Y')) . '" property="item" typeof="WebPage"><span property="name">' . get_the_time('Y') . '</span></a><meta property="position" content="'.$breadcrumb_index.'"></li>';
        $breadcrumb_index++;
        echo '<li property="itemListElement" typeof="ListItem">'.$dot.'<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '" property="item" typeof="WebPage"><span property="name">' . get_the_time('F') . '</span></a><meta property="position" content="'.$breadcrumb_index.'"></li>';
        $breadcrumb_index++;
        echo '<li class="active">'. $dot . get_the_time('d') . '</li>';
    } elseif ( is_month() ) {
        echo '<li property="itemListElement" typeof="ListItem">'.$dot.'<a href="' . get_year_link(get_the_time('Y')) . '" property="item" typeof="WebPage"><span property="name">' . get_the_time('Y') . '</span></a><meta property="position" content="'.$breadcrumb_index.'"></li>';
        $breadcrumb_index++;
        echo '<li class="active" property="itemListElement" typeof="ListItem">'.$dot.'<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '" property="item" typeof="WebPage"><span property="name">' . get_the_time('F') . '</span></a><meta property="position" content="'.$breadcrumb_index.'"></li>';
        $breadcrumb_index++;
    } elseif ( is_year() ) {
        echo '<li class="active" property="itemListElement" typeof="ListItem">'.$dot.'<a href="' . get_year_link(get_the_time('Y')) . '" property="item" typeof="WebPage"><span property="name">' . get_the_time('Y') . '</span></a><meta property="position" content="'.$breadcrumb_index.'"></li>';
        $breadcrumb_index++;
    } elseif( is_attachment() ) {
        echo '<li class="active" property="itemListElement" typeof="ListItem">'.$dot;
        echo '<a href="'.get_permalink().'" property="item" typeof="WebPage"><span property="name">'.get_the_title().'</span></a>';
        echo '<meta property="position" content="'.$breadcrumb_index.'"></li>';
        $breadcrumb_index++;
    } elseif ( is_404() ) {
        echo '<li class="active">'.$dot.__('404 ERROR', 'wpcom').'</li>';
    }

    if ( get_query_var('paged') ) {
        echo '<li class="active">'.$dot;
        echo sprintf( __('Paged %s', 'wpcom'), get_query_var('paged'));
        echo '</li>';
    }

    echo '</ol>';
}

function wpcom_get_category_parents( $id, $taxonomy='category', $visited = array() ) {
    global $breadcrumb_index;
    if(!$id) return '';
    $dot = WPCOM::icon('arrow-right-3', false);
    $chain = '';
    $parent = get_term( $id, $taxonomy );
    if ( is_wp_error( $parent ) )
        return '';
    $name = $parent->name;

    if ( $parent->parent && ( $parent->parent != $parent->term_id ) && !in_array( $parent->parent, $visited ) ) {
        $visited[] = $parent->parent;
        $chain .= wpcom_get_category_parents( $parent->parent, $taxonomy, $visited );
    }
    $chain .= '<li property="itemListElement" typeof="ListItem">'.$dot.'<a href="' . esc_url( get_term_link( $parent->term_id, $taxonomy ) ) . '" property="item" typeof="WebPage"><span property="name">'.$name.'</span></a><meta property="position" content="'.$breadcrumb_index.'"></li>';
    $breadcrumb_index++;
    return $chain;
}