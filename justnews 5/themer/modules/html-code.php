<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_Module_html_code extends WPCOM_Module {
    function __construct() {
        $options = array(
            array(
                'tab-name' => '常规设置',
                'code' => array(
                    'name' => 'HTML代码',
                    'type' => 'ta',
                    'rows' => 20,
                    'code' => ''
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
                    'value'  => apply_filters('module_default_margin_value', '20px')
                )
            )
        );
        parent::__construct( 'html-code', 'HTML代码', $options, 'mti:code', '/themer/mod-html-code.png' );
    }

    function template($atts, $depth) {
        echo $this->value('code');
    }
}

register_module( 'WPCOM_Module_html_code' );