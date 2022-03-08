<?php
if ( ! class_exists( 'WP_List_Table' ) ) require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

class WPCOM_Notifications_List extends WP_List_Table {
    protected $table;
    protected $table2;
    public function __construct(){
        global $wpdb;
        $this->table = $wpdb->prefix . 'wpcom_messages';
        $this->table2 = $wpdb->prefix . 'wpcom_notifications';
        wp_enqueue_script( 'jquery-ui-dialog' );
        wp_enqueue_style( 'wp-jquery-ui-dialog' );

        parent::__construct([
            'screen' => 'notifications'
        ]);
    }

    public function ajax_user_can() {
        return current_user_can( 'manage_options' );
    }

    function send_notifications(){
        $groups = WPCOM::category('user-groups');
        $_roles = wp_roles();
        $roles = $_roles->role_names; ?>
        <div id="notifications-dialog">
            <form method="post" id="notifications-form">
                <?php wp_nonce_field( 'wpcom_send_notifications', 'wpcom_send_notifications_nonce' ); ?>
                <table class="form-table">
                    <tr>
                        <th>
                            <label for="to">发送给</label>
                        </th>
                        <td>
                            <select name="to" id="notifications-to" class="regular-text">
                                <option value="0">群发全体用户</option>
                                <option value="1">按分组群发</option>
                                <option value="2">按角色群发</option>
                                <option value="3">发送给单个用户</option>
                            </select>
                        </td>
                    </tr>
                    <tr id="j-to-1" class="j-to-item" style="display: none">
                        <th><label for="group">分组</label></th>
                        <td>
                            <select name="group" id="notifications-group" class="regular-text">
                                <?php if(!empty($groups)){
                                    foreach ($groups as $id => $group){
                                        echo '<option value="'.$id.'">'.$group.'</option>';
                                    }
                                } ?>
                            </select>
                        </td>
                    </tr>
                    <tr id="j-to-2" class="j-to-item" style="display: none">
                        <th><label for="role">角色</label></th>
                        <td>
                            <select name="role" id="notifications-role" class="regular-text">
                                <?php if(!empty($roles)){
                                    foreach ($roles as $name => $role) {
                                        echo '<option value="' . $name . '">' . translate_user_role($role) . '</option>';
                                    }
                                } ?>
                            </select>
                        </td>
                    </tr>
                    <tr id="j-to-3" class="j-to-item" style="display: none">
                        <th><label for="user">用户ID</label></th>
                        <td>
                            <input type="text" name="user" class="regular-text" id="notifications-user" placeholder="请填写用户ID">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="title">标题</label></th>
                        <td>
                            <input type="text" name="title" id="notifications-title" class="regular-text" placeholder="请填写标题">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="content">内容</label></th>
                        <td>
                            <textarea name="content" id="notifications-content" class="regular-text" cols="30" rows="6" placeholder="请填写需要发送的内容"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <p class="submit"><input type="submit" name="submit" class="button button-primary" value="发送"></p>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <script>
            (function ($) {
                $(document).ready(function () {
                    $('#notifications-dialog').dialog({
                        title: '发通知',
                        dialogClass: 'wp-dialog',
                        autoOpen: false,
                        width: '680',
                        modal: true,
                        focus: false,
                        position: {
                            my: "center",
                            at: "center",
                            of: window
                        }
                    });
                    $('a.j-send-notifications').click(function(e) {
                        e.preventDefault();
                        $('#notifications-dialog').dialog('open');
                    });

                    var to = $('#notifications-to').val();
                    if(to) $('#j-to-'+to).show();

                    $('#notifications-to').on('change', function () {
                        var val = $(this).val();
                        $('.j-to-item').hide();
                        if(val) $('#j-to-'+val).show();
                    });
                    $('#notifications-form').on('submit', function (e) {
                        e.preventDefault();
                        var $form = $(this);
                        var $submit = $form.find('input[type=submit]');
                        if($submit.hasClass('disabled')) return false;
                        $submit.addClass('disabled').val('正在发送...');
                        $.ajax({
                            url: ajaxurl+'?action=wpcom_send_notification',
                            type: 'post',
                            data: $form.serialize(),
                            dataType: 'json',
                            success: function (res) {
                                $submit.removeClass('disabled').val('发送');
                                $('#notifications-dialog').dialog('close');
                                if(res.result==0){
                                    alert('发送成功！');
                                }else{
                                    alert(res.error ? res.error : '发送失败，请稍后再试！');
                                }
                            },
                            error: function () {
                                $submit.removeClass('disabled').val('发送');
                                $('#notifications-dialog').dialog('close');
                                alert('发送失败，请稍后再试！');
                            }
                        });
                    });
                });
            })(jQuery);
        </script>
    <?php }

    public function prepare_items() {
        global $wpdb, $orderby, $order;
        wp_reset_vars( array( 'orderby', 'order' ) );

        $this->process_bulk_action();

        $orderby = $orderby ?: 'ID';
        $order = $order ?: 'DESC';

        $paged = $this->get_pagenum();
        $offset = ($paged-1) * 50;

        $search = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';

        $where = $this->get_sql_where();
        $where .= $search ? " AND (title LIKE '%$search%' OR content LIKE '%$search%' OR from_user = '$search' OR to_user = '$search')" : '';
        $results = $wpdb->get_results( "SELECT t1.ID,t1.to_user,t1.title,t1.content,t1.status,t1.time,t2.status as status2 FROM $this->table t1 LEFT JOIN $this->table2 t2 ON t2.to_user=t1.to_user AND t2.msg_id=t1.ID $where ORDER BY t1.$orderby $order limit $offset,50");
        $total = $wpdb->get_var( "SELECT COUNT(*) FROM $this->table t1 LEFT JOIN $this->table2 t2 ON t2.to_user=t1.to_user AND t2.msg_id=t1.ID $where" );

        $this->set_pagination_args( [
            'total_items' => $total,
            'per_page'    => 50
        ] );
        $this->items = $results;
    }

    function get_sql_where(){
        $where = "WHERE t1.from_user='0'";
        $where .= " AND t1.type='notice'";
        return $where;
    }

    function get_columns(){
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'time' => __('Time', 'wpcom'),
            'to_user' => __('To', 'wpcom'),
            'title' => __('Title', 'wpcom'),
            'content' => _x('Content', 'notice', 'wpcom'),
            'status' => __('Status', 'wpcom'),
        );
        return $columns;
    }

    public function process_bulk_action() {
        global $wpdb;
        if ( 'delete-notification' === $this->current_action() ) {
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );
            if ( wp_verify_nonce( $nonce, 'bulk-notifications' ) ) {
                $ids = isset($_REQUEST['check']) ? $_REQUEST['check'] : array();
                if(!empty($ids)) {
                    $ids = implode( ',', array_map( 'absint', $ids ) );
                    $wpdb->query("DELETE FROM $this->table WHERE ID IN($ids)");
                }
            }else if(isset($_GET['id']) && $_GET['id']){
                $nonce = esc_attr( $_REQUEST['_wpnonce'] );
                if ( wp_verify_nonce( $nonce, 'delete-notification_'.$_GET['id'] ) ) {
                    $wpdb->delete($this->table, array('ID' => $_GET['id']));
                }
            }
        }
    }

    protected function get_bulk_actions() {
        $actions           = array();
        $actions['delete-notification'] = __( 'Delete' );
        return $actions;
    }
    protected function get_sortable_columns() {
        return array(
            'time' => 'time',
            'to_user' => 'to_user',
            'status' => 'status',
        );
    }
    protected function get_default_primary_column_name() {
        return 'time';
    }
    public function column_cb( $message ) { ?>
        <label class="screen-reader-text" for="cb-select-<?php echo $message->ID; ?>"> </label>
        <input type="checkbox" name="check[]" id="cb-select-<?php echo $message->ID; ?>" value="<?php echo esc_attr( $message->ID ); ?>" />
        <?php
    }
    public function column_to_user( $message ) {
        $display_name = '';
        if($message->to_user=='0'){
            $display_name = '全体用户';
        }else if(is_numeric($message->to_user) && $message->to_user<0){
            $to = 0-$message->to_user;
            $term = get_term( $to, 'user-groups' );
            if(is_wp_error($term)){
                $display_name = '-';
            }else{
                $display_name = '分组群发：' . $term->name;
            }
        }else if(!is_numeric($message->to_user) && $message->to_user){
            global $wp_roles;
            $role_name = isset($wp_roles->roles[$message->to_user]) ? $wp_roles->roles[$message->to_user]['name'] : '';
            $display_name = $role_name ? '角色群发：' . translate_user_role( $role_name ) : '-';
        }else{
            $user = get_user_by('ID', $message->to_user);
            if(!is_wp_error($user)) $display_name = $user->display_name;
        }
        echo $display_name;
    }
    public function column_title( $message ) {
        echo $message->title;
    }
    public function column_content( $message ) {
        echo $message->content;
    }
    public function column_time( $message ) {
        echo get_date_from_gmt($message->time, 'Y-m-d H:i:s');
    }
    public function column_status( $message ) {
        if(is_numeric($message->to_user) && $message->to_user > 0){
            echo $message->status ? __('已读', 'wpcom') : __('未读', 'wpcom');
        }else{
            echo  '*群发通知*';
        }
    }
    protected function handle_row_actions( $message, $column_name, $primary ) {
        if ( $primary !== $column_name ) return '';

        $actions           = array();
        $actions['delete'] = sprintf(
            '<a class="submitdelete" href="%s" onclick="return confirm( \'%s\' );">%s</a>',
            wp_nonce_url( "?page=wpcom-notifications&action=delete-notification&id=$message->ID", 'delete-notification_' . $message->ID ),
            esc_js( sprintf( __( "You are about to delete this notification\n  'Cancel' to stop, 'OK' to delete.", 'wpcom' ), $message->ID ) ),
            __( 'Delete' )
        );

        return $this->row_actions( $actions );
    }
}