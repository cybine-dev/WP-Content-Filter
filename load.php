<?php

require_once __DIR__ . '/modules/util.php';
require_once __DIR__ . '/modules/searchbox.php';
require_once __DIR__ . '/modules/postfilter.php';
require_once __DIR__ . '/modules/shortcode.php';

function add_cy_content_filter_scripts()
{
    wp_register_script('cy_content_filter_scripts', plugins_url('cybine-content-filter', __DIR__) . '/assets/js/cy-content-filter.js', [], time());
    wp_enqueue_script('cy_content_filter_scripts');
    
    wp_enqueue_style('cy_content_filter_styles', plugins_url('cybine-content-filter', __DIR__) . '/assets/css/cy-content-filter.css');
}

add_action('wp_enqueue_scripts', 'add_cy_content_filter_scripts', 100);
add_action('wp_ajax_cy-content-filter', 'cy_content_filter_feed');
add_action('wp_ajax_nopriv_cy-content-filter', 'cy_content_filter_feed');

add_shortcode('cy_content_filter', 'cy_content_filter_shortcode');