<li class="post-loop-empty">
    <img src="<?php echo get_template_directory_uri();?>/images/empty.svg" alt="empty">
    <?php if(is_search()){
        $kw = get_search_query(); ?>
        <?php if( $kw!='' ): ?>
            <p><?php _e("Sorry, but nothing matched your search terms. Please try again with some different keywords.", 'wpcom');?></p>
        <?php else : ?>
            <p><?php _e('Please type your keyword(s) to search.', 'wpcom'); ?></p>
        <?php endif; ?>
    <?php } else { ?>
        <p><?php _e("Sorry, It seems we can&rsquo;t find what you&rsquo;re looking for.", 'wpcom');?></p>
    <?php } ?>
</li>