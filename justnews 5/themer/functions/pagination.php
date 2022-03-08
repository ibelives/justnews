<?php
defined( 'ABSPATH' ) || exit;

// Pagenavi
function wpcom_pagination( $range = 9, $args = array() ) {
    global $paged, $wp_query, $page, $numpages, $multipage;

    if ( ($args && $args['numpages'] > 1) || ( isset($multipage) && $multipage && is_singular() ) ) {
        if($args) {
            $page = isset($args['paged']) ? $args['paged'] : $page;
            $numpages = isset($args['numpages']) ? $args['numpages'] : $numpages;
        }
        echo ' <ul class="pagination">';
        $prev = $page - 1;
        if ( $prev > 0 ) {
            echo '<li class="prev">'. wpcom_link_page( $prev, $args ) . WPCOM::icon('arrow-left', false) . '<span>'._x('Previous', 'pagination', 'wpcom').'</span>' . '</a></li>';
        }

        if($numpages > $range){
            if($page < $range){
                for($i = 1; $i <= ($range + 1); $i++){
                    echo $i==$page ? '<li class="active">' : '<li>';
                    echo wpcom_link_page($i, $args) . $i . "</a></li>";
                }
            } elseif($page >= ($numpages - ceil(($range/2)))){
                for($i = $numpages - $range; $i <= $numpages; $i++){
                    echo $i==$page ? '<li class="active">' : '<li>';
                    echo wpcom_link_page($i, $args) . $i . "</a></li>";
                }
            } elseif($page >= $range && $page < ($numpages - ceil(($range/2)))){
                for($i = ($page - ceil($range/2)); $i <= ($page + ceil(($range/2))); $i++){
                    echo $i==$page ? '<li class="active">' : '<li>';
                    echo wpcom_link_page($i, $args) . $i . "</a></li>";
                }
            }
        }else{
            for ( $i = 1; $i <= $numpages; $i++ ) {
                echo $i==$page ? '<li class="active">' : '<li>';
                echo wpcom_link_page($i, $args) . $i . "</a></li>";
            }
        }

        $next = $page + 1;
        if ( $next <= $numpages ) {
            echo '<li class="next">'. wpcom_link_page($next, $args) . '<span>'._x('Next', 'pagination', 'wpcom').'</span>' . WPCOM::icon('arrow-right', false) . '</a></li>';
        }
        $paged_arg = isset($args['paged_arg']) && $args['paged_arg'] ? $args['paged_arg'] : 'page';
        echo '<li class="pagination-go"><form method="get"><input class="pgo-input" type="text" name="'.$paged_arg.'" placeholder="'._x('GO', '页码', 'wpcom').'" /><button class="pgo-btn" type="submit">' . WPCOM::icon('arrow-right-2', false) . '</button></form></li>';
        echo '</ul>';
    }else if( ($max_page = $wp_query->max_num_pages) > 1 ){
        echo ' <ul class="pagination">';
        if(!$paged) $paged = 1;
        echo '<li class="disabled"><span>'.$paged.' / '.$max_page.'</span></li>';
        $prev = get_previous_posts_link(WPCOM::icon('arrow-left', false) . '<span>'._x('Previous', 'pagination', 'wpcom').'</span>');
        if($prev) echo '<li class="prev">'.$prev.'</li>';
        if($max_page > $range){
            if($paged < $range){
                for($i = 1; $i <= ($range + 1); $i++){
                    echo $i==$paged ? '<li class="active">' :  '<li>';
                    echo '<a href="' . get_pagenum_link($i) .'">'.$i.'</a></li>';
                }
            } elseif($paged >= ($max_page - ceil(($range/2)))){
                for($i = $max_page - $range; $i <= $max_page; $i++){
                    echo $i==$paged ? '<li class="active">' :  '<li>';
                    echo '<a href="' . get_pagenum_link($i) .'">'.$i.'</a></li>';
                }
            } elseif($paged >= $range && $paged < ($max_page - ceil(($range/2)))){
                for($i = ($paged - ceil($range/2)); $i <= ($paged + ceil(($range/2))); $i++){
                    echo $i==$paged ? '<li class="active">' :  '<li>';
                    echo '<a href="' . get_pagenum_link($i) .'">'.$i.'</a></li>';
                }
            }
        } else {
            for($i = 1; $i <= $max_page; $i++){
                echo $i==$paged ? '<li class="active">' :  '<li>';
                echo '<a href="' . get_pagenum_link($i) .'">'.$i.'</a></li>';
            }
        }
        $next = get_next_posts_link('<span>'._x('Next', 'pagination', 'wpcom').'</span>' . WPCOM::icon('arrow-right', false));
        if($next) echo '<li class="next">'.$next.'</li>';
        $search = is_search() ? '<input type="hidden" name="s" value="'.esc_attr(get_search_query()).'">' : '';
        echo '<li class="pagination-go"><form method="get">'.$search.'<input class="pgo-input" type="text" name="paged" placeholder="'._x('GO', '页码', 'wpcom').'" /><button class="pgo-btn" type="submit">' . WPCOM::icon('arrow-right-2', false) . '</button></form></li>';
        echo '</ul>';
    }
}

function wpcom_link_page( $i, $args ) {
    if(isset($args['url']) && $args['url']){
        if ( '' == get_option( 'permalink_structure' ) ) {
            $url = add_query_arg( isset($args['paged_arg']) && $args['paged_arg'] ? $args['paged_arg'] : 'page', $i, $args['url'] );
        } else {
            $url = trailingslashit( $args['url'] ) . user_trailingslashit( $i, 'single_paged' );
        }
        $url = '<a href="' . esc_url( $url ) . '" class="post-page-numbers">';
    }else{
        $url = _wp_link_page($i);
    }
    return $url;
}

add_filter('previous_posts_link_attributes', 'wpcom_prev_posts_link_attr');
function wpcom_prev_posts_link_attr($attr){
    return $attr.' class="prev"';
}
add_filter('next_posts_link_attributes', 'wpcom_next_posts_link_attr');
function wpcom_next_posts_link_attr($attr){
    return $attr.' class="next"';
}

function wpcom_comments_pagination($args = array()){
    global $wp_rewrite;
    if ( ! is_singular() ) return;

    $page = get_query_var( 'cpage' );
    if ( ! $page ) $page = 1;

    $max_page = get_comment_pages_count();
    $defaults = array(
        'base'         => add_query_arg( 'cpage', '%#%' ),
        'format'       => '',
        'total'        => $max_page,
        'current'      => $page,
        'echo'         => false,
        'type'         => 'array',
        'add_fragment' => '#comments',
    );
    if ( $wp_rewrite->using_permalinks() ) {
        $defaults['base'] = user_trailingslashit( trailingslashit( get_permalink() ) . $wp_rewrite->comments_pagination_base . '-%#%', 'commentpaged' );
    }

    $args       = wp_parse_args( $args, $defaults );
    $page_links = paginate_links( $args );

    if($page_links){
        foreach ($page_links as $link){
            if(preg_match('/current/i', $link)){
                echo '<li class="active">'.$link.'</li>';
            }else if(preg_match('/\"prev/i', $link)){
                echo '<li class="prev">'.$link.'</li>';
            }else if(preg_match('/\"next/i', $link)){
                echo '<li class="next">'.$link.'</li>';
            }else{
                echo '<li>'.$link.'</li>';
            }
        }
    }
}