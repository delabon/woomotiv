<?php

use WooMotiv\Framework\HTML;
use function WooMotiv\days_in_month;
use function WooMotiv\get_statistics;

$years = get_option('woomotiv_report_years', array());

if( empty( $years ) ){
    return HTML::div(
        HTML::h3( __('Report', 'woomotiv') )
        .HTML::p(__('Not enough data !', 'woomotiv') )
    );
}

$year = date('Y');
$month = 0;
$day = 0;
$days_list = array( 0 => __('Select day','woomotiv') );
$month_days = 0;
$filter_text = __('Year','woomotiv') . ": $year";

# Get selected year
if( isset( $_GET['year'] ) ){
    if( isset( $years[ (int)$_GET['year'] ] ) ){
        $year = (int)$_GET['year'];
    }
}

# Get selected month
if( isset( $_GET['month'] ) ){
    
    $tmp_val = (int)$_GET['month'];

    if( $tmp_val >= 1 && $tmp_val <= 12 ){

        $month = $tmp_val;
        $month_days = days_in_month( $month, $year );
        $filter_text .= " / " . __('Month','woomotiv') . ": $month";

        for ($i=1; $i <= $month_days; $i++) { 
            $days_list[ $i ] = $i;
        }
    }
}

# Get selected month
if( isset( $_GET['day'] ) ){

    $tmp_val = (int)$_GET['day'];

    if( $tmp_val >= 1 && $tmp_val <= $month_days ){
        $day = $tmp_val;
        $filter_text .= " / " . __('Day','woomotiv') . ": $day";
    }
}

# Get Stats From DB
$db_stats = get_statistics( $year, $month, $day);
$table_output = '';

if( count( $db_stats['products'] ) ){

    $products = $db_stats['products'];

    foreach ( $products as $row ) {
    
        $product = wc_get_product( (int)$row->product_id );

        if( ! $product ) continue;
        
        $image_id = $product->get_image_id();
        $thumb = wp_get_attachment_image( $image_id );
    
        $table_output .= HTML::tr(
            HTML::td( 
                HTML::a( $row->product_id
                    , array(
                        'href' => get_permalink( $row->product_id )
                    )
                )
            ) 
            .HTML::td( $thumb )
            .HTML::td( $row->post_title )
            .HTML::td( $row->totalClicks )
        );
    }
    
}

$table_output_2 = '';

if( count( $db_stats['custom_popups'] ) ){

    $popups = $db_stats['custom_popups'];

    foreach ( $popups as $row ) {
    
        $thumb = wp_get_attachment_image( $row->image_id );
    
        $table_output_2 .= HTML::tr(
            HTML::td( 
                HTML::span( $row->product_id )
            ) 
            .HTML::td( $thumb )
            .HTML::td( $row->content . '<br><br>' . $row->link )
            .HTML::td( $row->totalClicks )
        );
    }
    
}


return 

HTML::div(
    HTML::h3( __('Report', 'woomotiv') )

    .HTML::select(array( 
        'class' => 'vvoo_input vvoo_input_report_year',
        'items' => $years,
        'value' => $year,
        'wrapper' => false,
    ))

    .HTML::select(array( 
        'class' => 'vvoo_input vvoo_input_report_month',
        'value' => $month,
        'items' => array(
            0 => __('Select Month','woomotiv'), 
            1 => __('January','woomotiv'), 
            2 => __('February','woomotiv'),
            3 => __('March','woomotiv'), 
            4 => __('April','woomotiv'), 
            5 => __('May','woomotiv'), 
            6 => __('June','woomotiv'), 
            7 => __('July','woomotiv'), 
            8 => __('August','woomotiv'), 
            9 => __('September','woomotiv'), 
            10 => __('October','woomotiv'), 
            11 => __('November','woomotiv'), 
            12 => __('December','woomotiv')
        ),
        'wrapper' => false,
    ))

    .HTML::select(array( 
        'class' => 'vvoo_input vvoo_input_report_day',
        'items' => $days_list,
        'value' => $day,
        'wrapper' => false,
    ))

    .HTML::hr()

    .HTML::h4( __('Products','woomotiv') . ' / ' .$filter_text )

    .HTML::table(

        HTML::thead(
            HTML::tr(
                HTML::th( __('ID','woomotiv') )
                .HTML::th( __('Thumbnail','woomotiv') )
                .HTML::th( __('Product', 'woomotiv') )
                .HTML::th( __('Clicks','woomotiv') )
            )
        )

        .HTML::tbody(
            $table_output
        )

        , array(
            'id' => 'woom_report',
            'class' => "tablesorter"
        )
    )

    .HTML::h4( __('Custom Popups','woomotiv') . ' / ' . $filter_text )

    .HTML::table(

        HTML::thead(
            HTML::tr(
                HTML::th( __('ID','woomotiv') )
                .HTML::th( __('Thumbnail','woomotiv') )
                .HTML::th( __('Content / Url', 'woomotiv') )
                .HTML::th( __('Clicks','woomotiv') )
            )
        )

        .HTML::tbody(
            $table_output_2
        )

        , array(
            'id' => 'woom_report_2',
            'class' => "tablesorter"
        )
    )


    
)

;
