<?php
defined( 'ABSPATH' ) || exit;

class WPCOM_Module_rev_slider extends WPCOM_Module{
    function __construct(){
        $options = array(
            array(
                'tab-name' => '常规设置',
                'alias' => array(
                    'name' => '选择滑块',
                    'type' => 's',
                    'value'  => 'home',
                    'o' => WPCOM::get_all_sliders()
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
        parent::__construct( 'rev_slider', 'Slider Revolution', $options, 'desktop' );
    }

    function template($atts, $depth){
        if($atts['alias']) {
            echo do_shortcode('[rev_slider alias="' . $atts['alias'] . '"]');
        }
    }
}

if(shortcode_exists("rev_slider")) register_module( 'WPCOM_Module_rev_slider' );