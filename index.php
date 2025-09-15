<?php
// Fallback to block templates.
wp_head();
echo '<main class="wpnfinite-fallback">';
while ( have_posts() ) { the_post(); the_content(); }
echo '</main>';
wp_footer();
