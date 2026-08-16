<?php
/** Template Name: WPNfinite Narrow Content */
get_header(); echo '<section class="wpnfinite-container wpnfinite-content-area">'; while ( have_posts() ) : the_post(); echo '<article class="wpnfinite-page wpnfinite-narrow">'; the_title( '<h1 class="wpnfinite-entry-title">', '</h1>' ); the_content(); echo '</article>'; endwhile; echo '</section>'; get_footer();
