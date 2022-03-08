<?php
// TEMPLATE NAME: 可视化编辑器（自定义模板）
// Template Post Type: page,post
global $post;
$mds = get_post_meta($post->ID, '_page_modules', true);
if(!$mds) $mds = array();

get_header();
do_action('wpcom_render_page', $mds);
get_footer();