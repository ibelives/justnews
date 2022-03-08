<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_Module_gutenberg extends WPCOM_Module{
    function __construct(){
        $options = array(
            array(
                'tab-name' => '常规设置',
                'content' => array(
                    'name' => '内容',
                    'type' => 'gutenberg',
                    'desc' => '点击打开 Gutenberg 区块编辑器来编辑内容'
                )
            ),
            array(
                'tab-name' => '风格样式',
                'text-indent' => array(
                    'name' => '段落缩进',
                    'type' => 't'
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
        parent::__construct( 'gutenberg', '区块编辑器', $options, 'mti:border_color' );
    }
    function style($atts){
        $style = array(
            'text-indent' => array(
                ' > p' => $this->value('text-indent') ? 'text-indent: 2em;' : ''
            )
        );
        return $style;
    }
    function template($atts, $depth){
        echo do_shortcode(do_blocks($this->value('content')));
    }
}

register_module( 'WPCOM_Module_gutenberg' );