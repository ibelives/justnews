<?php global $options;get_header();?>
    <div class="wrap container">
        <?php if( isset($options['breadcrumb']) && $options['breadcrumb']=='1' ) wpcom_breadcrumb('breadcrumb'); ?>
        <div class="main">
            <div class="sec-panel sec-panel-default">
                <div class="sec-panel-head">
                    <h1><span><?php if( is_author() ) { ?><?php echo get_the_author(); ?>
                        <?php } elseif (is_day()) { ?><?php echo sprintf( __( 'Daily Archives: %s' , 'wpcom' ), get_the_date() ) ?>
                        <?php } elseif (is_month()) { ?><?php echo sprintf( __( 'Monthly Archives: %s' , 'wpcom' ), get_the_date(__( 'F Y', 'wpcom' )) ) ?>
                        <?php } elseif (is_year()) { ?><?php echo sprintf( __( 'Yearly Archives: %s' , 'wpcom' ), get_the_date(__( 'Y', 'wpcom' )) ) ?>
                        <?php } elseif (is_tax()) { ?><?php single_cat_title(); ?><?php } ?></span></h1>
                </div>
                <div class="sec-panel-body">
                    <ul class="post-loop post-loop-default">
                        <?php if(have_posts()) : ?>
                            <?php while( have_posts() ) : the_post();?>
                                <?php get_template_part( 'templates/loop' , 'default' ); ?>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <?php get_template_part( 'templates/loop' , 'none' ); ?>
                        <?php endif;?>
                    </ul>
                    <?php wpcom_pagination(5);?>
                </div>
            </div>
        </div>
        <aside class="sidebar">
            <?php get_sidebar();?>
        </aside>
    </div>
<?php get_footer();?>