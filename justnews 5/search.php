<?php global $options;get_header();?>
    <div class="wrap container">
        <?php if( isset($options['breadcrumb']) && $options['breadcrumb']=='1' ) wpcom_breadcrumb('breadcrumb'); ?>
        <div class="main">
            <div class="sec-panel sec-panel-default">
                <div class="sec-panel-head">
                    <h1><span><?php
                        $kw = get_search_query();
                        $keword = $kw!='' ? $kw : __('None', 'wpcom');
                        echo sprintf( __('Search for: %s', 'wpcom'), $keword);
                        ?></span></h1>
                </div>
                <div class="sec-panel-body">
                    <ul class="post-loop post-loop-default">
                        <?php if( have_posts() && $kw!='' ) : ?>
                            <?php while( have_posts() ) : the_post();?>
                                <?php get_template_part( 'templates/loop' , 'default' ); ?>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <?php get_template_part( 'templates/loop' , 'none' ); ?>
                        <?php endif; ?>
                    </ul>
                    <?php if($kw!='') wpcom_pagination(5);?>
                </div>
            </div>
        </div>
        <aside class="sidebar">
            <?php get_sidebar();?>
        </aside>
    </div>
<?php get_footer();?>