<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_Messages{
    function __construct(){
        global $wpdb;
        $this->table = $wpdb->prefix . 'wpcom_messages';
        add_action( 'admin_menu', array($this, 'init_database') );
        add_action( 'wpcom_profile_after_description', array($this, 'add_btn'), 11 );
        add_action( 'wpcom_follow_item_action', array($this, 'add_btn'), 11 );
        add_action( 'wpcom_user_card_action', array($this, 'add_btn'), 11 );
        add_action( 'wp_ajax_wpcom_message_box', array($this, 'message_box') );
        add_action( 'wp_ajax_nopriv_wpcom_message_box', array($this, 'message_box') );
        add_action( 'wp_ajax_wpcom_send_message', array($this, 'send_message') );
        add_action( 'wp_ajax_nopriv_wpcom_send_message', array($this, 'send_message') );
        add_action( 'wp_ajax_wpcom_load_messages', array($this, 'load_messages') );
        add_action( 'wp_ajax_wpcom_read_messages', array($this, 'read_messages') );
        add_action( 'wp_ajax_wpcom_check_messages', array($this, 'check_messages') );
        add_action( 'wpcom_account_tabs_messages', array($this, 'messages_list') );

        add_filter( 'wpcom_account_tabs', array($this, 'messages_tab'), 20 );
        add_filter( 'wpcom_unread_messages_count', array($this, 'get_unread_count'), 10, 2);
    }

    function messages_tab($tabs){
        $tabs[17] = array(
            'slug' => 'messages',
            'title' => __('Messages', 'wpcom'),
            'icon' => 'mail-circle'
        );
        return $tabs;
    }

    function messages_list(){
        global $wpcom_member;
        $page = get_query_var('pageid') ? get_query_var('pageid') : 1;
        $user_id = get_current_user_id();
        $args = array(
            'user' => $user_id,
            'list' => $this->get_messages_list($user_id, 10, $page),
            'pages' => $this->get_messages_list_pages($user_id, 10),
            'paged' => $page
        );
        echo $wpcom_member->load_template('message-list', $args);
    }

    function add_btn($user){
        echo '<button type="button" class="btn btn-primary btn-xs btn-message j-message" data-user="'.$user.'">'.wpcom::icon('mail-fill', false).__('Message', 'wpcom').'</button>';
    }

    function message_box(){
        global $wpcom_member;
        $res = array('result' => 0);
        $user_id = get_current_user_id();
        $user_to = isset($_REQUEST['user']) && $_REQUEST['user'] ? $_REQUEST['user'] : 0;
        if(!$user_id) {
            $res['result'] = -1;
            $res['msg'] = _x('Please login first', 'message', 'wpcom');
        }else if($user_id==$user_to){
            $res['result'] = -3;
            $res['msg'] = __('You can’t send yourself a private message!', 'wpcom');
        }else{
            $to_user = get_user_by('ID', $user_to);
            if($to_user->ID){
                $res['to_uid'] = $to_user->ID;
                $res['to_uname'] = $to_user->display_name;
                $res['to_url'] = wpcom_profile_url($to_user);
                $res['avatar'] = get_avatar_url( $user_id );
                $res['messages'] = '';
                $messages = $this->get_messages($user_id, $user_to);
                if($messages){
                    $count = count($messages);
                    header('Next-page: '.($count===10?'1':'0')); // 每页10条，如果不等于10条，则可能没有下一页了
                    foreach ($messages as $message){
                        $atts = array(
                            'message' => $message,
                            'user' => $user_id
                        );
                        $res['messages'] .= $wpcom_member->load_template('message', $atts);
                    }
                }
            }
        }
        echo json_encode($res);
        exit;
    }

    function send_message(){
        $res = array();
        $user_id = get_current_user_id();
        $to_user = isset($_REQUEST['to']) && $_REQUEST['to'] ? $_REQUEST['to'] : 0;
        $content = esc_html(stripslashes(isset($_REQUEST['content']) && $_REQUEST['content']!=='' ? $_REQUEST['content'] : ''));
        $last = isset($_REQUEST['last']) && $_REQUEST['last'] ? $_REQUEST['last'] : 0;

        if($user_id){
            if($user_id==$to_user){
                $res['result'] = -3;
                $res['msg'] = __('You can’t send yourself a private message!', 'wpcom');
            }else{
                $args = array(
                    'from_user' => $user_id,
                    'to_user' => $to_user,
                    'content' => $content
                );
                $id = $this->add_message($args);
                if($id){
                    $message = $this->get_message($id);
                    $res['result'] = 0;
                    $res['message_id'] = $id;
                    $res['message_content'] = convert_smilies($message->content);
                    $res['message_time'] = get_date_from_gmt($message->time, 'Y-m-d H:i');
                }

                $res['messages'] = '';

                $messages = $this->get_messages($user_id, $to_user, -$last, 100);
                if(is_array($messages) && count($messages)>1){
                    global $wpcom_member;
                    foreach ($messages as $message){
                        $atts = array(
                            'message' => $message,
                            'user' => $user_id
                        );
                        $res['messages'] .= $wpcom_member->load_template('message', $atts);
                    }
                }
            }
        }else{
            $res['result'] = -1;
            $res['msg'] = _x('Please login first', 'message', 'wpcom');
        }

        echo json_encode($res);
        exit;
    }

    function load_messages(){
        global $wpcom_member;
        $user_id = get_current_user_id();
        $user_to = isset($_REQUEST['user']) && $_REQUEST['user'] ? $_REQUEST['user'] : 0;
        $last_id = isset($_REQUEST['last']) && $_REQUEST['last'] ? $_REQUEST['last'] : 0;
        $messages = $this->get_messages($user_id, $user_to, $last_id);
        if($messages){
            $count = count($messages);
            header('Next-page: '.($count===10?'1':'0')); // 每页10条，如果不等于10条，则可能没有下一页了
            foreach ($messages as $message){
                $atts = array(
                    'message' => $message,
                    'user' => $user_id
                );
                echo $wpcom_member->load_template('message', $atts);
            }
        }
        exit;
    }

    function read_messages(){
        $user_id = get_current_user_id();
        $user_from = isset($_REQUEST['user']) && $_REQUEST['user'] ? $_REQUEST['user'] : 0;
        if($user_id && $user_from){
            echo $this->update_message_status($user_id, $user_from);
        }else{
            echo 0;
        }
        exit;
    }

    function check_messages(){
        $user_id = get_current_user_id();
        $to_user = isset($_REQUEST['user']) && $_REQUEST['user'] ? $_REQUEST['user'] : 0;
        $last = isset($_REQUEST['last']) && $_REQUEST['last'] ? $_REQUEST['last'] : 0;
        $res = array('result'=>0);
        if($user_id && $to_user){
            $messages = $this->get_messages($user_id, $to_user, -$last, 100);
            if($messages){
                global $wpcom_member;
                foreach ($messages as $message){
                    $atts = array(
                        'message' => $message,
                        'user' => $user_id
                    );
                    $res['messages'] .= $wpcom_member->load_template('message', $atts);
                }
            }
        }
        echo json_encode($res);
        exit;
    }

    function add_message($args){
        global $wpdb;
        if(isset($args['content']) && isset($args['to_user'])){
            $data = array(
                'from_user' => isset($args['from_user']) && $args['from_user'] ? $args['from_user'] : get_current_user_id(),
                'to_user' => $args['to_user'],
                'content' => $args['content'],
                'type' => isset($args['type']) && $args['type'] ? $args['type'] : 'private', // private私信，notice通知
                'status' => isset($args['status']) && $args['status'] ? $args['status'] : '0', // 0未读，1已读
                'time' => get_gmt_from_date(current_time( 'mysql' ))
            );
            $format = array('%d', '%s', '%s', '%s', '%s', '%s');
            $wpdb->insert($this->table, $data, $format);
            return $wpdb->insert_id;
        }
    }

    function update_message_status($user, $from, $status = '1'){
        global $wpdb;
        $user = $user ?: get_current_user_id();
        if($user && $from){
            $res = $wpdb->update($this->table, array('status' => $status), array('from_user'=>$from, 'to_user'=>$user, 'status'=>'0'));
            return $res;
        }
    }

    function get_messages($from, $to, $last=0, $num=10){
        global $wpdb;
        $table = $this->table;
        $limit = 'LIMIT ' . $num;
        $where_id = '';
        $order = 'DESC';
        if($last){
            $where_id = 'AND ' . ($last>0 ? 'ID < ' . $last : 'ID > '. -$last);
            if($last<0) $order = 'ASC';
        }
        $results = $wpdb->get_results( "SELECT * FROM $table WHERE ((from_user = '$from' AND to_user = '$to') OR (from_user = '$to' AND to_user = '$from')) $where_id ORDER BY ID $order $limit" );
        if($results && $order==='DESC') $results = array_reverse($results);
        return $results;
    }

    function get_message($id){
        global $wpdb;
        $results = $wpdb->get_results( "SELECT * FROM $this->table WHERE ID = '$id'" );
        if($results && isset($results[0])) return $results[0];
    }

    function get_messages_list($user, $num=10, $paged=1){
        global $wpdb;
        $user = $user ?: get_current_user_id();
        $limit = 'LIMIT ' . ($num*($paged-1)) . ', ' . $num;
        $group = "SELECT sum(case when status = '0' AND to_user = '$user' then 1 else 0 end) as unread, (case when from_user = '$user' then to_user else from_user end) as group_user, from_user, to_user FROM $this->table WHERE (to_user = '$user' OR from_user = '$user') AND type = 'private' GROUP BY group_user";
        $list = "SELECT max(M.ID) AS mid, G.unread, G.group_user FROM $this->table as M RIGHT JOIN ($group) as G ON ((M.from_user = G.from_user AND M.to_user = G.to_user) OR (M.from_user = G.to_user AND M.to_user = G.from_user)) GROUP BY G.group_user";
        $results = $wpdb->get_results( "SELECT T.ID, T.content, T.time, T.to_user, T.from_user, (case when T.status = '0' AND T.to_user = '$user' then '0' else '1' end) as status, L.unread, L.group_user FROM $this->table as T RIGHT JOIN ($list) as L ON T.ID = L.mid ORDER BY status ASC, T.ID DESC $limit" );

        return $results;
    }

    function get_messages_list_pages($user, $num=10){
        global $wpdb;
        $user = $user ?: get_current_user_id();
        $results = $wpdb->get_results("SELECT (case when from_user = '$user' then to_user else from_user end) as group_user FROM $this->table WHERE (to_user = '$user' OR from_user = '$user') AND type = 'private' GROUP BY group_user");
        $count = is_array($results) ? count($results) : 0;
        return ceil($count/$num);
    }

    function get_unread_count($count, $user){
        if(!$count){
            global $wpdb;
            $count = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(*) FROM $this->table WHERE to_user = %s AND type = 'private' AND status = '0'", $user ));
            $count = $count ?: 0;
        }
        return $count;
    }

    function init_database(){
        global $wpdb;
        $table = $this->table;
        if( $wpdb->get_var("SHOW TABLES LIKE '$table'") != $table ){
            $charset_collate = $wpdb->get_charset_collate();
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

            $create_sql = "CREATE TABLE {$table} (".
                "ID BIGINT(20) NOT NULL auto_increment,".
                "from_user BIGINT(20) NOT NULL,".
                "to_user varchar(20) NOT NULL,".
                "title longtext,".
                "content longtext,".
                "type varchar(20),".
                "status varchar(20),".
                "time datetime,".
                "PRIMARY KEY (ID)) {$charset_collate};";

            dbDelta( $create_sql );
        }
        add_submenu_page('users.php', _x('Messages', 'list', 'wpcom'), _x('Messages', 'list', 'wpcom'), 'manage_options', 'wpcom-messages', array($this, 'admin_page'), 3);
    }

    function admin_page(){ ?>
        <div class="wrap">
            <h2><?php echo _x('Messages', 'list', 'wpcom');?></h2>
            <form method="post">
                <?php
                if ( ! class_exists( 'WPCOM_Messages_List' ) ) require_once FRAMEWORK_PATH . '/includes/messages-list.php';
                $list = new WPCOM_Messages_List();
                $list->prepare_items();
                $list->search_box(__('Search messages', 'wpcom'), 'messages');
                $list->display();
                ?>
            </form>
        </div>
    <?php }
}