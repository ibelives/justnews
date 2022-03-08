<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_Module_image extends WPCOM_Module{
    function __construct(){
        $options = array(
            array(
                'tab-name' => '常规设置',
                'image' => array(
                    'name' => '图片',
                    'type' => 'u',
                    'desc' => '图片将会根据模块宽度100%显示'
                ),
                'alt' => array(
                    'name' => '替代文本',
                    'desc' => '可选，图片alt属性，图片替代文本，利于SEO'
                ),
                'url' => array(
                    'name' => '链接地址',
                    'type' => 'url',
                    'desc' => '可选'
                )
            ),
            array(
                'tab-name' => '风格样式',
                'align' => array(
                    'name' => '图片对齐',
                    'type' => 'r',
                    'ux' => 1,
                    'value' => 'center',
                    'o' => array(
                        'left' => '<i class="material-icons">format_align_left</i>',
                        'center' => '<i class="material-icons">format_align_center</i>',
                        'right' => '<i class="material-icons">format_align_right</i>',
                        'justify' => '<i class="material-icons">format_align_justify</i>'
                    )
                ),
                'width' => array(
                    'name' => '显示宽度',
                    'type' => 'length',
                    'mobile' => 1,
                    'filter' => 'align:left,align:center,align:right',
                    'units' => 'px, %'
                ),
                'radius' => array(
                    'name' => '圆角',
                    'type' => 'length',
                    'mobile' => 1,
                    'units' => 'px, %'
                ),
                'margin' => array(
                    'name' => '外边距',
                    'type' => 'trbl',
                    'use' => 'tb',
                    'mobile' => 1,
                    'desc' => '和上下模块/元素的间距',
                    'units' => 'px, %',
                    'value'  => apply_filters('module_default_margin_value', '20px')
                )
            )
        );
        parent::__construct( 'image', '图片', $options, 'mti:panorama', '/themer/mod-image.png' );
    }

    function style($atts){
        return array(
            'radius' => array(
                '.modules-image-inner > img, .modules-image-inner > a' => 'border-radius: {{value}}; overflow: hidden;'
            ),
            'width' => array(
                '.modules-image-inner > img, .modules-image-inner > a' => 'width: {{value}};'
            )
        );
    }

    function template($atts, $depth){ ?>
        <div class="modules-image-inner image-align-<?php echo $this->value('align');?>">
            <?php if($url = $this->value('url')){
                if($this->value('target')=='1' && !preg_match('/, /i', $url)){
                    $url .= ', _blank';
                } ?>
                <a <?php echo WPCOM::url($url);?>><?php echo wpcom_lazyimg($this->value('image'), $this->value('alt')); ?></a>
            <?php } else { ?>
                <?php echo wpcom_lazyimg($this->value('image'), $this->value('alt')); ?>
            <?php } ?>
        </div>
    <?php }
}

register_module( 'WPCOM_Module_image' );