<?php
class WPCOM_Module_navs extends WPCOM_Module {
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
                'cols' => array(
                    'name' => '每行显示',
                    'type' => 'r',
                    'ux' => 1,
                    'value'  => '4',
                    'o' => array(
                        '3' => '3个',
                        '4' => '4个',
                        '5' => '5个',
                        '6' => '6个'
                    )
                ),
                'links' => array(
                    'type' => 'rp',
                    'name' => '链接',
                    'o' => array(
                        'title' => array(
                            'name' => '标题'
                        ),
                        'url' => array(
                            'type' => 'url',
                            'name' => '链接'
                        ),
                        'desc' => array(
                            'name' => '简介',
                            'type' => 'ta',
                            'rows' => 2
                        ),
                        'img' => array(
                            'name' => 'Logo',
                            'type' => 'u',
                            'desc' => 'LOGO图片比例为1:1'
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
        parent::__construct('navs', '导航链接', $options, 'link', '/justnews/mod-navs.png');
    }

    function template( $atts, $depth ){
        $target = $this->value('target')=='1'; ?>
        <div class="sec-panel">
            <?php if( $this->value('title') ){ ?>
            <div class="sec-panel-head">
                <h3><span><?php echo $this->value('title');?></span> <small><?php echo $this->value('sub-title');?></small></h3>
            </div>
            <?php } ?>
            <div class="sec-panel-body">
                <div class="list list-navs list-navs-cols-<?php echo $this->value('cols');?>">
                <?php if( $this->value('links') ){ foreach($this->value('links') as $links){
                    $url = $links['url'];
                    if($target=='1' && !preg_match('/, /i', $url)){
                        $url .= ', _blank';
                    }?>
                    <a class="navs-link" <?php echo WPCOM::url($url);?>>
                        <?php if($links['img']){ ?>
                        <div class="navs-link-logo">
                            <img src="<?php echo esc_url($links['img']);?>" alt="<?php echo esc_attr($links['title']);?>">
                        </div><?php } ?>
                        <div class="navs-link-info"<?php if(!$links['img']){echo ' style="padding-left:0;"';}?>>
                            <h3><?php echo $links['title'];?></h3>
                            <p><?php echo $links['desc'];?></p>
                        </div>
                    </a>
                <?php } } ?>
                </div>
            </div>
        </div>
    <?php }
}
register_module( 'WPCOM_Module_navs' );