<?php

class CybineContentFilterSearchBox
{
    public function generateSearchInput(?array $args): string
    {
        $args = array_replace_recursive([
            'wrapper' => true,
            'wrapper-id' => null,
            'wrapper-class' => null,
            
            'input-id' => 'keyword-' . uniqid(),
            'input-class' => null,
            'input-placeholder' => 'Suche...',
            'input-name' => 'keyword',
            'input-type' => 'text',
        
            'label-text' => 'Suche',
            'onchange-function' => 'cycf_filter(event)',
        ], $args != null ? $args : []);

        $input_id = $args['input-id'];
        $input_id_option = CybineContentFilterUtils::parseOptionalValue('id', $input_id);
        $input_class = CybineContentFilterUtils::parseOptionalValue('class', $args['input-class']);
        $input_placeholder = CybineContentFilterUtils::parseOptionalValue('placeholder', $args['input-placeholder']);
        $input_name = $args['input-name'];
        $input_type = $args['input-type'];

        $label_text = $args['label-text'];
        $function = $args['onchange-function'];

        $input = "
            <label for='$input_id'>$label_text</label>
            <input $input_id_option $input_class
                    type='$input_type' 
                    name='$input_name' 
                    $input_placeholder
                    onInput='$function' 
                    />";

        if($args['wrapper'])
        {
            $wrapper_id = CybineContentFilterUtils::parseOptionalValue('id', $args['wrapper-id']);
            $wrapper_class = CybineContentFilterUtils::parseOptionalValue('class', $args['wrapper-class']);

            return "
                <div $wrapper_id $wrapper_class>
                    $input
                </div>";
        }

        return $input;
    }

    public function generateSelectInput(array $values, ?array $args): string
    {
        $args = array_replace_recursive([
            'wrapper' => true,
            'wrapper-id' => null,
            'wrapper-class' => null,

            'input-id' => 'select-filter-' . uniqid(),
            'input-class' => 'select-filter',
            'input-name' => 'select',

            'input-default' => [
                'name' => 'Bitte wähle eine Einstellung',
                'value' => null
            ],

            'label-text' => 'Select',
            'onchange-function' => 'cycf_filter(event)',
            'selected-function' => (fn(array $option): bool => $_GET['kategorie'] == $option['slug'])
        ], $args != null ? $args : []);

        if($args['input-default'] != null)
        {
            $values = array_merge_recursive([
                $args['input-default']
            ], $values);
        }

        $options = '';
		foreach ($values as $value)
		{
            $option_name = $value['name'];
            $option_value = CybineContentFilterUtils::parseOptionalValue('value', $value['value']);

            $is_selected = $args['selected-function']($value) ? 'selected' : '';

            $options .= "<option $option_value $is_selected>$option_name</option>\n";
        }
		
        $input_id = $args['input-id'];
        $input_id_option = CybineContentFilterUtils::parseOptionalValue('id', $input_id);
        $input_class = CybineContentFilterUtils::parseOptionalValue('class', $args['input-class']);
        $input_name = $args['input-name'];

        $label_text = $args['label-text'];
        $function = $args['onchange-function'];

        $input = "
            <label for='$input_id'>$label_text</label>
            <select $input_id_option $input_class name='$input_name' onchange='$function'>
                $options
            </select>";

        if($args['wrapper'])
        {
            $wrapper_id = CybineContentFilterUtils::parseOptionalValue('id', $args['wrapper-id']);
            $wrapper_class = CybineContentFilterUtils::parseOptionalValue('class', $args['wrapper-class']);

            return "
                <div $wrapper_id $wrapper_class>
                    $input
                </div>";
        }

        return $input;
    }

    public function generateCheckboxInputs(array $values, ?array $args): string
    {
        $args = array_replace_recursive([
            'wrapper' => true,
            'wrapper-id' => null,
            'wrapper-class' => null,

            'input-id-prefix' => 'checkbox-filter-',
            'input-class' => null,
            'input-name' => 'checkbox[]',

            'group-class' => null,

            'onchange-function' => 'cycf_filter(event)',
            'selected-function' => (fn(array $option): bool => $_GET['stichwort'] == $option['slug'])
        ], $args != null ? $args : []);

        $options = '';
		foreach ($values as $value)
		{
			$input_id = $args['input-id-prefix'] . uniqid();
            $input_class = CybineContentFilterUtils::parseOptionalValue('class', $args['input-class']);
            $input_name = $args['input-name'];
            $input_group_class = CybineContentFilterUtils::parseOptionalValue('class', $args['group-class']);

            $function = $args['onchange-function'];

			$is_selected = $args['selected-function']($value) ? 'checked' : '';

            $option_name = $value['name'];
            $option_value = $value['value'];

			$options .= "
				<div $input_group_class>
					<input id='$input_id' $input_class type='checkbox' name='$input_name' onClick='$function' value='$option_value' $is_selected />
					<label for='$input_id'>$option_name</label>
				</div>\n";
		}
		
        if($args['wrapper'])
        {
            $wrapper_id = CybineContentFilterUtils::parseOptionalValue('id', $args['wrapper-id']);
            $wrapper_class = CybineContentFilterUtils::parseOptionalValue('class', $args['wrapper-class']);

            return "
                <div $wrapper_id $wrapper_class>
                    $options
                </div>";
        }

        return $options;
    }

    public function fetchTerms(array $args): array
    {
        $args = array_replace_recursive([
            'taxonomy' => 'post_tag',
            'orderby' => 'name',
            'hide_empty' => true
        ], $args);

        $result = [];
        $terms = get_terms($args);
        foreach($terms as $term)
        {
            array_push($result, [
                'slug' => $term->slug,
                'name' => $term->name, 
                'value' => $term->term_id
            ]);
        }

        return $result;
    }

    public function generatePostFilter(?array $args): string
    {
        $unique_id = uniqid();
        $args = array_replace_recursive([
            'wrapper' => true,
            'wrapper-id' => 'filter-wrapper-' . $unique_id,
            'wrapper-class' => '',

            'filter-id' => 'filter-options-' . $unique_id,
            'filter-class' => '',

            'response' => true,
            'response-id' => 'filter-response-' . $unique_id,
            'response-class' => '',
            'response-initial-data' => '',

            'filters' => [
                [
                    'type' => 'search'
                ],
                [
                    'type' => 'select',
                    'data-type' => 'terms',
                    'term-args' => [
                        'taxonomy' => 'category',
                        'orderby' => 'name',
                        'hide_empty' => true
                    ]
                ],
                [
                    'type' => 'checkbox',
                    'data-type' => 'terms',
                    'term-args' => [
                        'taxonomy' => 'post_tag',
                        'orderby' => 'name',
                        'hide_empty' => true
                    ]
                ]
            ],
        ], $args != null ? $args : []);

        $filters = "<input name='action' value='cy-content-filter' hidden/>";
        foreach($args['filters'] as $filter)
        {
            $options = [];
            switch($filter['data-type'] ?? '')
            {
                case 'values':
                    $options = $filter['values'];
                    break;

                case 'terms':
                    $options = $this->fetchTerms($filter['term-args']);
                    break;
            }

            switch($filter['type'])
            {
                case 'search':
                    $filters .= $this->generateSearchInput($filter['args']);
                    break;

                case 'select':
                    $filters .= $this->generateSelectInput($options, $filter['args']);
                    break;

                case 'checkbox':
                    $filters .= $this->generateCheckboxInputs($options, $filter['args']);
                    break;
            }
        }

        $site_url = site_url();
        $form_id = CybineContentFilterUtils::parseOptionalValue('id', $args['filter-id']);
        $form_class = $args['filter-class'] ?? '';

        $form = "
            <form $form_id class='cy-content-filter--options $form_class' action='$site_url/wp-admin/admin-ajax.php' method='POST' onSubmit='event.preventDefault();'>
                $filters
            </form>";

        $response_id = CybineContentFilterUtils::parseOptionalValue('id', $args['response-id']);
        $response_class = $args['response-class'] ?? '';
        $response_data = $args['response-initial-data'];

        $response = "
            <div $response_id class='cy-content-filter--response $response_class'>
                $response_data
            </div>";

        $output = $form;
        if($args['response'])
        {
            $output .= $response;
        }

        if($args['wrapper'])
        {
            $wrapper_id = CybineContentFilterUtils::parseOptionalValue('id', $args['wrapper-id']);
            $wrapper_class = $args['wrapper-class'] ?? '';

            return "
                <div $wrapper_id class='cy-content-filter--wrapper $wrapper_class'>
                    $output
                </div>";
        }

        return $output;
    }
}