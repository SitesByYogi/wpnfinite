<?php
/** Template Name: WPNfinite Full Width */
get_header(); while ( have_posts() ) : the_post(); echo '<div class="wpnfinite-full-width">'; the_content(); echo '</div>'; endwhile; get_footer();
