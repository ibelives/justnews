<?php
get_header( 'embed' );

if ( have_posts() ) :
    echo '<ul class="post-loop post-loop-default post-loop-embed clearfix">';
	while ( have_posts() ) :
		the_post();
		get_template_part( 'templates/loop', 'default' );
	endwhile;
	echo '</ul>';
else :
	get_template_part( 'embed', '404' );
endif;

get_footer( 'embed' );