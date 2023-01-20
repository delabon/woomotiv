<?php 

use WooMotiv\Framework\HTML;

return 

HTML::input(array( 
    'type' => 'number',
    'title' => __('Font Size (Desktop/Tablet)', 'woomotiv'),
    'name' =>  'woomotiv_font_size', 
    'value' => woomotiv()->config->woomotiv_font_size,
    'class' => 'dlb_input vvoo_input_font_size',
))

.HTML::input(array( 
    'type' => 'number',
    'title' => __('Font Size (Mobile)', 'woomotiv'),
    'name' =>  'woomotiv_font_size_mobile', 
    'value' => woomotiv()->config->woomotiv_font_size_mobile,
    'class' => 'dlb_input vvoo_input_font_size_mobile',
))

.HTML::select(array( 
    'title' => __('Shape', 'woomotiv'),
    'name' =>  'woomotiv_shape', 
    'value' => woomotiv()->config->woomotiv_shape,
    'items' => array(
        'random'     => __('Random','woomotiv'),
        'rectangle'     => __('Rectangle','woomotiv'),
        'rectangle_2'     => __('Rectangle 2','woomotiv'),
        'rounded'       => __('Rounded','woomotiv'),
        'rounded_2'       => __('Rounded 2','woomotiv'),
        'bordered'      => __('Bordered','woomotiv'),
        'bordered_2'      => __('Bordered 2','woomotiv'),
    ),
    'class' => 'dlb_input vvoo_input_shape',
))

.HTML::select(array( 
    'title' => __('Size', 'woomotiv'),
    'name' =>  'woomotiv_style_size', 
    'value' => woomotiv()->config->woomotiv_style_size,
    'items' => array(
        'default' => __('Default','woomotiv'),
        'small' => __('Small','woomotiv'),
    ),
    'class' => 'dlb_input woomotiv_style_size',
))

.HTML::select(array( 
    'title' => __('Position', 'woomotiv'),
    'name' =>  'woomotiv_position', 
    'value' => woomotiv()->config->woomotiv_position,
    'items' => array(
        'top_center'    => __('Top Center','woomotiv'),
        'top_left'      => __('Top Left','woomotiv'),
        'top_right'     => __('Top Right','woomotiv'),
        'center_left'   => __('Center Left','woomotiv'),
        'center_right'  => __('Center Right','woomotiv'),
        'bottom_center' => __('Bottom Center','woomotiv'),
        'bottom_left'   => __('Bottom Left','woomotiv'),
        'bottom_right'  => __('Bottom Right','woomotiv'),
    ),
    'class' => 'dlb_input vvoo_input_position',
))

.HTML::select(array( 
    'title' => __('Opening Animation', 'woomotiv'),
    'name' =>  'woomotiv_animation', 
    'value' => woomotiv()->config->woomotiv_animation,
    'items' => array(
        'fade'          => __('Fade','woomotiv'),
        // 'pop'           => __('Pop','woomotiv'),
        // 'wobble-skew'   => __('Wobble Skew','woomotiv'),
        // 'buzz-out'      => __('Buzz Out','woomotiv'),
        'slideup'       => __('Slide Up','woomotiv'),
        'slidedown'     => __('Slide Down','woomotiv'),
        'slideleft'     => __('Slide Left','woomotiv'),
        'slideright'    => __('Slide Right','woomotiv'),
    ),
    'class' => 'dlb_input vvoo_input_animation',
))

.HTML::input(array( 
    'title' => __('Background Color', 'woomotiv'),
    'name' => 'woomotiv_bg',
    'value' => woomotiv()->config->woomotiv_bg,
    'class' => 'dlb_input dlb_input_colorpicker',
    'data-css' => 'bg',
))

.HTML::input(array( 
    'title' => __('Text Color', 'woomotiv'),
    'name' => 'woomotiv_style_text_color', 
    'value' => woomotiv()->config->woomotiv_style_text_color,
    'class' => 'dlb_input dlb_input_colorpicker',
    'data-css' => 'text',
))

.HTML::input(array( 
    'title' => __('Strong Tags Color', 'woomotiv'),
    'name' => 'woomotiv_style_strong_color', 
    'value' => woomotiv()->config->woomotiv_style_strong_color,
    'class' => 'dlb_input dlb_input_colorpicker',
    'data-css' => 'strong',
))

.HTML::input(array( 
    'title' => __('Stars Color (Unrated)', 'woomotiv'),
    'name' => 'woomotiv_style_stars_color', 
    'value' => woomotiv()->config->woomotiv_style_stars_color,
    'class' => 'dlb_input dlb_input_colorpicker',
    'data-css' => 'stars',
))

.HTML::input(array( 
    'title' => __('Stars Color (rated)', 'woomotiv'),
    'name' => 'woomotiv_style_stars_rated_color', 
    'value' => woomotiv()->config->woomotiv_style_stars_rated_color,
    'class' => 'dlb_input dlb_input_colorpicker',
    'data-css' => 'stars_rated',
))

.HTML::input(array( 
    'title' => __('Close Button Color', 'woomotiv'),
    'name' => 'woomotiv_style_close_color', 
    'value' => woomotiv()->config->woomotiv_style_close_color,
    'class' => 'dlb_input dlb_input_colorpicker',
    'data-css' => 'close',
))

.HTML::input(array( 
    'title' => __('Close Button Background Color', 'woomotiv'),
    'name' => 'woomotiv_style_close_bg_color', 
    'value' => woomotiv()->config->woomotiv_style_close_bg_color,
    'class' => 'dlb_input dlb_input_colorpicker',
    'data-css' => 'close_bg',
))

;
