<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_Module_container extends WPCOM_Module {
    function __construct() {
        $options = array(
            'bg-color' => array(
                'name' => '背景颜色',
                'type' => 'c',
                'gradient' => 1
            ),
            'bg-image' => array(
                'name' => '背景图片',
                'type' => 'u',
                'desc' => '温馨提示：如果设置了背景图片，则背景颜色不支持设置渐变色'
            ),
            'wrap' => array(
                'filter' => 'bg-image:!!!',
                'type' => 'wrapper',
                'o' => array(
                    'bg-image-repeat' => array(
                        'name' => '背景平铺',
                        'type' => 'r',
                        'ux' => 1,
                        'value'  => 'no-repeat',
                        'o' => array(
                            'no-repeat' => '不平铺',
                            'repeat' => '平铺',
                            'repeat-x' => '水平平铺',
                            'repeat-y' => '垂直平铺'
                        )
                    ),
                    'bg-image-size' => array(
                        'name' => '背景铺满',
                        'type' => 'r',
                        'ux' => 1,
                        'f' => 'bg-image-repeat:no-repeat',
                        'desc' => '自动调整背景图片显示',
                        'value'  => '1',
                        'mobile' => 1,
                        'o' => array(
                            '0' => '不使用',
                            '1' => '铺满模块',
                            '2' => '按宽度铺满',
                            '9' => '自定义'
                        )
                    ),
                    'bg-image-size2' => array(
                        'name' => '自定义尺寸',
                        'f' => 'bg-image-size:9',
                        'mobile' => 1,
                        'v-show' => 1,
                        'desc' => '即 background-size 值，非技术人员不推荐此选项'
                    ),
                    'bg-image-position' => array(
                        'name' => '背景位置',
                        'type' => 's',
                        'desc' => '分别为左右对齐方式和上下对齐方式',
                        'value'  => 'center center',
                        'o' => array(
                            'left top' => '左 上',
                            'left center' => '左 中',
                            'left bottom' => '左 下',
                            'center top' => '中 上',
                            'center center' => '中 中',
                            'center bottom' => '中 下',
                            'right top' => '右 上',
                            'right center' => '右 中',
                            'right bottom' => '右 下',
                        )
                    ),
                    'bg-image-shadow' => array(
                        'name' => '背景处理',
                        'type' => 'r',
                        'ux' => 1,
                        'desc' => '优化处理背景图片',
                        'value'  => '0',
                        'o' => array(
                            '0' => '不处理',
                            '1' => '暗化处理',
                            '2' => '亮化处理'
                        )
                    )
                )
            ),
            'radius' => array(
                'name' => '圆角半径',
                'type' => 'l',
                'value'  => '0',
                'mobile' => 1,
                'desc' => '容器的圆角半径，如不需要圆角可设置为0'
            ),
            'mobile-hide' => array(
                'name' => '移动端隐藏',
                'type' => 't',
                'desc' => '开启则在移动端不显示'
            ),
            'margin' => array(
                'name' => '外边距',
                'type' => 'trbl',
                'mobile' => 1,
                'use' => 'tb',
                'desc' => '和上下模块/元素的间距',
                'units' => 'px, %, vw, vh',
                'value'  => apply_filters('module_default_margin_value', '20px')
            ),
            'padding' => array(
                'name' => '内边距',
                'type' => 'trbl',
                'mobile' => 1,
                'desc' => '模块内容区域与边界的距离',
                'units' => 'px, %, vw, vh',
                'value'  => '20px 0'
            )
        );
        add_filter('wpcom_module_container_default_style', array($this, 'default_style'));
        parent::__construct( 'container', '容器模块', $options, 'mti:texture', '/themer/mod-container.png' );
    }

    function default_style($style){
        if($style && isset($style['padding'])) {
            unset($style['padding']);
            unset($style['padding_mobile']);
        }
        return $style;
    }

    function style($atts){
        $bg_img = $this->value('bg-image');
        $bg_image = '';
        $bg_color = WPCOM::gradient_color($this->value('bg-color'));
        if($bg_img && preg_match('/background-image:/i', $bg_color)){
            // 处理渐变色和背景图片问题
            $bg_image = preg_replace('/background-image:/i', 'background-image: url('.$bg_img.'), ', $bg_color);
            $bg_color = '';
        }else if($bg_img){
            $bg_image = 'background-image: url('.$bg_img.');';
        }

        $bg_size = $this->value('bg-image-size');
        if($bg_size=='9'){
            $bg_size = $this->value('bg-image-size2');
        }else if($bg_size){
            $bg_size = $bg_size == '1' ? 'cover' : '100% auto';
        }else if($bg_size==='0'){
            $bg_size = 'auto';
        }

        $bg_size_m = $this->value('bg-image-size_mobile');
        if($bg_size_m=='9'){
            $bg_size_m = $this->value('bg-image-size2_mobile')!=='' ? $this->value('bg-image-size2_mobile') : $this->value('bg-image-size2');
        }else if($bg_size_m){
            $bg_size_m = $bg_size_m == '1' ? 'cover' : '100% auto';
        }else if($bg_size_m==='0'){
            $bg_size_m = 'auto';
        }

        return array(
            'bg-color' => array(
                '.container-inner' => $bg_color
            ),
            'bg-image' => array(
                '.container-inner' => $bg_image
            ),
            'bg-image-shadow' => array(
                '.container-inner' => $this->value('bg-image-shadow') ? 'position: relative;' : ''
            ),
            'bg-image-repeat' => array(
                '.container-inner' => 'background-repeat: {{value}};'
            ),
            'bg-image-size' => array(
                '.container-inner' => $this->value('bg-image-repeat')==='no-repeat' && $bg_size!=='' ? ('background-size: ' . $bg_size . ';') : '',
            ),
            'bg-image-size_mobile' => array(
                '@[(max-width: 767px)] .container-inner' => $this->value('bg-image-repeat')==='no-repeat' && $bg_size_m!=='' ? ('background-size: ' . $bg_size_m . ';') : '',
            ),
            'bg-image-position' => array(
                '.container-inner' => 'background-position: {{value}};'
            ),
            'padding' => array(
                '.container-inner' => 'padding: {{value}};'
            ),
            'radius' => array(
                '.container-inner' =>  'border-radius: {{value}};'
            ),
            'mobile-hide' => array(
                '@[(max-width: 767px)]' =>  'display: none;'
            )
        );
    }

    function template($atts, $depth) { ?>
        <div class="container-inner j-modules-inner">
            <?php
            if($this->value('bg-image-shadow')=='1'){ ?><div class="module-shadow"></div><?php }
            if($this->value('bg-image-shadow')=='2'){ ?><div class="module-shadow module-shadow-white"></div>
            <?php }
            if($this->value('modules')){ foreach ($this->value('modules') as $module) {
                $module['settings']['modules-id'] = $module['id'];
                $module['settings']['parent-id'] = $this->value('modules-id');
                $module['settings']['fullwidth'] = $this->value('fluid') ? 0 : 1;
                do_action('wpcom_modules_' . $module['type'], $module['settings'], $depth+1);
            } } ?>
        </div>
    <?php }
}

register_module( 'WPCOM_Module_container' );