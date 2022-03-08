<?php
defined( 'ABSPATH' ) || exit;

add_action('wp_head', 'wpcom_seo', 1);
function wpcom_seo(){
    global $options, $post,$wp_query;
    $keywords = '';
    $description = '';
    $seo = '';
    if(!isset($options['seo']) || $options['seo']=='1') {
        $open_graph = !isset($options['open_graph']) || $options['open_graph'];
        if(!isset($options['seo'])){
            $options = isset($options) ? $options : array();
            $options['keywords'] = '';
            $options['description'] = '';
            $options['fav'] = '';
        }
        if (is_home() || is_front_page()) {
            $keywords = str_replace('，', ',', trim(strip_tags($options['keywords'])));
            $description = trim(strip_tags($options['description']));
        } else if (is_singular()) {
            $keywords = str_replace('，', ',', trim(strip_tags(get_post_meta( $post->ID, 'wpcom_seo_keywords', true))));
            if($keywords=='' && is_singular('post')){
                $post_tags = get_the_tags();
                if ($post_tags) {
                    foreach ($post_tags as $tag) {
                        $keywords = $keywords . $tag->name . ",";
                    }
                }
                $keywords = rtrim($keywords, ',');
            } else if($keywords=='' && is_singular('page')) {
                $keywords = $post->post_title;
            }else if(is_singular('product')){
                $product_tag = get_the_terms( $post->ID, 'product_tag' );
                if ($product_tag) {
                    foreach ($product_tag as $tag) {
                        $keywords = $keywords . $tag->name . ",";
                    }
                }
                $keywords = rtrim($keywords, ',');
            }elseif(is_singular('qa_post')){
                global $qa_options;
                if(!isset($qa_options)) $qa_options = get_option('qa_options');
                if(isset($qa_options['enable_related']) && $qa_options['related_by']){
                    $keywords = get_post_meta($post->ID, '_qa_tags', true);
                }
            }
            $description = trim(strip_tags(get_post_meta( $post->ID, 'wpcom_seo_description', true)));
            if($description=='' && !post_password_required( $post )) {
                if ($post->post_excerpt) {
                    $description = utf8_excerpt(strip_tags($post->post_excerpt), 200);
                } else {
                    $content = preg_replace("/\[(\/?map.*?)\]/si", "", $post->post_content);
                    $content = str_replace(' ', '', trim(strip_tags($content)));
                    $content = preg_replace('/\\s+/', ' ', $content );
                    $description = preg_match('/^\[[^\]]+\]$/i', $content) ? '' : utf8_excerpt($content, 200);
                }
                if($description=='' && is_wpcom_member_page('profile')){
                    global $profile;
                    $description = $profile->description;
                    $keywords .= ','. $profile->display_name;
                }
            }
            // 单独处理问答分类
            if($wp_query->get('qa_cat')){
                $cat = get_term_by( 'slug', $wp_query->get('qa_cat'), 'qa_cat' );
                if($cat){
                    $wp_query->set('title', $cat->name);
                    $keywords = get_term_meta( $cat->term_id, 'wpcom_seo_keywords', true );
                    $keywords = $keywords!='' ? $keywords : $cat->name;
                    $keywords = str_replace('，', ',', trim(strip_tags($keywords)));

                    $description = get_term_meta( $cat->term_id, 'wpcom_seo_description', true );
                    $description = $description!='' ? $description : term_description($cat->term_id);
                    $description = trim(strip_tags($description));
                }
            }
        } else if (is_category() || is_tag() || is_tax()) {
            $term = get_queried_object();
            $keywords = get_term_meta( $term->term_id, 'wpcom_seo_keywords', true );
            $keywords = $keywords!='' ? $keywords : single_cat_title('', false);
            $keywords = str_replace('，', ',', trim(strip_tags($keywords)));

            $description = get_term_meta( $term->term_id, 'wpcom_seo_description', true );
            $description = $description!='' ? $description : term_description();
            $description = trim(strip_tags($description));
        }else if(function_exists('is_woocommerce') && is_shop()){
            $post = get_post(wc_get_page_id( 'shop' ));
            $keywords = str_replace('，', ',', trim(strip_tags(get_post_meta( $post->ID, 'wpcom_seo_keywords', true))));
            if(!$keywords) $keywords = $post->post_title;
            $description = trim(strip_tags(get_post_meta( $post->ID, 'wpcom_seo_description', true)));
            if(!$description) {
                if ($post->post_excerpt) {
                    $description = utf8_excerpt(strip_tags($post->post_excerpt), 200);
                } else {
                    $content = preg_replace("/\[(\/?map.*?)\]/si", "", $post->post_content);

                    if(!(function_exists('is_wpcom_member_page') && is_wpcom_member_page())){
                        ob_start();
                        echo do_shortcode( $content );
                        $content = ob_get_contents();
                        ob_end_clean();
                    }

                    $content = str_replace(' ', '', trim(strip_tags($content)));
                    $content = preg_replace('/\\s+/', ' ', $content );

                    $description = utf8_excerpt($content, 200);
                }
            }
        }

        $wx_thumb = isset($options['wx_thumb']) ? $options['wx_thumb'] : '';
        $wx_thumb = is_numeric($wx_thumb) ? wp_get_attachment_url( $wx_thumb ) : $wx_thumb;
        if ($keywords) $seo .= '<meta name="keywords" content="' . esc_attr($keywords) . '" />' . "\n";
        if ($description) $seo .= '<meta name="description" content="' . esc_attr(trim(strip_tags($description))) . '" />' . "\n";
        if(is_singular() && !is_front_page()){
            global $paged;
            if(!$paged){$paged = 1;}
            $url = get_pagenum_link($paged);

            $img_url = WPCOM::thumbnail_url($post->ID, 'full');
            $GLOBALS['post-thumb'] = $img_url;
            if(!$img_url){
                preg_match_all('/<img[^>]*src=[\'"]([^\'"]+)[\'"].*>/iU', $post->post_content, $matches);
                if(isset($matches[1]) && isset($matches[1][0])){
                    $img_url = $matches[1][0];
                }
            }

            $image = $img_url ?: $wx_thumb;

            $type = 'article';
            if(is_singular('page')){
                $type = 'webpage';
            }else if(is_singular('product')){
                $type = 'product';
            }
            if($open_graph){
                $post_title = $wp_query->get('qa_cat') && $wp_query->get('title') ? $wp_query->get('title') : $post->post_title;
                $seo .= '<meta property="og:type" content="'.$type.'" />' . "\n";
                $seo .= '<meta property="og:url" content="'.$url.'" />' . "\n";
                $seo .= '<meta property="og:site_name" content="'.esc_attr(get_bloginfo( "name" )).'" />' . "\n";
                $seo .= '<meta property="og:title" content="'.esc_attr($post_title).'" />' . "\n";
                if($image) $seo .= '<meta property="og:image" content="'.esc_url($image).'" />' . "\n";
                if ($description) $seo .= '<meta property="og:description" content="'.esc_attr(trim(strip_tags($description))).'" />' . "\n";
            }
        }else if (is_home() || is_front_page()) {
            global $page;
            if(!$page){$page = 1;}
            $url = get_pagenum_link($page);

            $image = $wx_thumb;
            $title = isset($options['home-title']) ? $options['home-title'] : '';;

            if($title=='') {
                $desc = get_bloginfo('description');
                if ($desc) {
                    $title = get_option('blogname') . (isset($options['title_sep_home']) && $options['title_sep_home'] ? $options['title_sep_home'] : ' - ') . $desc;
                } else {
                    $title = get_option('blogname');
                }
            }
            if($open_graph) {
                $seo .= '<meta property="og:type" content="webpage" />' . "\n";
                $seo .= '<meta property="og:url" content="' . $url . '" />' . "\n";
                $seo .= '<meta property="og:site_name" content="' . esc_attr(get_bloginfo("name")) . '" />' . "\n";
                $seo .= '<meta property="og:title" content="' . esc_attr($title) . '" />' . "\n";
                if ($image) $seo .= '<meta property="og:image" content="' . esc_url($image) . '" />' . "\n";
                if ($description) $seo .= '<meta property="og:description" content="' . esc_attr(trim(strip_tags($description))) . '" />' . "\n";
            }
        } else if (is_category() || is_tag() || is_tax() ) {
            global $paged;
            if(!$paged){$paged = 1;}
            $url = get_pagenum_link($paged);
            $image = $wx_thumb;
            if($open_graph) {
                $seo .= '<meta property="og:type" content="webpage" />' . "\n";
                $seo .= '<meta property="og:url" content="' . $url . '" />' . "\n";
                $seo .= '<meta property="og:site_name" content="' . esc_attr(get_bloginfo("name")) . '" />' . "\n";
                $seo .= '<meta property="og:title" content="' . esc_attr(single_cat_title('', false)) . '" />' . "\n";
                if ($image) $seo .= '<meta property="og:image" content="' . esc_url($image) . '" />' . "\n";
                if ($description) $seo .= '<meta property="og:description" content="' . esc_attr(trim(strip_tags($description))) . '" />' . "\n";
            }
        }
    }

    if( isset($options['canonical']) && $options['canonical']=='1' && is_singular() ){
        $id = get_queried_object_id();
        if ( 0 !== $id && $url = wp_get_canonical_url( $id )) {
            $seo .= '<link rel="canonical" href="' . esc_url( $url ) . '" />' . "\n";
        }
    }
    if(is_attachment() && isset($options['noindex_attachment']) && $options['noindex_attachment']){
        $seo .= '<meta name="robots" content="noindex,nofollow" />'."\n";
    }
    $seo .= '<meta name="applicable-device" content="pc,mobile" />'."\n";
    $seo .= '<meta http-equiv="Cache-Control" content="no-transform" />'."\n";
    if(isset($options['fav']) && $options['fav']){
        $url = is_numeric($options['fav']) ? wp_get_attachment_url( $options['fav'] ) : $options['fav'];
        $seo .= '<link rel="shortcut icon" href="'.$url.'" />'."\n";
    }

    echo apply_filters('wpcom_head_seo', $seo);
}

// wp title
add_filter( 'wp_title_parts', 'wpcom_title_parts', 20 );
if ( ! function_exists( 'wpcom_title' ) ) :
    function wpcom_title_parts( $parts ){
        global $post, $options, $wp_title_parts, $wp_query;
        if( !isset($options['seo']) || $options['seo']=='1' ) {
            if ( is_tax() && get_queried_object()) {
                $parts = array( single_term_title( '', false ) );
            }
            $title_array = array();
            foreach ( $parts as $t ){
                if(trim($t)) $title_array[] = $t;
            }
            if ( is_singular() ) {
                // 问答分类插件已经处理，排除
                if(!$wp_query->get('qa_cat')){
                    $seo_title = trim(strip_tags(get_post_meta($post->ID, 'wpcom_seo_title', true)));
                    if ($seo_title != '') $title_array[0] = $seo_title;
                }
            } else if ( is_category() || is_tag() || is_tax() ) {
                $term = get_queried_object();
                $seo_title = get_term_meta($term->term_id, 'wpcom_seo_title', true);
                $seo_title = $seo_title != '' ? $seo_title : '';
                if ($seo_title != '') $title_array[0] = $seo_title;
            } else if(function_exists('is_woocommerce') && is_shop()) {
                $post = get_post(wc_get_page_id( 'shop' ));
                $seo_title = trim(strip_tags(get_post_meta($post->ID, 'wpcom_seo_title', true)));
                if ($seo_title != '') $title_array[0] = $seo_title;
            }
            $wp_title_parts = $title_array;
        }else{
            $wp_title_parts = $parts;
        }

        return $wp_title_parts;
    }
endif;

add_filter( 'wp_title', 'wpcom_title', 10, 3 );
if ( ! function_exists( 'wpcom_title' ) ) :
    function wpcom_title( $title, $sep, $seplocation) {
        global $paged, $page, $options, $wp_title_parts;

        if( !isset($options['seo']) || $options['seo']=='1' ) {
            if ((is_home() || is_front_page()) && isset($options['home-title']) && $options['home-title']) {
                return $options['home-title'];
            }

            $prefix = !empty($title) ? $sep : '';
            $title = $seplocation == 'right' ? implode($sep, array_reverse($wp_title_parts)).$prefix : $prefix.implode($sep, $wp_title_parts);
        }

        // 首页标题
        if ( empty($title) && (is_home() || is_front_page()) ) {
            $desc = get_bloginfo('description');
            if ($desc) {
                $title = get_option('blogname') . (isset($options['title_sep_home']) && $options['title_sep_home'] ? $options['title_sep_home'] : $sep) . $desc;
            } else {
                $title = get_option('blogname');
            }
        } else {
            if ($paged >= 2 || $page >= 2) // 增加页数
                $title = $title . sprintf(__('Page %s', 'wpcom'), max($paged, $page)) . $sep;
            if ('right' == $seplocation) {
                $title = $title . get_option('blogname');
            } else {
                $title = get_option('blogname') . $title;
            }
        }
        return $title;
    }
endif;