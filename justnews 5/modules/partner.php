<?php
class WPCOM_Module_partner extends WPCOM_Module {
    function __construct(){
        $options = array(
            array(
                'tab-name' => '常规设置',
                'title' => array(
                    'name' => '模块标题'
                ),
                'sub-title' => array(
                    'name' => '副标题'
                ),
                'more-title' => array(
                    'name' => '更多标题'
                ),
                'more-url' => array(
                    'type' => 'url',
                    'name' => '更多链接'
                ),
                'img-cols' => array(
                    'name' => '每行显示',
                    'desc' => '每行显示图片数量',
                    'type' => 's',
                    'value' => 6,
                    'o' => array(
                        '3' => '3张',
                        '4' => '4张',
                        '5' => '5张',
                        '6' => '6张',
                        '7' => '7张',
                        '8' => '8张',
                        '9' => '9张',
                        '10' => '10张'
                    )
                ),
                'from' => array(
                    'type' => 'r',
                    'name' => '内容来源',
                    'value' => '0',
                    'o' => array(
                        '0' => '独立添加',
                        '1' => '使用后台 <b>主题设置>首页设置>合作伙伴</b> 已有项目'
                    )
                ),
                'partners' => array(
                    'type' => 'rp',
                    'filter' => 'from:0',
                    'o' => array(
                        'alt' => array(
                            'name' => '标题',
                            'desc' => '选填，不会显示，会作为图片的alt属性'
                        ),
                        'img' => array(
                            'name' => '图片',
                            'type' => 'u',
                            'desc' => '图片宽度建议和上面设置的图片宽度选项一致，高度统一即可'
                        ),
                        'url' => array(
                            'type' => 'url',
                            'name' => '链接',
                            'desc' => '选填'
                        )
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
        parent::__construct('partner', '合作伙伴', $options, 'mti:group', '/justnews/mod-partner.png');
    }

    function template( $atts, $depth ){
        $from = isset($atts['from']) && $atts['from']=='1' ? 1 : 0;
        if($from==1){
            global $options;
            $atts['partners'] = array();
            $partners = isset($options['pt_img']) && $options['pt_img'] ? $options['pt_img'] : array();
            if($partners && $partners[0]){
                foreach($partners as $x => $pt) {
                    $url = $options['pt_url'] && $options['pt_url'][$x] ? $options['pt_url'][$x] : '';
                    $alt = $options['pt_title'] && $options['pt_title'][$x] ? $options['pt_title'][$x] : '';
                    $atts['partners'][] = array(
                        'url' => $url,
                        'alt' => $alt,
                        'img' => $pt
                    );
                }
            }
        } ?>
        <div class="sec-panel">
            <?php if( $this->value('title') ){ ?>
                <div class="sec-panel-head">
                    <h3>
                        <span><?php echo $this->value('title');?></span> <small><?php echo $this->value('sub-title');?></small>
                        <?php if($this->value('more-url') && $this->value('more-title')){ ?><a class="more" <?php echo WPCOM::url($this->value('more-url'));?>><?php echo $this->value('more-title');?></a><?php } ?>
                    </h3>
                </div>
            <?php } ?>
            <div class="sec-panel-body">
                <ul class="list list-partner">
                    <?php
                    $cols = $this->value('img-cols');
                    $width = floor(10000/$cols)/100;
                    $follow = $this->value('nofollow');
                    foreach($atts['partners'] as $partner){
                        $url = isset($partner['url']) ? $partner['url'] : '';
                        $alt = isset($partner['alt']) ? $partner['alt'] : '';
                        $img = isset($partner['img']) ? $partner['img'] : '';
                        if($follow=='1' && !preg_match('/, /i', $url)){
                            $url .= ', nofollow';
                        }
                        if($img){ ?>
                        <li style="width:<?php echo $width;?>%">
                            <?php if($url){ ?><a title="<?php echo esc_attr($alt);?>" <?php echo WPCOM::url($url);?>><?php } ?>
                            <?php echo wpcom_lazyimg($img, $alt?$alt:$atts['title']);?>
                            <?php if($url){ ?></a><?php } ?>
                        </li>
                    <?php } } ?>
                </ul>
            </div>
        </div>
    <?php }
}
register_module( 'WPCOM_Module_partner' );