<?php
get_header();

$used_ids     = array();
$reservations = wpnfinite_media_reservations();
$reserved_ids = wpnfinite_media_reserved_ids( $reservations );

// Editorial hero/trending content must always be standard WordPress posts.
// Pages (Home, About, Shop, etc.) are navigational surfaces and must never
// leak into discovery cards, even if another plugin/theme filter alters a query.
$hero_exclude = array_values( array_unique( array_map( 'absint', $reserved_ids ) ) );
$sticky_ids   = array_values( array_filter( array_map( 'absint', (array) get_option( 'sticky_posts', array() ) ) ) );
$hero_posts   = array();

if ( ! empty( $sticky_ids ) ) {
    $sticky_query = new WP_Query(
        array(
            'post_type'              => 'post',
            'post_status'            => 'publish',
            'post__in'               => $sticky_ids,
            'post__not_in'           => $hero_exclude,
            'posts_per_page'         => 1,
            'orderby'                => 'date',
            'order'                  => 'DESC',
            'ignore_sticky_posts'    => true,
            'no_found_rows'          => true,
            'suppress_filters'       => false,
        )
    );
    $hero_posts = array_values( array_filter( $sticky_query->posts, static function( $item ) {
        return $item instanceof WP_Post && 'post' === $item->post_type;
    } ) );
}

$hero_needed = 4 - count( $hero_posts );
if ( $hero_needed > 0 ) {
    $hero_fill_exclude = array_values( array_unique( array_merge( $hero_exclude, wp_list_pluck( $hero_posts, 'ID' ) ) ) );
    $hero_query = new WP_Query(
        array(
            'post_type'           => 'post',
            'post_status'         => 'publish',
            'posts_per_page'      => $hero_needed,
            'post__not_in'        => $hero_fill_exclude,
            'orderby'             => 'date',
            'order'               => 'DESC',
            'ignore_sticky_posts' => true,
            'no_found_rows'       => true,
        )
    );
    $hero_fill = array_values( array_filter( $hero_query->posts, static function( $item ) {
        return $item instanceof WP_Post && 'post' === $item->post_type;
    } ) );
    $hero_posts = array_merge( $hero_posts, $hero_fill );
}
$lead = ! empty($hero_posts) ? array_shift($hero_posts) : null;
if ($lead) $used_ids[] = $lead->ID;
wpnfinite_media_add_used_ids($used_ids,$hero_posts);

$community_url = post_type_exists('nfinite_creator_post') ? get_post_type_archive_link('nfinite_creator_post') : home_url('/community/');
?>
<div class="wpnfinite-media-home wpnfinite-living-home">
<?php if (wpnfinite_media_home_setting('show_intro',true)) : ?>
<section class="wpnfinite-home-v15-hero"><div class="wpnfinite-container wpnfinite-home-v15-hero__inner"><div class="wpnfinite-home-v15-hero__copy"><span class="wpnfinite-media-masthead__eyebrow"><?php esc_html_e('CREATE • UNDERSTAND • GROW • GET DISCOVERED','wpnfinite'); ?></span><h1><?php esc_html_e('Where creators build what’s next.','wpnfinite'); ?></h1><p><?php esc_html_e('PairOfDice combines creator technology, digital growth strategy, media and real opportunities to help independent creators build stronger businesses.','wpnfinite'); ?></p><div class="wpnfinite-home-v15-actions"><a class="wpnfinite-btn wpnfinite-btn-primary" href="<?php echo esc_url(function_exists('wpnfinite_platform_page_url')?wpnfinite_platform_page_url('discover'):home_url('/discover/')); ?>"><?php esc_html_e('Explore PairOfDice','wpnfinite'); ?></a><a class="wpnfinite-btn wpnfinite-btn-secondary" href="<?php echo esc_url(home_url('/creator-intelligence/')); ?>"><?php esc_html_e('Creator Intelligence →','wpnfinite'); ?></a></div></div><div class="wpnfinite-home-v15-hero__system"><span><?php esc_html_e('THE PAIROFDICE GROWTH SYSTEM','wpnfinite'); ?></span><div><b>01</b><strong>Create</strong><small>Tools built for your work.</small></div><div><b>02</b><strong>Understand</strong><small>Intelligence across your career.</small></div><div><b>03</b><strong>Grow</strong><small>Strategy that turns signals into action.</small></div><div><b>04</b><strong>Discover</strong><small>Media, community and opportunities.</small></div></div></div></section>
<?php endif; ?>

<?php if ( function_exists( 'wpnfinite_render_platform_home_sections' ) ) { wpnfinite_render_platform_home_sections(); } ?>


<section class="wpnfinite-container wpnfinite-ci-home"><div class="wpnfinite-ci-home__copy"><span class="wpnfinite-media-kicker"><?php esc_html_e('CREATOR INTELLIGENCE','wpnfinite'); ?></span><h2><?php esc_html_e('Know what’s working. Know what to do next.','wpnfinite'); ?></h2><p><?php esc_html_e('Creator Intelligence connects the signals behind your creative business and turns audience, content and performance data into clear next moves. PairOfDice is the first-party proving ground, not a requirement.','wpnfinite'); ?></p><div class="wpnfinite-home-v15-actions"><a class="wpnfinite-btn wpnfinite-btn-primary" href="<?php echo esc_url(home_url('/creator-intelligence/')); ?>"><?php esc_html_e('Explore Creator Intelligence','wpnfinite'); ?></a><a class="wpnfinite-living-text-link" href="https://ci.pairofdice.media/?signup=1"><?php esc_html_e('Open Creator Intelligence →','wpnfinite'); ?></a></div></div><a class="wpnfinite-ci-home__visual" href="<?php echo esc_url(home_url('/creator-intelligence/')); ?>"><img src="https://pairofdice.media/wp-content/uploads/2026/10/CI-Intelligence.png" alt="Creator Intelligence dashboard showing creator priorities and recommended actions" loading="lazy"></a></section>

<section class="wpnfinite-container wpnfinite-home-tools" id="creator-tools"><div class="wpnfinite-media-section__head"><div><span><?php esc_html_e('BUILT FOR CREATORS','wpnfinite'); ?></span><h2><?php esc_html_e('Tools for the business behind your creativity.','wpnfinite'); ?></h2><p><?php esc_html_e('Use what fits your work. Your PairOfDice workspace can grow with you.','wpnfinite'); ?></p></div></div><div class="wpnfinite-home-tools__grid"><a href="<?php echo esc_url(home_url('/creator-intelligence/')); ?>"><span>✦</span><small>INTELLIGENCE</small><h3>Creator Intelligence</h3><p>Understand your audience, content, business and next moves.</p><b>Explore →</b></a><a href="<?php echo esc_url(post_type_exists('nfinite_release')?get_post_type_archive_link('nfinite_release'):home_url('/music/')); ?>"><span>▶</span><small>MUSIC</small><h3>Music & Releases</h3><p>Build your catalog, manage releases and connect your music to PairOfDice.</p><b>Explore →</b></a><a href="<?php echo esc_url(home_url('/beats/')); ?>"><span>▦</span><small>PRODUCERS</small><h3>Beats</h3><p>Publish beats, manage licensing and build your producer business.</p><b>Explore →</b></a><div class="is-coming"><span>▰</span><small>COMING NEXT</small><h3>TV Studio</h3><p>Video publishing and creator programming built for the PairOfDice TV network.</p><b>In development</b></div></div></section>

<?php if($lead): ?>
<section class="wpnfinite-container wpnfinite-media-lead-grid wpnfinite-living-hero">
<?php
// setup_postdata() alone does not replace the global $post object. Because this
// template is rendered on a static front page, template tags would otherwise
// keep reading the Home page even though $lead contains a real editorial post.
$front_page_post = isset( $post ) ? $post : null;
$post            = $lead;
setup_postdata( $post );
?><article class="wpnfinite-media-lead"><a class="wpnfinite-media-lead__media" href="<?php the_permalink(); ?>"><?php has_post_thumbnail()?the_post_thumbnail('full'):print('<span class="wpnfinite-media-card__placeholder"></span>'); ?><span class="wpnfinite-media-badge"><?php echo esc_html(wpnfinite_media_type_label(get_the_ID())); ?></span></a><div class="wpnfinite-media-lead__body"><div class="wpnfinite-media-meta"><?php echo esc_html(get_the_date()); ?></div><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><p><?php echo esc_html(wpnfinite_media_get_excerpt(get_the_ID(),30)); ?></p></div></article><?php
wp_reset_postdata();
if ( $front_page_post instanceof WP_Post ) {
    $post = $front_page_post;
    setup_postdata( $post );
}
?>
<div class="wpnfinite-media-supporting"><div class="wpnfinite-living-rail-title"><span><?php esc_html_e('Trending now','wpnfinite'); ?></span></div><?php foreach($hero_posts as $post): setup_postdata($post); ?><article class="wpnfinite-media-mini"><a class="wpnfinite-media-mini__media" href="<?php the_permalink(); ?>"><?php has_post_thumbnail()?the_post_thumbnail('medium_large'):print('<span class="wpnfinite-media-card__placeholder"></span>'); ?></a><div><span class="wpnfinite-media-kicker"><?php echo esc_html(wpnfinite_media_type_label(get_the_ID())); ?></span><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3></div></article><?php endforeach; wp_reset_postdata(); ?></div>
</section><?php endif; ?>

<?php if(post_type_exists('nfinite_creator_post') && class_exists('Nfinite_Creators_Publishing')):
$community = get_posts(array('post_type'=>'nfinite_creator_post','post_status'=>'publish','posts_per_page'=>2,'orderby'=>'date','order'=>'DESC'));
if($community): ?>
<section class="wpnfinite-container wpnfinite-media-section wpnfinite-home-community"><div class="wpnfinite-media-section__head"><div><span><?php esc_html_e('Right now','wpnfinite'); ?></span><h2><?php esc_html_e('From the Community','wpnfinite'); ?></h2><p><?php esc_html_e('Updates, conversations and drops directly from PairOfDice creators.','wpnfinite'); ?></p></div><a href="<?php echo esc_url($community_url); ?>"><?php esc_html_e('Join the conversation','wpnfinite'); ?> →</a></div><div class="wpnfinite-home-community__grid"><?php foreach($community as $cp){ echo Nfinite_Creators_Publishing::render_feed_card($cp->ID); } ?></div></section>
<?php endif; endif; ?>

<?php if(post_type_exists('nfinite_release')):
$releases=new WP_Query(array('post_type'=>'nfinite_release','post_status'=>'publish','posts_per_page'=>4,'orderby'=>'date','order'=>'DESC','no_found_rows'=>true)); if($releases->have_posts()): ?>
<section class="wpnfinite-container wpnfinite-media-section"><div class="wpnfinite-media-section__head"><div><span><?php esc_html_e('Press play','wpnfinite'); ?></span><h2><?php esc_html_e('New Music','wpnfinite'); ?></h2></div><a href="<?php echo esc_url(get_post_type_archive_link('nfinite_release')); ?>"><?php esc_html_e('Explore music','wpnfinite'); ?> →</a></div><div class="wpnfinite-living-cards"><?php while($releases->have_posts()):$releases->the_post(); ?><article class="wpnfinite-living-card"><a class="wpnfinite-living-card__media" href="<?php the_permalink(); ?>"><?php has_post_thumbnail()?the_post_thumbnail('large'):print('<span class="wpnfinite-media-card__placeholder"></span>'); ?><span class="wpnfinite-living-play">▶</span></a><div class="wpnfinite-living-card__body"><span class="wpnfinite-media-kicker"><?php esc_html_e('Release','wpnfinite'); ?></span><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3></div></article><?php endwhile;wp_reset_postdata(); ?></div></section>
<?php endif; endif; ?>

<?php
$latest = new WP_Query(
    array(
        'post_type'           => 'post',
        'post_status'         => 'publish',
        'posts_per_page'      => 4,
        'post__not_in'        => array_values( array_unique( array_merge( $used_ids, $reserved_ids ) ) ),
        'orderby'             => 'date',
        'order'               => 'DESC',
        'ignore_sticky_posts' => true,
        'no_found_rows'       => true,
    )
);
$latest->posts = array_values( array_filter( $latest->posts, static function( $item ) {
    return $item instanceof WP_Post && 'post' === $item->post_type;
} ) );
$latest->post_count = count( $latest->posts );
if($latest->have_posts()): wpnfinite_media_add_used_ids($used_ids,$latest->posts); ?>
<section class="wpnfinite-container wpnfinite-media-section"><div class="wpnfinite-media-section__head"><div><span><?php esc_html_e('Fresh','wpnfinite'); ?></span><h2><?php esc_html_e('Latest Stories','wpnfinite'); ?></h2></div><a href="<?php echo esc_url(wpnfinite_media_archive_link('post')); ?>"><?php esc_html_e('View stories','wpnfinite'); ?> →</a></div><div class="wpnfinite-media-grid <?php echo esc_attr(wpnfinite_media_grid_count_class(count($latest->posts))); ?>"><?php while($latest->have_posts()):$latest->the_post();get_template_part('template-parts/media/card');endwhile;wp_reset_postdata(); ?></div></section><?php endif; ?>

<?php if(wpnfinite_media_home_setting('show_creators',true)&&function_exists('wpnfinite_has_nfinite_creators')&&wpnfinite_has_nfinite_creators()): $creators=wpnfinite_media_creator_query(4); if($creators&&$creators->have_posts()): ?>
<section class="wpnfinite-container wpnfinite-media-section wpnfinite-creator-spotlight"><div class="wpnfinite-media-section__head"><div><span><?php esc_html_e('Discover','wpnfinite'); ?></span><h2><?php esc_html_e('Creators to Know','wpnfinite'); ?></h2></div><a href="<?php echo esc_url(get_post_type_archive_link('nfinite_creator')); ?>"><?php esc_html_e('Explore creators','wpnfinite'); ?> →</a></div><div class="wpnfinite-creator-spotlight__grid <?php echo esc_attr(wpnfinite_media_grid_count_class(count($creators->posts))); ?>"><?php while($creators->have_posts()):$creators->the_post(); ?><article class="wpnfinite-creator-spotlight__card"><a class="wpnfinite-creator-spotlight__media" href="<?php the_permalink(); ?>"><?php has_post_thumbnail()?the_post_thumbnail('large'):print('<span class="wpnfinite-media-card__placeholder"></span>'); ?></a><div class="wpnfinite-creator-spotlight__body"><span class="wpnfinite-media-kicker"><?php echo esc_html(wpnfinite_media_creator_types(get_the_ID())); ?></span><h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><?php $loc=get_post_meta(get_the_ID(),'_nfinite_creator_location',true);if($loc):?><p><?php echo esc_html($loc);?></p><?php endif;?></div></article><?php endwhile;wp_reset_postdata(); ?></div></section>
<?php endif;endif; ?>

<?php if(post_type_exists('nfinite_event')):$events=new WP_Query(array('post_type'=>'nfinite_event','post_status'=>'publish','posts_per_page'=>3,'orderby'=>'date','order'=>'DESC','no_found_rows'=>true));if($events->have_posts()): ?>
<section class="wpnfinite-container wpnfinite-media-section"><div class="wpnfinite-media-section__head"><div><span><?php esc_html_e('Get outside','wpnfinite'); ?></span><h2><?php esc_html_e("What's Happening",'wpnfinite'); ?></h2></div><a href="<?php echo esc_url(get_post_type_archive_link('nfinite_event')); ?>"><?php esc_html_e('View events','wpnfinite'); ?> →</a></div><div class="wpnfinite-living-events"><?php while($events->have_posts()):$events->the_post();?><article><a href="<?php the_permalink();?>"><?php if(has_post_thumbnail())the_post_thumbnail('medium_large');?><div><span><?php echo esc_html(get_the_date('M j'));?></span><h3><?php the_title();?></h3><p><?php echo esc_html(wpnfinite_media_get_excerpt(get_the_ID(),16));?></p></div></a></article><?php endwhile;wp_reset_postdata();?></div></section>
<?php endif;endif; ?>

<section class="wpnfinite-container wpnfinite-home-pillars" id="pairofdice-network"><div class="wpnfinite-home-pillars__intro"><span class="wpnfinite-media-kicker"><?php esc_html_e('THE PAIROFDICE SYSTEM','wpnfinite');?></span><h2><?php esc_html_e('Technology, tools, strategy and a network built to work together.','wpnfinite');?></h2></div><div class="wpnfinite-home-pillars__grid"><a href="<?php echo esc_url(home_url('/creator-intelligence/'));?>"><b>01</b><h3>Creator Intelligence</h3><p>Understand the signals behind your creative business.</p></a><a href="#creator-tools"><b>02</b><h3>Creator Tools</h3><p>Create, publish, sell and operate from purpose-built tools.</p></a><a href="<?php echo esc_url(home_url('/contact/'));?>"><b>03</b><h3>Digital Growth Strategy</h3><p>Turn intelligence into campaigns, audience growth and revenue.</p></a><div><b>04</b><h3>PairOfDice Network</h3><p>Music, Radio, TV, Gaming, Events, editorial and community.</p><nav><a href="<?php echo esc_url(home_url('/radio/'));?>">Radio</a><a href="<?php echo esc_url(home_url('/videos/'));?>">TV / Video</a><a href="<?php echo esc_url($community_url);?>">Community</a></nav></div></div></section>

<section class="wpnfinite-container wpnfinite-media-cta wpnfinite-living-join"><div class="wpnfinite-media-cta__copy"><span class="wpnfinite-media-kicker"><?php esc_html_e('Join the community','wpnfinite');?></span><h2><?php esc_html_e('Share what you create. Find what’s next.','wpnfinite');?></h2><p><?php esc_html_e('Create your identity, connect your work and grow through the PairOfDice ecosystem.','wpnfinite');?></p></div><div class="wpnfinite-media-cta__action"><a class="wpnfinite-btn wpnfinite-btn-primary" href="https://ci.pairofdice.media/?signup=1"><?php esc_html_e('Join PairOfDice','wpnfinite');?></a><a class="wpnfinite-living-text-link" href="<?php echo esc_url($community_url);?>"><?php esc_html_e('Explore Community →','wpnfinite');?></a></div></section>
</div>
<?php get_footer(); ?>
