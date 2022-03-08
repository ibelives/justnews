<?php
defined( 'ABSPATH' ) || exit;
class WPCOM_Module_Button extends WPCOM_Module{
    function __construct(){
        $options = array(
            array(
                'tab-name' => '常规设置',
                'btns' => array(
                    't' => 'rp',
                    'name' => '按钮',
                    'options' => array(
                        'text' => array(
                            'name' => '按钮文本'
                        ),
                        'url' => array(
                            'name' => '按钮链接',
                            'type' => 'url'
                        ),
                        'icon' => array(
                            'name' => '图标',
                            'type' => 'icon'
                        ),
                        'icon-style' => array(
                            'name' => '图标位置',
                            'f' => 'icon:!!!',
                            'type' => 'r',
                            'ux' => 1,
                            'value'  => '0',
                            'options' => array(
                                '0' => '显示在左边',
                                '1' => '显示在右边'
                            )
                        ),
                        'style' => array(
                            'name' => '样式',
                            'type' => 'r',
                            'ux' => 1,
                            'vaule' => '0',
                            'desc' => '选择边框的话边框颜色与按钮文字颜色一致',
                            'o' => array(
                                '0' => '填充',
                                '1' => '边框'
                            )
                        ),
                        'bg-color' => array(
                            'name' => '背景颜色',
                            'type' => 'c',
                            'gradient' => 1
                        ),
                        'text-color' => array(
                            'name' => '文字颜色',
                            'type' => 'c'
                        ),
                        'hover-bg-color' => array(
                            'name' => '悬停背景颜色',
                            'type' => 'c',
                            'gradient' => 1
                        ),
                        'hover-text-color' => array(
                            'name' => '悬停文字颜色',
                            'type' => 'c'
                        )
                    )
                )
            ),
            array(
                'tab-name' => '风格样式',
                'size' => array(
                    'name' => '大小',
                    'type'  => 'r',
                    'ux' => 1,
                    'value'   => 'lg',
                    'mobile' => 1,
                    'options' => array(
                        'xs' => '特小号',
                        'sm' => '小号',
                        'normal' => '正常',
                        'lg' => '大号',
                        'xl' => '特大号'
                    )
                ),
                'radius' => array(
                    'name' => '圆角半径',
                    'type' => 'l',
                    'value'  => '3px',
                    'mobile' => 1,
                    'desc' => '按钮的圆角半径，如不需要圆角可设置为0'
                ),
                'block' => array(
                    'name' => '全宽',
                    'type' => 'toggle',
                    'mobile' => 1,
                    'value' => '0'
                ),
                'align' => array(
                    'name' => '对齐',
                    'f' => 'block:0',
                    'v-show' => 1,
                    'type' => 'r',
                    'ux' => 1,
                    'mobile' => 1,
                    'value' => 'center',
                    'options' => array(
                        'left' => '<i class="material-icons">format_align_left</i>',
                        'center' => '<i class="material-icons">format_align_center</i>',
                        'right' => '<i class="material-icons">format_align_right</i>'
                    )
                ),
                'spacing' => array(
                    't' => 'l',
                    'name' => '按钮间距',
                    'value' => '20px',
                    'mobile' => 1,
                    'desc' => '适用于有多个按钮的时候按钮上下左右之间的间距'
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
        parent::__construct('button', '按钮', $options, 'mti:crop_7_5', '/themer/mod-button.png');
    }
    function style($atts) {
        $style = array(
            'spacing' => array(
                '.modules-button-inner' => 'margin-top: calc(-{{value}} / 2);margin-bottom: calc(-{{value}} / 2);margin-left: calc(-{{value}} / 2);margin-right: calc(-{{value}} / 2);',
                '.btn' => 'margin-top: calc({{value}} / 2);margin-bottom: calc({{value}} / 2);margin-left: calc({{value}} / 2);margin-right: calc({{value}} / 2);'
            ),
            'radius' => array(
                '.btn' => 'border-radius: {{value}};'
            ),
            'align' => array(
                '.modules-button-inner' => 'text-align: {{value}};'
            ),
            'block' => array(
                '.btn' => $this->value('block') ? 'display: block;' : 'display: inline-block;',
            ),
            'block_mobile' => array(
                '@[(max-width: 767px)] .btn' => $this->value('block_mobile') ? 'display: block;' : 'display: inline-block;'
            )
        );
        $btns = $this->value('btns');
        if($btns){ foreach($btns as $i => $btn){
            $style['btns.'.$i.'.bg-color'] = array(
                '.btn-'.$i => WPCOM::gradient_color($btn['bg-color'])
            );
            $style['btns.'.$i.'.text-color'] = array(
                '.btn-'.$i => '{{color}};'
            );
            $style['btns.'.$i.'.hover-bg-color'] = array(
                '.btn-'.$i.':hover' => 'background-image: none;' . WPCOM::gradient_color($btn['hover-bg-color'])
            );
            $style['btns.'.$i.'.hover-text-color'] = array(
                '.btn-'.$i.':hover' => '{{color}};'
            );
            $style['btns.'.$i.'.style'] = array(
                '.btn-'.$i => 'border-color:' . $btn['text-color'] . ';',
                '.btn-'.$i.':hover' => 'border-color:' . $btn['hover-text-color'] . ';',
            );
        }}
        return $style;
    }
    function template( $atts, $depth ){
        $_classes = 'btn';
        if($this->value('round')) $_classes .= ' btn-round';
        if($this->value('size')) $_classes .= ' btn-' . $this->value('size');
        if($this->value('size_mobile')) $_classes .= ' btn-m-' . $this->value('size_mobile');
        $btns = $this->value('btns'); ?>

        <div class="modules-button-inner">
            <?php if($btns){ foreach ($btns as $i => $btn){
                $classes = $_classes . ($btn['bg-color'] ? ' btn-primary' : '');
                $classes .= isset($btn['style']) && $btn['style'] == '1' ? ' btn-border' : '';
                $classes .= isset($btn['icon-style']) && $btn['icon-style'] !== '' ? ' btn-icon-'.$btn['icon-style'] : '';
                $classes .= ' btn-' . $i;
                $btn['url'] = $btn['url'] ? $btn['url'] : '#';
                ?>
                <a class="<?php echo esc_attr($classes);?>" <?php echo WPCOM::url($btn['url']); ?>>
                    <?php if($btn['icon'] && !$btn['icon-style']) WPCOM::icon($btn['icon'], true, 'btn-icon');
                    echo $btn['text'];
                    if($btn['icon'] && $btn['icon-style']) WPCOM::icon($btn['icon'], true, 'btn-icon');?>
                </a>
            <?php }} ?>
        </div>
    <?php }
}
register_module( 'WPCOM_Module_Button' );