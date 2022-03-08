<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_Member {
    function __construct(){
        global $options, $wp_version;
        $this->member_path = FRAMEWORK_PATH . '/member';

        add_shortcode( 'wpcom-member', array( $this, 'shortcode' ) );

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts') );
        add_action( 'wp_ajax_wpcom_cropped_upload', array( $this, 'cropped_upload' ) );
        add_action( 'wpcom_options_updated', array( $this, 'flush_rewrite_rules' ) );
        add_action( 'save_post_page', array( $this, 'flush_rewrite_rules' ) );
        add_action( 'wpcom_cron_flush_rewrite_rules', array( $this, 'cron_flush_rewrite_rules' ) );
        add_action( 'wp_ajax_wpcom_user_posts', array( $this, 'user_posts' ) );
        add_action( 'wp_ajax_nopriv_wpcom_user_posts', array( $this, 'user_posts' ) );
        add_action( 'wp_ajax_wpcom_user_comments', array( $this, 'user_comments' ) );
        add_action( 'wp_ajax_nopriv_wpcom_user_comments', array( $this, 'user_comments' ) );
        add_action( 'wp_ajax_wpcom_login_modal', array( $this, 'login_modal' ) );
        add_action( 'wp_ajax_nopriv_wpcom_login_modal', array( $this, 'login_modal' ) );
        add_action( 'wp_logout', array( $this, 'after_logout' ) );
        add_action( 'template_redirect', array( $this, 'action_before_echo' ) );
        add_action( 'parse_query', array( $this, 'parse_query' ), 100 );
        add_action( 'wpcom_register_form', array( $this, 'register_form' ) );
        add_action( 'wpcom_login_form', array( $this, 'login_form' ) );
        add_action( 'wpcom_lostpassword_form_default', array( $this, 'lostpassword_form_default' ) );
        add_action( 'wpcom_lostpassword_form_send_success', array( $this, 'lostpassword_form_send_success' ) );
        add_action( 'wpcom_lostpassword_form_reset', array( $this, 'lostpassword_form_reset' ) );
        add_action( 'wpcom_lostpassword_form_finished', array( $this, 'lostpassword_form_finished' ) );
        add_action( 'wpcom_social_login', array( $this, 'social_login' ) );
        add_action( 'wpcom_approve_resend_form', array( $this, 'approve_resend_form' ) );
        add_action( 'user_register', array( $this, 'user_register' ) );
        add_action( 'wpcom_social_new_user', array( $this, 'social_new_user' ) );
        add_action( 'login_form_register', array( $this, 'disable_default_register'), 10 );
        add_action( 'login_head', array( $this, 'login_head' ) );

        add_filter( 'wpcom_localize_script', array($this, 'localize_script') );
        add_filter( 'upload_dir', array($this, 'upload_dir') );
        add_filter( 'get_avatar_url', array($this, 'get_avatar_url'), 10, 3 );
        add_filter( 'pre_get_avatar', array($this, 'pre_get_avatar'), 10, 3 );
        add_filter( 'rewrite_rules_array', array($this, 'rewrite_rules') );
        add_filter( 'query_vars', array($this, 'query_vars'), 10, 1 );
        add_filter( 'register_url', array($this, 'register_url'), 20 );
        add_filter( 'login_url', array($this, 'login_url'), 20, 2 );
        add_filter( 'logout_url', array($this, 'logout_url'), 20, 2 );
        add_filter( 'lostpassword_url', array($this, 'lostpassword_url'), 20, 2 );
        add_filter( 'author_link', array($this, 'author_link'), 20, 3 );
        add_filter( 'show_admin_bar', array($this, 'show_admin_bar') );
        add_filter( 'wp_title_parts', array($this, 'title_parts'), 15 );
        add_filter( 'user_has_cap', array( $this, 'user_has_cap' ), 10, 4 );
        add_filter( 'authenticate', array( $this, 'authenticate' ), 50, 3 );
        add_filter( 'views_users', array( $this, 'views_users' ) );
        add_filter( 'pre_get_users', array( $this, 'filter_users' ) );
        add_filter( 'bulk_actions-users', array( $this, 'bulk_actions_users' ) );
        add_filter( 'handle_bulk_actions-users', array( $this, 'handle_bulk_actions_users' ), 10, 3 );
        add_filter( 'body_class', array( $this, 'body_class' ), 10);
        add_filter( 'user_contactmethods', array( $this, 'user_contactmethods' ), 10);
        add_filter( 'wp_mail', array( $this, 'wp_mail' ), 10);
        add_filter( 'display_post_states', array($this, 'display_page_type'), 10, 2 );
        add_filter( 'manage_users_columns', array( $this, 'users_columns' ) );
        add_filter( 'manage_users_custom_column', array( $this, 'users_column_value' ), 10, 3 );
        add_filter( 'manage_users_sortable_columns', array( $this ,'user_registered_sortable') );
        add_filter( 'users_pre_query', array( $this, 'users_pre_query' ), 10, 2);
        // 小黑屋功能需要 WP 5.8.0+
        add_filter( 'wp_pre_insert_user_data', array( $this, 'pre_insert_user_data'), 10, version_compare($wp_version,'5.8','>=') ? 4 : 3);

        $account_tabs = wpcom_account_default_tabs();
        foreach ($account_tabs as $tab){
            add_action( 'wpcom_account_tabs_' . $tab['slug'], array( $this, 'account_tabs_' . $tab['slug'] ) );
        }

        $profile_tabs = wpcom_profile_default_tabs();
        foreach ($profile_tabs as $tab){
            add_action( 'wpcom_profile_tabs_' . $tab['slug'], array( $this, 'profile_tabs_' . $tab['slug'] ) );
        }

        if( isset($options['social_login_on']) && $options['social_login_on']=='1' ) {
            require_once FRAMEWORK_PATH . '/includes/social-login.php';
            new WPCOM_Social_Login();
        }

        $show_profile = apply_filters( 'wpcom_member_show_profile' , true );
        if( $show_profile ) {
            require_once FRAMEWORK_PATH . '/includes/user-groups.php';
            new WPCOM_User_Groups();
            add_action( 'admin_init', array( $this, 'block_access_wpadmin' ) );
            add_filter( 'get_user_metadata', array($this, 'user_description'), 10, 4 );

            if(isset($options['member_follow']) && ($options['member_follow']=='1' || $options['user_card']=='1')) {
                add_action('save_post_post', array($this, 'posts_count'), 10, 2);
                add_action('save_post_qa_post', array($this, 'qa_posts_count'), 10, 2);
                add_action('transition_comment_status', array($this, 'comments_count_status'), 10, 3);
                add_action('wp_insert_comment', array($this, 'comments_count'), 10, 2);
                add_action('wpcom_user_data_stats', array($this, 'user_data_stats'), 10, 2);

                add_filter( 'wpcom_posts_count', array($this, 'get_posts_count'), 5, 2);
                add_filter( 'wpcom_comments_count', array($this, 'get_comments_count'), 5, 2);
                if(defined('QAPress_VERSION')) {
                    add_filter('wpcom_questions_count', array($this, 'get_questions_count'), 5, 2);
                    add_filter('wpcom_answers_count', array($this, 'get_answers_count'), 5, 2);
                }
            }
        }
    }

    function flush_rewrite_rules(){
        $args = array();
        $args[] = mt_rand(1000, 99999) . '_' . time();
        wp_schedule_single_event( time() + 2, 'wpcom_cron_flush_rewrite_rules', $args );
    }

    function cron_flush_rewrite_rules(){
        flush_rewrite_rules();
    }

    function rewrite_rules( $rules ) {
        global $options, $permalink_structure;
        if(!isset($permalink_structure)) $permalink_structure = get_option('permalink_structure');
        $new_rules = array();
        $pre = preg_match( '/^\/index\.php\//i', $permalink_structure) ? 'index.php/' : '';

        if( isset($options['member_page_account']) && $options['member_page_account'] ) {
            $page_uri = get_page_uri( $options['member_page_account'] );
            $new_rules[ $pre . $page_uri . '/([^/]+)/([^/]+)/?$'] = 'index.php?pagename='.$page_uri.'&subpage=$matches[1]&pageid=$matches[2]';
            $new_rules[ $pre . $page_uri . '/([^/]+)/?$'] = 'index.php?pagename='.$page_uri.'&subpage=$matches[1]';
        }

        if( isset($options['member_page_profile']) && $options['member_page_profile'] ){
            $page_uri = get_page_uri( $options['member_page_profile'] );
            $new_rules[ $pre . $page_uri . '/([^/]+)/([^/]+)/?$'] = 'index.php?pagename='.$page_uri.'&user=$matches[1]&subpage=$matches[2]';
            $new_rules[ $pre . $page_uri . '/([^/]+)/?$'] = 'index.php?pagename='.$page_uri.'&user=$matches[1]';
        }

        return array_merge($new_rules, $rules);
    }

    function query_vars($public_query_vars) {
        $public_query_vars[] = 'subpage';
        $public_query_vars[] = 'user';
        $public_query_vars[] = 'pageid';
        return $public_query_vars;
    }

    function upload_dir( $array ){
        if( isset($array['subdir']) && ( $array['subdir'] == '/1234/06' || $array['subdir'] =='' ) ){
            $type = $GLOBALS['image_type'] ? 'covers' : 'avatars';
            $array['subdir'] = '/member/' . $type;
            $array['path'] = $array['basedir'] . '/member/' . $type;
            $array['url'] = $array['baseurl'] . '/member/' . $type;
        }
        return $array;
    }

    function get_avatar_url( $url, $id_or_email, $args ){
        global $pagenow, $options;
        if( $pagenow == 'options-discussion.php' ) return $url;

        $user_id = 0;
        if ( is_numeric( $id_or_email ) ) {
            $user_id = absint( $id_or_email );
        } elseif ( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
            $user = get_user_by( 'email', $id_or_email );
            if( isset($user->ID) && $user->ID ) $user_id = $user->ID;
        } elseif ( $id_or_email instanceof WP_User ) {
            $user_id = $id_or_email->ID;
        } elseif ( $id_or_email instanceof WP_Post ) {
            $user_id = $id_or_email->post_author;
        } elseif ( $id_or_email instanceof WP_Comment ) {
            $user_id = $id_or_email->user_id;
            if( !$user_id ){
                $user = get_user_by( 'email', $id_or_email->comment_author_email );
                if( isset($user->ID) && $user->ID ) $user_id = $user->ID;
            }
        }

        if ( $user_id && $avatar = get_user_meta( $user_id, 'wpcom_avatar', 1) ) {
            if(preg_match('/^(http|https|\/\/)/i', $avatar)){
                $url = $avatar;
            }else{
                $uploads = wp_upload_dir();
                $url = $uploads['baseurl'] . $avatar;
            }
        }else if( isset($options['member_avatar']) && $options['member_avatar'] ){
            $url = is_numeric($options['member_avatar']) ? wp_get_attachment_url( $options['member_avatar'] ) : esc_url($options['member_avatar']);
        }

        $url = preg_replace('/^(http|https):/i', '', $url);
        return $url;
    }

    function pre_get_avatar( $avatar, $id_or_email, $args ){
        $url = $this->get_avatar_url( $avatar, $id_or_email, $args );
        if($url){
            if($args['alt']=='' && is_numeric( $id_or_email )){
                $user = get_user_by( 'ID', absint( $id_or_email ) );
                $args['alt'] = $user->display_name;
            }
            $class = array( 'avatar', 'avatar-' . (int) $args['size'], 'photo' );
            if ( $args['class'] ) {
                if ( is_array( $args['class'] ) ) {
                    $class = array_merge( $class, $args['class'] );
                } else {
                    $class[] = $args['class'];
                }
            }
            $avatar = sprintf(
                    "<img alt='%s' src='%s' class='%s' height='%d' width='%d' %s/>",
                    esc_attr( $args['alt'] ),
                    esc_url( $url ),
                    esc_attr( join( ' ', $class ) ),
                    (int) $args['height'],
                    (int) $args['width'],
                    $args['extra_attr']
            );
        }
        return $avatar;
    }

    function enqueue_scripts(){
        global $profile;
        if( is_wpcom_member_page( 'account' ) ||
            ( is_wpcom_member_page('profile') && ( get_current_user_id() == $profile->ID || current_user_can( 'edit_users' ) ) )
        ){
            wp_enqueue_style( 'crop', FRAMEWORK_URI . '/assets/css/cropper.min.css', array('stylesheet'), THEME_VERSION );
            wp_enqueue_script( 'crop', FRAMEWORK_URI . '/assets/js/cropper.min.js', array( 'jquery' ), THEME_VERSION, true );
            wp_enqueue_script( 'login', FRAMEWORK_URI . '/assets/js/login.js', array( 'jquery' ), THEME_VERSION, true );
        }else if( is_wpcom_member_page( 'login' ) || is_wpcom_member_page( 'register' ) || is_wpcom_member_page('lostpassword' ) ){
            wp_enqueue_script( 'login', FRAMEWORK_URI . '/assets/js/login.js', array( 'jquery' ), THEME_VERSION, true );
        }
    }

    function localize_script( $scripts ){
        global $options;
        $captcha = wpcom_member_captcha_type();
        if( $captcha == 'noCaptcha' && isset($options['nc_appkey']) && $options['nc_appkey']!='' && $options['nc_access_id']!=''  && $options['nc_access_secret']!='' ) {
            $nc_scene = 'nc_login';
            if( is_wpcom_member_page('register' ) ) $nc_scene = 'nc_register';

            $nc_scene = apply_filters( 'wpcom_no_captcha_type', $nc_scene );

            if( wp_is_mobile() ){
                $nc_scene = $nc_scene . '_h5';
            }
            $lang = get_locale();
            $lang_nc = array( 'ja' => 'ja_JP', 'zh_CN' => 'cn', 'zh_HK' => 'tw', 'zh_TW' => 'tw',);
            if(preg_match('/^en_/i', $lang)) $lang_nc[$lang] = 'en';

            $scripts['noCaptcha'] = array(
                'scene' => $nc_scene,
                'appkey' => $options['nc_appkey'],
                'language' => isset($lang_nc[$lang]) ? $lang_nc[$lang] : $lang
            );
        }else if( $captcha == 'TCaptcha' && isset($options['tc_appkey']) && $options['tc_appkey']!='' && $options['tc_appid']!='' ){
            $scripts['TCaptcha'] = array(
                'appid' => $options['tc_appid']
            );
        }else if( $captcha == 'hCaptcha' && isset($options['hc_sitekey']) && $options['hc_sitekey']!='' && $options['hc_secret']!='' ){
            $scripts['hCaptcha'] = array(
                'sitekey' => $options['hc_sitekey']
            );
        }else if( $captcha == 'reCAPTCHA' && isset($options['gc_sitekey']) && $options['gc_sitekey']!='' && $options['gc_secret']!='' ){
            $scripts['reCAPTCHA'] = array(
                'sitekey' => $options['gc_sitekey']
            );
        }

        if($captcha && $captcha !== 'noCaptcha'){
            $scripts['captcha_label'] = __("I'm not a robot", "wpcom");
            $scripts['captcha_verified'] = __("You are verified", "wpcom");
        }

        $scripts['errors'] = apply_filters( 'wpcom_member_errors', array() );

        if( is_wpcom_member_page( 'account' ) || (is_wpcom_member_page('profile') && get_current_user_id()) ){
            $scripts['cropper'] = array(
                'title' => __('Select photo', 'wpcom'),
                'desc_0' => __('Select your profile photo', 'wpcom'),
                'desc_1' => __('Select your cover photo', 'wpcom'),
                'btn' => __('Select photo', 'wpcom'),
                'apply' => __('Apply', 'wpcom'),
                'cancel' => __('Cancel', 'wpcom'),
                'alert_size' => __('This image is too large!', 'wpcom'),
                'alert_filetype' => __('Sorry this is not a valid image.', 'wpcom'),
                'err_nonce' => __('Nonce check failed!', 'wpcom'),
                'err_fail' => __('Image upload failed!', 'wpcom'),
                'err_login' => __('You must login first!', 'wpcom'),
                'err_empty' => __('Please select a photo!', 'wpcom'),
                'ajaxerr' => __('Request failed!', 'wpcom')
            );
        }

        return $scripts;
    }

    function title_parts( $part ){
        if( is_wpcom_member_page('profile') ){
            global $wp_query, $options;
            $user_slug = isset($wp_query->query['user']) && $wp_query->query['user'] ? $wp_query->query['user'] : '';
            if( !$user_slug ) return $part;

            if( isset($options['member_user_slug']) && $options['member_user_slug']=='2' ) {
                $profile = get_user_by( 'ID', $user_slug );
            } else {
                $profile = get_user_by( 'slug', $user_slug );
            }
            if( $profile ) {
                $tabs = apply_filters( 'wpcom_profile_tabs', array() );
                ksort($tabs);
                $default = current($tabs);
                $subpage = isset($wp_query->query_vars['subpage']) ? $wp_query->query_vars['subpage'] : $default['slug'];
                $display_name = $profile->display_name;
                foreach ($tabs as $tab){
                    if($tab['slug'] === $subpage){
                        $display_name = sprintf(__('%s’s %s', 'wpcom'), $display_name, $tab['title']);
                        break;
                    }
                }
                $part[] = $display_name;
            }
        }else if( is_wpcom_member_page('account') ){
            global $wp_query;
            $tabs = apply_filters( 'wpcom_account_tabs', array() );
            ksort($tabs);
            $default = current($tabs);
            $subpage = isset($wp_query->query_vars['subpage']) ? $wp_query->query_vars['subpage'] : $default['slug'];
            foreach ($tabs as $tab){
                if($tab['slug'] === $subpage){
                    $title = $tab['title'];
                    break;
                }
            }
            if(isset($title) && $title) $part[] = $title;
        }
        return $part;
    }

    function shortcode( $atts ){
        if( isset( $atts ['type'] ) && $atts ['type'] != '' && method_exists( $this, 'shortcode_' . $atts ['type'] ) ){
            $show_profile = apply_filters( 'wpcom_member_show_profile' , true );
            $excerpt = array('userlist', 'profile');
            if(!$show_profile && in_array($atts ['type'], $excerpt)) {
                return '错误：不支持的短代码类型';
            }else{
                return $this->{'shortcode_'.$atts ['type']}( $atts );
            }
        }
    }

    function shortcode_account(){
        global $wp_query;
        if( !get_current_user_id() ) return false;

        if( isset($wp_query->query_vars['subpage']) && $wp_query->query_vars['subpage'] != '' ) {
            $subpage = $wp_query->query_vars['subpage'];
        }else{
            $subpage = 'general';
        }

        $tabs = apply_filters( 'wpcom_account_tabs', array() );
        ksort($tabs);

        $atts = array(
            'subpage' => $subpage,
            'user' => wp_get_current_user(),
            'tabs' => $tabs
        );

        $atts['args'] = apply_filters( 'wpcom_account_args', array() );
        return $this->load_template('account', $atts) ;
    }

    function shortcode_lostpassword(){
        global $wp_query;
        $subpage = isset($wp_query->query['subpage']) && $wp_query->query['subpage'] ? $wp_query->query['subpage'] : 'default';

        $atts = array(
            'subpage' => $subpage
        );
        return $this->load_template('lostpassword', $atts) ;
    }

    function shortcode_profile(){
        if( isset( $GLOBALS['profile'] ) ){
            global $wp_query;
            $tabs = apply_filters( 'wpcom_profile_tabs', array() );
            ksort($tabs);
            $default = current($tabs);
            $subpage = isset($wp_query->query['subpage']) && $wp_query->query['subpage'] ? $wp_query->query['subpage'] : $default['slug'];

            $atts = array(
                'profile' => $GLOBALS['profile'],
                'subpage' => $subpage,
                'tabs' => $tabs
            );

            $tabs_slug = array();
            foreach ( $tabs as $t){
                $tabs_slug[] = $t['slug'];
            }

            if( ! in_array( $subpage, $tabs_slug) ) {
                status_header(404);
            }

            return $this->load_template('profile', $atts) ;
        }
    }

    function shortcode_userlist( $atts ) {
        global $wpdb, $options;
        $paged = get_query_var('paged') ? get_query_var('paged') : (get_query_var('page') ? get_query_var('page') : 1);
        $users = null; $user_ids = array();
        $number = isset($atts['per_page']) && $atts['per_page'] ? $atts['per_page'] : 10;
        $offset = ($paged-1) * $number;
        $orderby = isset($atts['orderby']) && $atts['orderby'] ? $atts['orderby'] : 'registered';
        $order = isset($atts['order']) && $atts['order'] ? $atts['order'] : 'DESC';
        $cols = isset($atts['cols']) && $atts['cols'] ? $atts['cols'] : '2';
        if( $cols!='2' && $cols!='3' && $cols!='4' ) $cols = 2;

        $args = array('number' => $number, 'offset' => $offset, 'paged' => $paged, 'orderby' => $orderby, 'order' => $order);
        $member_reg_active = isset($options['member_reg_active']) && $options['member_reg_active'] ? $options['member_reg_active']: '0';
        if( $member_reg_active!='0' ){
            // 开启审核则只显示审核通过的用户
            $args['user_status'] = 0;
        }

        if( isset($atts['group']) && $atts['group'] ) {
            $user_ids = get_objects_in_term( explode(',', $atts['group']), 'user-groups' );
        }else if( isset($atts['users']) && $atts['users'] ){
            $user_ids = explode(',', $atts['users']);
        }

        if( $user_ids ) $args['include'] = $user_ids;

        $users_query = new WP_User_Query( $args );
        $users = $users_query->get_results();

        ob_start();
        if( !$users || is_wp_error($users) ){
            global $wp_query;
            echo '<p style="text-align: center;">' . __( 'No user found.', 'wpcom' ) . '</p>';
            $wp_query->set_404();
            status_header(404);
        }else{
            $atts['users'] = $users;
            $atts['cols'] = $cols;
            echo $this->load_template( 'user-list', $atts ) ;
            $pagi_args = array( 'paged'=> $paged, 'numpages' => ceil($users_query->total_users / $number) );
            wpcom_pagination( 5, $pagi_args );
        }
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    function account_tabs_general(){
        $metas = apply_filters('wpcom_account_tabs_general_metas', array() );
        ksort($metas);
        ?>
        <form class="member-account-form" action="" method="post">
            <?php wp_nonce_field( 'member_form_general', 'member_form_general_nonce' ); ?>
            <?php foreach ($metas as $meta){ echo $this->account_field_item($meta); } ?>

            <div class="member-account-item">
                <label class="member-account-label"></label>
                <button class="btn btn-primary btn-sm" type="submit"><?php _e( 'Save Changes', 'wpcom' ); ?></button>
            </div>
        </form>
    <?php }

    function account_tabs_bind(){
        $user = wp_get_current_user();
        $action = isset($_GET['action']) && $_GET['action'] ? $_GET['action'] : '';
        if ($action=='') {
            $metas = apply_filters('wpcom_account_tabs_bind_metas', array());
            ksort($metas);
            ?>
            <div class="member-account-form">
                <?php wp_nonce_field('member_form_bind', 'member_form_bind_nonce'); ?>
                <?php foreach ($metas as $meta) {
                    echo $this->account_field_item($meta);
                } ?>
            </div>
        <?php } else if($action=='bind'){
            $type = isset($_GET['type']) ? $_GET['type'] : '';
            $metas = $type == 'phone' ? apply_filters('wpcom_sms_code_items', array()) : apply_filters('wpcom_email_code_items', array());?>
            <div class="wpcom-errmsg alert alert-danger j-errmsg"></div>
            <form id="accountbind-form" class="j-member-form member-account-form" action="" method="post">
                <?php wp_nonce_field( 'member_form_accountbind', 'member_form_accountbind_nonce' ); ?>
                <?php foreach ($metas as $meta){ echo $this->account_field_item($meta); } ?>
                <div class="member-account-item">
                    <label class="member-account-label"></label>
                    <input type="hidden" name="type" value="<?php echo $type;?>">
                    <button class="btn btn-primary btn-sm" type="submit"><?php _e( 'Save Changes', 'wpcom' ); ?></button>
                </div>
            </form>
        <?php } else if($action=='change'){
            $type = isset($_GET['type']) ? $_GET['type'] : '';
            $by = isset($_GET['by']) && $_GET['by'] ? $_GET['by'] : '';
            $token = isset($_GET['token']) && $_GET['token'] ? $_GET['token'] : '';
            $steps = array(
                0 => _x('STEP 1', '验证方式', 'wpcom'),
                1 => _x('STEP 2', '安全验证', 'wpcom'),
                2 => _x('STEP 3', '绑定帐号', 'wpcom')
            );
            $current_step = 0;
            if($by) $current_step = 1;
            if($token) $current_step = 2;?>
            <div class="account-bind-process-wrap">
            <ul class="member-lp-process account-bind-process">
                <?php $i = 1; $active = 0; foreach ($steps as $key => $step ) {
                    if( $key==$current_step ) {
                        $classes = 'active';
                        $active = 1;
                    }else if( $key!=$current_step && $active == 1 ){
                        $classes = '';
                    }else{
                        $classes = 'processed active';
                    }
                    if($key==2){ ?>
                        <li class="last <?php echo $classes; ?>">
                            <i><?php echo $i; ?></i>
                            <p><?php echo $step;?></p>
                        </li>
                    <?php } else{ ?>
                        <li class="<?php echo $classes; ?>">
                            <div class="process-index">
                                <i><?php echo $i; ?></i>
                                <p><?php echo $step;?></p>
                            </div>
                            <div class="process-line"></div>
                        </li>
                    <?php } ?>
                    <?php $i++; } ?>
            </ul>
            </div>
            <?php if($by){
                $metas = $by == 'phone' ? apply_filters('wpcom_sms_code_items', array()) : apply_filters('wpcom_email_code_items', array());
                $metas[10]['value'] = $by == 'phone' ? $user->mobile_phone : $user->user_email;
                $metas[10]['disabled'] = true;?>
                <div class="wpcom-errmsg alert alert-danger j-errmsg"></div>
                <form id="accountbind-form" class="j-member-form j-no-phone-form member-account-form" action="" method="post">
                    <?php wp_nonce_field( 'member_form_account_change_bind', 'member_form_account_change_bind_nonce' ); ?>
                    <?php foreach ($metas as $meta){ echo $this->account_field_item($meta); } ?>
                    <div class="member-account-item">
                        <label class="member-account-label"></label>
                        <input type="hidden" name="type" value="<?php echo $by;?>">
                        <input type="hidden" name="change" value="<?php echo $type;?>">
                        <button class="btn btn-primary btn-sm" type="submit"><?php _e( 'Next', 'wpcom' ); ?></button>
                    </div>
                </form>
            <?php } else if($token){
                $uid = check_password_reset_key( $token, $user->user_login );
                if(is_wp_error($uid)){ ?>
                    <div class="wpcom-errmsg alert alert-danger" style="display:block;" role="alert">
                        <i class="wpcom-icon wi"><svg aria-hidden="true"><use xlink:href="#wi-warning"></use></svg></i><?php _e( 'Verification failed', 'wpcom' ); ?>
                        <div class="close" data-dismiss="alert"><?php WPCOM::icon('close');?></div>
                    </div>
                <?php }else{
                    $type = isset($_GET['type']) ? $_GET['type'] : '';
                    $metas = $type == 'phone' ? apply_filters('wpcom_sms_code_items', array()) : apply_filters('wpcom_email_code_items', array());?>
                    <div class="wpcom-errmsg alert alert-danger j-errmsg"></div>
                    <form id="accountbind-form" class="j-member-form member-account-form" action="" method="post">
                        <?php wp_nonce_field( 'member_form_accountbind', 'member_form_accountbind_nonce' ); ?>
                        <?php foreach ($metas as $meta){ echo $this->account_field_item($meta); } ?>
                        <div class="member-account-item">
                            <label class="member-account-label"></label>
                            <input type="hidden" name="type" value="<?php echo $type;?>">
                            <button class="btn btn-primary btn-sm" type="submit"><?php _e( 'Save Changes', 'wpcom' ); ?></button>
                        </div>
                    </form>
                <?php }
            }else{
                if(isset($GLOBALS['validation']['error']) && $GLOBALS['validation']['error']){?>
                    <div class="wpcom-errmsg alert alert-danger" style="display:block;" role="alert">
                        <i class="wpcom-icon wi"><svg aria-hidden="true"><use xlink:href="#wi-warning"></use></svg></i><?php echo $GLOBALS['validation']['error']; ?>
                        <div class="close" data-dismiss="alert"><?php WPCOM::icon('close');?></div>
                    </div>
                <?php } ?>
                <form class="member-account-form" action="" method="post">
                    <div class="member-account-item">
                        <label class="member-account-label"><?php _e( 'Verify by', 'wpcom' ); ?></label>
                        <div class="member-account-input">
                            <select name="by">
                                <?php if(is_wpcom_enable_phone()){ ?><option value="phone"<?php echo (isset($_POST['by'])&&$_POST['by']=='phone'?' selected':'');?>><?php _ex( 'Phone number', 'Verify', 'wpcom' ); ?></option><?php } ?>
                                <option value="email"<?php echo (isset($_POST['by'])&&$_POST['by']=='email'?' selected':'');?>><?php _ex( 'Email address', 'Verify', 'wpcom' ); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="member-account-item">
                        <label class="member-account-label"></label>
                        <button class="btn btn-primary" type="submit"><?php _e( 'Next', 'wpcom' ); ?></button>
                    </div>
                </form>
            <?php }
        }
    }

    function account_tabs_password(){
        $metas = apply_filters('wpcom_account_tabs_password_metas', array() );
        ksort($metas);
        ?>
        <form class="member-account-form" action="" method="post">
            <?php wp_nonce_field( 'member_form_password', 'member_form_password_nonce' ); ?>
            <?php foreach ($metas as $meta){ echo $this->account_field_item($meta); } ?>

            <div class="member-account-item">
                <label class="member-account-label"></label>
                <button class="btn btn-primary btn-sm" type="submit"><?php _e( 'Save Changes', 'wpcom' ); ?></button>
            </div>
        </form>
    <?php }

    function account_field_item( $args ){
        global $options;
        $validation = isset($GLOBALS['validation']) ? $GLOBALS['validation'] : null;

        if( isset($validation['error']) && isset($validation['error']['existing_user_email'])){
            $validation['error']['user_email'] = $validation['error']['existing_user_email'];
        }

        $html = '';
        if( $args && isset($args['type']) ){
            $label = isset($args['label']) ? $args['label'] : '';
            $name = isset($args['name']) ? $args['name'] : '';
            $value = isset($args['value']) ? $args['value'] : '';
            $disabled = isset($args['disabled']) ? $args['disabled'] : false;
            $maxlength = isset($args['maxlength']) ? $args['maxlength'] : '';
            $desc = isset($args['desc']) ? $args['desc'] : '';
            $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';
            $validate = isset($args['validate']) ? $args['validate'] : '';

            $error = $validation && isset($validation['error'][$name]) ? $validation['error'][$name] : '';
            $value = $validation && isset($validation['value'][$name]) ? $validation['value'][$name] : $value;

            switch ($args['type']) {
                case 'TCaptcha':
                    if( isset($options['tc_appid']) && $options['tc_appid']!='' && $options['tc_appkey']!='' ) {
                        $html = '<div class="member-account-item TCaptcha"><label class="member-account-label"></label><div class="member-account-input captcha-button j-TCaptcha"><div class="captcha-icon"><i></i></div><span>'.__("I'm not a robot", "wpcom").'</span><input type="hidden" class="j-Tticket" name="ticket"><input type="hidden" class="j-Trandstr" name="randstr"></div></div>';
                    }
                    break;
                case 'noCaptcha':
                    if( isset($options['nc_appkey']) && $options['nc_appkey']!='' && $options['nc_access_id']!=''  && $options['nc_access_secret']!='' ) {
                        $html = '<div class="member-account-item no-captcha"><label class="member-account-label"></label><div class="nc-container j-NC" id="j-NC-'.rand(1000,9999).'"><input type="hidden" class="j-Ncsessionid" name="csessionid"><input type="hidden" class="j-Nsig" name="sig"><input type="hidden" class="j-Ntoken" name="token"><input type="hidden" class="j-Nscene" name="scene"></div></div>';
                    }
                    break;
                case 'hCaptcha':
                    if( isset($options['hc_sitekey']) && $options['hc_sitekey']!='' && $options['hc_secret']!='' ) {
                        $html = '<div class="member-account-item hCaptcha"><label class="member-account-label"></label><div class="member-account-input captcha-button"><div class="h-captcha"></div><div class="captcha-icon"><i></i></div><span>'.__("I'm not a robot", "wpcom").'</span></div></div>';
                    }
                    break;
                case 'reCAPTCHA':
                    if( isset($options['gc_sitekey']) && $options['gc_sitekey']!='' && $options['gc_secret']!='' ) {
                        $html = '<div class="member-account-item reCAPTCHA"><label class="member-account-label"></label><div class="member-account-input captcha-button"><div class="g-recaptcha"></div><div class="captcha-icon"><i></i></div><span>'.__("I'm not a robot", "wpcom").'</span></div></div>';
                    }
                    break;
                case 'smsCode':
                    $html = '<div class="member-account-item'.($error?' error':'').' sms-code"><label class="member-account-label"></label><div class="member-account-input"><input type="text" class="is-input sms-code-input require" id="'.$name.'" name="'.$name.'" placeholder="'.$placeholder.'"'.($validate?' data-rule="'.$validate.'"':'').' data-label="'.$label.'" autocomplete="off"><div class="btn btn-lg send-sms-code j-send-sms-code" data-target="'.(isset($args['target']) ? $args['target'] : '').'">'.__('Get Code', 'wpcom').'</div>'.wp_nonce_field( 'send_sms_code', 'send_sms_code_nonce', true, false ).'</div></div>';
                    break;
                case 'textarea':
                    $rows = isset($args['rows']) ? $args['rows'] : '';
                    $html = '<div class="member-account-item'.($error?' error':'').'"><label class="member-account-label">'.$label.'</label>';
                    $html .= '<div class="member-account-input"><textarea class="is-input" name="'.$name.'"'.($disabled?' disabled':'') . ($maxlength?' maxlength="'.$maxlength.'"':'') . ($rows?' rows="'.$rows.'"':'') . ' placeholder="'.$placeholder.'">'.esc_attr($value).'</textarea>';
                    if($error) $html .= '<div class="member-account-desc error">'.$error.'</div>';
                    if($desc) $html .= '<div class="member-account-desc">'.$desc.'</div>';
                    $html .= '</div></div>';
                    break;
                case 'password':
                    $html = '<div class="member-account-item'.($error?' error':'').'"><label class="member-account-label">'.$label.'</label>';
                    $html .= '<div class="member-account-input"><input class="is-input" type="password" name="'.$name.'" value="'.esc_attr($value).'"'.($disabled?' disabled':'').' placeholder="'.$placeholder.'">';
                    if($error) $html .= '<div class="member-account-desc error">'.$error.'</div>';
                    if($desc) $html .= '<div class="member-account-desc">'.$desc.'</div>';
                    $html .= '</div></div>';
                    break;
                case 'text':
                case 'default':
                    if($disabled){
                        $html = '<div class="member-account-item member-text-line item-'.$name.($error?' error':'').'"><label class="member-account-label">'.$label.'</label><div class="member-account-input">';
                        $html .= '<div class="member-account-text">'.$value.'</div>';
                    }else {
                        $html = '<div class="member-account-item item-'.$name.($error?' error':'').'"><label class="member-account-label">'.$label.'</label><div class="member-account-input">';
                        $html .= '<input class="is-input" type="text" name="'.$name.'" value="'.esc_attr($value).'"'.($disabled?' disabled':'') . ($maxlength?' maxlength="'.$maxlength.'"':'') . ' placeholder="'.$placeholder.'"'.($validate?' data-rule="'.$validate.'"':'').' data-label="'.$label.'">';
                    }
                    if($error) $html .= '<div class="member-account-desc error">'.$error.'</div>';
                    if($desc) $html .= '<div class="member-account-desc">'.$desc.'</div>';
                    $html .= '</div></div>';
                    break;
            }
        }
        return $html;
    }

    function profile_tabs_posts(){
        global $post, $profile, $is_author;
        $is_author = 0;
        $current_user = wp_get_current_user();
        if( $current_user->ID && $profile->ID == $current_user->ID ) {
            $is_author = 1;
        }

        wp_reset_query();
        $per_page = get_option('posts_per_page');
        $args = array(
            'posts_per_page' => $per_page,
            'author' => $profile->ID,
            'post_status' => $is_author ? array( 'draft', 'pending', 'publish' ) : array( 'publish' )
        );
        $posts = new WP_Query($args);
        $class = apply_filters( 'wpcom_profile_tabs_posts_class', 'profile-posts-list clearfix' );
        ?>
        <?php if( $posts->have_posts() ) : ?>
            <ul class="<?php echo esc_attr($class); ?>" data-user="<?php echo $profile->ID;?>">
                <?php while( $posts->have_posts() ) : $posts->the_post();?>
                    <?php echo $this->load_template('post', array( 'post' => $post ));?>
                <?php endwhile; wp_reset_postdata(); ?>
            </ul>
            <?php if($posts->max_num_pages>1){ ?><div class="load-more-wrap"><div class="btn load-more j-user-posts"><?php _e( 'Load more posts', 'wpcom' );?></div></div><?php } ?>
        <?php else : ?>
            <div class="profile-no-content">
                <?php echo wpcom_empty_icon('post'); if( get_current_user_id()==$profile->ID ){ _e( 'You have not created any posts.', 'wpcom' ); }else{ _e( 'This user has not created any posts.', 'wpcom' ); } ?>
            </div>
        <?php endif; ?>
    <?php }

    function user_posts(){
        global $post, $is_author;
        if( isset($_POST['user']) && is_numeric($_POST['user']) && $user = get_user_by('ID', $_POST['user'] ) ){
            $is_author = 0;
            $current_user = wp_get_current_user();
            if( $current_user->ID && $user->ID == $current_user->ID ) {
                $is_author = 1;
            }

            $per_page = get_option('posts_per_page');
            $page = $_POST['page'];
            $page = $page ? $page : 1;
            $arg = array(
                'posts_per_page' => $per_page,
                'paged' => $page,
                'author' => $user->ID,
                'post_status' => $is_author ? array( 'draft', 'pending', 'publish' ) : array( 'publish' )
            );
            $posts = new WP_Query($arg);

            if( $posts->have_posts() ) {
                while ($posts->have_posts()) : $posts->the_post();
                    echo $this->load_template('post', array('post' => $post));
                endwhile;
                wp_reset_postdata();
            }else{
                echo 0;
            }
        }
        exit;
    }

    function profile_tabs_comments(){
        global $profile;
        $is_user = get_current_user_id() == $profile->ID;
        $number = 10;

        $args = array(
            'number' => $number,
            'user_id' => $profile->ID,
            'status' => $is_user ? 'all':'approve',
            'offset' => 0
        );

        $comments_query = new WP_Comment_Query;
        $comments = $comments_query->query($args);
        $count = $comments_query->query($args+array('count'=>1));
        ?>
        <?php if( $comments ) : ?>
            <ul class="profile-comments-list clearfix" data-user="<?php echo $profile->ID;?>">
                <?php foreach($comments as $comment) : ?>
                    <?php echo $this->load_template('comment', array( 'comment' => $comment ));?>
                <?php endforeach; ?>
            </ul>
            <?php if($count/$number>1){ ?><div class="load-more-wrap"><div class="btn load-more j-user-comments"><?php _e( 'Load more comments', 'wpcom' );?></div></div><?php } ?>
        <?php else : ?>
            <div class="profile-no-content">
                <?php echo wpcom_empty_icon('comment'); if( get_current_user_id()==$profile->ID ){ _e( 'You have not made any comments.', 'wpcom' ); }else{ _e( 'This user has not made any comments.', 'wpcom' ); } ?>
            </div>
        <?php endif; ?>
    <?php }

    function user_comments(){
        if( isset($_POST['user']) && is_numeric($_POST['user']) && $user = get_user_by('ID', $_POST['user'] ) ){
            $is_user = get_current_user_id() == $user->ID;
            $number = 10;
            $page = $_POST['page'];
            $page = $page ?: 1;
            $args = array(
                'number' => $number,
                'user_id' => $user->ID,
                'status' => $is_user ? 'all':'approve',
                'offset' => ($page-1) * $number
            );

            $comments_query = new WP_Comment_Query;
            $comments = $comments_query->query($args);

            if( $comments ) {
                foreach($comments as $comment) :
                    echo $this->load_template('comment', array( 'comment' => $comment ));
                endforeach;
            }else{
                echo 0;
            }
        }
        exit;
    }

    function after_logout(){
        wp_redirect( home_url() );
    }

    function register_form(){
        global $options;
        $items = apply_filters( 'wpcom_register_form_items', array() );
        ksort($items);
        $terms = isset($options['member_page_terms']) && $options['member_page_terms'] ? $options['member_page_terms'] : '';
        if($terms){
            $terms = get_post($terms);
            if(isset($terms->ID)){
                $terms = sprintf('<a href="%s" target="_blank">%s</a>', get_permalink($terms->ID), get_the_title($terms->ID));
            }
        }
        $privacy = get_privacy_policy_url();
        if($privacy){
            $policy_page_id     = (int) get_option( 'wp_page_for_privacy_policy' );
            $page_title         = ( $policy_page_id ) ? get_the_title( $policy_page_id ) : '';
            $privacy = sprintf('<a href="%s" target="_blank">%s</a>', $privacy, $page_title);
        } ?>
        <div class="wpcom-errmsg alert alert-danger j-errmsg"></div>
        <form id="register-form" class="member-form j-member-form" method="post">
            <?php foreach ( $items as $item ){ echo $this->login_field_item( $item ); } ?>
            <?php wp_nonce_field( 'member_form_register', 'member_form_register_nonce' ); ?>
            <?php if($terms || $privacy){ ?>
            <div class="member-remember form-group checkbox">
                <label>
                    <input type="checkbox" id="privacy" name="privacy" class="is-input" value="true" data-rule="terms">
                    <?php
                    if($privacy && $terms){
                        echo sprintf(__('I have read and agree to the %s and %s', 'wpcom'), $terms, $privacy);
                    }else{
                        echo sprintf(__('I have read and agree to the %s', 'wpcom'), $terms?:$privacy);
                    } ?>
                </label>
            </div>
            <?php } ?>
            <button class="btn btn-primary btn-block btn-lg" type="submit"><?php _e('Create an account', 'wpcom');?></button>
        </form>
    <?php }

    function login_form(){
        global $options;
        $sms_login = is_wpcom_enable_phone() && isset($options['sms_login']) && $options['sms_login'] ? $options['sms_login'] : '0';
        $items = apply_filters( 'wpcom_login_form_items', array() );
        if($sms_login=='1'){
            $items2 = apply_filters( 'wpcom_sms_code_items', array() );
        }else if($sms_login=='2'){
            $items2 = $items;
            $items = apply_filters( 'wpcom_sms_code_items', array() );
        }
        ksort($items);
        if($sms_login) ksort($items2);?>
        <div class="wpcom-errmsg alert alert-danger j-errmsg"></div>
        <form id="login-form" class="member-form j-member-form" method="post">
            <div class="member-form-items">
                <?php foreach ( $items as $item ){ echo $this->login_field_item( $item ); } ?>
            </div>
            <?php wp_nonce_field( 'member_form_login', 'member_form_login_nonce' ); ?>
            <div class="member-remember checkbox">
                <label><input type="checkbox" id="remember" name="remember" value="true"><?php _e('Remember me', 'wpcom');?></label>
                <a class="member-form-forgot" href="<?php echo wp_lostpassword_url(); ?>"><?php _e('Forgot password?', 'wpcom');?></a>
            </div>
            <button class="btn btn-primary btn-block btn-lg" type="submit"><?php _e('Sign In', 'wpcom');?></button>
            <?php if($sms_login){ ?>
                <script type="text/template" id="j-tpl-login"><?php foreach ( $items as $item ){ echo $this->login_field_item( $item ); } ?></script>
                <script type="text/template" id="j-tpl-login2"><?php foreach ( $items2 as $item ){ echo $this->login_field_item( $item ); } ?></script>
            <?php } ?>
        </form>
    <?php }

    function login_modal(){
        $type = $_POST['type'];
        $type = $type === 'register' ? 'register' : 'login';
        echo do_shortcode('[wpcom-member type="form" action="'.$type.'"]');
        exit;
    }

    function lostpassword_form_default(){
        $items = apply_filters( 'wpcom_lostpassword_form_items', array() );
        ksort($items);?>
        <form id="lostpassword-form" class="member-form lostpassword-form j-member-form" method="post">
            <div class="wpcom-errmsg alert alert-danger j-errmsg"></div>
            <?php foreach ( $items as $item ){ echo $this->login_field_item( $item ); } ?>
            <?php wp_nonce_field( 'member_form_lostpassword', 'member_form_lostpassword_nonce' ); ?>
            <button class="btn btn-primary btn-block btn-lg" type="submit"><?php _e( 'Submit', 'wpcom' );?></button>
        </form>
    <?php }

    function lostpassword_form_send_success(){
        $is_phone = isset($_GET['phone']) && $_GET['phone'] ? 1 : 0;
        if($is_phone){
            $phone = WPCOM_Session::get('lost_password_phone');
            $items = apply_filters( 'wpcom_sms_code_items', array() );
            $items[10]['value'] = $phone;
            $items[10]['disabled'] = true;
            if($phone){ ?>
                <form id="smscode-form" class="member-form lostpassword-form j-member-form j-no-phone-form" method="post">
                    <div class="wpcom-errmsg alert alert-danger j-errmsg"></div>
                    <?php foreach ( $items as $item ){ echo $this->login_field_item( $item ); } ?>
                    <?php wp_nonce_field( 'member_form_smscode', 'member_form_smscode_nonce' ); ?>
                    <button class="btn btn-primary btn-block btn-lg" type="submit"><?php _e( 'Submit', 'wpcom' );?></button>
                </form>
            <?php } else { ?>
                <div class="member-form lostpassword-form lostpassword-form-status">
                    <div class="status-icon status-icon-warning"><?php WPCOM::icon('warning');?></div>
                    <h3 class="lostpassword-failed"><?php _e( 'Your phone number error!', 'wpcom'); ?></h3>
                    <p><?php _e( 'Unable to get the phone number, please return to the previous step.', 'wpcom'); ?></p>
                </div>
            <?php }
        } else { ?>
            <div class="member-form lostpassword-form lostpassword-form-status">
                <div class="status-icon status-icon-success"><?php WPCOM::icon('success');?></div>
                <h3 class="lostpassword-success"><?php _e( 'Password reset email send successfully!', 'wpcom'); ?></h3>
                <p><?php _e( 'Check your email for a link to reset your password. If it doesn’t appear within a few minutes, check your spam folder.', 'wpcom'); ?></p>
            </div>
        <?php }
    }

    function lostpassword_form_reset(){
        if( isset($GLOBALS['reset-error']) && $GLOBALS['reset-error']){ ?>
            <div class="member-form lostpassword-form lostpassword-form-status">
                <div class="status-icon status-icon-warning"><?php WPCOM::icon('warning');?></div>
                <h3 class="lostpassword-failed"><?php _e('Password reset link invalid', 'wpcom');?></h3>
                <p><?php echo $GLOBALS['reset-error']; ?></p>
                <a class="btn btn-primary" href="<?php echo wp_lostpassword_url(); ?>"><?php _e('Click here to resend password reset email', 'wpcom');?></a>
            </div>
        <?php }else{
            $items = apply_filters( 'wpcom_resetpassword_form_items', array() );
            ksort($items);?>
            <form id="resetpassword-form" class="member-form resetpassword-form lostpassword-form j-member-form" method="post">
                <div class="wpcom-errmsg alert alert-danger j-errmsg"></div>
                <?php foreach ( $items as $item ){ echo $this->login_field_item( $item ); } ?>
                <?php wp_nonce_field( 'member_form_resetpassword', 'member_form_resetpassword_nonce' ); ?>
                <button class="btn btn-primary btn-block btn-lg" type="submit"><?php _e( 'Submit', 'wpcom' );?></button>
            </form>
        <?php }
    }

    function lostpassword_form_finished(){ ?>
        <div class="member-form lostpassword-form lostpassword-form-status">
            <div class="status-icon status-icon-success"><?php WPCOM::icon('success');?></div>
            <h3 class="lostpassword-success"><?php _e('Password reset successfully', 'wpcom');?></h3>
            <p><?php _e('Your password has been reset successfully! ', 'wpcom');?></p>
            <a class="btn btn-primary" href="<?php echo wp_login_url();?>"><?php _e(' Click here to return to the login page', 'wpcom');?></a>
        </div>
    <?php }

    function login_field_item( $args ){
        global $options;
        $html = '';
        if( $args && isset($args['type']) ){
            $label = isset($args['label']) ? $args['label'] : '';
            $name = isset($args['name']) ? $args['name'] : '';
            $icon = isset($args['icon']) ? $args['icon'] : '';
            $icon = $icon ? WPCOM::icon($icon, false) : '';
            $require = isset($args['require']) ? $args['require'] : false;
            $maxlength = isset($args['maxlength']) ? $args['maxlength'] : '';
            $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';
            $validate = isset($args['validate']) ? $args['validate'] : '';
            $value = isset($args['value']) ? $args['value'] : '';
            $disabled = isset($args['disabled']) ? $args['disabled'] : false;

            switch ($args['type']) {
                case 'TCaptcha':
                    if( isset($options['tc_appid']) && $options['tc_appid']!='' && $options['tc_appkey']!='' ) {
                        $html = '<div class="form-group TCaptcha"><div class="captcha-button j-TCaptcha"><div class="captcha-icon"><i></i></div><span>'.__("I'm not a robot", "wpcom").'</span></div><input type="hidden" class="j-Tticket" name="ticket"><input type="hidden" class="j-Trandstr" name="randstr"></div>';
                    }
                    break;
                case 'noCaptcha':
                    if( isset($options['nc_appkey']) && $options['nc_appkey']!='' && $options['nc_access_id']!=''  && $options['nc_access_secret']!='' ) {
                        $html = '<div class="form-group"><div class="nc-container j-NC" id="j-NC-'.rand(1000,9999).'"></div></div><input type="hidden" class="j-Ncsessionid" name="csessionid"><input type="hidden" class="j-Nsig" name="sig"><input type="hidden" class="j-Ntoken" name="token"><input type="hidden" class="j-Nscene" name="scene">';
                    }
                    break;
                case 'hCaptcha':
                    if( isset($options['hc_sitekey']) && $options['hc_sitekey']!='' && $options['hc_secret']!='' ) {
                        $html = '<div class="form-group hCaptcha"><div class="captcha-button"><div class="h-captcha"></div><div class="captcha-icon"><i></i></div><span>'.__("I'm not a robot", "wpcom").'</span></div></div>';
                    }
                    break;
                case 'reCAPTCHA':
                    if( isset($options['gc_sitekey']) && $options['gc_sitekey']!='' && $options['gc_secret']!='' ) {
                        $html = '<div class="form-group reCAPTCHA"><div class="captcha-button"><div class="g-recaptcha"></div><div class="captcha-icon"><i></i></div><span>'.__("I'm not a robot", "wpcom").'</span></div></div>';
                    }
                    break;
                case 'smsCode':
                    $html = '<div class="form-group sms-code">'.wp_nonce_field( 'send_sms_code', 'send_sms_code_nonce', true, false ).'<label>'.$icon.' <input type="text" class="form-input is-input require" id="'.$name.'" name="'.$name.'" placeholder="'.$placeholder.'"'.($validate?' data-rule="'.$validate.'"':'').' data-label="'.$label.'" autocomplete="off"></label><div class="btn btn-lg send-sms-code j-send-sms-code" data-target="'.(isset($args['target']) ? $args['target'] : '').'">'.__('Get Code', 'wpcom').'</div></div>';
                    break;
                case 'hidden':
                    $html = '<input type="hidden" name="' . $name . '" value="' . $value . '">';
                    break;
                case 'password':
                case 'text':
                case 'default':
                    if($disabled && $value){
                        $input = '<div class="form-input">'.$value.'</div>';
                    }else {
                        $input = '<input type="' . $args['type'] . '" class="form-input is-input' . ($require ? ' require' : '') . '" id="' . $name . '" name="' . $name . '" placeholder="' . $placeholder . '"' . ($maxlength ? ' maxlength="' . $maxlength . '"' : '') . ($validate ? ' data-rule="' . $validate . '"' : '') . ' data-label="' . $label . '">';
                        if($args['type']==='password') $input .= '<span class="show-password">'.WPCOM::icon('eye-off-fill', false).'</span>';
                    }
                    $html = '<div class="form-group"><label>'.$icon.' '.$input.'</label></div>';
                    break;
            }
            $html = apply_filters('wpcom_login_field_'.$args['type'], $html, $args);
        }
        return $html;
    }

    function shortcode_form( $atts ){
        if( isset($atts['action']) && $atts['action'] ){
            global $options;
            $member_reg_active = isset($options['member_reg_active']) && $options['member_reg_active'] ? $options['member_reg_active']: '0';

            if( $atts['action'] == 'register' && $member_reg_active=='1' && isset($_REQUEST['approve']) && $_REQUEST['approve'] ){
                if( $_REQUEST['approve'] =='false' ){
                    $atts['notice'] = isset($options['member_reg_notice']) && $options['member_reg_notice'] ? $options['member_reg_notice']: '';
                } else if($_REQUEST['approve'] =='pending' && isset($_REQUEST['login']) && isset($_REQUEST['key']) ) {
                    $login = wp_unslash( $_REQUEST['login'] );
                    $key = wp_unslash( $_REQUEST['key'] );
                    if( $login && $key ){
                        $user = check_password_reset_key( $key, $login );
                        if( !$user || is_wp_error($user) ) {
                            if ( $user && $user->get_error_code() === 'expired_key' )
                                $error = __( 'Your activation link has expired.', 'wpcom' );
                            else
                                $error = __( 'Your activation link is invalid.', 'wpcom' );

                            $resend_url = add_query_arg( array('approve' => 'resend', 'login' => $login), wp_registration_url() );
                            $atts['notice'] = $error . '<p><a href="'.$resend_url.'">'.__( 'Resend activation email', 'wpcom' ).'</a></p>';
                        }
                    }
                } else if($_REQUEST['approve'] =='true') {
                    $atts['notice'] = __( 'Your account has been activated successfully.', 'wpcom' );
                    $atts['notice'] .= '<p><a href="'.wp_login_url().'">'.__( 'Click here to login', 'wpcom' ).'</a></p>';
                } else if($_REQUEST['approve'] =='resend') {
                    return $this->load_template('approve-resend', $atts);
                }
                return $this->load_template('approve-notice', $atts);
            }else if( $atts['action'] == 'register' && $member_reg_active=='2' && isset($_REQUEST['approve']) && $_REQUEST['approve'] == 'false' ){
                $atts['notice'] = isset($options['member_reg_notice']) && $options['member_reg_notice'] ? $options['member_reg_notice']: '';
                return $this->load_template('approve-notice', $atts);
            } else{
                return $this->load_template($atts['action'], $atts);
            }
        }
    }

    function social_login(){
        global $options;
        $socials = apply_filters( 'wpcom_socials', array() );
        ksort($socials);
        $target = isset($options['social_login_target']) && !$options['social_login_target'] ? '' : ' target="_blank"';
        if( $socials ){ ?>
            <ul class="member-social-list">
                <?php foreach ( $socials as $social ){ if( $social['id'] && $social['key'] ) { ?>
                <li class="social-item social-<?php echo $social['name'];?>">
                    <a href="<?php echo esc_url(wpcom_social_login_url($social['name']));?>"<?php echo $target;?> data-toggle="tooltip" data-placement="top" title="<?php echo esc_attr(sprintf( __('Log in with %s', 'wpcom'), $social['title'] ));?>" aria-label="<?php echo esc_attr($social['title']);?>">
                        <?php WPCOM::icon($social['icon']);?>
                    </a>
                </li>
                <?php } } ?>
            </ul>
        <?php }
    }

    function approve_resend_form(){
        $items = apply_filters( 'wpcom_approve_resend_form_items', array() );
        ksort($items);?>
        <div class="wpcom-errmsg alert alert-danger j-errmsg"></div>
        <form id="approve_resend-form" class="member-form j-member-form" method="post">
            <?php foreach ( $items as $item ){ echo $this->login_field_item( $item ); } ?>
            <?php wp_nonce_field( 'member_form_approve_resend', 'member_form_approve_resend_nonce' ); ?>
            <button class="btn btn-primary btn-block btn-lg" type="submit"><?php _e( 'Resend activation email', 'wpcom' );?></button>
        </form>
    <?php }

    function load_template( $template, $atts ) {
        if (file_exists(STYLESHEETPATH . '/member/' . $template . '.php')) {
            $file = STYLESHEETPATH . '/member/' . $template . '.php';;
        }else if(file_exists( TEMPLATEPATH . '/member/' . $template . '.php' )){
            $file = TEMPLATEPATH . '/member/' . $template . '.php';
        }else{
            $file = $this->member_path . '/templates/' . $template . '.php';
        }

        if ( file_exists( $file ) ) {
            extract($atts);
            ob_start();
            include $file;
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }
    }

    function cropped_upload(){
        $res = array();
        $res['result'] = '';

        if ( ! check_ajax_referer('wpcom_cropper', 'nonce', false) )
            $res['result'] = -1;

        if( $res['result']=='' ) {
            $user = wp_get_current_user();
            if ($user->ID) {
                $img = isset($_POST['image']) ? $_POST['image'] : '';
                $type = isset($_POST['type']) ? $_POST['type'] : 0;
                $type = $type ?: 0; // 0: 头像； 1: 封面
                $uid = isset($_POST['user']) ? $_POST['user'] : 0;
                $corp_user = $user->ID;
                if ($uid && $uid != $user->ID && current_user_can('edit_users')) {
                    $corp_user = $uid;
                }

                $GLOBALS['image_type'] = $type;

                $filename = substr(md5($corp_user), 5, 16) . '.' . time() . '.jpg';
                $mirror = wp_upload_bits($filename, '', base64_decode(str_replace('data:image/jpeg;base64,', '', $img)), '1234/06');
                if (!$mirror['error']) {
                    $res['result'] = 1;
                    $res['url'] = $mirror['url'];


                    $key = $type ? 'wpcom_cover' : 'wpcom_avatar';
                    $pre_img = get_user_meta($corp_user, $key, 1);
                    $uploads = wp_upload_dir();
                    if ($pre_img) {
                        $pre_img = str_replace($uploads['baseurl'], '', $pre_img);
                        @unlink($uploads['basedir'] . $pre_img);
                    }
                    update_user_meta($corp_user, $key, str_replace($uploads['baseurl'], '', $res['url']));
                    // 基于wp_generate_attachment_metadata钩子，兼容云储存插件同步
                    $mirror['file'] = str_replace($uploads['basedir']. '/', '', $mirror['file']);
                    apply_filters ( 'wp_generate_attachment_metadata', $mirror, 0 );
                } else {
                    $res['result'] = -2;
                }
            } else {
                $res['result'] = -3;
            }
        }

        echo json_encode($res);
        exit;
    }

    function parse_query(){
        global $wp_query;
        if(isset($wp_query->query['subpage']) && $wp_query->query['subpage'] && isset($wp_query->query['page'])){
            $wp_query->set('pageid', $wp_query->query['page']);
            $wp_query->set('page', '');
        }
    }

    function action_before_echo(){
        global $wp_query, $options;
        $user = wp_get_current_user();
        if(isset($user->user_status) && $user->user_status == '1'){ // 黑名单，强制退出登录
            wp_logout();
            exit;
        }
        if ( is_wpcom_member_page( 'account' ) ) {
            $subpage = isset($wp_query->query['subpage']) && $wp_query->query['subpage'] ? $wp_query->query['subpage'] : 'general';
            // 登录判断
            if(!$user->ID){
                wp_redirect( wp_login_url( wpcom_subpage_url( $subpage ) ) );
                exit;
            }

            if( $_SERVER['REQUEST_METHOD'] == 'POST' ){ //表单提交
                do_action( 'wpcom_account_' . $subpage . '_post' );
            }else if( $subpage == 'logout' ){
                wp_logout();
                exit;
            }
        } else if( is_wpcom_member_page('profile') ){
            $user_slug = isset($wp_query->query['user']) && $wp_query->query['user'] ? $wp_query->query['user'] : '';

            if( $user_slug && isset($options['member_user_slug']) && $options['member_user_slug']=='2' ) {
                $profile = get_user_by( 'ID', $user_slug );
            } elseif( $user_slug ) {
                $profile = get_user_by( 'slug', $user_slug );
            }

            if( !$user_slug && $profile = wp_get_current_user() ){
                if( $profile->ID ){
                    wp_redirect( wpcom_author_url( $profile->ID, $profile->user_nicename ) );
                    exit;
                }
            }

            // 检查用户状态
            $approve = '';
            if(isset($profile->ID) && $profile->user_status == '0'){ // 查查旧数据
                $approve = get_user_meta( $profile->ID, 'wpcom_approve', true );
                if($approve=='0'){
                    update_user_meta( $profile->ID, 'wpcom_approve', '' );
                    wp_update_user( array( 'ID' => $profile->ID, 'user_status' => -1 ) );
                }else {
                    $approve = 1;
                }
            }else if(isset($profile->user_status)){
                $approve = $profile->user_status == '0' ? 1 : 0;
            }

            $member_reg_active = isset($options['member_reg_active']) && $options['member_reg_active'] ? $options['member_reg_active']: '0';

            // 未通过审核的用户，仅管理员可见
            if($approve && is_multisite() && !is_user_member_of_blog( $profile->ID )){ // 多站点检查是否站点注册用户
                $approve = 0;
            }
            $not_approve = $approve=='0' && ($member_reg_active!='0' || $profile->user_status=='1') && !current_user_can( 'edit_users' );
            if( !$user_slug || !isset($profile) || !$profile || $not_approve ) {
                $wp_query->set_404();
                status_header(404);
            } else {
                $GLOBALS['profile'] = $profile;
            }
        } else if( $user->ID && (is_wpcom_member_page( 'login' ) || is_wpcom_member_page( 'register' ) ) ){
            if( !(isset($_GET['from']) && $_GET['from'] == 'bind') ){ // 绑定
                $redirect = wpcom_subpage_url();
                $redirect = $redirect ?: home_url();
                wp_redirect( $redirect );
                exit;
            }
        } else if( is_wpcom_member_page('login') && ( !isset($options['login_redirect']) || $options['login_redirect']=='') ){
            $redirect_url = isset( $_SERVER['HTTP_REFERER'] ) && $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : '';
            if( !isset($_GET['redirect_to']) && $redirect_url ){
                $pu = parse_url($redirect_url);
                if(isset($pu['query']) && $pu['query']){
                    parse_str( $pu['query'],$data );
                    if( isset($data['redirect_to']) && $data['redirect_to'] ){
                        $redirect_url = $data['redirect_to'];
                    }
                }
                $site_domain = parse_url(get_bloginfo('url'), PHP_URL_HOST);
                $red_domain = parse_url($redirect_url, PHP_URL_HOST);
                if( $site_domain == $red_domain ) {
                    // 去除域名，改成路径，防止某些服务器安全配置导致无法识别的问题
                    $redirect_url = preg_replace('/^(http|https):\/\/[^\/]+\//i', '/', $redirect_url);
                    wp_redirect(wp_login_url($redirect_url));
                    exit;
                }
            }
        }else if(is_wpcom_member_page('lostpassword' )){
            $subpage = isset($wp_query->query['subpage']) && $wp_query->query['subpage'] ? $wp_query->query['subpage'] : 'default';
            if($subpage === 'reset'){
                $rp_cookie = 'wp-resetpass-' . COOKIEHASH;
                if ( isset( $_GET['key'] ) ) {
                    $value = sprintf( '%s:%s', wp_unslash( $_GET['login'] ), wp_unslash( $_GET['key'] ) );
                    setcookie( $rp_cookie, $value, 0, '/', COOKIE_DOMAIN, is_ssl(), true );
                    wp_safe_redirect( remove_query_arg( array( 'key', 'login' ) ) );
                    exit;
                }

                if ( isset( $_COOKIE[ $rp_cookie ] ) && 0 < strpos( $_COOKIE[ $rp_cookie ], ':' ) ) {
                    list( $rp_login, $rp_key ) = explode( ':', wp_unslash( $_COOKIE[ $rp_cookie ] ), 2 );
                    $user = check_password_reset_key( $rp_key, $rp_login );
                } else {
                    $user = false;
                }

                if( ! $user || is_wp_error( $user ) ){
                    setcookie( $rp_cookie, ' ', time() - YEAR_IN_SECONDS, '/', COOKIE_DOMAIN, is_ssl(), true );
                    if ( $user && $user->get_error_code() === 'expired_key' )
                        $GLOBALS['reset-error'] = __('Your password reset link has expired', 'wpcom');//'您的密码重置链接已过期，请重新请求新链接。';
                    else
                        $GLOBALS['reset-error'] = __('Your password reset link appears to be invalid', 'wpcom');//'您的密码重设链接无效，请重新请求新链接。';
                }
            }
        }else if(is_wpcom_member_page( 'register' ) && isset($_REQUEST['approve']) && $_REQUEST['approve']){
            // 激活成功后跳转
            $member_reg_active = isset($options['member_reg_active']) && $options['member_reg_active'] ? $options['member_reg_active']: '0';
            if( $member_reg_active=='1' && $_REQUEST['approve'] =='pending' && isset($_REQUEST['login']) && isset($_REQUEST['key'])){
                $login = wp_unslash( $_REQUEST['login'] );
                $key = wp_unslash( $_REQUEST['key'] );
                if( $login && $key ){
                    $user = check_password_reset_key( $key, $login );
                    if( !$user || is_wp_error($user) ) {
                    }else if( $user->ID ) {
                        // update_user_meta( $user->ID, 'wpcom_approve', 1 );
                        wp_update_user( array( 'ID' => $user->ID, 'user_status' => 0 ) );
                        $url = wp_registration_url();
                        $url = add_query_arg( 'approve', 'true', $url );
                        wp_redirect( $url );
                        exit;
                    }else{
                        die('Error');
                    }
                }else{
                    die('Error');
                }
            }
        }
    }

    function register_url( $url ){
        if( $register_url = wpcom_register_url() ){
            $url = $register_url;
        }
        return $url;
    }
    function login_url( $url, $redirect ){
        if( $login_url = wpcom_login_url($redirect) ){
            $url = $login_url;
        }
        return $url;
    }

    function login_head(){
        if(isset($_GET['redirect_to']) && $_GET['redirect_to'] && $login_url = wp_login_url($_GET['redirect_to'])){
            wp_redirect($login_url);
            exit;
        }
    }

    function logout_url( $url, $redirect ){
        if( $logout_url = wpcom_logout_url($redirect) ){
            $url = $logout_url;
        }
        return $url;
    }

    function lostpassword_url( $url, $redirect ){
        if( $lostpassword_url = wpcom_lostpassword_url($redirect) ){
            $url = $lostpassword_url;
        }
        return $url;
    }

    function author_link( $link, $author_id, $author_nicename ){
        if( $author_link = wpcom_author_url( $author_id, $author_nicename ) ){
            $link = $author_link;
        }
        return $link;
    }

    function block_access_wpadmin(){
        global $current_user, $pagenow;
        $can_access = array( 'admin-ajax.php', 'async-upload.php', 'media-upload.php');
        if( in_array($pagenow, $can_access) ) return false;
        if($current_user->ID) {
            $group = wpcom_get_user_group($current_user->ID);
            if($group){
                $wpadmin = get_term_meta($group->term_id, 'wpcom_wpadmin', true);
                if (!(current_user_can('manage_options') || $wpadmin == '1')) {
                    wp_redirect(home_url());
                    exit;
                }
            }else if( !current_user_can('manage_options') ){
                wp_redirect(home_url());
                exit;
            }
        }
    }

    function body_class( $classes ){
        if( is_wpcom_member_page('account') ){
            $classes[] = 'wpcom-member';
            $classes[] = 'member-account';
        }else if( is_wpcom_member_page('profile') ){
            $classes[] = 'wpcom-member';
            $classes[] = 'member-profile';
        }else if( is_wpcom_member_page('login') ){
            $classes[] = 'wpcom-member';
            $classes[] = 'member-login';
        }else if( is_wpcom_member_page('register') ){
            $classes[] = 'wpcom-member';
            $classes[] = 'member-register';
        }else if( is_wpcom_member_page('lostpassword') ){
            $classes[] = 'wpcom-member';
            $classes[] = 'member-lostpassword';
        }
        return $classes;
    }

    function show_admin_bar( $show ){
        global $current_user;
        if($current_user->ID) {
            $group = wpcom_get_user_group($current_user->ID);
            if($group) {
                $adminbar = get_term_meta($group->term_id, 'wpcom_adminbar', true);
                if ($adminbar != '1') {
                    $show = false;
                }
            }else if( !current_user_can('edit_published_posts') ){
                $show = false;
            }
        }
        return $show;
    }

    function user_has_cap( $allcaps, $caps, $args, $user ){
        global $pagenow, $current_user, $options, $cap_checked;
        if( !isset($cap_checked) ) $cap_checked = array();
        if( $user->ID && in_array($user->ID, $cap_checked) ) return $allcaps;

        if( $user->ID && ( $pagenow=='user-edit.php' || $pagenow=='users.php' || is_wpcom_member_page() ) ) {
            $cap_checked[] = $user->ID;
            // 自己是超级管理员的话，不能取消自己的超级管理员权限
            if( $current_user->ID && $current_user->ID==$user->ID && is_super_admin( $user->ID ) ) return $allcaps;

            $group = wpcom_get_user_group($user->ID);

            if($group) {
                $allcaps = $this->set_default_role($user->ID, $group->term_id);
            }else if( isset($options['member_group']) && $options['member_group'] ){
                // 无用户组则分配默认用户组
                wp_set_object_terms( $user->ID, array( (int)$options['member_group'] ), 'user-groups', false );
            }
        }
        return $allcaps;
    }

    function user_register( $user_id ){
        global $options;
        if( isset($options['member_group']) && $options['member_group'] ){
            // 分配默认用户组
            wp_set_object_terms( $user_id, array( (int)$options['member_group'] ), 'user-groups', false );

            // 分配默认系统角色
            $this->set_default_role($user_id);
        }
        $member_reg_active = isset($options['member_reg_active']) && $options['member_reg_active'] ? $options['member_reg_active']: '0';
        if( !is_wpcom_enable_phone() && $member_reg_active!='0' ){
            // 注册用户需要验证
            // update_user_meta( $user_id, 'wpcom_approve', 0 );
            wp_update_user( array( 'ID' => $user_id, 'user_status' => -1 ) );
            if( !WPCOM_Session::get('user') ) { // 非社交登录渠道
                if ($member_reg_active == '1') { // 如果是邮件激活方式，则发送激活邮件给用户
                    wpcom_send_active_email($user_id);
                } else if ($member_reg_active == '2') { // 如果是后台审核，则发送审核邮件给管理员
                    wpcom_send_active_to_admin($user_id);
                }
            }
        }
    }

    private function set_default_role($user_id, $term_id=''){
        global $options;
        if( $term_id || (isset($options['member_group']) && $options['member_group']) ) {
            $term_id = $term_id ? $term_id : $options['member_group'];
            $user = get_user_by('ID', $user_id);
            $sys_role = get_term_meta($term_id, 'wpcom_sys_role', true);
            $default_roles = array('subscriber', 'contributor', 'author', 'editor', 'administrator');
            $roles = $user->roles;
            if (!$roles) $roles = array();
            if (in_array($sys_role, $default_roles) && !in_array($sys_role, $roles)) { // 权限和当前用户组权限不一样
                foreach ($roles as $role) {
                    if (in_array($role, $default_roles)) {
                        $user->remove_role($role);
                    }
                }
                if (in_array($sys_role, $default_roles)) $user->add_role($sys_role);
            }
            return $user->allcaps;
        }
    }

    function social_new_user( $user_id ){
        global $options;
        $this->set_default_role($user_id);
        $member_reg_active = isset($options['member_reg_active']) && $options['member_reg_active'] ? $options['member_reg_active']: '0';
        if( $member_reg_active!='0' ){
            // 注册用户需要验证的情况，对社交登录注册的用户默认验证审核通过
            // update_user_meta( $user_id, 'wpcom_approve', 1 );
            wp_update_user( array( 'ID' => $user_id, 'user_status' => 0 ) );
        }
    }

    function authenticate( $user, $username, $password ){
        if( $user instanceof WP_User && $username ){
            $get_user = get_user_by( 'login', $username );
            if ( ! $get_user && strpos( $username, '@' ) ) {
                $get_user = get_user_by( 'email', $username );
            }
            if( $get_user->ID ){
                global $options;
                // 查查旧数据，向下兼容
                if($get_user->user_status == '0'){
                    $_approve = get_user_meta( $get_user->ID, 'wpcom_approve', true );
                    if($_approve == '0'){ // 未激活用户
                        update_user_meta($get_user->ID, 'wpcom_approve', '');
                        wp_update_user( array( 'ID' => $get_user->ID, 'user_status' => -1 ) );
                        $get_user->user_status = '-1';
                    }
                }

                $member_reg_active = isset($options['member_reg_active']) && $options['member_reg_active'] ? $options['member_reg_active']: '0';
                if( $get_user->user_status=='-1' && $member_reg_active!='0' ){
                    $err = '';
                    if($member_reg_active=='1'){
                        $resend_url = add_query_arg( array('approve' => 'resend', 'login' => $username), wp_registration_url() );
                        $err = sprintf( __( 'Please activate your account. <a href="%s" target="_blank">Resend activation email</a>', 'wpcom' ), $resend_url );
                    }else if($member_reg_active=='2'){
                        $err = __( 'Account awaiting approval.', 'wpcom' );
                    }
                    if($err) $user = new WP_Error( 'not_approve', $err );
                }else if($get_user->user_status=='1'){ // 黑名单用户
                    $err = __( 'Blacklist user.', 'wpcom' );
                    if($err) $user = new WP_Error( 'not_approve', $err );
                }
            }
        }else if( is_wpcom_enable_phone() && preg_match("/^1[3-9]{1}\d{9}$/", $username) ){ // 手机登录
            $args = array(
                'meta_key'     => 'mobile_phone',
                'meta_value'   => $username,
            );
            $users = get_users($args);
            if($users && $users[0]->ID && wp_check_password($password, $users[0]->user_pass, $users[0]->ID)) {
                $user = $users[0];
            }
        }

        return $user;
    }

    // 处理 user_status
    function users_pre_query($res, $that){
        if(isset($that->query_vars['user_status'])){
            $that->query_where .= " AND user_status = '".$that->query_vars['user_status']."'";
        }
        return $res;
    }

    function pre_insert_user_data($data, $update, $id, $userdata = array()){
        if(isset($userdata['user_status'])){
            $data['user_status'] = $userdata['user_status'];
        }
        return $data;
    }

    function views_users( $views ){
        if( !current_user_can( 'edit_users' ) ) return $views;

        $current = '';
        if ( isset($_REQUEST['status']) && $_REQUEST['status'] == 'unapproved' ) $current = 'class="current"';

        $users = get_users(array('user_status' => '-1') );
        $count = count($users);
        $views[ 'unapproved' ] = '<a href="'.admin_url('users.php').'?status=unapproved" ' . $current . '>'. __( 'Unapproved', 'wpcom' ) . ' <span class="count">（'.$count.'）</span></a>';

        $current2 = '';
        if ( isset($_REQUEST['status']) && $_REQUEST['status'] == 'blacklist' ) $current2 = 'class="current"';

        $users2 = get_users(array('user_status' => '1') );
        $count2 = count($users2);
        $views[ 'blacklist' ] = '<a href="'.admin_url('users.php').'?status=blacklist" ' . $current2 . '>'. __( 'Blacklist', 'wpcom' ) . ' <span class="count">（'.$count2.'）</span></a>';

        return $views;
    }

    function filter_users( $query ){
        global $pagenow;
        if (is_admin() && 'users.php' == $pagenow ) {
            if(!isset($_GET['orderby'])){
                $query->set('orderby', 'registered');
                $query->set('order', 'desc');
            }
            if(!isset($query->query_vars['user_status']) && isset($_REQUEST['status'])){
                if($_REQUEST['status']=='unapproved') {
                    $query->set('user_status', '-1');
                }else if($_REQUEST['status']=='blacklist') {
                    $query->set('user_status', '1');
                }
            }
        }
        return $query;
    }

    function display_page_type($post_states, $post){
        global $options;
        if($post->post_type === 'page'){
            if(isset($options['member_page_login']) && $options['member_page_login'] && $options['member_page_login'] == $post->ID){
                $type = '登录页';
            }else if(isset($options['member_page_register']) && $options['member_page_register'] && $options['member_page_register'] == $post->ID){
                $type = '注册页';
            }else if(isset($options['member_page_lostpassword']) && $options['member_page_lostpassword'] && $options['member_page_lostpassword'] == $post->ID){
                $type = '重置密码';
            }else if(isset($options['member_page_account']) && $options['member_page_account'] && $options['member_page_account'] == $post->ID){
                $type = '帐号设置页';
            }else if(isset($options['member_page_profile']) && $options['member_page_profile'] && $options['member_page_profile'] == $post->ID){
                $type = '个人中心页';
            }else if(isset($options['social_login_page']) && $options['social_login_page'] && $options['social_login_page'] == $post->ID){
                $type = '社交绑定页';
            }
            if(isset($type) && $type) $post_states['member_page'] = '<i class="wpcom wpcom-logo"></i> ' . $type;
        }
        return $post_states;
    }

    function user_description($val, $user, $meta_key, $single){
        if($meta_key==='description'){
            global $options;
            $meta_cache = wp_cache_get( $user, 'user_meta' );
            if ( ! $meta_cache ) {
                $meta_cache = update_meta_cache( 'user', array( $user ) );
                if ( isset( $meta_cache[ $user ] ) ) {
                    $meta_cache = $meta_cache[ $user ];
                } else {
                    $meta_cache = null;
                }
            }

            if ( isset( $meta_cache[ $meta_key ] ) ) {
                if ( $single ) {
                    $val = maybe_unserialize( $meta_cache[ $meta_key ][0] );
                } else {
                    $val = array_map( 'maybe_unserialize', $meta_cache[ $meta_key ] );
                }
            }

            if($val==='' && isset($options['member_desc']) && $options['member_desc']){
                $val = $options['member_desc'];
            }
        }
        return $val;
    }

    function bulk_actions_users( $actions ){
        if( current_user_can( 'edit_users' ) ) {
            $actions['approve'] = __('Approve', 'wpcom');
            $actions['disapprove'] = __('Disapprove', 'wpcom');
            $actions['blacklist'] = __('Add to blacklist', 'wpcom');
            $actions['remove-blacklist'] = __('Remove from blacklist', 'wpcom');
        }
        return $actions;
    }

    function handle_bulk_actions_users( $redirect_to, $doaction, $ids ){
        if( !$ids || !current_user_can( 'edit_users' ) ) return $redirect_to;
        global $options;
        if( $doaction=='approve' ){
            foreach ( $ids as $id ){
                wp_update_user( array( 'ID' => $id, 'user_status' => 0 ) );
                update_user_meta($id, 'wpcom_approve', ''); // 清除旧数据
                // 管理员审核方式则发送邮件通知
                if( isset($options['member_reg_active']) && $options['member_reg_active']=='2' ){
                    // 检查之前的状态，有变化则通知
                    $_user = get_user_by( 'ID', $id );
                    if(isset($_user->ID) && $_user->user_status != '0') wpcom_send_actived_email( $id );
                }
            }
        }else if( $doaction=='disapprove' ){
            foreach ( $ids as $id ){
                wp_update_user( array( 'ID' => $id, 'user_status' => -1 ) );
                update_user_meta($id, 'wpcom_approve', ''); // 清除旧数据
            }
        }else if( $doaction=='blacklist' ){
            foreach ( $ids as $id ){
                wp_update_user( array( 'ID' => $id, 'user_status' => 1 ) );
            }
        }else if( $doaction=='remove-blacklist' ){
            foreach ( $ids as $id ){
                wp_update_user( array( 'ID' => $id, 'user_status' => 0 ) );
            }
        }
        return $redirect_to;
    }

    function disable_default_register(){
        $url = wpcom_register_url();
        if($url){
            wp_redirect( $url );
            exit;
        }
    }

    function users_columns( $columns ) {
        $columns['registered'] = __('Registered', 'wpcom');
        $_columns = array();
        foreach ($columns as $key => $column){
            switch ($key) {
                case 'username':
                    $_columns['user'] = __('User');
                    break;
                case 'name':
                    if(is_wpcom_enable_phone()) {
                        $_columns['phone'] = _x('Phone number', 'label', 'wpcom');
                    }
                    break;
                case 'email':
                    $_columns['_email'] = __( 'Email' );
                    break;
                default:
                    $_columns[$key] = $column;
                    break;
            }
        }
        return $_columns;
    }

    function users_column_value( $val, $column_name, $user_id ) {
        $user = get_user_by( 'ID', $user_id );
        switch ($column_name) {
            case 'registered' :
                $offset = get_option( 'gmt_offset' );
                $val = date('Y.m.d H:i:s', strtotime( $user->user_registered ) + ($offset * HOUR_IN_SECONDS) );
                break;
            case 'phone' :
                $val = $user->mobile_phone;
                break;
            case '_email' :
                $email = $user->user_email;
                if(wpcom_is_empty_mail($email)) $email = '';
                if($email){
                    $email = "<a href='" . esc_url( "mailto:$email" ) . "'>$email</a>";
                }
                $val = $email;
                break;
            case 'user' :
                $actions     = array();
                $super_admin = '';
                if ( is_multisite() && current_user_can( 'manage_network_users' ) ) {
                    if ( in_array( $user->user_login, get_super_admins(), true ) ) {
                        $super_admin = ' &mdash; ' . __( 'Super Admin' );
                    }
                }
                if ( current_user_can( 'list_users' ) ) {
                    // Set up the user editing link
                    $edit_link = esc_url( add_query_arg( 'wp_http_referer', urlencode( wp_unslash( $_SERVER['REQUEST_URI'] ) ), get_edit_user_link( $user->ID ) ) );
                    if ( current_user_can( 'edit_user', $user->ID ) ) {
                        $actions['edit'] = '<a href="' . $edit_link . '">' . __( 'Edit' ) . '</a>';
                        $edit            = "<strong><a href=\"{$edit_link}\">{$user->display_name}</a>{$super_admin}</strong><br />";
                    } else {
                        $edit = "<strong>{$user->display_name}{$super_admin}</strong><br />";
                    }

                    if ( ! is_multisite() && get_current_user_id() != $user->ID && current_user_can( 'delete_user', $user->ID ) ) {
                        $actions['delete'] = "<a class='submitdelete' href='" . wp_nonce_url( "users.php?action=delete&amp;user=$user->ID", 'bulk-users' ) . "'>" . __( 'Delete' ) . '</a>';
                    }
                    if ( is_multisite() && get_current_user_id() != $user->ID && current_user_can( 'remove_user', $user->ID ) ) {
                        $actions['remove'] = "<a class='submitdelete' href='" . wp_nonce_url( "users.php?action=remove&amp;user=$user->ID", 'bulk-users' ) . "'>" . __( 'Remove' ) . '</a>';
                    }

                    // Add a link to the user's author archive, if not empty.
                    $author_posts_url = get_author_posts_url( $user->ID );
                    if ( $author_posts_url ) {
                        $actions['view'] = sprintf(
                            '<a href="%s" aria-label="%s" target="_blank">%s</a>',
                            esc_url( $author_posts_url ),
                            /* translators: %s: author's display name */
                            esc_attr( sprintf( __( 'View posts by %s' ), $user->display_name ) ),
                            __( 'View' )
                        );
                    }
                    $actions = apply_filters( 'user_row_actions', $actions, $user );
                } else {
                    $edit = "<strong>{$user->display_name}{$super_admin}</strong>";
                }
                $avatar = get_avatar( $user->ID, 32 );
                $val = "$avatar $edit";

                $action_count = count( $actions );
                $i            = 0;

                if ( ! $action_count ) {
                    return '';
                }

                $out = '<div class="row-actions">';
                foreach ( $actions as $action => $link ) {
                    ++$i;
                    ( $i == $action_count ) ? $sep = '' : $sep = ' | ';
                    $out                          .= "<span class='$action'>$link$sep</span>";
                }
                $out .= '</div>';

                $out .= '<button type="button" class="toggle-row"><span class="screen-reader-text">' . __( 'Show more details' ) . '</span></button>';

                $val .= $out;
                break;
            default :
                break;
        }
        return $val;
    }

    function user_registered_sortable( $columns ){
        $columns['registered'] = 'registered';
        return $columns;
    }

    function user_contactmethods($user_contact){
        if(is_wpcom_enable_phone()) {
            $user_contact['mobile_phone'] = __('Mobile Phone', 'wpcom');
        }
        return $user_contact;
    }

    function wp_mail($atts){
        // 邮件发送过滤系统填错邮箱，即未设置邮箱的用户
        if ( isset( $atts['to'] ) ) {
            if(is_array($atts['to'])){
                foreach ($atts['to'] as $k => $to){
                    if(wpcom_is_empty_mail($to)){
                        unset($atts['to'][$k]);
                    }
                }
            }else if(wpcom_is_empty_mail($atts['to'])){
                $atts['to'] = '';
            }
        }
        return $atts;
    }

    function get_posts_count($count, $user){
        if($count==='') $count = $this->update_post_count($user);
        return $count ?: 0;
    }

    function get_comments_count($count, $user){
        if($count==='') $count = $this->update_comment_count($user);
        return $count ?: 0;
    }

    function get_questions_count($count, $user){
        if($count==='') $count = $this->update_question_count($user);
        return $count ?: 0;
    }

    function get_answers_count($count, $user){
        if($count==='') $count = $this->update_answer_count($user);
        return $count ?: 0;
    }

    function posts_count($postid, $post){
        if($postid) $this->update_post_count($post->post_author);
    }

    function qa_posts_count($postid, $post){
        if($postid) $this->update_question_count($post->post_author);
    }

    function comments_count($comment_ID, $comment){
        if($comment_ID && $comment->user_id) {
            if($comment->comment_type==='' || $comment->comment_type==='comment'){
                $this->update_comment_count($comment->user_id);
            }else if($comment->comment_type==='answer'){
                $this->update_answer_count($comment->user_id);
            }
        }
    }

    function comments_count_status($new_status, $old_status, $comment){
        if($comment->user_id) {
            if($comment->comment_type==='' || $comment->comment_type==='comment'){
                $this->update_comment_count($comment->user_id);
            }else if($comment->comment_type==='answer'){
                $this->update_answer_count($comment->user_id);
            }
        }
    }

    function update_post_count($user){
        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT( * ) FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish' AND post_author = %d", $user));
        if(!is_wp_error($count)) {
            update_user_option($user, 'posts_count', $count);
            return $count;
        }
    }

    function update_comment_count($user){
        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT( * ) FROM {$wpdb->comments} WHERE (comment_type = '' OR comment_type = 'comment') AND comment_approved = 1 AND user_id = %d", $user));
        if(!is_wp_error($count)) {
            update_user_option($user, 'comments_count', $count);
            return $count;
        }
    }

    function update_question_count($user){
        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT( * ) FROM {$wpdb->posts} WHERE post_type = 'qa_post' AND post_status = 'publish' AND post_author = %d", $user));
        if(!is_wp_error($count)) {
            update_user_option($user, 'questions_count', $count);
            return $count;
        }
    }

    function update_answer_count($user){
        global $wpdb;
        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT( * ) FROM {$wpdb->comments} WHERE comment_type = 'answer' AND comment_approved = 1 AND user_id = %d", $user));
        if(!is_wp_error($count)) {
            update_user_option($user, 'answers_count', $count);
            return $count;
        }
    }

    function user_data_stats($user, $link=true){
        global $options, $wpdb;
        $user = isset($user->ID) ? $user : get_user_by('ID', $user);
        $posts = apply_filters('wpcom_posts_count', $user->{$wpdb->get_blog_prefix() . 'posts_count'}, $user->ID);
        if ($posts >= 1000) $posts = sprintf("%.1f", $posts / 1000) . 'K';
        $comments = apply_filters('wpcom_comments_count', $user->{$wpdb->get_blog_prefix() . 'comments_count'}, $user->ID);
        if ($comments >= 1000) $comments = sprintf("%.1f", $comments / 1000) . 'K';
        $posts_url = wpcom_profile_url( $user, 'posts' );
        $comments_url = wpcom_profile_url( $user, 'comments' );
        $tag = $link ? 'a' : 'div';
        ?>
        <<?php echo $tag;?> class="user-stats-item"<?php echo $link ? ' href="'.esc_url($posts_url).'" target="_blank"':'';?>>
            <b><?php echo $posts;?></b>
            <span><?php echo _x('posts', 'stats', 'wpcom');?></span>
        </<?php echo $tag;?>>
        <?php if(!isset($options['comments_open']) || $options['comments_open']=='1'){ ?>
        <<?php echo $tag;?> class="user-stats-item"<?php echo $link ? ' href="'.esc_url($comments_url).'" target="_blank"':'';?>>
            <b><?php echo $comments;?></b>
            <span><?php echo _x('comments', 'stats', 'wpcom');?></span>
        </<?php echo $tag;?>>
        <?php } ?>
        <?php if(defined('QAPress_VERSION')){
            $questions = apply_filters('wpcom_questions_count', $user->{$wpdb->get_blog_prefix() . 'questions_count'}, $user->ID);
            if ($questions >= 1000) $questions = sprintf("%.1f", $questions / 1000) . 'K';
            $answers = apply_filters('wpcom_answers_count', $user->{$wpdb->get_blog_prefix() . 'answers_count'}, $user->ID);
            if ($answers >= 1000) $answers = sprintf("%.1f", $answers / 1000) . 'K';
            $questions_url = wpcom_profile_url( $user, 'questions' );
            if($questions){ ?>
                <<?php echo $tag;?> class="user-stats-item"<?php echo $link ? ' href="'.esc_url($questions_url).'" target="_blank"':'';?>>
                    <b><?php echo $questions;?></b>
                    <span><?php echo _x('questions', 'stats', 'wpcom');?></span>
                </<?php echo $tag;?>>
            <?php }
            if($answers){ ?>
                <<?php echo $tag;?> class="user-stats-item"<?php echo $link ? ' href="'.esc_url($questions_url).'" target="_blank"':'';?>>
                    <b><?php echo $answers;?></b>
                    <span><?php echo _x('answers', 'stats', 'wpcom');?></span>
                </<?php echo $tag;?>>
            <?php }
        }
        if(isset($options['member_follow']) && $options['member_follow']=='1'){
            $followers = apply_filters('wpcom_followers_count', $user->{$wpdb->get_blog_prefix() . 'followers_count'}, $user->ID);
            if ($followers >= 1000) $followers = sprintf("%.1f", $followers / 1000) . 'K';
            $followers_url = wpcom_profile_url( $user, 'follows' ); ?>
            <<?php echo $tag;?> class="user-stats-item"<?php echo $link ? ' href="'.esc_url($followers_url).'" target="_blank"':'';?>>
                <b><?php echo $followers;?></b>
                <span><?php echo _x('followers', 'stats', 'wpcom');?></span>
            </<?php echo $tag;?>>
        <?php }
    }
}

$GLOBALS['wpcom_member'] = new WPCOM_Member();