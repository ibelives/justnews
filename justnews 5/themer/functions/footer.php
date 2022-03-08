<?php
defined( 'ABSPATH' ) || exit;

add_action('wp_footer', 'wpcom_footer', 1);
if(!function_exists('wpcom_footer')){
    function wpcom_footer(){
        global $options;
        $show_action = apply_filters('wpcom_show_action_tool', true);
        if($show_action){
            $style = isset($options['action_style']) && $options['action_style'] ? $options['action_style'] : '0';
            $cstyle = isset($options['action_cstyle']) && $options['action_cstyle'] ? $options['action_cstyle'] : '0';
            $pos = isset($options['action_pos']) && $options['action_pos'] ? $options['action_pos'] : '0';
            ?>
            <div class="action action-style-<?php echo $style;?> action-color-<?php echo $cstyle;?> action-pos-<?php echo $pos;?>"<?php echo isset($options['action_bottom'])?' style="bottom:'.$options['action_bottom'].';"':''?>>
                <?php
                if(isset($options['action_icon']) && $options['action_icon']){
                    foreach ($options['action_icon'] as $i => $icon){
                        if($icon){
                            $title = isset($options['action_title']) && isset($options['action_title'][$i]) ? $options['action_title'][$i] : '';
                            $type = isset($options['action_type']) && isset($options['action_type'][$i]) ? $options['action_type'][$i] : '';
                            $target = isset($options['action_target']) && isset($options['action_target'][$i]) ? $options['action_target'][$i] : '';
                            if($type==='0'){ ?>
                                <a class="action-item" <?php echo WPCOM::url($target, false);?>>
                                    <?php WPCOM::icon($icon, true, 'action-item-icon');?>
                                    <?php if($style) echo '<span>'.$title.'</span>';?>
                                </a>
                            <?php }else{ ?>
                                <div class="action-item">
                                    <?php WPCOM::icon($icon, true, 'action-item-icon');?>
                                    <?php if($style) echo '<span>'.$title.'</span>';?>
                                    <div class="action-item-inner action-item-type-<?php echo $type;?>">
                                        <?php if($type==='1') {
                                            echo '<img class="action-item-img" src="'.esc_url($target).'" alt="'.esc_attr($title).'">';
                                        }else{
                                            echo wpautop($target);
                                        }?>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php }
                    }
                } ?>
                <?php if(isset($options['share'])&&$options['share']=='1'){ ?>
                    <div class="action-item j-share">
                        <?php WPCOM::icon('share', true, 'action-item-icon');?>
                        <?php if($style) echo '<span>'.__('SHARE', 'wpcom').'</span>';?>
                    </div>
                <?php }
                if ((isset($options['gotop']) && $options['gotop'] == '1') || !isset($options['gotop'])) { ?>
                    <div class="action-item gotop j-top">
                        <?php WPCOM::icon('arrow-up-2', true, 'action-item-icon');?>
                        <?php if($style) echo '<span>'.__('TOP', 'wpcom').'</span>';?>
                    </div>
                <?php } ?>
            </div>
        <?php }
        if(isset($options['footer_bar_icon']) && is_array($options['footer_bar_icon']) && !empty($options['footer_bar_icon']) && !(count($options['footer_bar_icon'])===1 && current($options['footer_bar_icon'])=='')){?>
            <div class="footer-bar">
                <?php foreach($options['footer_bar_icon'] as $i => $fb){ if($fb){
                    $type = isset($options['footer_bar_type'][$i]) && $options['footer_bar_type'][$i]=='1' ? $options['footer_bar_type'][$i] : '0';
                    $bg = isset($options['footer_bar_bg'][$i]) && $options['footer_bar_bg'][$i] ? ' style="background-color: '.WPCOM::color($options['footer_bar_bg'][$i]).';"' : '';
                    $color = isset($options['footer_bar_color'][$i]) && $options['footer_bar_color'][$i] ? ' style="color: '.WPCOM::color($options['footer_bar_color'][$i]).';"' : '';?>
                    <div class="fb-item"<?php echo $bg;?>>
                        <a <?php echo WPCOM::url($options['footer_bar_url'][$i]);?><?php if($type=='1'){ echo ' class="j-footer-bar-icon"';} echo $color;?>>
                            <?php WPCOM::icon($fb, true, 'fb-item-icon');?>
                            <span><?php echo $options['footer_bar_title'][$i];?></span>
                        </a>
                    </div>
                <?php } $i++;} ?>
            </div>
        <?php }
    }
}

add_action('wp_footer', 'wpcom_footer_share_js', 999);
if(!function_exists('wpcom_footer_share_js')){
    function wpcom_footer_share_js(){
        global $options; ?>
        <?php if(isset($options['share'])&&$options['share']=='1' && get_locale()=='zh_CN'){ ?>
            <script>(function ($) {$(document).ready(function () {setup_share(1);})})(jQuery);</script>
        <?php } else if(isset($options['share']) && $options['share']=='1') { ?>
            <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-542188574c8ebd62"></script>
            <script>(function ($) {$(document).ready(function () {setup_share();})})(jQuery);</script>
        <?php }
    }
}

if(!function_exists('wpcom_footer_class')){
    function wpcom_footer_class($class=''){
        global $options;
        $_class = 'footer';
        if(isset($options['footer_bar_icon']) && is_array($options['footer_bar_icon']) && !empty($options['footer_bar_icon']) && !(count($options['footer_bar_icon'])===1 && current($options['footer_bar_icon'])==''))
            $_class .= ' width-footer-bar';
        if($class) $_class .= ' ' . $class;
        return $_class;
    }
}

add_action('wp_footer', 'wpcom_top_news', 20);
if(!function_exists('wpcom_top_news')){
    function wpcom_top_news(){
        global $options;
        if(isset($options['top_news']) && trim($options['top_news'])!=='') { ?>
            <div class="top-news" style="<?php echo WPCOM::gradient_color($options['top_news_bg']);?>">
                <div class="top-news-content container">
                    <div class="content-text"><?php echo $options['top_news']; ?></div>
                    <?php WPCOM::icon('close', true, 'top-news-close');?>
                </div>
            </div>
        <?php }
    }
}