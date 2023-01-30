<?php

require_once __DIR__ . '/searchbox.php';
require_once __DIR__ . '/postfilter.php';

$response_args = [
    'no-content-class' => 'no-results entry-title',
    'no-content-text' => 'Es wurden leider keine Tutorials für diese Suchkriterien gefunden',

    'headline-tag' => 'h3',

    'button-wrapper-class' => 'et_pb_button_module_wrapper et_pb_module et_pb_button_alignment_',

    'button-class' => 'et_pb_button et_pb_module et_pb_bg_layout_dark'
];

function cy_content_filter_shortcode($atts = null, $content = null)
{
    global $response_args;

    $post_filter = new CybineContentFilterPostFilter();

    $args = shortcode_atts([
        'post_types' => 'post',
        'response-initial-data' => $post_filter->fetchFeed(null, $response_args),
        'filters' => [
            [
                'type' => 'search',
                'args' => [
                    'wrapper-class' => 'input_container keyword_search'
                ]
            ],
            [
                'type' => 'select',
                'data-type' => 'terms',
                'term-args' => [
                    'taxonomy' => 'category',
                    'orderby' => 'name',
                    'hide_empty' => true
                ],
                'args' => [
                    'wrapper-class' => 'select_container tutorial-category',

                    'input-id' => 'category-filter-' . uniqid(),
                    'input-class' => 'category-filter',
                    'input-name' => 'category',

                    'input-default' => [
                        'name' => 'Alle',
                        'value' => null
                    ],

                    'label-text' => 'Kategorie',
                ]
            ],
            [
                'type' => 'checkbox',
                'data-type' => 'terms',
                'term-args' => [
                    'taxonomy' => 'post_tag',
                    'orderby' => 'name',
                    'hide_empty' => true
                ],
                'args' => [
                    'wrapper-class' => 'checkbox_container tutorial-tag',
        
                    'input-id-prefix' => 'tag-filter-',
                    'input-class' => 'tag-filter',
                    'input-name' => 'tagfilter[]',
        
                    'group-class' => 'tag-filter-option',
                ]
            ],
            [
                'type' => 'select',
                'data-type' => 'values',
                'values' => [
                    [
                        'slug' => null,
                        'name' => 'Test',
                        'value' => '123'
                    ],
                    [
                        'slug' => null,
                        'name' => 'Test2',
                        'value' => '1234'
                    ]
                ],
                'args' => [
                    'wrapper-class' => 'select_container tutorial-category',

                    'input-id' => 'test-filter-' . uniqid(),
                    'input-class' => 'test-filter',
                    'input-name' => 'test',

                    'input-default' => [
                        'name' => 'Alle',
                        'value' => null
                    ],

                    'label-text' => 'Test',
                ]
            ]
        ]
    ], $atts);

    $search_box = new CybineContentFilterSearchBox();
    return $search_box->generatePostFilter($args);
}

function cy_content_filter_feed()
{
    global $response_args;

    $filter = [];
    if (isset($_POST['category']) && $_POST['category'] != 'Alle') 
    {
        $filter['cat'] = $_POST['category'];
    }

    if (isset($_POST['tagfilter']) && $_POST['tagfilter'] != 'Alle') 
    {
        $filter['tag__and'] = array_map('intval', $_POST['tagfilter']);
    }

    if (isset($_POST['keyword'])) 
    {
        $filter['s'] = esc_attr($_POST['keyword']);
    }

    if (isset($_POST['post_type']))
    {
        $filter['post_type'] = $_POST['post_type'];
    }

    $post_filter = new CybineContentFilterPostFilter();

    exit($post_filter->fetchFeed($filter, $response_args));
}