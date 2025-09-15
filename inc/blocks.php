<?php
if (! defined('ABSPATH')) {
    exit;
}

add_action('init', function () {
    register_block_type(get_stylesheet_directory() . '/blocks/page-header');
});
