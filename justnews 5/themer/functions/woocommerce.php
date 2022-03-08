<?php
defined( 'ABSPATH' ) || exit;

add_filter( 'woocommerce_enqueue_styles', '__return_false' );
remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

add_filter( 'woocommerce_ship_to_different_address_checked', '__return_false' );

add_filter( 'woocommerce_get_myaccount_page_permalink', 'wpcom_woo_myaccount_page_permalink' );
add_action( 'woocommerce_before_edit_account_address_form', 'wc_print_notices', 10 );


add_action( 'wp_enqueue_scripts', 'wpcom_woo_scripts' );
function wpcom_woo_scripts(){
    // 载入主样式
    if (function_exists('WC')) {
        wp_enqueue_style('wpcom-woo', get_template_directory_uri() . '/css/woocommerce.css', array(), THEME_VERSION);
        wp_enqueue_style('wpcom-woo-smallscreen', get_template_directory_uri() . '/css/woocommerce-smallscreen.css', array(), THEME_VERSION, 'only screen and (max-width: 768px)');

        if ( is_cart() || is_checkout() || is_wc_endpoint_url( 'edit-address' ) || is_wpcom_member_page() ) {
            $city_select_path = FRAMEWORK_URI . '/assets/js/woo-address.js';
            wp_enqueue_script('woo-city-select', $city_select_path, array('jquery', 'woocommerce'), THEME_VERSION, true);
            $cities = json_encode( wpcom_woo_get_cities() );
            wp_localize_script( 'woo-city-select', 'wc_cities_select_params', array(
                'cities' => $cities,
                'i18n_select_city_text' => esc_attr__( 'Select an option&hellip;', 'woocommerce' )
            ) );
        }
    }
}

add_filter('woocommerce_format_sale_price', 'woo_format_sale_price', 10, 3);
function woo_format_sale_price($price, $regular_price, $sale_price ) {
    $price = '<ins>' . ( is_numeric( $sale_price ) ? wc_price( $sale_price ) : $sale_price ) . '</ins> <del>' . ( is_numeric( $regular_price ) ? wc_price( $regular_price ) : $regular_price ) . '</del>';
    return $price;
}

add_filter( 'woocommerce_product_get_rating_html', 'woo_product_get_rating_html', 10, 2 );
function woo_product_get_rating_html($rating_html, $rating){
    if($rating<=0){
        $rating_html  = '<div class="star-rating"></div>';
    }
    return $rating_html;
}

add_action( 'wpcom_woo_cart_icon', 'wpcom_woo_cart_icon' );
function wpcom_woo_cart_icon() {
    global $options;
    if ( isset($options['show_cart']) && $options['show_cart']=='1' && function_exists('WC') ) {
        $count = WC()->cart->cart_contents_count;
        $html = '<a class="cart-contents" href="'.wc_get_cart_url().'">' . WPCOM::icon('shopping-cart', false);
        if ( $count > 0 ) {
            $html .= '<span class="shopping-count">' . esc_html( $count ) . '</span>';
        }
        $html .= '</a>';
        $html = apply_filters( 'wpcom_woo_cart_icon_html', $html, $count );
        echo '<div class="shopping-cart woocommerce">' . $html . '</div>';
    }
}

/**
 * Ensure cart contents update when products are added to the cart via AJAX
 */
add_filter( 'woocommerce_add_to_cart_fragments', 'wpcom_woo_icon_add_to_cart_fragment' );
function wpcom_woo_icon_add_to_cart_fragment( $fragments ) {
    $count = WC()->cart->cart_contents_count;
    $html = '<a class="cart-contents" href="'.wc_get_cart_url().'">' . WPCOM::icon('shopping-cart', false);
    if ( $count > 0 ) {
        $html .= '<span class="shopping-count">' . esc_html( $count ) . '</span>';
    }
    $html .= '</a>';

    $fragments['a.cart-contents'] = apply_filters( 'wpcom_woo_cart_icon_html', $html, $count );
    return $fragments;
}

add_filter( 'woocommerce_product_reviews_tab_title', 'wpcom_reviews_tab_title' );
function wpcom_reviews_tab_title( ) {
    global $product;
    return sprintf( __( 'Reviews (%d)', 'wpcom' ), $product->get_review_count() );
}

add_filter( 'woocommerce_billing_fields', 'wpcom_woo_address_to_edit', 10, 2);
function wpcom_woo_address_to_edit( $address, $country ) {
    global $options;

    $i = 1;
    $billing_ordered_fields = array();
    foreach ($address as $key => $field) {
        $address[$key]['priority'] = $i * 10;
        $billing_ordered_fields[$key] = $field;
        $i++;
    }

    $address = $billing_ordered_fields;

    if($country === 'CN'){
        $address['billing_last_name']['required'] = 0;
        $address['billing_postcode']['required'] = 0;
        $address['billing_address_2']['required'] = 0; // 为兼容旧版本没有address_2的情况，此项非必填
    }
    $address['billing_country']['priority'] = 5;

    // 虚拟商品隐藏地址
    if(isset($options['virtual_skip_shipping']) && $options['virtual_skip_shipping'] && is_checkout()) {
        $only_virtual = true;
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            if (!$cart_item['data']->is_virtual()) $only_virtual = false;
        }
        if ($only_virtual) {
            unset($address['billing_address_1']);
            unset($address['billing_address_2']);
            unset($address['billing_city']);
            unset($address['billing_postcode']);
            unset($address['billing_country']);
            unset($address['billing_state']);
            unset($address['billing_phone']);
            unset($address['billing_first_name']);
            unset($address['billing_last_name']);
            unset($address['billing_email']);
            add_filter('woocommerce_enable_order_notes_field', '__return_false');
            add_filter('wpcom_virtual_skip_shipping', '__return_true');
        }
    }
    return $address;
}

add_filter( 'woocommerce_get_country_locale', 'wpcom_woo_get_country_locale', 20, 1 );
function wpcom_woo_get_country_locale( $fields ) {
    $fields['CN']['first_name']['label'] = __('Name', 'wpcom');
    $fields['CN']['first_name']['class'] = array('form-row form-row-wide');
    $fields['CN']['last_name']['hidden'] = true;
    $fields['CN']['last_name']['required'] = 0;
    $fields['CN']['company']['hidden'] = true;
    $fields['CN']['company']['required'] = 0;
    $fields['CN']['state']['priority'] = 50;
    $fields['CN']['postcode']['required'] = 0;
    $fields['CN']['postcode']['hidden'] = true;
    $fields['CN']['city']['priority'] = 60;
    $fields['CN']['city']['placeholder'] = __( 'Select an option&hellip;', 'woocommerce' );
    $fields['CN']['city']['label'] = __('City', 'wpcom');
    $fields['CN']['address_1']['priority'] = 70;
    $fields['CN']['address_1']['label'] = __('District', 'wpcom');
    $fields['CN']['address_1']['placeholder'] = __( 'Select an option&hellip;', 'woocommerce' );
    $fields['CN']['address_2']['priority'] = 80;
    $fields['CN']['address_2']['required'] = 1;
    $fields['CN']['address_2']['label'] = __('Address', 'wpcom');
    $fields['CN']['address_2']['placeholder'] = __('Detailed address', 'wpcom');
    $fields['CN']['address_2']['label_class'] = array('');
    return $fields;
}

add_filter( 'woocommerce_country_locale_field_selectors', 'wpcom_woo_country_locale_field_selectors', 10, 1 );
function wpcom_woo_country_locale_field_selectors($fileds){
    $fileds['first_name'] = '#billing_first_name_field, #shipping_first_name_field';
    $fileds['last_name'] = '#billing_last_name_field, #shipping_last_name_field';
    $fileds['company'] = '#billing_company_field, #shipping_company_field';
    return $fileds;
}


add_filter( 'woocommerce_form_field_args', 'wpcom_woo_form_field_args', 10, 3);
function wpcom_woo_form_field_args($args, $key, $value){
    if( $args['type']=='state' && $value=='' && $args['country']=='CN'){
        $args['default'] = 'CN2';
    }
    return $args;
}

add_filter( 'woocommerce_form_field_country', 'wpcom_woo_form_field_country', 10, 3);
function wpcom_woo_form_field_country($field, $key, $args){
    $countries = 'shipping_country' === $key ? WC()->countries->get_shipping_countries() : WC()->countries->get_allowed_countries();

    if ( 1 === count( $countries ) ) { // 只有一个国家，则隐藏国家选项
         $field = str_replace('<p class="form-row ', '<p class="form-row hide ', $field);
    }
    return $field;
}

add_filter('woocommerce_localisation_address_formats', 'wpcom_woo_address_formats');
function wpcom_woo_address_formats($format){
    $format = array();
    if( is_cart() ){
        $format['CN'] = "{state} {city}";
        $format['TW'] = $format['CN'];
        $format['HK'] = $format['CN'];
        $format['default'] = "{city}, {state}, {postcode}, {country}";
    }else{
        $format['CN'] = "{name} {phone}\n{state}-{city}-{address_1}-{address_2}";
        $format['TW'] = $format['CN'];
        $format['HK'] = $format['CN'];
        $format['default'] = "{name} {phone}\n{address_1} {address_2}, {city}, {state}, {postcode}\n{country}";
    }
    return $format;
}

add_filter( 'woocommerce_formatted_address_replacements', 'wpcom_woo_formatted_address_replacements', 10, 2 );
function wpcom_woo_formatted_address_replacements($formatted_address, $arg){
    $formatted_address['{phone}'] = isset($arg['phone']) ? $arg['phone'] : '';
    return $formatted_address;
}

add_filter( 'woocommerce_account_menu_items', 'wpcom_woo_account_menu_items' );
function wpcom_woo_account_menu_items( $items ){
    global $options;
    if( isset($options['member_enable']) && $options['member_enable']=='1' ){
        $items['orders'] = __('Orders', 'wpcom');
        $items['downloads'] = __('Downloads', 'wpcom');
        $items['edit-address'] = __('Addresses', 'wpcom');
        unset($items['dashboard']);
        unset($items['edit-account']);
        unset($items['customer-logout']);
    }
    return $items;
}

add_filter( 'woocommerce_get_cancel_order_url', 'wpcom_woo_cancel_order_url' );
function wpcom_woo_cancel_order_url( $url ){
    global $options;
    if( isset($options['member_enable']) && $options['member_enable']=='1' ) {
        preg_match('/order_id=([\d]+)/i', $url, $matches);
        if(isset($matches[1]) && $matches[1]){
            $order    = wc_get_order( $matches[1] );
            $url = wp_nonce_url(
                add_query_arg(
                    array(
                        'cancel_order' => 'true',
                        'order' => $order->get_order_key(),
                        'order_id' => $order->get_id(),
                        'redirect' => wpcom_subpage_url('orders'),
                    ), $order->get_cancel_endpoint()
                ), 'woocommerce-cancel_order'
            );
        }
    }
    return $url;
}

add_filter('loop_shop_columns', 'wpcom_woo_shop_columns');
function wpcom_woo_shop_columns(){
    global $options;
    return isset($options['shop_list_col']) && $options['shop_list_col'] ? $options['shop_list_col'] : 4;
}

add_filter( 'body_class', 'wpcom_woo_body_class' );
function wpcom_woo_body_class( $classes ){
    if(!function_exists('is_woocommerce')) return $classes;
    global $options;
    $classes = (array) $classes;
    $class = '';
    if(is_singular( 'product' )) {
        $class = isset($options['related_col']) && $options['related_col'] ? 'columns-'.$options['related_col'] : 'columns-4';
    }else if(is_post_type_archive( 'product' ) || is_woocommerce()){
        $class = isset($options['shop_list_col']) && $options['shop_list_col'] ? 'columns-'.$options['shop_list_col'] : 'columns-4';
    }
    $classes[] = $class;
    return $classes;
}

add_filter( 'woocommerce_output_related_products_args', 'wpcom_woo_related_products_args');
add_filter( 'woocommerce_upsell_display_args', 'wpcom_woo_related_products_args');
function wpcom_woo_related_products_args( $args ){
    global $options;
    $args['columns'] = isset($options['related_col']) ? $options['related_col'] : 4;
    $args['posts_per_page'] = isset($options['related_posts_per_page']) ? $options['related_posts_per_page'] : 4;
    return $args;
}

add_filter( 'loop_shop_per_page', 'wpcom_woo_shop_per_page');
function wpcom_woo_shop_per_page( $posts ){
    global $options;
    return isset($options['shop_posts_per_page']) ? $options['shop_posts_per_page'] : $posts;
}

remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );
add_action( 'woocommerce_archive_description', 'wpcom_woo_archive_description', 10 );
function wpcom_woo_archive_description(){
    if ( is_search() ) {
        return;
    }

    if ( is_post_type_archive( 'product' ) ) {
        $shop_page   = get_post( wc_get_page_id( 'shop' ) );
        if ( $shop_page ) {
            $description = wc_format_content( $shop_page->post_content );
            if ( $description ) {
                echo '<div class="page-description">' . $description . '</div>';
            }
        }
    }
}

add_filter( 'woocommerce_is_account_page', 'wpcom_wc_is_account_page' );
function wpcom_wc_is_account_page( $res ){
    global $options;
    if( isset($options['member_enable']) && $options['member_enable']=='1' ) {
        return is_wpcom_member_page();
    }else{
        return $res;
    }
}

add_filter( 'wpcom_account_tabs', 'wpcom_woo_add_tabs' );
function wpcom_woo_add_tabs( $tabs ){
    if( !function_exists('is_woocommerce') ) return $tabs;

    $orders = get_option( 'woocommerce_myaccount_orders_endpoint', 'orders' );
    $downloads = get_option( 'woocommerce_myaccount_downloads_endpoint', 'downloads' );
    $edit_address = get_option( 'woocommerce_myaccount_edit_address_endpoint', 'edit-address' );
    $view_order = get_option( 'woocommerce_myaccount_view_order_endpoint', 'view-order' );

    if($orders) {
        $tabs[14] = array(
            'slug' => $orders,
            'title' => __('Orders', 'wpcom'),
            'icon' => 'order-circle'
        );
        add_action( 'wpcom_account_tabs_'.$orders, 'wpcom_account_tabs_orders' );
    }

    if($downloads) {
        $tabs[15] = array(
            'slug' => $downloads,
            'title' => __('Downloads', 'wpcom'),
            'icon' => 'download-circle'
        );
        add_action( 'wpcom_account_tabs_'.$downloads, 'wpcom_account_tabs_downloads' );
    }
    if($edit_address) {
        $tabs[16] = array(
            'slug' => $edit_address,
            'title' => __('Addresses', 'wpcom'),
            'icon' => 'address-circle'
        );
        add_action( 'wpcom_account_tabs_'.$edit_address, 'wpcom_account_tabs_address' );

    }
    if($view_order) {
        $tabs[9999] = array(
            'slug' => $view_order,
            'title' => __('Orders', 'wpcom'),
            'icon' => 'order-circle',
            'parent' => 'orders'
        );
        add_action( 'wpcom_account_tabs_'.$view_order, 'wpcom_account_tabs_view_order' );
    }

    return $tabs;
}

add_filter('woocommerce_get_view_order_url', 'wpcom_woo_order_url', 10, 2);
function wpcom_woo_order_url($url, $that){
    $permalink_structure = get_option('permalink_structure');
    if(!$permalink_structure){ // 默认链接规则
        $view_order = get_option( 'woocommerce_myaccount_view_order_endpoint', 'view-order' );
        $page = wpcom_subpage_url($view_order);
        $url =  add_query_arg( 'pageid', $that->get_id(), $page );
    }
    return $url;
}

function wpcom_account_tabs_orders() {
    $page = get_query_var('pageid') ? get_query_var('pageid') : 1;
    ?>
    <div class="woocommerce">
        <?php do_action( 'woocommerce_account_orders_endpoint', $page ); ?>
    </div>
<?php }

function wpcom_account_tabs_downloads() { ?>
    <div class="woocommerce">
        <?php do_action( 'woocommerce_account_downloads_endpoint' ); ?>
    </div>
<?php }

function wpcom_account_tabs_address() { ?>
    <div class="woocommerce">
        <?php do_action( 'woocommerce_account_edit-address_endpoint', 'billing' ); ?>
    </div>
<?php }

function wpcom_account_tabs_view_order() {
    $order_id = get_query_var('pageid') ? get_query_var('pageid') : 0; ?>
    <div class="woocommerce">
        <?php woocommerce_order_details_table($order_id); ?>
    </div>
<?php }

function wpcom_woo_myaccount_page_permalink( $link ){
    global $options;
    if( isset($options['member_enable']) && $options['member_enable']=='1' && $options['member_page_account'] ) {
        return wpcom_account_url();
    }
    return $link;
}

add_filter( 'woocommerce_get_image_size_gallery_thumbnail', 'wpcom_woo_get_image_thumbnail' );
function wpcom_woo_get_image_thumbnail(){
    return wc_get_image_size( 'thumbnail' );
}

add_filter( 'woocommerce_get_image_size_single', 'wpcom_woo_get_image_single' );
function wpcom_woo_get_image_single( $size ){
    global $_wp_additional_image_sizes;
    if( isset($_wp_additional_image_sizes['woocommerce_single']) ) return $_wp_additional_image_sizes['woocommerce_single'];

    $size['width'] = absint( wc_get_theme_support( 'single_image_width', get_option( 'woocommerce_single_image_width', 800 ) ) );
    $cropping = get_option( 'woocommerce_thumbnail_cropping', '1:1' );

    if ( 'uncropped' === $cropping ) {
        $size['height'] = '';
        $size['crop']   = 0;
    } elseif ( 'custom' === $cropping ) {
        $width          = max( 1, get_option( 'woocommerce_thumbnail_cropping_custom_width', '4' ) );
        $height         = max( 1, get_option( 'woocommerce_thumbnail_cropping_custom_height', '3' ) );
        $size['height'] = absint( round( ( $size['width'] / $width ) * $height ) );
        $size['crop']   = 1;
    } else {
        $cropping_split = explode( ':', $cropping );
        $width          = max( 1, current( $cropping_split ) );
        $height         = max( 1, end( $cropping_split ) );
        $size['height'] = absint( round( ( $size['width'] / $width ) * $height ) );
        $size['crop']   = 1;
    }
    return $size;
}

// Place the code below in your theme's functions.php file
add_filter( 'woocommerce_get_catalog_ordering_args', 'wpcom_get_catalog_ordering_args' );
function wpcom_get_catalog_ordering_args( $args ) {
    $orderby_value = isset( $_GET['orderby'] ) ? wc_clean( $_GET['orderby'] ) : apply_filters( 'woocommerce_default_catalog_orderby', get_option( 'woocommerce_default_catalog_orderby' ) );
    if ( 'sales' == $orderby_value ) {
        $args['orderby'] = 'meta_value_num';
        $args['order'] = 'DESC';
        $args['meta_key'] = 'total_sales';
    }
    return $args;
}

add_filter( 'woocommerce_default_catalog_orderby_options', 'wpcom_catalog_orderby' );
add_filter( 'woocommerce_catalog_orderby', 'wpcom_catalog_orderby' );
function wpcom_catalog_orderby( $sortby ) {
    $sortby['sales'] = __('Sort by sales', 'wpcom');
    return $sortby;
}

add_filter( 'wc_product_sku_enabled', 'wpcom_product_sku_enabled', 20);
function wpcom_product_sku_enabled($res){
    global $product;
    if(!is_admin() && !$product->get_sku()){
        $res = false;
    }
    return $res;
}

add_filter('woocommerce_order_get_billing_email', 'wpcom_billing_email_filter');
add_filter('default_checkout_billing_email', 'wpcom_billing_email_filter');
function wpcom_billing_email_filter($email){
    if(wpcom_is_empty_mail($email)) { // 未设置邮箱
        $email = '';
    }
    return $email;
}

add_filter('woocommerce_my_account_edit_address_field_value', 'wpcom_woo_address_field_value', 10, 2);
function wpcom_woo_address_field_value($value, $key){
    if($key === 'billing_email' && wpcom_is_empty_mail($value)) { // 未设置邮箱
        $value = '';
    }
    return $value;
}

add_filter('woocommerce_mail_callback_params', 'wpcom_woo_mail_callback_params', 10, 2);
function wpcom_woo_mail_callback_params($params, $that){
    if(wpcom_is_empty_mail($params[0]) && $that && $that->object) { // 未设置邮箱
        $params[0] = $that->object->get_billing_email();
    }
    return $params;
}

add_filter( 'woocommerce_ajax_variation_threshold', 'wpcom_woo_ajax_threshold' );
function wpcom_woo_ajax_threshold() {
    return 50;
}

add_filter( 'woocommerce_apply_user_tracking', '__return_false', 10 );
add_action('admin_init', 'wpcom_remove_tracking', 10);
function wpcom_remove_tracking(){
    wp_deregister_script('woo-tracks');
    wp_register_script( 'woo-tracks', '', array( 'wp-hooks' ), gmdate( 'YW' ), false );
}

add_filter( 'woocommerce_kses_notice_allowed_tags', 'wpcom_notice_allowed_tags' );
function wpcom_notice_allowed_tags($tags){
    if(is_array($tags) && !isset($tags['svg'])){
        $tags['svg'] = array('aria-hidden' => 1, 'class' => 1);
        $tags['use'] = array('xlink:href'=> 1);
    }
    return $tags;
}

add_filter( 'woocommerce_cart_item_remove_link', 'wpcom_cart_remove_link' );
function wpcom_cart_remove_link($link){
    $link = str_replace('&times;', WPCOM::icon('close', false), $link);
    return $link;
}

add_action('init', 'wpcom_register_custom_order_statuses');
function wpcom_register_custom_order_statuses() {
    register_post_status('wc-shipped ', array(
        'label' => __( 'Shipped', 'wpcom' ),
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Shipped <span class="count">(%s)</span>', 'Shipped <span class="count">(%s)</span>', 'wpcom')
    ));
}

// Add a custom order status to list of WC Order statuses
add_filter('wc_order_statuses', 'wpcom_add_custom_order_statuses');
function wpcom_add_custom_order_statuses($order_statuses) {
    $new_order_statuses = array();

    // add new order status before processing
    foreach ($order_statuses as $key => $status) {
        $new_order_statuses[$key] = $status;
        if ('wc-processing' === $key) {
            $new_order_statuses['wc-shipped'] = __('Shipped', 'wpcom' );
        }
    }
    return $new_order_statuses;
}

add_filter( 'woocommerce_my_account_my_orders_query', 'wpcom_woo_account_orders' );
function wpcom_woo_account_orders( $args ) {
    global $options;
    if(isset($options['shop_orders_limit']) && $options['shop_orders_limit']) $args['limit'] = $options['shop_orders_limit'];
    return $args;
}

add_filter( 'woocommerce_customer_available_downloads', 'wpcom_woo_downloads_per_page');
function wpcom_woo_downloads_per_page($downloads){
    global $options, $wp_query, $downloads_total;
    $downloads = $downloads ? array_reverse($downloads) : $downloads;
    if($downloads && isset($options['shop_downloads_limit']) && is_numeric($options['shop_downloads_limit'])){
        $downloads_total = count($downloads);
        $paged = 1;
        if(isset($_GET['page'])) {
            $paged = $_GET['page'];
        }else if(isset($wp_query->query['pageid'])){
            $paged = $wp_query->query['pageid'];
        }
        $paged = $paged ? $paged : 1;
        $offset = ($paged - 1) * $options['shop_downloads_limit'];
        $downloads = array_slice($downloads, $offset, $options['shop_downloads_limit']);
    }
    return $downloads;
}

add_action( 'woocommerce_after_account_downloads', 'wpcom_woo_downloads_pagination' );
function wpcom_woo_downloads_pagination($has_downloads){
    global $options, $wp_query, $downloads_total;
    if($has_downloads && isset($options['shop_downloads_limit']) && is_numeric($options['shop_downloads_limit'])) {
        $paged = 1;
        if (isset($_GET['page'])) {
            $paged = $_GET['page'];
        } else if (isset($wp_query->query['pageid'])) {
            $paged = $wp_query->query['pageid'];
        }
        $paged = $paged ?: 1;
        $max_pages = ceil( $downloads_total / $options['shop_downloads_limit'] );
        $pagi_args = array(
            'paged' => $paged,
            'numpages' => $max_pages,
            'url' => wc_get_endpoint_url('downloads')
        );
        wpcom_pagination(5, $pagi_args);
    }
}

add_filter('woocommerce_add_notice', 'wpcom_woo_login_form');
function wpcom_woo_login_form($html){
    global $options;
    if(preg_match('/class="showlogin"/i', $html) && isset($options['member_enable']) && $options['member_enable']=='1'){
        $login_url = wpcom_login_url();
        $login_url = add_query_arg('modal-type', 'login', $login_url);
        $html = str_replace('<a href="#" class="showlogin">', '<a href="'.$login_url.'" class="login-form-link">', $html);
    }
    return $html;
}

/**
 * yith wishlist 兼容
 */

add_filter( 'yith_wcwl_no_product_to_remove_message', 'wpcom_yith_empty' );
function wpcom_yith_empty(){
    return __( 'No products were added to the wishlist', 'wpcom' );
}

add_filter( 'yith_wcwl_button_icon', 'wpcom_yith_wcwl_button_icon' );
function wpcom_yith_wcwl_button_icon( $icon ){
    $icon = 'fa-heart-o';
    return $icon;
}

add_filter( 'yith_wcwl_wishlist_title', '__return_false' );

/**
 * 订单配送地址级联选择优化
 */

add_filter( 'woocommerce_billing_fields', 'wpcom_woo_billing_fields', 10, 2 );
add_filter( 'woocommerce_shipping_fields', 'wpcom_woo_shipping_fields', 10, 2 );
add_filter( 'woocommerce_form_field_city', 'wpcom_woo_form_field_city', 10, 4 );

function wpcom_woo_billing_fields( $fields, $country ) {
    $fields['billing_city']['type'] = 'city';
    return $fields;
}

function wpcom_woo_shipping_fields( $fields, $country ) {
    $fields['shipping_city']['type'] = 'city';
    return $fields;
}

function wpcom_woo_form_field_city($field, $key, $args, $value ){
    $country = isset( $args['country'] ) ? $args['country'] : WC()->checkout->get_value( 'billing_state' === $key ? 'billing_country' : 'shipping_country' );
    $state = isset( $args['state'] ) ? $args['state'] : WC()->checkout->get_value( 'billing_city' === $key ? 'billing_state' : 'shipping_state' );
    $cities = wpcom_woo_get_cities( $country, $state );

    if ( ! empty( $args['custom_attributes'] ) && is_array( $args['custom_attributes'] ) ) {
        foreach ( $args['custom_attributes'] as $attribute => $attribute_value ) {
            $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
        }
    }

    if ( $args['required'] ) {
        $args['class'][] = 'validate-required';
        $required        = '&nbsp;<abbr class="required" title="' . esc_attr__( 'required', 'woocommerce' ) . '">*</abbr>';
    } else {
        $required = '&nbsp;<span class="optional">(' . esc_html__( 'optional', 'woocommerce' ) . ')</span>';
    }
    $label_id        = $args['id'];
    $sort            = $args['priority'] ?: '';
    $field_container = '<p class="form-row %1$s" id="%2$s" data-priority="' . esc_attr( $sort ) . '">%3$s</p>';

    if ( is_array( $cities ) ) {
        $data_label = ! empty( $args['label'] ) ? 'data-label="' . esc_attr( $args['label'] ) . '"' : '';

        $field .= '<select name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" class="city_select ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . implode( ' ', $custom_attributes ) . ' data-placeholder="' . esc_attr( $args['placeholder'] ? $args['placeholder'] : esc_html__( 'Select an option&hellip;', 'woocommerce' ) ) . '"  data-input-classes="' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" ' . $data_label . '>
						<option value="">' . esc_html__( 'Select an option&hellip;', 'woocommerce' ) . '</option>';

        foreach ( $cities as $ckey => $cvalue ) {
            $label = is_string($cvalue) ? $cvalue : $ckey;
            $field .= '<option value="' . esc_attr( $ckey ) . '" ' . selected( $value, $ckey, false ) . '>' . esc_html($label ) . '</option>';
        }

        $field .= '</select>';

    } else {
        $field .= '<input type="text" class="input-text ' . esc_attr( implode( ' ', $args['input_class'] ) ) . '" value="' . esc_attr( $value ) . '"  placeholder="' . esc_attr($args['placeholder'] ? $args['placeholder'] : '' ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $args['id'] ) . '" ' . implode( ' ', $custom_attributes ) . ' data-input-classes="' . esc_attr( implode( ' ', $args['input_class'] ) ) . '"/>';
    }

    if ( ! empty( $field ) ) {
        $field_html = '';

        if ( $args['label'] && 'checkbox' !== $args['type'] ) {
            $field_html .= '<label for="' . esc_attr( $label_id ) . '" class="' . esc_attr( implode( ' ', $args['label_class'] ) ) . '">' . wp_kses_post( $args['label'] ) . $required . '</label>';
        }

        $field_html .= '<span class="woocommerce-input-wrapper">' . $field;

        if ( $args['description'] ) {
            $field_html .= '<span class="description" id="' . esc_attr( $args['id'] ) . '-description" aria-hidden="true">' . wp_kses_post( $args['description'] ) . '</span>';
        }

        $field_html .= '</span>';

        $container_class = esc_attr( implode( ' ', $args['class'] ) );
        $container_id    = esc_attr( $args['id'] ) . '_field';
        $field           = sprintf( $field_container, $container_class, $container_id, $field_html );
    }

    return $field;
}

function wpcom_woo_get_cities($country = '', $state = ''){
    $file = FRAMEWORK_PATH . '/assets/js/places.json';
    if(file_exists($file)){
        $places = @file_get_contents($file);
        $places = json_decode($places, true);
    }
    if($country){
        $_state = isset($places[$country]) ? $places[$country] : false;
        if($state){
            return isset($_state[$state]) ? $_state[$state] : false;
        }else{
            return $_state;
        }
    }else{
        return isset($places) ? $places : false;
    }
}

add_filter('post_type_link', 'wpcom_woo_product_link', 10, 2);
function wpcom_woo_product_link( $link, $post ){
    global $options;
    if(function_exists('WC') && isset($options['shop_single_url']) && $options['shop_single_url']){
        $post = get_post($post);
        if (!is_wp_error($post) && $post->post_type == 'product') {
            $link = home_url( 'product/' . $post->ID .'.html' );
        }
    }
    return $link;
}
add_action( 'init', 'wpcom_woo_product_rewrite' );
function wpcom_woo_product_rewrite(){
    global $options;
    if(function_exists('WC') && isset($options['shop_single_url']) && $options['shop_single_url']){
        add_rewrite_rule('product/([0-9]+)?.html$', 'index.php?post_type=product&p=$matches[1]', 'top' );
    }
}