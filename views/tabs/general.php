<?php 

use WooMotiv\Framework\HTML;

return 

HTML::radio(array( 
    'title' => __('Display', 'woomotiv'),
    'name' =>  'woomotiv_display_order', 
    'value' => woomotiv()->config->woomotiv_display_order,
    'items' => array(
        'recent_sales' => __('Recent Sales ( Reviews )','woomotiv'),
        'random_sales' => __('Random Sales ( Reviews )','woomotiv'),
    ),
))

.HTML::checkbox(array( 
    'title' => __('No-repeat', 'woomotiv'),
    'description' => __('Do not repeat the same Sales/Reviews popups.', 'woomotiv'),
    'name' => 'woomotiv_no_repeat_sales_reviews', 
    'value' => woomotiv()->config->woomotiv_no_repeat_sales_reviews,
    'text' => __('Enable','woomotiv'),
))

.HTML::checkbox(array( 
    'title' => __('Display Orders With Status "Processing"', 'woomotiv'),
    'name' => 'woomotiv_display_processing_orders', 
    'value' => woomotiv()->config->woomotiv_display_processing_orders,
    'text' => __('Enable','woomotiv'),
))

.HTML::checkbox(array( 
    'title' => __('Display Review Popups', 'woomotiv'),
    'description' => __('Enable this option if you want push review popups.', 'woomotiv') . '<br>' . __('Woomotiv will only show reviews that have 4 stars and above.', 'woomotiv'),
    'name' => 'woomotiv_display_reviews', 
    'value' => woomotiv()->config->woomotiv_display_reviews,
    'text' => __('Enable','woomotiv'),
))

.HTML::input(array( 
    'title' => __('Number of Popups to Show', 'woomotiv'),
    'description' => __('Minimum: 1','woomotiv'),
    'name' => 'woomotiv_limit', 
    'value' => woomotiv()->config->woomotiv_limit,
))

.HTML::input(array( 
    'title' => __('Delay Time Between 2 Notifications', 'woomotiv'),
    'description' => __('Second(s)','woomotiv'),
    'name' => 'woomotiv_interval', 
    'value' => woomotiv()->config->woomotiv_interval,
))

.HTML::input(array( 
    'title' => __('Notification Display Time', 'woomotiv'),
    'description' => __('Second(s)','woomotiv'),
    'name' => 'woomotiv_hide', 
    'value' => woomotiv()->config->woomotiv_hide,
))

.HTML::checkbox(array( 
    'title' => __('Display Admin Own Notifications', 'woomotiv'),
    'description' => __('Enable this option if you want the admin to see his own orders in the popups', 'woomotiv'),
    'name' => 'woomotiv_admin_popups', 
    'value' => woomotiv()->config->woomotiv_admin_popups,
    'text' => __('Enable','woomotiv'),
))

.HTML::checkbox(array( 
    'title' => __('Display Customers Own Notifications', 'woomotiv'),
    'description' => __('Enable this option if you want the logged in customers to see their own orders in the popups', 'woomotiv'),
    'name' => 'woomotiv_logged_own_orders', 
    'value' => woomotiv()->config->woomotiv_logged_own_orders,
    'text' => __('Enable','woomotiv'),
))

.HTML::checkbox(array( 
    'title' => __('Display Buyer Avatar', 'woomotiv'),
    'description' => __('If enabled the user avatar will be displayed instead of the product image','woomotiv'),
    'name' => 'woomotiv_user_avatar', 
    'value' => woomotiv()->config->woomotiv_user_avatar,
    'text' => __('Enable','woomotiv'),
))

.HTML::checkbox(array( 
    'title' => __('Hide on Mobile', 'woomotiv'),
    'name' => 'woomotiv_hide_on_mobile', 
    'value' => woomotiv()->config->woomotiv_hide_on_mobile,
    'text' => __('Hide','woomotiv'),
))

.HTML::checkbox(array( 
    'title' => __('Hide Close Button', 'woomotiv'),
    'name' => 'woomotiv_hide_close_button', 
    'value' => woomotiv()->config->woomotiv_hide_close_button,
    'text' => __('Hide','woomotiv'),
))

;
