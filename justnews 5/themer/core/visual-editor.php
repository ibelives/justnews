<?php

class WPCOM_Visual_Editor{
    private static $_preview;
    function __construct(){
        add_action('admin_init', array($this, 'page_init'), 20);
        add_action('init', array($this, 'frontend_init'));
        add_action('visual_editor_preview_init' , array($this , 'live_preview' ));
        add_action('wpcom_render_page', array($this , 'render_page' ));
        add_action('wp_ajax_wpcom_page_modules', array($this, 'page_modules'));
        add_action('wp_ajax_wpcom_save_module', array($this, 'save_module'));
        add_action('wp_ajax_wpcom_get_module', array($this, 'get_module'));
        add_action('wp_ajax_wpcom_ve_save', array($this, 'save'));
        add_action('admin_bar_menu', array($this, 'admin_bar_item'), 100 );
        add_action('load-post.php', array($this, 'remove_script'), 50 );

        add_filter('show_admin_bar', array($this, 'show_admin_bar'), 100);
        add_filter('admin_title', array($this, 'admin_title'));
        add_filter('wpcom_modules', array($this, 'save_module_options'));
        add_filter('wpcom_reset_module_id', array($this, 'reset_module_id'), 10, 2);
        add_filter('wpcom_exclude_post_metas', array($this, 'exclude_css_meta'));

        add_filter( 'classic_editor_enabled_editors_for_post', array($this, 'gutenberg_check_disabled_regular'), 10, 2 );
        add_filter( 'use_block_editor_for_post_type', array($this, 'gutenberg_check_disabled'), 10, 2 );
    }
    function page_init(){
        $this->init_gutenberg();
        if(!current_user_can('customize') || !$this->is_visual_editor()) return false;
        add_filter('replace_editor', '__return_true');
        add_filter('admin_body_class', array($this, 'admin_body_class'));
        add_action('admin_enqueue_scripts', array($this, 'script_init'));
        add_action('admin_footer', array($this, 'editor_init'));
        add_action('admin_print_footer_scripts', array($this, 'footer_scripts'));
        wp_enqueue_media();
        require_once ABSPATH . 'wp-admin/admin-header.php';
    }

    function admin_body_class($classes){
        $classes .= 'visual-editor';
        return $classes;
    }

    function script_init(){
        wp_enqueue_style('wpcom-visual-editor', FRAMEWORK_URI . "/assets/css/visual-editor.css", false, FRAMEWORK_VERSION, "all");
        wp_enqueue_style('material-icons');
        wp_enqueue_script('wpcom-visual-editor', FRAMEWORK_URI . "/assets/js/visual-editor.js", array('jquery'), FRAMEWORK_VERSION, true);
        WPCOM::panel_script();
    }

    function remove_script(){
        if(current_user_can('customize') && $this->is_visual_editor()){
            wp_dequeue_script( 'plugin-panel' );
            wp_dequeue_style( 'plugin-panel' );
            wp_dequeue_script( 'meta' );
            wp_dequeue_style( 'meta' );
            wp_dequeue_script( 'mobx-meta' );
            wp_dequeue_style( 'mobx-meta' );
        }
    }

    function editor_init(){
        $post_id = isset($_GET['post']) && $_GET['post'] ? $_GET['post'] : null;
        $post = get_post($post_id);
        $url = add_query_arg(array(
            'post_id' => $post->ID,
            'preview' => 'true',
            'visual-editor' => 'true',
            '_nonce' => wp_create_nonce( 'wpcom-ve-preview-' . $post->ID )
        ), set_url_scheme( get_permalink( $post->ID ) )); ?>
        <div class="visual-editor-container">
            <header id="ve-header" class="visual-editor-header clearfix">
                <div class="ve-header-left">
                    <a class="ve-header-item ve-header-close" href="<?php echo get_permalink($post->ID);?>">
                        <i class="material-icons">&#xe5cd;</i>
                    </a>
                    <div class="ve-header-item ve-header-add">
                        <i class="material-icons">&#xe148;</i>
                    </div>
                </div>
                <div class="ve-header-right">
                    <div class="ve-header-item ve-header-pc active">
                        <i class="material-icons">&#xe30b;</i>
                    </div>
                    <div class="ve-header-item ve-header-mobile">
                        <i class="material-icons">&#xe325;</i>
                    </div>
                    <?php if($post->post_type !== 'page_module'){ ?>
                        <div class="ve-header-item ve-header-setting">
                            <i class="material-icons">&#xe8b8;</i>
                        </div>
                    <?php } ?>
                    <div class="ve-header-submit loading">发布</div>
                    <?php $nonce = wp_create_nonce('wpcom-ve-save-' . $post->ID);?>
                    <input type="hidden" id="ve-nonce" value="<?php echo $nonce;?>">
                </div>
                <div id="ve-notice" class="ve-notice active"><span class="ve-notice-icon"><svg class="ve-notice-loading-svg" viewBox="22 22 44 44"><circle class="ve-notice-loading-circle" cx="44" cy="44" r="20.2" fill="none" stroke-width="3.6"></circle></svg></span><span>可视化编辑器加载中</span></div>
            </header>
            <section id="ve-wrapper" class="visual-editor-wrapper">
                <div class="ve-iframe-inner">
                    <iframe class="ve-iframe" id="ve-iframe" src="<?php echo $url;?>"></iframe>
                    <div class="ve-loading"><i class="dashicons-wpcom-logo"></i></div>
                </div>
            </section>
        </div>
    <?php $this->module_panel();}

    function footer_scripts(){
        global $wpcom_panel;
        if ($wpcom_panel && $wpcom_panel->get_demo_config()) {
            echo '<script>var _modules = ' . wp_json_encode($this->modules()) . ';var _page_modules = {};</script>';
        }
    }

    function frontend_init(){
        if(current_user_can('customize') && $this->is_visual_page()){
            do_action('visual_editor_preview_init');
        }else{
            add_filter('wpcom_custom_css', array($this, 'modules_style'), 20 );
            add_action('wpcom_enqueue_cache_style', array($this, 'enqueue_module_script'));
        }
    }

    function live_preview() {
        global $wpcom_panel;
        if( $wpcom_panel && $wpcom_panel->get_demo_config() ) {
            self::$_preview = 1;
            add_filter('get_post_metadata', array($this, 'module_preview_filter' ), 50, 3);
            add_filter('body_class', array($this, 'body_class'));
            add_action('wp_head', array($this, 'modules_style_preview'), 30 );
            add_action('wp_enqueue_scripts', array($this, 'live_preview_script'));
        }
    }

    function live_preview_script(){
        wp_enqueue_style("themer-customizer", FRAMEWORK_URI . "/assets/css/customizer.css", false, FRAMEWORK_VERSION, "all");
        wp_enqueue_style('material-icons');
        wp_enqueue_script('themer-customizer', FRAMEWORK_URI . '/assets/js/customizer.js', array('jquery'), FRAMEWORK_VERSION, true);
    }

    function module_preview_filter($res, $object_id, $meta_key){
        if(isset($_POST['module-datas']) && $_POST['module-datas'] && isset($_GET['post_id']) && $_GET['post_id'] == $object_id){
            if($meta_key === '_page_modules') {
                $_data = base64_decode($_POST['module-datas']);
                $data = $_data ? json_decode($_data, true) : '';
                if ($data) $res = array($data);
            }else if($meta_key === 'wpcom_css' && isset($_POST['css'])) {
                $css = base64_decode($_POST['css']);
                $res = array($css?:'');
            }
        }
        return $res;
    }

    function module_panel(){ ?>
        <div id="wpcom-panel" class="wpcom-module-modal"><module-panel :ready="ready" /></div>
        <div style="display: none;"><?php wp_editor( 'EDITOR', 'WPCOM-EDITOR', WPCOM::editor_settings(array('textarea_name'=>'EDITOR-NAME')) );?></div>
        <script>_panel_options = <?php echo $this->init_panel_options();?>;</script>
    <?php }

    function init_panel_options(){
        global $post;
        $res = array();
        $res['type'] = 'module';
        $res['ver'] = THEME_VERSION;
        $res['theme-id'] = THEME_ID;
        $res['framework_url'] = FRAMEWORK_URI;
        $res['framework_ver'] = FRAMEWORK_VERSION;
        $res['assets_ver'] = defined('ASSETS_VERSION')?ASSETS_VERSION:'';
        $res = apply_filters('wpcom_module_panel_options', $res);
        $res['settings'] = array(
            'title' => $post->post_title,
            'home' => get_option('show_on_front')==='page' && get_option('page_on_front') == $post->ID ? 1 : 0,
            'css' => get_post_meta($post->ID, 'wpcom_css', true)
        );
        return wp_json_encode($res);
    }

    function page_modules(){
        $id = $_POST['id'];
        if($id && current_user_can( 'customize' ) && $modules = get_post_meta($id, '_page_modules', true)){
            if(is_array($modules) && isset($modules['type'])) $modules = array($modules);
            echo json_encode($modules);
        }else{
            echo '[]';
        }
        exit;
    }

    function save_module(){
        $res = array(
            'result' => -1
        );
        if(current_user_can( 'customize' )){
            $title = isset($_POST['title']) ? $_POST['title'] : '';
            $excerpt = isset($_POST['desc']) ? $_POST['desc'] : '';
            $module = isset($_POST['module']) ? $_POST['module'] : '';
            $module = json_decode(stripslashes($module), true);
            $post = array(
                'post_title' => $title,
                'post_excerpt' => $excerpt,
                'post_type' => 'page_module',
                'post_status' => 'publish'
            );
            $post_id = wp_insert_post($post);
            if(!is_wp_error($post_id) && $module){
                $this->save_page_modules($post_id, $module);
                $res['result'] = 0;
                $res['id'] = $post_id;
                $res['title'] = $title;
            }
        }
        echo wp_json_encode($res);
        exit;
    }

    function get_module(){
        $res = array(
            'result' => -1
        );
        if(isset($_POST['id']) && $_POST['id'] && current_user_can( 'customize' )){
            $mds = get_post_meta($_POST['id'], '_page_modules', true);
            $mid = isset($_POST['mid']) && $_POST['mid'] ? $_POST['mid'] : 0;
            if ($mds && is_array($mds)) {
                ob_start();
                if(isset($mds['type'])) $mds = array($mds);
                $data = array();
                foreach($mds as $i => $md){
                    $data[$i] = apply_filters('wpcom_reset_module_id', $md, $mid);
                    do_action( 'wpcom_modules_' . $data[$i]['type'], $data[$i]['settings'], 0);
                }
                $html = ob_get_contents();
                ob_end_clean();
                $res['data'] = $data;
                $res['html'] = $html;
                $res['result'] = 0;
            }
        }
        echo wp_json_encode($res);
        exit;
    }

    function render_page( $modules = null ){
        global $post;
        $render = $modules ?: get_post_meta($post->ID, '_page_modules', true);
        if(!$render) $render = array();
        if(self::$_preview==1) echo '<div class="wpcom-container">';
        if(is_array($render) && count($render)>0) {
            if(isset($render['type'])) $render = array($render);
            foreach ($render as $v) {
                $v['settings']['modules-id'] = $v['id'];
                if($v['type']==='my_module') $v['type'] = 'my-module';
                do_action('wpcom_modules_' . $v['type'], $v['settings'], 0);
            }
        }else{
            echo '<div class="wpcom-inner"></div>';
        }
        if(self::$_preview==1) echo '</div>';
    }

    public function save(){
        $nonce = isset($_POST['nonce']) ? $_POST['nonce']: '';
        $id = isset($_POST['id']) ? $_POST['id'] : '';
        $verify = wp_verify_nonce($nonce, 'wpcom-ve-save-' . $id);
        $res = array(
            'result' => -1,
            'msg' => '保存失败，请重试'
        );
        if(current_user_can('customize') && $verify){
            $data = isset($_POST['data']) ? $_POST['data'] : '';
            $data = json_decode(stripslashes($data), true);
            $settings = isset($_POST['settings']) ? $_POST['settings'] : '';
            $settings = json_decode(stripslashes($settings), true);
            $this->save_page_modules($id, $data);
            if(isset($settings['home'])){
                if($settings['home']) { // 设为首页
                    update_option('show_on_front', 'page');
                    update_option('page_on_front', $id);
                }else if(get_option('show_on_front')==='page' && get_option('page_on_front') == $id){
                    // 不设为首页，需要判断之前是否是首页，是的话则取消
                    update_option('page_on_front', '');
                }
            }
            if(isset($settings['title']) && $settings['title']){
                wp_update_post(array('ID' => $id, 'post_title' => trim($settings['title'])));
            }
            if(isset($settings['css'])){
                update_post_meta($id, 'wpcom_css', $settings['css']);
            }
            $res = array(
                'result' => 0,
                'msg' => '提交发布成功！'
            );
        }
        echo wp_json_encode($res);
        exit;
    }

    public function reset_module_id($module, $mid){
        $module['id'] = $mid . '_' . $module['id'];
        if ($module['settings'] && isset($module['settings']['modules']) && $module['settings']['modules']) {
            foreach ($module['settings']['modules'] as $a => $s) {
                $module['settings']['modules'][$a] = $this->reset_module_id($s, $mid);
            }
        }
        if ($module['settings'] && isset($module['settings']['girds']) && $module['settings']['girds']) {
            foreach ($module['settings']['girds'] as $b => $girds) {
                foreach ($girds as $c => $gird) {
                    $module['settings']['girds'][$b][$c] = $this->reset_module_id($gird, $mid);
                }
            }
        }
        return $module;
    }

    public function enqueue_module_script(){
        global $post;
        if( is_singular() && (is_page_template('page-home.php') || is_singular('page_module')) ) {
            $modules = get_post_meta($post->ID, '_page_modules', true);
            if( !$modules ) $modules = array();
            if(isset($modules['type'])) $modules = array($modules);
        }else if( function_exists('get_default_mods') ){
            $modules = get_default_mods();
        }

        if( isset($modules) && is_array($modules) && $modules ) {
            if ( count($modules) > 0 ) {
                foreach ($modules as $module) $this->loop_module_script($module, 1);
            }
        }
    }

    public function modules_style($css=''){
        global $post;
        if( is_singular() && (is_page_template('page-home.php') || is_singular('page_module')) ) {
            $modules = get_post_meta($post->ID, '_page_modules', true);
            if( !$modules ) $modules = array();
            if(isset($modules['type'])) $modules = array($modules);
        }else if( function_exists('get_default_mods') ){
            $modules = get_default_mods();
        }

        if( isset($modules) && is_array($modules) && $modules ) {
            ob_start();
            if ( count($modules) > 0 ) {
                foreach ($modules as $module) $this->loop_module_script($module);
            }
            $styles = ob_get_contents();
            ob_end_clean();

            if($post && $post->ID) {
                $_css = get_post_meta($post->ID, 'wpcom_css', true);
                if($_css) $styles .= "\r\n" . $_css;
            }
            do_action('wpcom_after_parse_modules_style', $modules);
            if ( $styles != '' ) $css .= $styles;
        }
        return $css;
    }

    public function modules_style_preview(){
        $css = $this->modules_style();
        if($css) echo '<style>'.$css.'</style>';
    }

    private function loop_module_script($module, $onlyjs = 0){
        global $wpcom_modules, $_module_scripts;
        $_module_scripts = $_module_scripts ?: array();
        $module['settings']['modules-id'] = (isset($module['settings']['parent-id']) && $module['settings']['parent-id'] ? $module['settings']['parent-id'].'_' : '') . $module['id'];
        if (isset($wpcom_modules[$module['type']])){
            if(!$onlyjs) $wpcom_modules[$module['type']]->get_style($module['settings']);
            if(!in_array($module['type'], $_module_scripts)){
                $wpcom_modules[$module['type']]->register_script();
                $_module_scripts[] = $module['type'];
            }
        }

        if ($module['settings'] && isset($module['settings']['modules']) && $module['settings']['modules']) {
            foreach ($module['settings']['modules'] as $s) {
                if(isset($module['settings']['parent-id'])) $s['settings']['parent-id'] = $module['settings']['parent-id'];
                $this->loop_module_script($s, $onlyjs);
            }
        }
        if ($module['settings'] && isset($module['settings']['girds']) && $module['settings']['girds']) {
            foreach ($module['settings']['girds'] as $girds) {
                foreach ($girds as $gird) {
                    if(isset($module['settings']['parent-id'])) $gird['settings']['parent-id'] = $module['settings']['parent-id'];
                    $this->loop_module_script($gird, $onlyjs);
                }
            }
        }
        if(($module['type']=='my_module'||$module['type']=='my-module') && isset($module['settings']['mid']) && $module['settings']['mid']){
            $post = get_post($module['settings']['mid']);
            if(isset($post->post_status) && $post->post_status === 'publish') {
                $mds = get_post_meta($post->ID, '_page_modules', true);
                if ($mds && is_array($mds)) {
                    if(isset($mds['type'])) $mds = array($mds);
                    foreach($mds as $md){
                        $md['settings']['parent-id'] = $module['id'];
                        $this->loop_module_script($md, $onlyjs);
                    }
                }
            }
        }
    }

    function show_admin_bar($show){
        if(current_user_can('customize') && $this->is_visual_page()) $show = false;
        return $show;
    }

    function admin_title($title){
        if(current_user_can('customize') && $this->is_visual_editor()){
            $title = sprintf( __( '%1$s &lsaquo; %2$s &#8212; WordPress' ), '可视化编辑', get_bloginfo( 'name' ) );
        }
        return $title;
    }

    function body_class($classes){
        return array_merge( $classes, array( 'visual-editor' ) );
    }

    private function is_visual_page(){
        global $is_visual_page;
        if(isset($is_visual_page) && $is_visual_page) return true;
        $post_id = isset($_GET['post_id']) ? $_GET['post_id'] : '';
        $visual = isset($_GET['visual-editor']) && $_GET['visual-editor'];
        $nonce = isset($_GET['_nonce']) && $_GET['_nonce'] ? $_GET['_nonce'] : '';
        $is_visual_page = $visual && $post_id && wp_verify_nonce($nonce, 'wpcom-ve-preview-'.$post_id) && (get_page_template_slug($post_id) == 'page-home.php' || get_post_type($post_id)==='page_module');
        return $is_visual_page;
    }

    private function is_visual_editor(){
        global $pagenow;
        return $pagenow==='post.php' && isset($_GET['visual-editor']) && $_GET['visual-editor'];
    }

    private function modules(){
        return apply_filters( 'wpcom_modules', new stdClass() );
    }

    private function save_page_modules($id, $data){
        if($data){
            if(version_compare(PHP_VERSION,'5.4.0','<')){
                $data = wp_slash(wp_json_encode($data));
            }else{
                $data = wp_slash(wp_json_encode($data, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE));
            }
            if($data) return update_post_meta($id, '_page_modules', $data);
        }
    }

    function save_module_options($modules){
        $modules->{'save-module'} = array(
            'options' => array(
                'title' => array(
                    'name' => '保存标题'
                ),
                'desc' => array(
                    'name' => '备注信息',
                    't' => 'ta'
                ),
                'type' => array(
                    'name' => '保存方式',
                    'desc' => '<b>两者区别</b>：引用类似电脑的快捷方式，复制则会拷贝一份完全一样的模块；选择引用保存会将当前模块保存起来并替换成该模块的引用，复制则不影响当前模块；引用可以方便后期统一调整，无需每个模块单独编辑',
                    'type' => 'r',
                    'ux' => 1,
                    'value' => '0',
                    'o' => array(
                        '0' => '引用保存',
                        '1' => '复制保存'
                    )
                ),
            )
        );
        $modules->{'page-setting'} = array(
            'options' => array(
                'title' => array(
                    'n' => '页面标题'
                ),
                'home' => array(
                    'n' => '设为首页',
                    'd' => '将当前页面设置为网站首页',
                    't' => 'toggle'
                ),
                'css' => array(
                    'n' => '自定义CSS',
                    't' => 'ta',
                    'd' => '此处添加的CSS代码仅在当前页面显示',
                    'code' => 'css'
                )
            )
        );
        return $modules;
    }

    function exclude_css_meta($metas){
        $metas += array('css');
        return $metas;
    }

    function admin_bar_item() {
        if ( !current_user_can( 'customize' ) ) return;
        global $wp_admin_bar, $post;
        if($post && $post->ID && get_queried_object_id() == $post->ID && get_page_template_slug($post->ID) == 'page-home.php') {
            $editor_url = add_query_arg(array('visual-editor' => 'true'), get_edit_post_link($post->ID));
            $wp_admin_bar->add_menu(array(
                'id' => 've-link',
                'title' => '<span class="ab-icon"><svg viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" style="width:16px;height:16px;" fill="rgba(240,245,250,.6)"><path d="M1013.279 489.694c-0.95-28.585-4.03-56.951-9.918-84.931-21.925-104.164-70.769-193.81-149.27-265.666C730.099 25.604 583.818-16.716 418.535 12.297 304.454 32.32 209.16 88.362 132.89 175.516 59.99 258.823 19.54 356.3 10.434 466.588c-3.906 47.3-0.883 94.31 8.915 140.808C42.28 716.216 95.173 807.94 178.347 881.545c90.304 79.91 196.42 121.4 316.897 126.183l31.602 0.013c26.434-0.854 52.715-3.447 78.634-8.627 122.905-24.566 223.79-85.739 301.287-184.186 67.664-85.954 102.442-184.355 106.523-293.631-0.001-10.535-0.004-21.07-0.011-31.603z m-553.53 114.782a70040.09 70040.09 0 0 1-57.111 59.913c-14.044 14.71-30.733 23.622-51.592 22.278-15.641-1.008-29.554-7.301-42.67-15.519-19.7-12.335-37.046-27.659-53.648-43.708A24485.24 24485.24 0 0 1 102.4 478.88c-34.285-33.723-36.605-77.93-6.999-115.832 18.823-24.099 43.697-35.597 71.17-36.704 22.924 0.324 40.568 6.779 55.422 20.88 33.114 31.44 66.423 62.676 99.65 93.998 3.262 3.073 6.76 5.838 10.655 8.058 11.961 6.815 21 5.738 31.264-3.449 34.752-31.104 68.228-63.58 102.3-95.414 13.897-12.985 28.972-23.38 48.157-26.365 37.053-5.766 66.394 7.114 87.621 37.695 22.734 32.75 18.44 71.23-10.156 102.562-43.233 47.362-87.521 93.731-131.736 140.167z m440.937-138.228c-12.6 19.93-27.502 38.123-43.56 55.248-47.843 51.016-93.632 103.852-139.508 156.605-21.543 24.77-56.224 33.72-89.726 22.109-30.88-10.703-52.958-30.902-64.005-62.07-12.873-36.33-12.562-72.506 2.56-108.206 2.676-6.317 5.905-12.43 11.545-18.332 0 3.18-0.01 5.22 0 7.257 0.048 10.29 5.47 18.419 14.827 22.236 9.826 4.01 19.429 2.28 26.528-5.596 11.563-12.844 22.645-26.117 34.143-39.018 28.686-32.193 57.73-64.069 86.132-96.51 10.747-12.273 21.183-24.674 33.346-35.684 30.821-27.896 74.582-20.312 101.224-3.188 24.119 15.5 37.49 38.367 38.337 67.426 0.397 13.682-4.56 26.197-11.843 37.723z"></path></svg></span>可视化编辑<style>#wp-admin-bar-ve-link:hover svg{fill:#00b9eb;}</style>',
                'href' => $editor_url
            ));
        }
    }

    function init_gutenberg() {
        global $pagenow, $wp_version;
        if ( version_compare( $wp_version, '5.4.0', '>' ) && ('post.php' === $pagenow || 'post-new.php' === $pagenow)
            && isset($_GET['post_type']) && $_GET['post_type'] === 'gutenberg_content' && current_user_can('customize') ) {
            $labels = array(
                'name' => '区块编辑器',
                'singular_name' => '区块编辑器',
            );
            $args = array(
                'labels' => $labels,
                'public' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => false,
                'query_var' => false,
                'capability_type' => 'page',
                'has_archive' => false,
                'hierarchical' => false,
                'menu_position' => null,
                'show_in_rest' => true,
                'supports' => array( 'editor' ),
            );
            register_post_type( 'gutenberg_content', $args );

            add_action( 'admin_enqueue_scripts', array($this, 'remove_gutenberg_ui') );
            add_action( 'add_meta_boxes', array( $this, 'gutenberg_metabox' ) );
        }
    }
    function remove_gutenberg_ui() {
        $style = '
        #adminmenumain, #wpadminbar, .edit-post-header .edit-post-fullscreen-mode-close, .edit-post-header .editor-post-save-draft, .edit-post-header .block-editor-post-preview__dropdown,.edit-post-header .components-button.editor-post-publish-panel__toggle {
            display: none;
        }
        .edit-post-header .components-button.close-iframe{
            margin-right: 0;
        }
        @media (min-width: 783px){
            .interface-interface-skeleton{
                left: 0;
                top: 0;
            }
        }
        html.wp-toolbar {
            padding: 0 !important;
        }
        .wp-toolbar #wpcontent {
            margin: 0;
        }
        .wp-toolbar #wpbody {
            padding-top: 0;
        }
        .gutenberg .gutenberg__editor .edit-post-layout .edit-post-header, html .block-editor-page .edit-post-header {
            top: 0;
            left: 0;
        }
        .gutenberg .gutenberg__editor .edit-post-layout.is-sidebar-opened .edit-post-layout__content, html .block-editor-page .edit-post-layout.is-sidebar-opened .edit-post-layout__content {
            margin-right: 0;
        }
        .editor-post-title {
            display: none !important;
        }
        ';
        $script = "
        wp.domReady( function() {
            let _loaded = 0, _editor = wp.data.select('core/editor'), _checked = true;
            wp.data.subscribe(function (e) {
                if(!_loaded && _editor.getCurrentPostId()){
                    _loaded = 1;
                    wp.data.dispatch( 'core/edit-post').removeEditorPanel( 'post-link' );
                    wp.data.dispatch( 'core/edit-post').removeEditorPanel( 'post-status' );
                    setTimeout(function(){
                        wp.data.dispatch('core/editor').autosave = function(){}
                        let notices = wp.data.select( 'core/notices' ).getNotices();
                        if(notices && notices.length){
                            for(let x in notices){
                                wp.data.dispatch( 'core/notices' ).removeNotice(notices[x].id);
                            }
                        }
                        jQuery('.edit-post-header__settings').append('<button type=\"button\" class=\"components-button save-content is-primary\">提交</button><button type=\"button\" class=\"components-button close-iframe has-icon\"><svg width=\"24\" height=\"24\" xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 24 24\"><path d=\"M13 11.8l6.1-6.3-1-1-6.1 6.2-6.1-6.2-1 1 6.1 6.3-6.5 6.7 1 1 6.5-6.6 6.5 6.6 1-1z\"></path></svg></button>');
                        
                        parent.postMessage(JSON.parse(JSON.stringify({type:'gutenberg-load'})), '*');
                        jQuery('.edit-post-header').on('click', '.close-iframe', function(){
                            parent.postMessage(JSON.parse(JSON.stringify({type:'gutenberg-close'})), '*');
                            let blocks = wp.blocks.parse( ' ' );
                            if(blocks) wp.data.dispatch( 'core/editor' ).editPost( { blocks: blocks } );
                        }).on('click', '.save-content', function(){
                            let content = _editor.getEditedPostContent();
                            parent.postMessage(JSON.parse(JSON.stringify({type:'gutenberg-save',content: content})), '*');
                            let blocks = wp.blocks.parse( ' ' );
                            if(blocks) wp.data.dispatch( 'core/editor' ).editPost( { blocks: blocks } );
                        });
                    }, 0);
                }
            });
            window.addEventListener('message', function (e) {
                if(e.origin !== window.location.origin) return false;
                if(e.data.type && e.data.type === 'gutenberg-content'){
                    let blocks = wp.blocks.parse( e.data.content );
                    if(blocks) wp.data.dispatch( 'core/editor' ).editPost( { blocks: blocks } );
                }
            }, false);
        });
        ";
        wp_add_inline_style( 'wp-edit-blocks', $style );
        wp_add_inline_script( 'wp-edit-post', $script );
    }

    function gutenberg_check_disabled( $result, $postType ) {
        global $pagenow;
        if ( ('post.php' === $pagenow || 'post-new.php' === $pagenow) && 'gutenberg_content' === $postType) return true;
        return $result;
    }

    function gutenberg_check_disabled_regular( $editors, $postType ) {
        if ( 'gutenberg_content' === $postType ) $editors['gutenberg_editor'] = false;
        return $editors;
    }

    function gutenberg_metabox(){
        add_meta_box('gutenberg-metas', '模块说明', array($this, 'gutenberg_metabox_html'), 'gutenberg_content', 'side', 'high', array());
    }

    function gutenberg_metabox_html(){
        echo '<p>通过此模块可直接调用系统区块编辑器（Gutenberg）来添加区块，为页面便捷引入海量区块资源，丰富页面布局</p>';
    }
}

new WPCOM_Visual_Editor();