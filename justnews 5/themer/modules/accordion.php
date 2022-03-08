<?php
defined( 'ABSPATH' ) || exit;
class WPCOM_Module_accordion extends WPCOM_Module {
    function __construct() {
        $options = array(
            array(
                'tab-name' => '常规设置',
                'title' => array(
                    'name' => '模块标题'
                ),
                'sub-title' => array(
                  'name' => '模块副标题'
                ),
                'shoufengqin' => array(
                    'type' => 'repeat',
                    'name' => '手风琴',
                    'items' => array(
                        'title' => array(
                            'name' => '标题'
                        ),
                        'content' => array(
                            'name' => '内容',
                            'type' => 'e',
                            'mini' => 1
                        )
                    )
                ),
                'unfold-toggle' => array(
                    'type' => 'toggle',
                    'name' => '是否允许多个展开'
                )
            ),
            array(
                'tab-name' => '风格样式',
                'color' => array(
                    'name' => '模块标题颜色',
                    'type' => 'color'
                ),
                'title-color' => array(
                    'name' => '选项标题颜色',
                    'type' => 'color',
                    'desc' => '选项标题部分的文字颜色'
                ),
                'content-color' => array(
                    'name' => '选项内容颜色',
                    'type' => 'color',
                    'desc' => '选项内容部分的文字颜色'
                ),
                'style' => array(
                    'name' => '显示风格',
                    'type' => 'radio',
                    'ux' => 1,
                    'value' => '0',
                    'o' => array(
                        '0' => '默认风格',
                        '1' => '有背景色'
                    )
                ),
                'bg-color' => array(
                    'name' => '选项背景色',
                    'type' => 'color',
                    'f' => 'style:1'
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
        if(!function_exists('wpcom_module_title')){
            unset($options[0]['title']);
            unset($options[0]['sub-title']);
            unset($options[1]['color']);
        }
        parent::__construct('accordion', '手风琴', $options, 'mti:horizontal_split', '/themer/mod-accordion.png');
    }
    function style( $atts ){
        return array(
            'color' => array(
                ',.sec-title' => '{{color}};',
                '.sec-title-wrap:after, .sec-title-wrap:before' => '{{background-color}};'
            ),
            'title-color' => array(
                '.panel-title' => '{{color}};'
            ),
            'content-color' => array(
                '.panel-group' => '{{color}};',
            ),
            'bg-color' => array(
                '.accordion-style-2 .panel' => '{{background-color}};'
            )
        );
    }
    function template( $atts, $depth ){
        $style = $this->value('style') ? ' accordion-style-2' : '';
        if(function_exists('wpcom_module_title')){
            wpcom_module_title($this->value('title'), $this->value('sub-title'));
        } ?>
        <div class="wp-block-wpcom-accordion panel-group<?php echo $style;?>" id="accordion-<?php echo $this->value('modules-id');?>" role="tablist" aria-multiselectable="true">
            <?php if($this->value('shoufengqin')){ foreach($this->value('shoufengqin') as $index => $item) {; ?>
            <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="heading-<?php echo $this->value('modules-id');?>-<?php echo $index; ?>">
                    <h4 class="panel-title">
                        <a role="button" data-toggle="collapse" data-parent="<?php echo $this->value('unfold-toggle')?  '' : '#accordion-'.$this->value('modules-id') ;?>"
                        href="#accordion-<?php echo $this->value('modules-id');?>-<?php echo $index; ?>"
                        aria-expanded="<?php echo !$index ?>" aria-controls="accordion-<?php echo $this->value('modules-id');?>-<?php echo $index; ?>" class="collapsed">
                            <?php echo $item ['title'] ; ?>
                        </a>
                    </h4>
                </div>
                <div id="accordion-<?php echo $this->value('modules-id');?>-<?php echo $index; ?>" class="panel-collapse collapse <?php echo $index ? '' : 'in' ?>" role="tabpanel" aria-labelledby="heading-<?php echo $this->value('modules-id');?>-<?php echo $index; ?>">
                    <div class="panel-body">
                        <?php echo wpautop($item['content']); ?>
                    </div>
                </div>
            </div>
            <?php }}; ?>
        </div>
    <?php }

}

register_module( 'WPCOM_Module_accordion' );