<?php 

use WooMotiv\Framework\HTML;

return

HTML::textarea(array( 
    'title' => __('Sale Content Template', 'woomotiv'),
    'description' => __('Available Template Tags','woomotiv') . ': 
        <br>{new_line} 
        <br>{product} 
        <br>{buyer} {buyer_username} {buyer_first_name} {buyer_last_name} 
        <br>{date} 
        <br>{city} {country} {state} 
        <br>{by_woomotiv}',
    'name' =>  'woomotiv_content_content', 
    'value' => woomotiv()->config->woomotiv_content_content
))

.HTML::textarea(array( 
    'title' => __('Review Content Template', 'woomotiv'),
    'description' => __('Available Template Tags','woomotiv') . ': 
        <br>{stars}
        <br>{new_line} 
        <br>{product} 
        <br>{buyer} {buyer_username} {buyer_first_name} {buyer_last_name}
        <br>{date}
        <br>{by_woomotiv}',
    'name' =>  'woomotiv_template_review', 
    'value' => woomotiv()->config->woomotiv_template_review
))

;
