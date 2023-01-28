<?php

function add_divi_tutorials_scripts()
{
	wp_register_script('divi-tutorials-scripts', get_stylesheet_directory_uri() . '/js/divi-tutorials-scripts.js', false, '', true);
	wp_enqueue_script('divi-tutorials-scripts');
}

add_action('wp_enqueue_scripts', 'add_divi_tutorials_scripts', 100);

function nxt_megafilter_shortcode() {
	$args['post_type'] = ['post'];
	return nxt_tutorial_filter_shortcode($args);
}
add_shortcode('nxt_megafilter', 'nxt_megafilter_shortcode');

/* Filter Function for tutorials */
// This function creates 1) a search box 2) the default arguments for our custom query and it then calls another function to output the posts on our site
function nxt_tutorial_filter_shortcode($atts = null, $content = null)
{
	$a = shortcode_atts([
		'post_type' => 'post',
	], $atts);

	$site_url = site_url();
	
	// Add an input field that searches for the keyword in titles of posts
	$search_input = "
		<div class='input_container keyword_search'>
			<label for='keyword'>Suche</label>
			<input type='text' name='keyword' id='keyword' placeholder='Suchbegriff...' onkeyup='nxt_filter(event)' />
		</div>";
	
	$term_args = array(
		'taxonomy' => 'category',
		'orderby=name',
		'hide_empty' => true,
	);
		
	$category_content = '';
	// Build a select that lists all the categories (that are not empty)
	if ($terms = get_terms($term_args)) 
	{
		$category_options = '';
		foreach ($terms as $term)
		{
			$is_selected = $_GET['kategorie'] == $term->slug ? 'selected' : '';
			$category_options .= "<option value='$term->term_id' $is_selected>$term->name</option>\n";
		}
		
		$category_content = "
			<div class='select_container tutorial-category'>
				<label for='categoryfilter'>Kategorie</label>
				<div class='select-holder'>
					<select id='categoryfilter' class='categoryfilter' name='categoryfilter' onchange='nxt_filter(event)'>
						<option>Alle</option>
						$category_options
					</select>
				</div> 
				<!-- .select-holder -->
			</div> 
			<!-- select_container -->";
	}

	$term_args = array(
		'taxonomy' => 'post_tag',
		'orderby=name',
		'hide_empty' => true,
	);

	$tag_content = '';
	// Prepare Filter for Tags -> we build a fieldset of checkboxes that's listing all the tags that are available
	if ($terms = get_terms($term_args)) 
	{
		$tag_options = '';
		foreach ($terms as $term)
		{
			$tag_id = uniqid();
			$is_selected = $_GET['stichwort'] == $term->slug ? 'checked' : '';
			$tag_options .= "
				<div class='sind-noch-klassennamen-offen-fragezeichen'>
					<input id='$tag_id' type='checkbox' name='tagfilter[]' onclick='nxt_filter(event)' value='$term->term_id' $is_selected />
					<label for='$tag_id'>$term->name</label>
				</div>\n";
		}
		
		$tag_content = "
			<div class='select_container tutorial-tag'>
				<div class='checkbox-holder'>
					<div class='fieldset'>
						$tag_options
					</div>
				</div> 
				<!-- .checkbox-holder -->
			</div> 
			<!-- select_container -->";
	}

	// We're building the default arguments for our custom WP Query
	$args = array(
		'class'			=> 'phpStorm',
		'post_type'		=> $a["post_type"],
		'post_status'	=> 'publish',
		'ppp'			=> 12,
		'orderby'		=> 'date', // we will sort posts by title
		'order'			=> 'DESC', // ASC or DESC
	);

	$feed = nxt_feed_function($args);

	return "
		<div id='filter_container' class='magic_selects'>
			<form id='filter' action='$site_url/wp-admin/admin-ajax.php' method='POST' onSubmit='event.preventDefault();' class='karmachameleon'>
				$search_input
				$category_content
				$tag_content
				<!-- 
					<div class='button_container'>
						<button>Filter anwenden</button>
					</div>
				-->
				<input type='hidden' name='action' value='myfilter'>
			</form>
		</div>
		<div id='response'>
			$feed
		</div>";
}

// This function outputs the contents that someone filtered for on the overview page of all tutorials
function nxt_filter_function()
{
	// print_r($_POST);
	$args = [
		'class'			=> 'phpStorm',
		'post_type'		=> 'post',
		'post_status'	=> 'publish',
		'orderby'		=> 'title', // we will sort posts by title
		'order'			=> 'DESC', // ASC or DESC
		'ppp' 			=> 12,
		's'				=> '',
		// 'paged' => 1,
	];

	// filter for categories
	if (isset($_POST['categoryfilter']) && $_POST['categoryfilter'] != 'Alle') 
	{
		$args['cat'] = $_POST['categoryfilter'];
	}

	// filter for tags
	if (isset($_POST['tagfilter']) && $_POST['tagfilter'] != 'Alle') 
	{
		// $nxt_tags = implode("+", $_POST['tagfilter']);
		$args['tag__and'] = array_map('intval', $_POST['tagfilter']);
	}

	if (isset($_POST['keyword'])) 
	{
		$args['s'] = esc_attr($_POST['keyword']);
	}

	if (isset($_POST['post_type'])) {
		$args['post_type'] = $_POST['post_type'];
	}

	// var_dump($args);
	echo nxt_feed_function($args);
	die();
}

add_action('wp_ajax_myfilter', 'nxt_filter_function');
add_action('wp_ajax_nopriv_myfilter', 'nxt_filter_function');

function data_fetch()
{
	$args = [
		'posts_per_page'	=> -1,
		's'					=> esc_attr($_POST['keyword']),
		'post_type'			=> 'post',
	];

	echo nxt_feed_function($args);

	die();
}

add_action('wp_ajax_data_fetch', 'data_fetch');
add_action('wp_ajax_nopriv_data_fetch', 'data_fetch');

// Output a simple blog post feed (list all tutorials)
function nxt_feed_function($atts, $content = null)
{
	$a = shortcode_atts([
		'button' => 'yes',
		'cat' => '',
		'class' => '',
		'order' => 'DESC',
		'orderby' => 'date',
		'post_status' => 'publish',
		'post_type' => 'post',
		'ppp' => 5,
		's' => '',
		'tag_id' => '',
		'tag__in' => '',
		'tag__and' => '',
	], $atts);

	$feed_args = [
		'cat' => $a["cat"],
		'order' => $a["order"],
		'orderby' => $a["orderby"],
		'post_status' => $a["post_status"],
		'post_type' => $a["post_type"],
		'posts_per_page' => $a["ppp"],
		's' => $a["s"],
		'tag_id' => $a["tag_id"],
		'tag__in' => $a["tag__in"],
		'tag__and' => $a["tag__and"],
	];

	if (isset($_POST['post_type']))
	{
		$feed_args['post_type'] = $_POST['post_type'];
	}
	
	$feed_query = new WP_Query($feed_args);
	if (!$feed_query->have_posts()) 
	{
		return "
			<div>
				<h2 class='no-results entry-title'>Es wurden leider keine Tutorials für diese Suchkriterien gefunden</h2>
			</div>";
	}

	$posts = '';
	while ($feed_query->have_posts()) 
	{
		$feed_query->the_post();

		$nxt_post_id = get_the_ID();
		$nxt_permalink = get_the_permalink();
		$nxt_title = get_the_title();

		// Get category slugs so we can output them at the article container
		$nxt_categories = [];
		foreach (get_the_category($nxt_post_id) as $c) 
		{
			$cat = get_category($c);
			array_push($nxt_categories, $cat->slug);
		}

		$post_categories = 'no-category';
		if (!empty($nxt_categories)) 
		{
			$post_categories = implode(' ', $nxt_categories);
		}

		$nxt_content = (has_excerpt()) ? get_the_excerpt() : trim(get_the_content());

		$button = '';
		if ($a["button"] == 'yes') 
		{
			$button = "
				<div class='et_pb_button_module_wrapper et_pb_module et_pb_button_alignment_'>
					<a class='et_pb_button et_pb_module et_pb_bg_layout_dark' href='$nxt_permalink'>Weiterlesen</a>
				</div>";
		}

		$posts .= "
			<article class='nxt_post_infini_scroll $post_categories'>
				<a class='thumbnail_container_link' href='$nxt_permalink' title='$nxt_title'>
					<div class='featured-image-container' style='background-image: url(get_the_post_thumbnail_url());'>
					</div>
				</a>
				<div class='post_content'>
					<h3 class='entry-title'>
						<a href='$nxt_permalink' title='$nxt_title'>$nxt_title'</a>
					</h3>
					<div class='post_excerpt'>
						$nxt_content
					</div>
					$button
				</div> 
				<!-- .post_content -->
			</article>\n";
	}

	wp_reset_postdata();

	$additional_class = $a['class'];
	return "
		<div class='trendy-eistee  $additional_class'>
			$posts
		</div> 
		<!-- .trendy-eistee -->";
}
