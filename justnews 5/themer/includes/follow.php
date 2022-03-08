<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_Follow{
    function __construct(){
        add_action( 'wpcom_profile_after_description', array($this, 'add_btn') );
        add_action( 'wpcom_profile_after_description', array($this, 'add_stats'), 5 );
        add_action( 'wpcom_follow_item_action', array($this, 'add_btn') );
        add_action( 'wpcom_user_card_action', array($this, 'add_btn') );
        add_action( 'wp_ajax_wpcom_follow', array($this, 'follow_action') );
        add_action( 'wp_ajax_nopriv_wpcom_follow', array($this, 'follow_action') );
        add_action( 'wp_ajax_wpcom_check_follow', array($this, 'check_follow') );
        add_action( 'wp_ajax_wpcom_user_follows', array($this, 'load_follows') );
        add_action( 'wp_ajax_nopriv_wpcom_user_follows', array($this, 'load_follows') );
        add_action( 'wp_ajax_wpcom_user_followers', array($this, 'load_followers') );
        add_action( 'wp_ajax_nopriv_wpcom_user_followers', array($this, 'load_followers') );
        add_action( 'wpcom_profile_tabs_follows', array($this, 'follows_tab') );
        add_action( 'wpcom_follow_user', array($this, 'update_count'), 10, 2 );
        add_action( 'wpcom_unfollow_user', array($this, 'update_count'), 10, 2 );

        add_filter( 'wpcom_follow_btn_html', array($this, 'follow_btn_html'), 5 );
        add_filter( 'wpcom_followed_btn_html', array($this, 'followed_btn_html'), 5 );
        add_filter( 'wpcom_localize_script', array($this, 'localize_script') );
        add_filter( 'wpcom_profile_tabs', array($this, 'profile_tab') );
        add_filter( 'wpcom_followers_count', array($this, 'get_followers_count'), 5, 2);
    }

    function follow_action(){
        $res = array('result' => 0, 'msg' => __('Followed!', 'wpcom'));
        $user_id = get_current_user_id();
        $follow = isset($_REQUEST['follow']) ? $_REQUEST['follow'] : 0;
        if($user_id){
            if($user_id==$follow){
                $res['result'] = -3;
                $res['msg'] = __('You can’t follow yourself!', 'wpcom');
            }else if($this->is_followed($follow, $user_id) && $this->unfollow($follow, $user_id)){
                $res['result'] = 1; // 取消关注成功
                $res['msg'] = __('Unfollow successfully!', 'wpcom');
            } else if(!$this->follow($follow, $user_id)){
                $res['result'] = -2;
                $res['msg'] = __('Follow failed, please try again later!', 'wpcom');
            }
        }else{
            $res['result'] = -1;
            $res['msg'] = _x('Please login first', 'follow', 'wpcom');;
        }
        echo json_encode($res);
        exit;
    }

    function check_follow(){
        $ids = isset($_REQUEST['ids']) ? $_REQUEST['ids'] : array();
        $user_id = get_current_user_id();
        $res = new stdClass;
        if($user_id && $ids && is_array($ids)){
            foreach ($ids as $id) {
                $res->{$id} = $this->is_followed($id, $user_id);
            }
        }
        echo json_encode($res);
        exit;
    }

    function load_follows(){
        global $wpcom_member;
        $user_id = isset($_REQUEST['user']) ? $_REQUEST['user'] : 0;
        $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 2;
        $follows = $this->get_follows($user_id, 10, $page);
        if($follows && is_array($follows)){
            foreach ($follows as $follow) echo $wpcom_member->load_template('follow', array('follow' => $follow));
        }else{
            echo 0;
        }
        exit;
    }

    function load_followers(){
        global $wpcom_member;
        $user_id = isset($_REQUEST['user']) ? $_REQUEST['user'] : 0;
        $page = isset($_REQUEST['page']) ? $_REQUEST['page'] : 1;
        $follows = $this->get_followers($user_id, 10, $page);
        if($follows && is_array($follows)){
            foreach ($follows as $follow) echo $wpcom_member->load_template('follow', array('follow' => $follow));
        }else{
            echo 0;
        }
        if($page==1) {
            global $wpdb;
            $table = _get_meta_table( 'user' );
            $option_name = $wpdb->get_blog_prefix() . '_wpcom_follow';
            $count = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(*) FROM $table WHERE meta_key = '$option_name' AND meta_value = '$user_id'" ));
            header('Next-page: '.($count>10?'1':'0'));
        }
        exit;
    }

    function follow($followed, $user=''){
        global $wpdb;
        $user = $user ? $user : get_current_user_id();
        $option_name = $wpdb->get_blog_prefix() . '_wpcom_follow';
        if($user && $followed && is_numeric($user) && is_numeric($followed) && !$this->is_followed($followed, $user)){
            $res = add_user_meta($user, $option_name, $followed);
            if($res){
                do_action('wpcom_follow_user', $user, $followed);
            }
            return $res;
        }
        return false;
    }

    function unfollow($followed, $user=''){
        global $wpdb;
        $user = $user ? $user : get_current_user_id();
        $option_name = $wpdb->get_blog_prefix() . '_wpcom_follow';
        if($user && $followed && is_numeric($user) && is_numeric($followed) && $this->is_followed($followed, $user)){
            $res = delete_user_meta( $user, $option_name, $followed );
            if($res){
                do_action('wpcom_unfollow_user', $user, $followed);
            }
            return $res;
        }
        return false;
    }

    function update_count($user, $followed){
        global $wpdb;
        $table = _get_meta_table('user');
        $option_name = $wpdb->get_blog_prefix() . '_wpcom_follow';
        $count = $wpdb->get_var($wpdb->prepare("SELECT COUNT( * ) FROM $table WHERE meta_key = %s AND meta_value = %d", $option_name, $followed));
        if(is_wp_error($count)){
            $filter = current_filter();
            $count = get_user_meta($followed, $wpdb->get_blog_prefix() . 'followers_count', true);
            $count = $count ? $count : 0;
            if($filter==='wpcom_follow_user'){
                $count += 1;
            }else if($count>0){
                $count -= 1;
            }
        }
        update_user_option($followed, 'followers_count', $count);
        return $count;
    }

    function is_followed($followed, $user=''){
        global $wpdb;
        $user = $user ? $user : get_current_user_id();
        if($user && $followed && is_numeric($user) && is_numeric($followed)) {
            $table = _get_meta_table('user');
            $option_name = $wpdb->get_blog_prefix() . '_wpcom_follow';
            if ($wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table WHERE meta_key = %s AND user_id = %d AND meta_value = %d", $option_name, $user, $followed))) {
                return true;
            }
        }
        return false;
    }

    function get_follows($user, $number=-1, $paged=1){
        global $wpdb;
        $option_name = $wpdb->get_blog_prefix() . '_wpcom_follow';
        $ids = get_user_meta($user, $option_name);
        if($ids) {
            if($number>0){
                $ids = array_slice(array_reverse($ids), $number*($paged-1), $number);
            }
            if($ids){
                $users = get_users(array('include' => $ids, 'orderby' => 'include'));
                if(!is_wp_error($users)) return $users;
            }
        }
        return false;
    }

    function get_followers($user, $number=-1, $paged=1){
        global $wpdb;
        $table = _get_meta_table( 'user' );
        $option_name = $wpdb->get_blog_prefix() . '_wpcom_follow';
        $limit = '';
        if($number>0) $limit = 'LIMIT ' . ($number*($paged-1)) . ', '.$number;
        $meta_list = $wpdb->get_results( "SELECT user_id FROM $table WHERE meta_key = '$option_name' AND meta_value = '$user' ORDER BY umeta_id DESC $limit" );
        $ids = array();
        if($meta_list){
            foreach ($meta_list as $meta){
                if($meta->user_id && !in_array($meta->user_id, $ids)) $ids[] = $meta->user_id;
            }
        }
        if($ids) {
            $users = get_users(array('include' => $ids, 'orderby' => 'include'));
            if(!is_wp_error($users)) return $users;
        }
        return false;
    }

    function get_followers_count($count){
        if($count==='') $count = 0;
        return $count;
    }

    function profile_tab($tabs){
        $tabs += array(
            27 => array(
                'slug' => 'follows',
                'title' => __( 'Follows', 'wpcom' )
            )
        );
        return $tabs;
    }

    function follows_tab(){
        global $wpcom_member, $profile, $wpdb;
        $option_name = $wpdb->get_blog_prefix() . '_wpcom_follow';
        $ids = get_user_meta($profile->ID, $option_name);
        $number = 10;
        $atts = array(
            'user_id' => $profile->ID,
            'total' => is_array($ids) ? count($ids) : 0,
            'number' => $number,
            'follows' => $this->get_follows($profile->ID, $number)
        );
        echo $wpcom_member->load_template('follows', $atts);
    }

    function follow_btn_html($btn){
        $btn = WPCOM::icon('add', false) . __('Follow', 'wpcom');
        return $btn;
    }

    function followed_btn_html($btn){
        $btn = __('Followed', 'wpcom');
        return $btn;
    }

    function add_btn($user){
        $is_followed = $this->is_followed($user);
        if($is_followed){
            $html = apply_filters('wpcom_followed_btn_html', '');
        }else{
            $html = apply_filters('wpcom_follow_btn_html', '');
        }
        echo '<button type="button" class="btn btn-xs btn-follow j-follow'.($is_followed?' followed':' btn-primary').'" data-user="'.$user.'">' . $html . '</button>';
    }

    function add_stats($user){
        global $options;
        if(isset($options['member_follow']) && $options['member_follow']=='1'){ ?>
        <div class="profile-stats">
            <div class="profile-stats-inner">
                <?php do_action('wpcom_user_data_stats', $user, false);?>
            </div>
        </div>
    <?php } }

    function localize_script($scripts){
        $scripts['follow_btn'] = apply_filters('wpcom_follow_btn_html', '');
        $scripts['followed_btn'] = apply_filters('wpcom_followed_btn_html', '');
        return $scripts;
    }
}