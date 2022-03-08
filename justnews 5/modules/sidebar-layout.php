<?php
class WPCOM_Module_sidebar_layout extends WPCOM_Module{
    function __construct() {
        $sidebar = array('' => ' 默认边栏');
        if(isset($GLOBALS['options']['sidebar_id']) && $GLOBALS['options']['sidebar_id']) {
            foreach ($GLOBALS['options']['sidebar_id'] as $i => $id) {
                if($id && $GLOBALS['options']['sidebar_name'][$i]) {
                    $sidebar[$id] = $GLOBALS['options']['sidebar_name'][$i];
                }
            }
        }

        $options = array(
            array(
                'tab-name' => '常规设置',
                'sidebar' => array(
                    'name' => '显示边栏',
                    'type' => 's',
                    'desc' => '选择需要显示的边栏',
                    'o' => $sidebar
                ),
                'float' => array(
                    'name' => '边栏位置',
                    'type' => 'r',
                    'ux' => 1,
                    'value'  => 'right',
                    'o' => array(
                        'left' => '左边',
                        'right' => '右边',
                    )
                )
            ),
            array(
                'tab-name' => '风格样式',
                'margin' => array(
                    'name' => '外边距',
                    'type' => 'trbl',
                    'use' => 'tb',
                    'mobile' => 1,
                    'desc' => '和上下模块/元素的间距',
                    'units' => 'px, %',
                    'value'  => '20px'
                )
            )
        );
        parent::__construct( 'sidebar-layout', '边栏布局', $options, 'mti:web', '/themer/mod-sidebar-layout.png' );
    }

    function classes( $atts, $depth = 0 ){
        $classes = 'container j-modules-wrap';
        return $classes;
    }

    function style( $atts ){
        return array(
            'float' => array(
                '.main' => 'float: '.($this->value('float')=='left'?'right':'left').';',
                '.sidebar' => 'float: {{value}};'
            )
        );
    }

    function template($atts, $depth){ ?>
        <div class="main j-modules-inner">
            <?php if(isset($atts['modules']) && count($atts['modules'])){ foreach ($atts['modules'] as $module) {
                $module['settings']['modules-id'] = $module['id'];
                $module['settings']['fullwidth'] = isset($atts['fluid']) && $atts['fluid'] ? 0 : 1;
                do_action('wpcom_modules_' . $module['type'], $module['settings'], $depth+1);
            } } ?>
        </div>
        <aside class="sidebar sidebar-on-<?php echo $this->value('float', 'right');?>">
            <?php
            $sidebar = isset($atts['sidebar']) && $atts['sidebar'] ? $atts['sidebar'] : 'primary';
            dynamic_sidebar($sidebar);
            ?>
        </aside>
    <?php }
}

register_module( 'WPCOM_Module_sidebar_layout' );