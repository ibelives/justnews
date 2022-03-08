<?php
defined( 'ABSPATH' ) || exit;

if( !function_exists('wpcom_get_related_post') ) :
    function wpcom_get_related_post( $showposts = 10, $title = '相关文章', $tpl = '', $class = '', $img = false ){
        if( $showposts == '0' ) return false;
        global $post, $options, $related_posts;

        $args = array(
            'post__not_in' => array($post->ID),
            'showposts' => $showposts,
            'ignore_sticky_posts' => 1,
            'orderby' => 'rand'
        );

        if(isset($options['related_by']) && $options['related_by']=='1'){
            $tag_list = array();
            $tags = get_the_tags($post->ID);
            if($tags) {
                foreach ($tags as $tag) {
                    $tid = $tag->term_id;
                    if (!in_array($tid, $tag_list)) {
                        $tag_list[] = $tid;
                    }
                }
            }
            $args['tag__in'] = $tag_list;
        }else{
            $cat_list = array();
            $categories = get_the_category($post->ID);
            if($categories) {
                foreach ($categories as $category) {
                    $cid = $category->term_id;
                    if (!in_array($cid, $cat_list)) {
                        $cat_list[] = $cid;
                    }
                }
            }
            $args['cat'] = join(',', $cat_list);
        }

        if($img) $args['meta_query'] = array(array('key' => '_thumbnail_id'));

        $cache_key = md5(maybe_serialize($args));
        $cache = wp_cache_get( $cache_key, 'related_post' );

        if($cache == '-1'){ // 没有相关文章
            return false;
        }else if($cache) {
            return $cache;
        }

        $related_posts = new WP_Query($args);
        $output = '';
        if( $related_posts->have_posts() ) {
            $output .= '<h3 class="entry-related-title">'.$title.'</h3>';
            $output .=  '<ul class="entry-related '.$class.'">';
            while ( $related_posts->have_posts() ) { $related_posts->the_post();
                if ( $tpl ) {
                    ob_start();
                    get_template_part( $tpl );
                    $output .= ob_get_contents();
                    ob_end_clean();
                } else {
                    $output .= '<li class="related-item"><a href="' . get_the_permalink() . '" title="' . esc_attr(get_the_title()) . '">' . get_the_title() . '</a></li>';
                }
            }
            $output = str_replace(array('<h2 ', '</h2>'), array('<h4 ', '</h4>'), $output);
            $output .= '</ul>';
            wp_cache_set( $cache_key, $output, 'related_post', DAY_IN_SECONDS );
        }else{ // 没有相关文章
            wp_cache_set( $cache_key, -1, 'related_post', DAY_IN_SECONDS );
        }
        wp_reset_postdata();
        return $output;
    }
endif;

if( !function_exists('wpcom_related_post') ) :
    function wpcom_related_post($showposts = 10, $title = '相关文章', $tpl = '', $class = '', $img = false){
        $html = wpcom_get_related_post( $showposts, $title, $tpl, $class, $img );
        if($html) echo $html;
    }
endif;