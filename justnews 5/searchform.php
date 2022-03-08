<form class="search-form" action="<?php echo get_bloginfo('url');?>" method="get" role="search">
    <input type="text" class="keyword" name="s" placeholder="<?php _e('Type your search here ...', 'wpcom');?>" value="<?php echo get_search_query(); ?>">
    <button type="submit" class="submit"><?php WPCOM::icon('search');?></button>
</form>