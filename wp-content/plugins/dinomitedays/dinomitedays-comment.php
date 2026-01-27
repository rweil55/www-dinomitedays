<?php

class Dinomitedays_Comment
{
    public function __construct()
    {
        add_filter('comment_form_defaults', array($this, 'customize_comment_form'));
    }

    public function customize_comment_form($defaults)
    {
        $defaults['title_reply'] = __('Leave a Dino-mite Comment!', 'dinomitedays');
        $defaults['label_submit'] = __('Roar it Out!', 'dinomitedays');
        return $defaults;
    }
    public function dropdown($attribute)
    {
        $defaults = array(
            'show_option_all' => '',
            'show_option_none' => '',
            'option_none_value' => '-1',
            'orderby' => 'NAME',
            'order' => 'ASC',
            'show_count' => 0,
            'hide_empty' => 1,
            'child_of' => 0,
            'exclude' => '',
            'echo' => 1,
            'selected' => 0,
            'hierarchical' => 0,
            'name' => '',
            'id' => '',
            'class' => '',
            'tab_index' => 0,
            'taxonomy' => 'category',
            'hide_if_empty' => false,
            'value_field' => 'term_id',
        );

        $attribute = wp_parse_args($attribute, $defaults);

        wp_dropdown_categories($attribute);
    } // end function dropdown
} // end class Dinomitedays_Comment