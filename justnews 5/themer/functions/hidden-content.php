<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_Hidden_Content{
    function __construct(){
        add_shortcode( 'wpcom-content', array( $this, 'shortcode' ) );
        add_action('wp_ajax_wpcom_get_hidden_content', array($this, 'hidden_content'));
        add_action('wp_ajax_nopriv_wpcom_get_hidden_content', array($this, 'hidden_content'));
        add_action('rest_api_init', array($this, 'rest_hidden_content'), 100 );
    }

    function shortcode($args, $content=''){
        if( isset( $args['type'] ) && $args['type'] !== '' ){
            if(!isset($args['tips']) && $content && !preg_match('/^<script([^>]+)text\/html([^>]+)>/i', $content)){
                    $args['tips'] = $content;
            }else if(isset($args['tips'])){
                $args['tips'] = preg_replace('/(&lt|&amp;lt)/', '<', $args['tips']);
                $args['tips'] = preg_replace('/(&gt|&amp;gt)/', '>', $args['tips']);
                $args['tips'] = preg_replace('(&quot|&amp;quot)', '"', $args['tips']);
            }else{
                $args['tips'] = '';
            }

            extract($args);
            ob_start();
            include FRAMEWORK_PATH . '/member/templates/hidden-content.php';
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        }
    }

    function hidden_content(){
        $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : 0;
        $res = $this->get_hidden_content($post_id);
        echo json_encode($res);
        exit;
    }

    function rest_hidden_content(){
        $api = new WPCOM_REST_HC_Controller();
        $api->register_routes();
    }

    public function get_hidden_content($post_id){
        global $post;
        $res = array();
        if($post_id && $post = get_post($post_id)){
            if(isset($post->ID)){
                $blocks = $this->get_hide_content_blocks($post->post_content);
                if($blocks && is_array($blocks)){
                    foreach ($blocks as $block){
                        if(isset($block['attrs']) && isset($block['attrs']['type']) && isset($block['attrs']['id']) && $block['attrs']['id']){
                            switch ($block['attrs']['type']){
                                case '0': // 回复
                                    $content = $this->shortcode_comment_view($block);
                                    break;
                                case '1': // 登录
                                    $content = $this->shortcode_login_view($block);
                                    break;
                                case '2': // 用户组
                                    $content = $this->shortcode_group_view($block);
                                    break;
                            }
                            if(isset($content) && $content) $res[$block['attrs']['id']] = $content;
                        }
                    }
                }
            }
        }
        return $res;
    }

    function get_hide_content_blocks($content){
        $blocks = parse_blocks($content);
        return $this->loop_blocks($blocks);
    }

    function loop_blocks($blocks){
        $res = array();
        if($blocks){
            foreach ($blocks as $block){
                if($block['blockName'] === 'wpcom/hidden-content'){
                    $res[] = $block;
                }else if(isset($block['innerBlocks']) && $block['innerBlocks']){
                    $childs = $this->loop_blocks($block['innerBlocks']);
                    if($childs) $res = array_merge($res, $childs);
                }
            }
        }
        return $res;
    }

    function shortcode_comment_view($block){
        global $post, $current_user;
        $show = 0;
        if($current_user && isset($current_user->ID) && $current_user->ID){
            $c_args = array(
                'post_id' => $post->ID,
                'user_id' => $current_user->ID,
                'count'   => true
            );
            $show = get_comments( $c_args );
        }else if(isset($_COOKIE['comment_author_email_' . COOKIEHASH])){
            $email = urldecode($_COOKIE['comment_author_email_' . COOKIEHASH]);
            if($email){
                $c_args = array(
                    'post_id' => $post->ID,
                    'author_email' => $email,
                    'count'   => true
                );
                $show = get_comments( $c_args );
            }
        }
        if($show && $block['innerBlocks']){
            $output = '';
            foreach ( $block['innerBlocks'] as $b ) {
                $output .= render_block( $b );
            }
            $content = apply_filters('the_content', $output);
            return $content;
        }
    }

    function shortcode_login_view($block){
        global $current_user;
        if($current_user && isset($current_user->ID) && $current_user->ID && $block['innerBlocks']){
            $output = '';
            foreach ( $block['innerBlocks'] as $b ) {
                $output .= render_block( $b );
            }
            $content = apply_filters('the_content', $output);
            return $content;
        }
    }

    function shortcode_group_view($block){
        global $current_user;
        if($current_user && isset($current_user->ID) && $current_user->ID && isset($block['attrs']['user_group']) && is_array($block['attrs']['user_group'])){
            $group = wpcom_get_user_group($current_user->ID);
            if($group && in_array($group->term_id, $block['attrs']['user_group']) && $block['innerBlocks']){
                $output = '';
                foreach ( $block['innerBlocks'] as $b ) {
                    $output .= render_block( $b );
                }
                $content = apply_filters('the_content', $output);
                return $content;
            }
        }
    }
}

$GLOBALS['hiddenContent'] = new WPCOM_Hidden_Content();

class WPCOM_REST_HC_Controller extends WP_REST_Users_Controller{
    protected $meta;
    public function __construct(){
        $this->namespace = 'wp/v2';
        $this->rest_base = 'hidden-content';
    }

    public function register_routes()
    {
        register_rest_route($this->namespace, '/' . $this->rest_base, array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array($this, 'get_items'),
                'args'                => $this->get_collection_params(),
                'permission_callback' => array( $this, 'no_check' )
            ),
            'schema' => array($this, 'get_public_item_schema'),
        ));
    }
    function get_collection_params() {
        return array(
            'post_id' => array(
                'required'          => true,
                'default'           => 0,
                'type'              => 'integer',
                'validate_callback' => 'rest_validate_request_arg',
            )
        );
    }
    function get_items($request) {
        $content = $GLOBALS['hiddenContent']->get_hidden_content($request['post_id']);
        return rest_ensure_response( $content );
    }
    function no_check(){
        return true;
    }
}

