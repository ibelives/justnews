<?php
if ( ! class_exists( 'WP_List_Table' ) ) require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';

class WPCOM_Messages_List extends WP_List_Table {
    protected $table;
    public function __construct(){
        global $wpdb;
        $this->table = $wpdb->prefix . 'wpcom_messages';
        parent::__construct([
            'screen' => 'messages'
        ]);
    }

    public function ajax_user_can() {
        return current_user_can( 'manage_options' );
    }

    public function prepare_items() {
        global $wpdb, $orderby, $order;
        wp_reset_vars( array( 'orderby', 'order' ) );

        $this->process_bulk_action();

        $orderby = $orderby ?: 'ID';
        $order = $order ?: 'DESC';

        $paged = $this->get_pagenum();
        $offset = ($paged-1) * 50;

        $search = isset( $_REQUEST['s'] ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';

        $search_sql = $search ? "(content LIKE '%$search%' OR from_user = '$search' OR to_user = '$search') AND" : '';
        $results = $wpdb->get_results( "SELECT * FROM $this->table WHERE $search_sql from_user > 0 ORDER BY $orderby $order limit $offset,50" );
        $total = $wpdb->get_var( "SELECT count(*) FROM $this->table WHERE $search_sql from_user > 0" );

        $this->set_pagination_args( [
            'total_items' => $total,
            'per_page'    => 50
        ] );
        $this->items = $results;
    }

    function get_columns(){
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'time' => __('Time', 'wpcom'),
            'from_user' => __('From', 'wpcom'),
            'to_user' => __('To', 'wpcom'),
            'content' => __('Content', 'wpcom'),
            'status' => __('Status', 'wpcom'),
        );
        return $columns;
    }

    public function process_bulk_action() {
        global $wpdb;
        if ( 'delete-message' === $this->current_action() ) {
            $nonce = esc_attr( $_REQUEST['_wpnonce'] );
            if ( wp_verify_nonce( $nonce, 'bulk-messages' ) ) {
                $ids = isset($_REQUEST['check']) ? $_REQUEST['check'] : array();
                if(!empty($ids)) {
                    $ids = implode( ',', array_map( 'absint', $ids ) );
                    $wpdb->query("DELETE FROM $this->table WHERE ID IN($ids)");
                }
            }else if(isset($_GET['id']) && $_GET['id']){
                $nonce = esc_attr( $_REQUEST['_wpnonce'] );
                if ( wp_verify_nonce( $nonce, 'delete-message_'.$_GET['id'] ) ) {
                    $wpdb->delete($this->table, array('ID' => $_GET['id']));
                }
            }
        }
    }

    protected function get_bulk_actions() {
        $actions           = array();
        $actions['delete-message'] = __( 'Delete' );
        return $actions;
    }
    protected function get_sortable_columns() {
        return array(
            'time' => 'time',
            'from_user' => 'from_user',
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
    public function column_from_user( $message ) {
        $user = get_user_by('ID', $message->from_user);
        printf(
            '<strong><a class="row-title" href="%s" target="_blank">%s</a></strong>',
            get_edit_user_link($user->ID),
            $user->display_name
        );
    }
    public function column_to_user( $message ) {
        $user = get_user_by('ID', $message->to_user);
        printf(
            '<strong><a class="row-title" href="%s" target="_blank">%s</a></strong>',
            get_edit_user_link($user->ID),
            $user->display_name
        );
    }
    public function column_content( $message ) {
        echo convert_smilies($message->content);
    }
    public function column_time( $message ) {
        echo get_date_from_gmt($message->time, 'Y-m-d H:i:s');
    }
    public function column_status( $message ) {
        echo $message->status ? __('Read', 'wpcom') : __('Unread', 'wpcom');
    }
    protected function handle_row_actions( $message, $column_name, $primary ) {
        if ( $primary !== $column_name ) return '';

        $actions           = array();
        $actions['delete'] = sprintf(
            '<a class="submitdelete" href="%s" onclick="return confirm( \'%s\' );">%s</a>',
            wp_nonce_url( "?page=wpcom-messages&action=delete-message&id=$message->ID", 'delete-message_' . $message->ID ),
            esc_js( sprintf( __( "You are about to delete this message\n  'Cancel' to stop, 'OK' to delete.", 'wpcom' ), $message->ID ) ),
            __( 'Delete' )
        );

        return $this->row_actions( $actions );
    }
}