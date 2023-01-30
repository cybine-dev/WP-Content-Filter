<?php

require_once __DIR__ . '/util.php';

class CybineContentFilterPostFilter
{
    public function fetchFeed(?array $filter, ?array $args): string
    {
        $args = array_replace_recursive([
            'wrapper' => true,
            'wrapper-id' => null,
            'wrapper-class' => null,
            
            'no-content-tag' => 'h2',
            'no-content-id' => null,
            'no-content-class' =>  null,
            'no-content-text' => 'Keine Einträge gefunden',

            'article-class' => null,
            'article-default-image' => plugin_dir_url( __FILE__ ) . '/assets/img/default.png',

            'headline-tag' => 'h3',
            'headline-id' => null,
            'headline-class' => null,

            'button-wrapper' => true,
            'button-wrapper-class' => null,

            'button' => true,
            'button-class' => null,
            'button-text' => 'Weiterlesen',
        ], $args != null ? $args : []);

        $filter = array_replace_recursive([
            'cat' => '',
            'order' => 'DESC',
            'orderby' => 'date',
            'post_status' => 'publish',
            'post_type' => 'post',
            'posts_per_page' => 5,
            's' => '',
            'tag_id' => '',
            'tag__in' => '',
            'tag__and' => ''
        ], $filter != null ? $filter : []);
        
        $feed_query = new WP_Query($filter);
        if (!$feed_query->have_posts()) 
        {
            $result_tag = $args['no-content-tag'];
            $result_id = CybineContentFilterUtils::parseOptionalValue('id', $args['no-content-id']);
            $result_class = CybineContentFilterUtils::parseOptionalValue('class', $args['no-content-class']);
            $result_text = $args['no-content-text'];

            $result = "<$result_tag $result_id $result_class>$result_text</$result_tag>";

            if($args['wrapper'])
            {
                $wrapper_id = CybineContentFilterUtils::parseOptionalValue('id', $args['wrapper-id']);
                $wrapper_class = CybineContentFilterUtils::parseOptionalValue('class', $args['wrapper-class']);

                return "
                    <div $wrapper_id $wrapper_class>
                        $result
                    </div>";
            }

            return $result;
        }

        $posts = '';
        while ($feed_query->have_posts()) 
        {
            $feed_query->the_post();

            $post_id = get_the_ID();
            $permalink = get_the_permalink();
            $title = get_the_title();

            // Get category slugs so we can output them at the article container
            $categories = [];
            foreach (get_the_category($post_id) as $category) 
            {
                array_push($categories, get_category($category)->slug);
            }

            $post_categories = 'no-category';
            if (!empty($categories)) 
            {
                $post_categories = implode(' ', $categories);
            }

            $content = has_excerpt() ? get_the_excerpt() : $this->truncate(get_the_content());

            $button = '';
            if ($args['button']) 
            {
                $button_class = CybineContentFilterUtils::parseOptionalValue('class', $args['button-class']);
                $button_text = $args['button-text'];

                $button = "<a $button_class href='$permalink'>$button_text</a>";

                if($args['button-wrapper'])
                {
                    $button_wrapper_class = CybineContentFilterUtils::parseOptionalValue('class', $args['button-wrapper-class']);

                    $button = "
                        <div $button_wrapper_class>
                            $button
                        </div>";
                }
            }

            $article_class = CybineContentFilterUtils::parseOptionalValue('class', $args['article-class'] . ' ' . $post_categories);
            $article_image = get_the_post_thumbnail_url() ?? $args['article-default-image'];

            $headline_tag = $args['headline-tag'];
            $headline_id = CybineContentFilterUtils::parseOptionalValue('id', $args['headline-id']);
            $headline_class = CybineContentFilterUtils::parseOptionalValue('class', $args['headline-class']);

            $posts .= "
                <article $article_class>
                    <a class='thumbnail_container_link' href='$permalink' title='$title'>
                        <div class='featured-image-container' style='background-image: url($article_image);'>
                        </div>
                    </a>
                    <div class='post_content'>
                        <$headline_tag $headline_id $headline_class>
                            <a href='$permalink' title='$title'>$title</a>
                        </$headline_tag>
                        <div class='post_excerpt'>
                            $content
                        </div>
                        $button
                    </div>
                </article>";
        }

        wp_reset_postdata();

        if($args['wrapper'])
        {
            $wrapper_id = CybineContentFilterUtils::parseOptionalValue('id', $args['wrapper-id']);
            $wrapper_class = CybineContentFilterUtils::parseOptionalValue('class', $args['wrapper-class']);

            return "
                <div $wrapper_id $wrapper_class>
                    $posts
                </div>";
        }

        return $posts;
    }

    private function truncate(string $text, int $length = 30, string $more = '...', bool $striptags = false): string
    {
        $content = wpautop($text);
		if (!$striptags)
        {
            $content = htmlentities($content);
        }
		
		return force_balance_tags(html_entity_decode(wp_trim_words($content, $length, $more)));
	}
}