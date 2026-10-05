<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function wpnfinite_home_control_defaults() {
    return array('discovery_min'=>4,'fallback_feed'=>'trending','curated_ids'=>array(),'discovery_heading'=>'Discover Something New','discovery_description'=>'Creators, music, stories, video and opportunities worth discovering now.','discovery_link_label'=>'Explore Discovery');
}
function wpnfinite_home_control_settings() {
    return wp_parse_args((array)get_option('wpnfinite_home_control',array()),wpnfinite_home_control_defaults());
}
function wpnfinite_home_control_sanitize($input) {
    $allowed=array('trending','popular','new');
    $ids=isset($input['curated_ids'])?(array)$input['curated_ids']:array();
    return array(
        'discovery_min'=>min(8,max(1,absint($input['discovery_min']??4))),
        'fallback_feed'=>in_array(($input['fallback_feed']??''),$allowed,true)?$input['fallback_feed']:'trending',
        'curated_ids'=>array_values(array_unique(array_filter(array_map('absint',$ids)))),
        'discovery_heading'=>sanitize_text_field($input['discovery_heading']??'Discover Something New'),
        'discovery_description'=>sanitize_text_field($input['discovery_description']??'Creators, music, stories, video and opportunities worth discovering now.'),
        'discovery_link_label'=>sanitize_text_field($input['discovery_link_label']??'Explore Discovery'),
    );
}
function wpnfinite_home_control_admin_menu() {
    add_theme_page(__('Homepage Control Hub','wpnfinite'),__('Homepage Control Hub','wpnfinite'),'manage_options','wpnfinite-home-control','wpnfinite_home_control_page');
}
add_action('admin_menu','wpnfinite_home_control_admin_menu');
function wpnfinite_home_control_register() { register_setting('wpnfinite_home_control_group','wpnfinite_home_control',array('sanitize_callback'=>'wpnfinite_home_control_sanitize')); }
add_action('admin_init','wpnfinite_home_control_register');

function wpnfinite_home_control_candidates() {
    $types=get_post_types(array('public'=>true),'names'); unset($types['attachment'],$types['page']);
    return get_posts(array('post_type'=>array_values($types),'post_status'=>'publish','posts_per_page'=>250,'orderby'=>'date','order'=>'DESC'));
}
function wpnfinite_home_control_page() {
    if(!current_user_can('manage_options'))return; $s=wpnfinite_home_control_settings(); $posts=wpnfinite_home_control_candidates(); ?>
    <div class="wrap"><h1><?php esc_html_e('PairOfDice Homepage Control Hub','wpnfinite');?></h1><p><?php esc_html_e('Control what PairOfDice Discovery shows when a visitor does not have enough personalization data. Personalized recommendations still win once enough user signals exist.','wpnfinite');?></p>
    <form method="post" action="options.php"><?php settings_fields('wpnfinite_home_control_group'); ?>
    <table class="form-table"><tr><th scope="row">Discovery heading</th><td><input type="text" class="regular-text" name="wpnfinite_home_control[discovery_heading]" value="<?php echo esc_attr($s['discovery_heading']);?>"><p class="description">Main heading shown above the Discovery feed.</p></td></tr>
    <tr><th scope="row">Discovery description</th><td><input type="text" class="large-text" name="wpnfinite_home_control[discovery_description]" value="<?php echo esc_attr($s['discovery_description']);?>"><p class="description">Supporting copy beneath the Discovery heading.</p></td></tr>
    <tr><th scope="row">Discovery link label</th><td><input type="text" class="regular-text" name="wpnfinite_home_control[discovery_link_label]" value="<?php echo esc_attr($s['discovery_link_label']);?>"><p class="description">Text for the link to the full Discovery experience. The arrow is added automatically.</p></td></tr>
    <tr><th scope="row">Minimum personalized items</th><td><input type="number" min="1" max="8" name="wpnfinite_home_control[discovery_min]" value="<?php echo esc_attr($s['discovery_min']);?>"><p class="description">If Recommended For You returns fewer than this many items, the homepage uses your defaults below.</p></td></tr>
    <tr><th scope="row">Automatic fallback</th><td><select name="wpnfinite_home_control[fallback_feed]"><?php foreach(array('trending'=>'Trending','popular'=>'Popular','new'=>'New & Fresh') as $v=>$l):?><option value="<?php echo esc_attr($v);?>" <?php selected($s['fallback_feed'],$v);?>><?php echo esc_html($l);?></option><?php endforeach;?></select><p class="description">Used when no curated defaults are selected.</p></td></tr>
    <tr><th scope="row">Curated Discovery defaults</th><td><p class="description" style="margin-bottom:12px">Choose up to 8 default cards. Their order below is the homepage order.</p><?php for($i=0;$i<8;$i++): $selected=absint($s['curated_ids'][$i]??0);?><p><label><strong><?php echo esc_html(($i+1).'.');?></strong> <select name="wpnfinite_home_control[curated_ids][]" style="min-width:440px"><option value="0">— Automatic —</option><?php foreach($posts as $p):?><option value="<?php echo esc_attr($p->ID);?>" <?php selected($selected,$p->ID);?>><?php echo esc_html('['.get_post_type_object($p->post_type)->labels->singular_name.'] '.$p->post_title);?></option><?php endforeach;?></select></label></p><?php endfor;?></td></tr></table><?php submit_button('Save Homepage Defaults');?></form></div><?php
}

function wpnfinite_discovery_has_personalization($minimum=4) {
    if(!is_user_logged_in() || !class_exists('Nfinite_Creators_Discovery'))return false;
    $items=Nfinite_Creators_Discovery::get_feed('recommended',array('limit'=>$minimum));
    return count($items)>=$minimum;
}
function wpnfinite_render_curated_discovery($ids) {
    $posts=get_posts(array('post_type'=>'any','post_status'=>'publish','post__in'=>array_map('absint',$ids),'orderby'=>'post__in','posts_per_page'=>8));
    if(!$posts)return '';
    ob_start(); echo '<section class="nfinite-discovery-feed nfinite-discovery-feed--curated"><header><span class="nfinite-eyebrow">PairOfDice Discovery</span><h2>Recommended by PairOfDice</h2></header><div class="nfinite-discovery-grid">';
    foreach($posts as $post){$type=get_post_type_object($post->post_type);$creator='';if(class_exists('Nfinite_Creators_Discovery')){$payload=Nfinite_Creators_Discovery::item_payload($post);$image=$payload['image']??'';$creator=$payload['creator']??'';}else{$image=get_the_post_thumbnail_url($post,'medium_large')?:'';} echo '<article class="nfinite-discovery-card" data-discovery-type="'.esc_attr($post->post_type).'">';if($image)echo '<a class="nfinite-discovery-art" href="'.esc_url(get_permalink($post)).'"><img src="'.esc_url($image).'" alt="" loading="lazy"></a>';echo '<div class="nfinite-discovery-card-body"><small>'.esc_html($type?$type->labels->singular_name:$post->post_type).'</small><h3><a href="'.esc_url(get_permalink($post)).'">'.esc_html(get_the_title($post)).'</a></h3>';if($creator)echo '<p>'.esc_html($creator).'</p>';if(class_exists('Nfinite_Creators_Library'))echo Nfinite_Creators_Library::button($post->ID,'save');echo '</div></article>';}
    echo '</div></section>'; return ob_get_clean();
}
