<?php
/**
 * 列表模板信息设置
 * 在列表添加和编辑页面新增模板选择选项
 */
defined( 'ABSPATH' ) || exit;

// 列表选项
function add_tax_tpl_column( $columns ){
    $columns['tpl'] = '列表模板';
    return $columns;
}

// 列表选项 模板
function add_tax_tpl_column_content( $content, $column_name, $term_id ){

    if( $column_name !== 'tpl' ){
        return $content;
    }

    $term_id = absint( $term_id );
    $val = get_term_meta( $term_id, 'wpcom_tpl', true );

    $content .= $val ? '<tax-tpl data-tpl="'.$val.'" />' : '默认模板';

    return $content;
}

// 列表选项 排序
function add_tax_tpl_column_sortable( $sortable ){
    $sortable[ 'tpl' ] = 'tpl';
    return $sortable;
}

add_action('pre_get_posts', 'wpcom_tax_posts_per_page');
function wpcom_tax_posts_per_page( $query ) {
    if( $query->is_main_query() && (is_category() || is_tag() || is_tax()) && !is_admin() ) {
        global $options;
        $tax = $query->get_queried_object_id();
        $tpl = get_term_meta( $tax, 'wpcom_tpl', true );
        if($tpl){
            $id = 'per_page_for_' . $tpl;
            $sidebar = get_term_meta( $tax, 'wpcom_sidebar', true );
            $sidebar = !(!$sidebar && $sidebar!=='');
            if(!$sidebar) $id = $id . '_full';
            $num = get_option($id);
            if($num) $query->set( 'posts_per_page', $num );
        }
    }
}

add_action('_admin_menu', 'wpcom_tax_tpl_init');
function wpcom_tax_tpl_init(){
    global $wpcom_panel;
    $tpls = $wpcom_panel->get_term_tpls();
    $keys = array();
    if($tpls) {
        foreach ($tpls as $key => $tpl) {
            if($key) {
                $keys = explode(',', $key);
                $keys = $keys ?: array($key);
                break;
            }
        }
    }
    if($keys){
        foreach ($keys as $k){
            $k = trim($k);
            add_filter('manage_edit-'.$k.'_columns', 'add_tax_tpl_column' );
            add_filter('manage_'.$k.'_custom_column', 'add_tax_tpl_column_content', 10, 3 );
            add_filter('manage_edit-'.$k.'_sortable_columns', 'add_tax_tpl_column_sortable' );
        }
    }
}