<?php

global $wpdb;

use WooMotiv\Framework\Helper;
use WooMotiv\Framework\HTML;

// get products
$products = array(
    0 => __('Select products to exclude', 'woomotiv')
);

$productRecords = $wpdb->get_results("
    SELECT *
    FROM {$wpdb->prefix}posts
    WHERE
        post_type = 'product'
");

foreach( $productRecords as $product ){
    $products[$product->ID] = $product->post_title;
}

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
    'description' => __( "Add the excluded pages URL's here. ex:",'woomotiv') 
                    . '<br> http://mysite.com, http://mysite.com/product/hoodie-with-zipper/'
                    . '<br><br> <strong>' .__('You can also use a wildcard at the end:') . '</strong> <br>http://mysite.com/my-page-slug<strong>*</strong>',
    'name' =>  'woomotiv_filter_pages', 
    'value' => woomotiv()->config->woomotiv_filter_pages,
    'placeholder' => 'http://mysite.com, http://mysite.com/product/hoodie-with-zipper/',
))

.HTML::checkbox(array(
    'title' => __('Keep on URLs with query', 'woomotiv'),
    'description' => __( "Allow popups on URLs with query http://mysite.com?a=123",'woomotiv'),
    'name' => 'woomotiv_filter_keep_query_pages',
    'value' => woomotiv()->config->woomotiv_filter_keep_query_pages,
    'text' => __('Enable','woomotiv'),
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

.HTML::selectMultiple(array( 
    'title' => __('Products Excluded', 'woomotiv'),
    'description' => __('Click on <strong>"ctrl"</strong> and select from the list.','woomotiv'),
    'name' =>  'woomotiv_filter_products', 
    'value' => woomotiv()->config->woomotiv_filter_products,
    'items' => $products,
))

.HTML::checkbox(array( 
    'title' => __('Show out of stock products', 'woomotiv'),
    'description' => __( "Enable this if you want to show out of stock products.",'woomotiv'),
    'name' => 'woomotiv_filter_out_of_stock', 
    'value' => woomotiv()->config->woomotiv_filter_out_of_stock,
    'text' => __('Enable','woomotiv'),
))

;
