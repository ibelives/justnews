<?php
/**
 * Navigation Menu API: WPCOM_Nav_Walker_Edit class
 *
 * @package WordPress
 * @subpackage Administration
 * @since 4.4.0
 */
defined( 'ABSPATH' ) || exit;

/**
 * Create HTML list of nav menu input items.
 *
 * @since 3.0.0
 *
 * @see Walker_Nav_Menu
 */
class WPCOM_Nav_Walker_Edit extends Walker_Nav_Menu {
	/**
	 * Starts the list before the elements are added.
	 *
	 * @see Walker_Nav_Menu::start_lvl()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   Not used.
	 */
	public function start_lvl( &$output, $depth = 0, $args = array() ) {}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @see Walker_Nav_Menu::end_lvl()
	 *
	 * @since 3.0.0
	 *
	 * @param string $output Passed by reference.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   Not used.
	 */
	public function end_lvl( &$output, $depth = 0, $args = array() ) {}

	/**
	 * Start the element output.
	 *
	 * @see Walker_Nav_Menu::start_el()
	 * @since 3.0.0
	 *
	 * @global int $_wp_nav_menu_max_depth
	 *
	 * @param string $output Used to append additional content (passed by reference).
	 * @param object $item   Menu item data object.
	 * @param int    $depth  Depth of menu item. Used for padding.
	 * @param array  $args   Not used.
	 * @param int    $id     Not used.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
		global $_wp_nav_menu_max_depth, $wp_version;
		$_wp_nav_menu_max_depth = $depth > $_wp_nav_menu_max_depth ? $depth : $_wp_nav_menu_max_depth;

		ob_start();
		$item_id = esc_attr( $item->ID );
		$removed_args = array(
			'action',
			'customlink-tab',
			'edit-menu-item',
			'menu-item',
			'page-tab',
			'_wpnonce',
		);

		$original_title = false;
		if ( 'taxonomy' == $item->type ) {
			$original_title = get_term_field( 'name', $item->object_id, $item->object, 'raw' );
			if ( is_wp_error( $original_title ) )
				$original_title = false;
		} elseif ( 'post_type' == $item->type ) {
			$original_object = get_post( $item->object_id );
			$original_title = get_the_title( $original_object->ID );
		} elseif ( 'post_type_archive' == $item->type ) {
			$original_object = get_post_type_object( $item->object );
			if ( $original_object ) {
				$original_title = $original_object->labels->archives;
			}
		}

		$classes = array(
			'menu-item menu-item-depth-' . $depth,
			'menu-item-' . esc_attr( $item->object ),
			'menu-item-edit-' . ( ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? 'active' : 'inactive'),
		);

		$title = $item->title;

		if ( ! empty( $item->_invalid ) ) {
			$classes[] = 'menu-item-invalid';
			/* translators: %s: title of menu item which is invalid */
			$title = sprintf( __( '%s (Invalid)' ), $item->title );
		} elseif ( isset( $item->post_status ) && 'draft' == $item->post_status ) {
			$classes[] = 'pending';
			/* translators: %s: title of menu item in draft status */
			$title = sprintf( __('%s (Pending)'), $item->title );
		}

		$title = ( ! isset( $item->label ) || '' == $item->label ) ? $title : $item->label;

		$submenu_text = '';
		if ( 0 == $depth )
			$submenu_text = 'style="display: none;"';
		?>
		<li id="menu-item-<?php echo $item_id; ?>" class="<?php echo implode(' ', $classes ); ?>">
			<div class="menu-item-bar">
				<div class="menu-item-handle">
					<span class="item-title">
                        <?php if(version_compare($wp_version,'5.8', '>=')){ ?><input id="menu-item-checkbox-<?php echo $item_id; ?>" type="checkbox" class="menu-item-checkbox" data-menu-item-id="<?php echo $item_id; ?>" disabled="disabled" /><?php } ?>
                        <span class="menu-item-title"><?php echo esc_html( $title ); ?></span>
                        <span class="is-submenu" <?php echo $submenu_text; ?>><?php _e( 'sub item' ); ?></span></span>
					<span class="item-controls">
						<span class="item-type"><?php echo esc_html( $item->type_label ); ?></span>
						<span class="item-order hide-if-js">
							<a href="<?php
								echo wp_nonce_url(
									add_query_arg(
										array(
											'action' => 'move-up-menu-item',
											'menu-item' => $item_id,
										),
										remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
									),
									'move-menu_item'
								);
							?>" class="item-move-up" aria-label="<?php esc_attr_e( 'Move up' ) ?>">&#8593;</a>
							|
							<a href="<?php
								echo wp_nonce_url(
									add_query_arg(
										array(
											'action' => 'move-down-menu-item',
											'menu-item' => $item_id,
										),
										remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
									),
									'move-menu_item'
								);
							?>" class="item-move-down" aria-label="<?php esc_attr_e( 'Move down' ) ?>">&#8595;</a>
						</span>
						<a class="item-edit" id="edit-<?php echo $item_id; ?>" href="<?php
							echo ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? admin_url( 'nav-menus.php' ) : add_query_arg( 'edit-menu-item', $item_id, remove_query_arg( $removed_args, admin_url( 'nav-menus.php#menu-item-settings-' . $item_id ) ) );
						?>" aria-label="<?php esc_attr_e( 'Edit menu item' ); ?>"><span class="screen-reader-text"><?php _e( 'Edit' ); ?></span></a>
					</span>
				</div>
			</div>

			<div class="menu-item-settings wp-clearfix" id="menu-item-settings-<?php echo $item_id; ?>">
                <menu-panel :level="level"><?php echo base64_encode(json_encode($item));?></menu-panel>

                <?php
                /**
                 * Fires just before the move buttons of a nav menu item in the menu editor.
                 *
                 * @since 5.4.0
                 *
                 * @param int      $item_id Menu item ID.
                 * @param WP_Post  $item    Menu item data object.
                 * @param int      $depth   Depth of menu item. Used for padding.
                 * @param stdClass $args    An object of menu item arguments.
                 * @param int      $id      Nav menu ID.
                 */
                do_action( 'wp_nav_menu_item_custom_fields', $item_id, $item, $depth, $args, $id );
                ?>

                <fieldset class="field-move hide-if-no-js description description-wide">
                    <span class="field-move-visual-label" aria-hidden="true"><?php _e( 'Move' ); ?></span>
                    <button type="button" class="button-link menus-move menus-move-up" data-dir="up"><?php _e( 'Up one' ); ?></button>
                    <button type="button" class="button-link menus-move menus-move-down" data-dir="down"><?php _e( 'Down one' ); ?></button>
                    <button type="button" class="button-link menus-move menus-move-left" data-dir="left"></button>
                    <button type="button" class="button-link menus-move menus-move-right" data-dir="right"></button>
                    <button type="button" class="button-link menus-move menus-move-top" data-dir="top"><?php _e( 'To the top' ); ?></button>
                </fieldset>

                <div class="menu-item-actions description-wide submitbox">
                    <?php if ( 'custom' !== $item->type && false !== $original_title ) : ?>
                        <p class="link-to-original">
                            <?php
                            /* translators: %s: Link to menu item's original object. */
                            printf( __( 'Original: %s' ), '<a href="' . esc_attr( $item->url ) . '">' . esc_html( $original_title ) . '</a>' );
                            ?>
                        </p>
                    <?php endif; ?>

                    <?php
                    printf(
                        '<a class="item-delete submitdelete deletion" id="delete-%s" href="%s">%s</a>',
                        $item_id,
                        wp_nonce_url(
                            add_query_arg(
                                array(
                                    'action'    => 'delete-menu-item',
                                    'menu-item' => $item_id,
                                ),
                                admin_url( 'nav-menus.php' )
                            ),
                            'delete-menu_item_' . $item_id
                        ),
                        __( 'Remove' )
                    );
                    ?>
                    <span class="meta-sep hide-if-no-js"> | </span>
                    <?php
                    printf(
                        '<a class="item-cancel submitcancel hide-if-no-js" id="cancel-%s" href="%s#menu-item-settings-%s">%s</a>',
                        $item_id,
                        esc_url(
                            add_query_arg(
                                array(
                                    'edit-menu-item' => $item_id,
                                    'cancel'         => time(),
                                ),
                                admin_url( 'nav-menus.php' )
                            )
                        ),
                        $item_id,
                        __( 'Cancel' )
                    );
                    ?>
                </div>

				<input class="menu-item-data-db-id" type="hidden" name="menu-item-db-id[<?php echo $item_id; ?>]" value="<?php echo $item_id; ?>" />
				<input class="menu-item-data-object-id" type="hidden" name="menu-item-object-id[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->object_id ); ?>" />
				<input class="menu-item-data-object" type="hidden" name="menu-item-object[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->object ); ?>" />
				<input class="menu-item-data-parent-id" type="hidden" name="menu-item-parent-id[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menu_item_parent ); ?>" />
				<input class="menu-item-data-position" type="hidden" name="menu-item-position[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menu_order ); ?>" />
				<input class="menu-item-data-type" type="hidden" name="menu-item-type[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->type ); ?>" />
			</div><!-- .menu-item-settings-->
            <ul class="menu-item-transport"></ul>
		<?php
		$output .= ob_get_clean();
	}
}

add_filter( 'wp_edit_nav_menu_walker', 'wpcom_nav_walke_fun', 10 );
add_action( 'wp_update_nav_menu_item', 'wpcom_update_nav_menu_item', 20, 2 );
function wpcom_nav_walke_fun($walker){
    global $wpcom_panel;
    if($wpcom_panel->get_demo_config()) $walker = 'WPCOM_Nav_Walker_Edit';
    return $walker;
}

function wpcom_update_nav_menu_item( $menu_id, $menu_item_db_id ){
    if ( isset($_POST['menu-item-image']) && is_array( $_POST['menu-item-image']) ) {
        $image = isset($_POST['menu-item-image'][$menu_item_db_id]) ? $_POST['menu-item-image'][$menu_item_db_id] : '';
        update_post_meta( $menu_item_db_id, 'wpcom_image', $image );
    }
    if ( isset($_POST['menu-item-target']) && is_array( $_POST['menu-item-target']) ) {
        $target = isset($_POST['menu-item-target'][$menu_item_db_id]) ? $_POST['menu-item-target'][$menu_item_db_id] : '';
        update_post_meta( $menu_item_db_id, 'target', $target ? '_blank' : '' );
    }
    if ( isset($_POST['menu-item-style']) && is_array( $_POST['menu-item-style']) ) {
        $style = isset($_POST['menu-item-style'][$menu_item_db_id]) ? $_POST['menu-item-style'][$menu_item_db_id] : '';
        update_post_meta( $menu_item_db_id, 'wpcom_style', $style );
    }
}

add_filter( 'wp_setup_nav_menu_item', 'wpcom_setup_nav_menu_item', 20 );
function wpcom_setup_nav_menu_item( $menu_item ){
    global $hook_suffix;
    if ( isset($_POST['menu-item-image']) && isset($_POST['menu-item-image'][$menu_item->ID]) ) {
        $menu_item->image = $_POST['menu-item-image'][$menu_item->ID];
    }else{
        $menu_item->image = get_post_meta( $menu_item->ID, 'wpcom_image', true );
    }
    if ( isset( $_POST['menu-item-target']) && isset($_POST['menu-item-target'][$menu_item->ID]) ) {
        $menu_item->target = $_POST['menu-item-target'][$menu_item->ID];
    }else if($hook_suffix==='nav-menus.php'){
        $menu_item->target = $menu_item->target ? '1' : '';
    }
    if ( isset( $_POST['menu-item-style']) && isset($_POST['menu-item-style'][$menu_item->ID]) ) {
        $menu_item->style = $_POST['menu-item-style'][$menu_item->ID];
    }else{
        $menu_item->style = get_post_meta( $menu_item->ID, 'wpcom_style', true );
    }
    return $menu_item;
}

add_filter( 'wp_nav_menu_args', 'wpcom_nav_menu_args' );
function wpcom_nav_menu_args( $args ){
    if( isset($args['advanced_menu']) && $args['advanced_menu'] ){
        if( isset($args['menu_class']) && $args['menu_class'] ){
            $args['menu_class'] .= ' wpcom-adv-menu';
        }else{
            $args['menu_class'] = 'wpcom-adv-menu';
        }
    }
    return $args;
}

add_action('admin_enqueue_scripts', 'wpcom_menu_panel_scripts');
function wpcom_menu_panel_scripts(){
    global $pagenow;
    if($pagenow === 'nav-menus.php'){
        WPCOM::panel_script();
    }
}

add_action('admin_print_footer_scripts-nav-menus.php', 'wpcom_menu_panel_options');
function wpcom_menu_panel_options(){ ?>
    <script>_panel_options = <?php echo wpcom_init_menu_options();?>;</script>
    <div style="display: none;"><?php wp_editor( 'EDITOR', 'WPCOM-EDITOR', WPCOM::editor_settings(array('textarea_name'=>'EDITOR-NAME')) );?></div>
<?php }

function wpcom_init_menu_options(){
    $settings = array(
        'url' => array(
            'f' => 'object:custom',
            'name' => 'URL'
        ),
        'title' => array(
            'name' => '导航标签'
        ),
        'image' => array(
            'name' => '图标/图片',
            'type' => 'icon',
            'img' => 1
        ),
        'target' => array(
            'name' => '在新标签页中打开链接',
            'type' => 't'
        ),
        'style' => array(
            'f' => 'level:0',
            'name' => '下拉菜单风格',
            'type' => 'r',
            'ux' => 2,
            'o' => array(
                array('' => '默认风格||/themer/menu-style-0.png'),
                array('1' => '高级风格||/themer/menu-style-1.png'),
                array('2' => '图文#图片居左||/themer/menu-style-2.png'),
                array('3' => '图文#图片居上||/themer/menu-style-3.png')
            )
        ),
        'classes' => array(
            'name' => 'CSS类',
            'desc' => '可选，即class属性'
        ),
        'xfn' => array(
            'name' => '链接关系（XFN）',
            'desc' => '可选，rel属性，可设置nofollow'
        )
    );
    $res = array('type' => 'menu');
    $res['ver'] = THEME_VERSION;
    $res['theme-id'] = THEME_ID;
    $res['settings'] = $settings;
    $res['framework_url'] = FRAMEWORK_URI;
    $res['framework_ver'] = FRAMEWORK_VERSION;
    $res['assets_ver'] = defined('ASSETS_VERSION')?ASSETS_VERSION:'';
    $res = apply_filters('wpcom_menu_panel_options', $res);
    return json_encode($res);
}

add_filter('manage_nav-menus_columns', 'wpcom_nav_menus_columns', 20);
function wpcom_nav_menus_columns(){
    return array();
}

/**
 * 默认禁止提交，为按钮添加disabled，前端渲染完成后再移除disabled
 */
add_action('load-nav-menus.php', 'wpcom_menu_btn_replace_start');
function wpcom_menu_btn_replace_start(){
    //开启缓冲
    ob_start("wpcom_menu_btn_replace");
}

add_action('admin_print_footer_scripts-nav-menus.php', 'wpcom_menu_btn_replace_end');
function wpcom_menu_btn_replace_end(){
    // 关闭缓冲
    if (ob_get_level() > 0) ob_end_flush();
}

function wpcom_menu_btn_replace($str){
    $regexp = "/<(input|button)[^<>]+name=\"save_menu\"[^<>]+>/i";
    $str = preg_replace_callback($regexp, "wpcom_menu_btn_replace_callback", $str);
    return $str;
}

function wpcom_menu_btn_replace_callback($matches){
    return preg_replace('/name=\"save_menu\"/i', 'name="save_menu" disabled', $matches[0]);
}