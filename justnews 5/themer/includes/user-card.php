<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_User_Card{
    function __construct(){
        add_action('wp_ajax_wpcom_user_card', array($this, 'user_card'));
        add_action('wp_ajax_nopriv_wpcom_user_card', array($this, 'user_card'));

        add_filter( 'wpcom_localize_script', array($this, 'localize_script') );
    }

    function user_card(){
        $uid = isset($_REQUEST['user']) && $_REQUEST['user'] ? $_REQUEST['user'] : '';
        $res = array('result' => -1);
        if($uid){
            global $wpcom_member;
            $user = get_user_by('ID', $uid);
            if($user && isset($user->ID)){
                $res['result'] = 0;
                $res['html'] = $wpcom_member->load_template('user-card', array('user'=>$user));
            }
        }
        echo json_encode($res);
        exit;
    }

    function localize_script($script){
        $script['user_card'] = 1;
        return $script;
    }
}