<?php
class WPCOM_Module_links extends WPCOM_Module {
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
                    'name' => '更多链接',
                    'type' => 'url'
                ),
                'cat' => array(
                    'name' => '链接分类',
                    'desc' => '请选择链接分类，不选择则显示所有公开链接',
                    'type' => 'cat-single',
                    'tax' => 'link_category'
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
        parent::__construct('links', '友情链接', $options, 'mti:link', '/justnews/mod-links.png');
    }

    function template( $atts, $depth ){
        $bookmarks = get_bookmarks(array('limit' => -1, 'category' => $this->value('cat'), 'category_name' => '', 'hide_invisible' => 1, 'show_updated' => 0, 'orderby' => 'rating' ));?>
        <div class="sec-panel">
            <?php if($this->value('title')){ ?>
                <div class="sec-panel-head">
                    <h3>
                        <span><?php echo $this->value('title');?></span> <small><?php echo $this->value('sub-title');?></small>
                        <?php if($this->value('more-url') && $this->value('more-title')){ ?><a class="more" <?php echo WPCOM::url($this->value('more-url'));?>><?php echo $this->value('more-title');?></a><?php } ?>
                    </h3>
                </div>
            <?php } ?>
            <div class="sec-panel-body">
                <div class="list list-links">
                    <?php foreach($bookmarks as $link){ if($link->link_visible=='Y'){ ?>
                        <a <?php if($link->link_target){?>target="<?php echo $link->link_target;?>" <?php } ?><?php if($link->link_description){?>title="<?php echo esc_attr($link->link_description);?>" <?php } ?>href="<?php echo $link->link_url?>"<?php if($link->link_rel){?> rel="<?php echo $link->link_rel;?>"<?php } ?>><?php echo $link->link_name?></a>
                    <?php }} ?>
                </div>
            </div>
        </div>
    <?php }
}
register_module( 'WPCOM_Module_links' );