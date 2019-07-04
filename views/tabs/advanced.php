<?php 

use WooMotiv\Framework\HTML;

return 

HTML::checkbox(array( 
    'title' => __('Campaign Tracking', 'woomotiv'),
    'name' => 'woomotiv_tracking', 
    'value' => woomotiv()->config->woomotiv_tracking,
    'text' => __('Enable','woomotiv'),
))

.HTML::checkbox(array( 
    'title' => __('Disable Popup Link', 'woomotiv'),
    'description' => __( "Enable this option if you want to disable the popup link.",'woomotiv'),
    'name' => 'woomotiv_disable_link', 
    'value' => woomotiv()->config->woomotiv_disable_link,
    'text' => __('Enable','woomotiv'),
))

.HTML::input(array( 
    'title' => __('Product Title Max Words', 'woomotiv'),
    'description' => __('min: 1','woomotiv'),
    'name' => 'woomotiv_product_max_words', 
    'value' => woomotiv()->config->woomotiv_product_max_words,
))

;
