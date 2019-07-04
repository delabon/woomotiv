<?php 

use WooMotiv\Framework\HTML;

return 

HTML::select(array( 
    'title' => __('Show / Hide On All Pages', 'woomotiv'),
    'description' => __( "Add the pages exluded below.",'woomotiv'),
    'name' =>  'woomotiv_filter', 
    'value' => woomotiv()->config->woomotiv_filter,
    'items' => array(
        'show' => __('Show','woomotiv'),
        'hide' => __('Hide','woomotiv'),
    ),
))

.HTML::textarea(array( 
    'title' => __('Pages Excluded', 'woomotiv'),
    'description' => __( "Add the excluded pages URL's here. ex:",'woomotiv') . '<br> http://mysite.com, http://mysite.com/product/hoodie-with-zipper/',
    'name' =>  'woomotiv_filter_pages', 
    'value' => woomotiv()->config->woomotiv_filter_pages,
    'placeholder' => 'http://mysite.com, http://mysite.com/product/hoodie-with-zipper/',
))

.HTML::checkbox(array( 
    'title' => __('Hide on All Articles', 'woomotiv'),
    'name' => 'woomotiv_filter_posts', 
    'value' => woomotiv()->config->woomotiv_filter_posts,
    'text' => __('Enable','woomotiv'),
))

.HTML::input(array( 
    'name' => 'woomotiv_woocategories', 
    'value' => woomotiv()->config->woomotiv_woocategories,
    'title' => __('Show Only On These Woocommerce Categories', 'woomotiv'),
    'description' => __('Leave empty if you want to show popups on all categories.','woomotiv')
                        .'<br> ex: 6,18,10',
))

;
