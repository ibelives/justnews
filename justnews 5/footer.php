<?php global $options; ?>
</div>
<footer class="<?php echo wpcom_footer_class();?>">
    <div class="container">
        <div class="clearfix">
            <?php if(isset($options['footer_logo']) && trim($options['footer_logo'])){ ?>
            <div class="footer-col footer-col-logo">
                <img src="<?php echo esc_url(trim($options['footer_logo'])); ?>" alt="<?php echo esc_attr(get_bloginfo("name")); ?>">
            </div>
            <?php } ?>
            <div class="footer-col footer-col-copy">
                <?php wp_nav_menu( array( 'container' => false, 'depth'=> 1, 'theme_location' => 'footer', 'items_wrap' => '<ul class="footer-nav hidden-xs">%3$s</ul>', 'fallback_cb' => 'WPCOM_Nav_Walker::fallback' ) ); ?>
                <div class="copyright">
                    <?php echo ($copyright=isset($options['copyright'])?$options['copyright']:'')?wpautop($copyright):'Copyright © 2022 '.get_bloginfo("name").' 版权所有  Powered by <a href="http://www.wpcom.cn" target="_blank">WordPress</a>'?>
                </div>
            </div>
            <div class="footer-col footer-col-sns">
                <div class="footer-sns">
                    <?php if(isset($options['fticon_i']) && $options['fticon_i']){
                        foreach ($options['fticon_i'] as $i => $icon){ if($icon){ ?>
                            <a <?php if($options['fticon_t'][$i]=='1'){ echo 'class="sns-wx" href="javascript:;"'; } else { echo WPCOM::url($options['fticon_u'][$i]);} ?> aria-label="icon">
                                <?php WPCOM::icon($icon, true, 'sns-icon');?>
                                <?php if($options['fticon_t'][$i]=='1'){ ?><span style="background-image:url(<?php echo trim($options['fticon_u'][$i]); ?>);"></span><?php } ?>
                            </a>
                        <?php } } } ?>
                </div>
            </div>
        </div>
    </div>
</footer>
<?php wp_footer();?>
</body>
</html>