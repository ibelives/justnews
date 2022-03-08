<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_Notifications{
    function __construct(){
        global $wpdb;
        $this->table = $wpdb->prefix . 'wpcom_messages';
        $this->table2 = $wpdb->prefix . 'wpcom_notifications';
        add_action( 'admin_menu', array($this, 'init_database') );
        add_action( 'wp_ajax_wpcom_read_notification', array($this, 'set_read') );
        add_action( 'wpcom_account_tabs_notifications', array($this, 'notifications_list') );
        add_action( 'wp_ajax_wpcom_send_notification', array($this, 'send') );

        add_filter( 'wpcom_account_tabs', array($this, 'notifications_tab'), 21 );
        add_filter( 'wpcom_unread_notifications_count', array($this, 'get_unread_count'), 10, 2);

        // 注册系统默认需要发送通知的事件
        // 投稿通过、未通过
        add_action( 'transition_post_status', array($this, 'post_notify'), 10, 3 );
        // 评论相关通知
        add_action('wp_insert_comment', array($this, 'comment_notify'), 10, 2);
        // 新关注消息
        add_action( 'wpcom_follow_user', array( $this, 'follow_notify' ), 10, 2 );
    }

    function notifications_tab($tabs){
        $tabs[18] = array(
            'slug' => 'notifications',
            'title' => __('Notifications', 'wpcom'),
            'icon' => 'notice-circle'
        );
        return $tabs;
    }

    function add_notification($to, $title, $content){
        global $wpdb;
        if($to!=='' && $title && $content){
            $data = array(
                'from_user' => 0,
                'to_user' => $to,
                'title' => $title,
                'content' => $content,
                'type' => 'notice',
                'status' => '0',
                'time' => get_gmt_from_date(current_time( 'mysql' ))
            );
            $format = array('%d', '%s', '%s', '%s', '%s', '%s', '%s');
            $wpdb->insert($this->table, $data, $format);
            return $wpdb->insert_id;
        }
    }

    function set_read(){
        $id = isset($_REQUEST['id']) ? $_REQUEST['id'] : 0;
        $user = isset($_REQUEST['user']) ? $_REQUEST['user'] : get_current_user_id();
        if($id && $user && ($user==get_current_user_id() || current_user_can('manage_options'))) {
            global $wpdb;
            $notice = $this->get_notification($id);
            if($notice){
                $res = $wpdb->update($this->table, array('status' => 1), array('ID' => $id, 'to_user' => $user));
                if(!$res){ // 更新失败，则应该是群发消息，阅读状态记录到表2
                    $results = $wpdb->get_results( "SELECT * FROM $this->table2 WHERE to_user = '$user' AND msg_id='$id'" );
                    if($results && isset($results[0])){
                        $res = $wpdb->update($this->table2, array('status' => '1'), array('msg_id' => $id, 'to_user' => $user));
                    }else{
                        $res = $wpdb->insert($this->table2, array(
                            'to_user' => $user,
                            'msg_id' => $id,
                            'status' => '1'
                        ), array('%d', '%d', '%s'));
                    }
                }
            }
            echo $res;
        }else{
            echo 0;
        }
        exit;
    }

    function send(){
        $res = array('result' => -1);
        if (check_ajax_referer('wpcom_send_notifications', 'wpcom_send_notifications_nonce', false)) {
            $to = isset($_REQUEST['to']) ? $_REQUEST['to'] : '';
            $title = isset($_REQUEST['title']) ? $_REQUEST['title'] : '';
            $content = isset($_REQUEST['content']) ? $_REQUEST['content'] : '';
            $to_user = '';
            switch ($to){
                case '0':
                    $to_user = '0';
                    break;
                case '1':
                    $to_user = isset($_REQUEST['group']) ? $_REQUEST['group'] : '';
                    $to_user = $to_user ? '-'.$to_user : $to_user;
                    break;
                case '2':
                    $to_user = isset($_REQUEST['role']) ? $_REQUEST['role'] : '';
                    break;
                case '3':
                    $to_user = isset($_REQUEST['user']) ? $_REQUEST['user'] : '';
                    break;
            }
            if($to_user==='') $res['error'] = '发送对象不能为空';
            if($title==='') $res['error'] = '标题不能为空';
            if($content==='') $res['error'] = '内容不能为空';
            if($to_user!=='' && $title!=='' && $content!==''){
                $this->add_notification($to_user, $title, $content);
                $res['result'] = 0;
            }
        }
        echo json_encode($res);
        exit;
    }

    function get_notification($id){
        global $wpdb;
        $results = $wpdb->get_results( "SELECT * FROM $this->table WHERE ID = '$id' AND type='notice'" );
        if($results && isset($results[0])) return $results;
    }

    function notifications_list(){
        global $wpcom_member;
        $page = get_query_var('pageid') ? get_query_var('pageid') : 1;
        $user_id = get_current_user_id();
        $num = 20;
        $list = $this->get_notifications_list($user_id, $num, $page);
        $pages = $this->get_messages_list_pages($user_id, $num);
        $args = array(
            'list' => $list,
            'pages' => $pages,
            'paged' => $page
        );
        echo $wpcom_member->load_template('notification', $args);
    }
    function get_notifications_list($user, $num=10, $paged=1){
        global $wpdb;
        $user = $user ?: get_current_user_id();
        $limit = 'LIMIT ' . ($num*($paged-1)) . ', ' . $num;
        $where = $this->get_sql_where($user);
        $results = $wpdb->get_results( "SELECT t1.ID,t1.title,t1.content,t1.status,t1.time,t2.status as status2 FROM $this->table t1 LEFT JOIN $this->table2 t2 ON t2.to_user='$user' AND t2.msg_id=t1.ID $where ORDER BY t1.time DESC $limit" );
        return $results;
    }

    function get_messages_list_pages($user, $num=10){
        global $wpdb;
        $user = $user ?: get_current_user_id();
        $where = $this->get_sql_where($user);
        $results = $wpdb->get_results( "SELECT t1.ID,t1.title,t1.content,t1.status,t1.time,t2.status as status2 FROM $this->table t1 LEFT JOIN $this->table2 t2 ON t2.to_user='$user' AND t2.msg_id=t1.ID $where" );
        $count = is_array($results) ? count($results) : 0;
        return ceil($count/$num);
    }

    function get_unread_count($count, $user){
        if(!$count){
            global $wpdb;
            $where = $this->get_sql_where($user);
            $where .= " AND (t2.status='0' OR (t2.status is NULL AND t1.status='0'))";
            $count = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(*) FROM $this->table t1 LEFT JOIN $this->table2 t2 ON t2.to_user = %s AND t2.msg_id=t1.ID $where", $user ));
            $count = $count ?: 0;
        }
        return $count;
    }

    function get_sql_where($user){
        $where = "WHERE t1.from_user='0'";
        $group = wpcom_get_user_group($user);
        $user_meta = get_userdata($user);
        $where .= " AND (t1.to_user='$user' OR t1.to_user='0'";
        if($group) $where .= " OR t1.to_user='-$group->term_id'";
        if($user_meta && $user_meta->roles) {
            if(is_array($user_meta->roles)){
                foreach ($user_meta->roles as $role){
                    $where .= " OR t1.to_user='$role'";
                }
            }else{
                $where .= " OR t1.to_user='$user_meta->roles'";
            }
        }
        $where .= ") AND t1.type='notice'";
        $_user = get_user_by('ID', $user);
        $where .= " AND t1.time>'$_user->user_registered'";
        return $where;
    }

    function init_database(){
        global $wpdb;
        $table = $this->table;
        $table2 = $this->table2;
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
        }else{
            $count = $wpdb->get_var($wpdb->prepare( "SELECT COUNT(*) FROM information_schema.columns WHERE table_name='%s' AND column_name='%s'", $this->table, 'title' ));
            if($count == 0){
                $wpdb->query("ALTER TABLE $this->table ADD `title` LONGTEXT AFTER `to_user`");
                $wpdb->query("ALTER TABLE $this->table CHANGE `to_user` `to_user` varchar(20) NOT NULL");
            }
        }
        if( $wpdb->get_var("SHOW TABLES LIKE '$table2'") != $table2 ) {
            $charset_collate = $wpdb->get_charset_collate();
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            $create_sql2 = "CREATE TABLE {$table2} (" .
                "ID BIGINT(20) NOT NULL auto_increment,".
                "to_user BIGINT(20) NOT NULL," .
                "msg_id BIGINT(20) NOT NULL," .
                "status varchar(20)," .
                "PRIMARY KEY (ID)) {$charset_collate};";

            dbDelta($create_sql2);
        }
        add_submenu_page('users.php', _x('Notifications', 'list', 'wpcom'), _x('Notifications', 'list', 'wpcom'), 'manage_options', 'wpcom-notifications', array($this, 'admin_page'), 4);
    }

    function admin_page(){ ?>
        <div class="wrap">
            <h1 class="wp-heading-inline"><?php echo _x('Notifications', 'list', 'wpcom');?></h1>
            <a href="javascript:;" class="page-title-action j-send-notifications">发通知</a>
            <form method="post">
                <?php
                if ( ! class_exists( 'WPCOM_Notifications_List' ) ) require_once FRAMEWORK_PATH . '/includes/notification-list.php';
                $list = new WPCOM_Notifications_List();
                $list->prepare_items();
                $list->search_box(__('Search notifications', 'wpcom'), 'notifications');
                $list->display();
                ?>
            </form>
            <?php $list->send_notifications();?>
        </div>
    <?php }

    function comment_notify($id, $comment){
        if($id && $comment->comment_approved==1 && $comment->comment_post_ID){
            $post = get_post($comment->comment_post_ID);
            if($post && isset($post->ID) && in_array($post->post_type, array('post', 'qa_post'))){
                $type = array(
                    'post' => '文章',
                    'qa_post' => '问题'
                );
                $comment_title = array(
                    'post' => '评论',
                    'qa_post' => '回复'
                );
                $reply_title = array(
                    'post' => '回复',
                    'qa_post' => '评论'
                );

                if($comment->comment_parent){
                    $parent = get_comment($comment->comment_parent);
                    if($parent->user_id && $parent->user_id !== $comment->user_id){
                        $to = $parent->user_id;
                        $title = '你在'.$type[$post->post_type].'《'.$post->post_title.'》的'.$comment_title[$post->post_type] . '有新'.$reply_title[$post->post_type];
                        $content = '亲爱的用户：你好！<br>';
                        $content .= '你在'.$type[$post->post_type].'《'.$post->post_title.'》的'.$comment_title[$post->post_type].'有了新'.$reply_title[$post->post_type].'：<br>';
                        $content .= '<blockquote>'.$comment->comment_content.'</blockquote>';
                        $content .= '更多详情请访问：<a href="'.get_permalink($post->ID).'" target="_blank">'.get_permalink($post->ID).'</a>';
                    }
                }else if($post->post_author!=$comment->user_id){
                    $to = $post->post_author;
                    $title = $type[$post->post_type] . '《'.$post->post_title.'》有新'.$comment_title[$post->post_type];
                    $content = '亲爱的用户：你好！<br>';
                    $content .= '你的'.$type[$post->post_type].'《'.$post->post_title.'》有新'.$comment_title[$post->post_type].'：<br>';
                    $content .= '<blockquote>'.$comment->comment_content.'</blockquote>';
                    $content .= '更多详情请访问：<a href="'.get_permalink($post->ID).'" target="_blank">'.get_permalink($post->ID).'</a>';
                }
                if(isset($to) && $to){
                    $this->add_notification($to, $title, $content);
                }
            }
        }
    }

    function post_notify( $new_status, $old_status, $_post ){
        global $post;
        if ($old_status === 'publish' || $old_status === $new_status) return false;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return false;

        if (!isset($post->ID)) $post = $_post;
        $user = get_user_by('ID', $post->post_author);
        if(!$user) return false;
        if ($post->ID !== $_post->ID || $post->post_type!='post' || $user->has_cap( 'publish_posts' )) return false;

        $content = $content = '亲爱的用户：你好！<br>';
        if($new_status==='publish'){ // 审核通过
            $title = '文章《'.$post->post_title.'》审核通过';
            $content .= '恭喜，你的文章《<a href="'.get_permalink($post->ID).'" target="_blank">'.$post->post_title.'</a>》已经审核通过，感谢你对本站的支持！';
        }else if($old_status==='pending' && $new_status==='draft'){ // 审核未通过
            $title = '文章《'.$post->post_title.'》审核未通过';
            $content .= '很抱歉你的文章《<a href="'.get_edit_link($post->ID).'" target="_blank">'.$post->post_title.'</a>》审核未通过，感谢你对本站的支持！';
        }else if($old_status==='pending' && $new_status==='trash'){ // 删除
            $title = '文章《'.$post->post_title.'》审核未通过';
            $content .= '很抱歉你的文章《'.$post->post_title.'》审核未通过，感谢你对本站的支持！';
        }
        if(isset($title) && $title) $this->add_notification($post->post_author, $title, $content);
    }

    function follow_notify( $user_id, $followed ){
        $user = get_user_by('ID', $user_id);
        $title = $user->display_name . ' 关注了你';
        $content = $content = '亲爱的用户：你好！<br>';
        $content .= $user->display_name . ' 关注了你，点击查看Ta的信息：<a href="'.wpcom_author_url( $user_id, $user->user_nicename ).'" target="_blank">'.wpcom_author_url( $user_id, $user->user_nicename ).'</a>';
        $this->add_notification($followed, $title, $content);
    }
}