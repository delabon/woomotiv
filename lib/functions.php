<?php

namespace WooMotiv;

/**
 * Returns a link to upgrade
 *
 * @return string
 */
function upgrade_link()
{
    if ( wmv_fs()->is_free_plan() ) {
        return '&nbsp;&nbsp;<a style="color:red;" href="' . admin_url( 'admin.php?page=woomotiv-pricing' ) . '">' . __( 'Upgrade', 'woomotiv' ) . '</a>';
    }
}

/**
 * Returns an upgrade notice
 *
 * @return string
 */
function upgrade_notice()
{
    if ( wmv_fs()->is_free_plan() ) {
        return '
            <div class="dlb_alert _error">' . __( 'Please upgrade to use this feature. Upgrading helps me complete developing Woomotiv.', 'woomotiv' ) . upgrade_link() . '</div>';
    }
}

/**
 * Returns DateTime object of the current time with the WP timezone
 *
 * @return DateTime
 */
function date_now()
{
    return new \DateTime( "now", Timezone::getWpTimezone() );
}

/**
 * Convert a date to the wordpress timezone
 *@param string|DateTime $date
 * @return DateTime
 */
function convert_timezone( $date = null )
{
    $timezone = Timezone::getWpTimezone();
    if ( !$date ) {
        $date = new \DateTime();
    }
    if ( is_string( $date ) ) {
        $date = new \DateTime( $date );
    }
    $date->setTimezone( $timezone );
    return $date;
}

/**
 * Undocumented function
 *
 * @param [type] $id_or_email
 * @param [type] $args
 * @return void
 */
function mod_avatar( $id_or_email, $default = '', $alt = '' )
{
    $file = get_avatar_url( $id_or_email, array(
        'size'    => 150,
        'default' => 404,
    ) );
    $file_headers = @get_headers( $file, 0 );
    if ( !$file_headers || strpos( $file_headers[0], '404 Not Found' ) !== false ) {
        return '<img src="' . $default . '" alt="' . $alt . '">';
    }
    return '<img src="' . $file . '" alt="' . $alt . '">';
}

/** 
 * days_in_month($month, $year) 
 * Returns the number of days in a given month and year, taking into account leap years. 
 * 
 * $month: numeric month (integers 1-12) 
 * $year: numeric year (any integer) 
 * 
 * Prec: $month is an integer between 1 and 12, inclusive, and $year is an integer. 
 * Post: none 
**/
function days_in_month( $month, $year )
{
    // calculate number of days in a month
    return ( $month == 2 ? ( $year % 4 ? 28 : (( $year % 100 ? 29 : (( $year % 400 ? 28 : 29 )) )) ) : (( ($month - 1) % 7 % 2 ? 30 : 31 )) );
}

/**
 * Return stats rows
 * @return array
 */
function get_statistics( $year, $month = 0, $day = 0 )
{
}

/**
 * Get orders
 * 
 * @return array
 */
function get_orders()
{
    global  $wpdb ;
    $raw = "\n        SELECT \n            POSTS.ID, POSTS.post_status, POSTS.post_type, \n            WCITEMS.order_item_id, WCITEMS.order_item_type, \n            WCITEMMETA.meta_value  AS 'product_id', \n            POSTMETA.meta_value AS 'customer_id'\n        \n        FROM {$wpdb->prefix}posts AS POSTS\n        \n        LEFT JOIN\n            {$wpdb->prefix}woocommerce_order_items AS WCITEMS \n                ON \n                    WCITEMS.order_id = POSTS.ID\n                AND \n                    WCITEMS.order_item_type IN ('line_item')\n        \n        LEFT JOIN\n            {$wpdb->prefix}woocommerce_order_itemmeta AS WCITEMMETA\n                ON\n                    WCITEMS.order_item_id = WCITEMMETA.order_item_id\n                AND\n                    WCITEMMETA.meta_key = '_product_id'\n        \n        LEFT JOIN\n            {$wpdb->prefix}postmeta AS POSTMETA\n                ON \n                    POSTMETA.post_id = POSTS.ID\n                AND\n                    POSTMETA.meta_key = '_customer_user'\n        ";
    $raw .= "\n            WHERE\n                post_status = 'wc-completed'\n                AND\n                post_type = 'shop_order'\n        ";
    // exclude current user orders
    if ( is_user_logged_in() ) {
        
        if ( current_user_can( 'manage_options' ) ) {
            if ( (int) woomotiv()->config->woomotiv_admin_popups == 0 ) {
                $raw .= ' AND wp_postmeta.meta_value <> ' . get_current_user_id();
            }
        } else {
            if ( (int) woomotiv()->config->woomotiv_logged_own_orders == 0 ) {
                $raw .= ' AND wp_postmeta.meta_value <> ' . get_current_user_id();
            }
        }
    
    }
    // random or recent sales
    
    if ( woomotiv()->config->woomotiv_display_order == 'random_sales' ) {
        $raw .= " ORDER BY RAND()";
    } else {
        $raw .= " ORDER BY POSTS.post_date DESC";
    }
    
    // limit
    $raw .= " LIMIT " . woomotiv()->config->woomotiv_limit;
    $orders = array();
    foreach ( $wpdb->get_results( $raw ) as $data ) {
        $order = wc_get_order( $data->ID );
        $items = $order->get_items();
        $products = array();
        // only keep the published products
        foreach ( $items as $item ) {
            
            if ( $item->get_product() ) {
                $product = $item->get_product();
                $products[] = $product;
            }
        
        }
        if ( !count( $products ) ) {
            continue;
        }
        // select a random product
        $random = array_rand( $products, 1 );
        $product = $products[$random];
        $orders[] = (object) array(
            'id'      => $data->ID,
            'order'   => wc_get_order( $data->ID ),
            'product' => $product,
        );
    }
    return $orders;
}

/**
 * Get reviews
 *
 * @return array
 */
function get_reviews()
{
    /** Only Premium */
    if ( wmv_fs()->is_free_plan() ) {
        return array();
    }
}

/**
 * Get custom popups
 *
 * @return array
 */
function get_custom_popups()
{
    /** Only Premium */
    if ( wmv_fs()->is_free_plan() ) {
        return array();
    }
}

/**
 * Check for valid nonce
 * @return bool
 */
function validateNounce()
{
    if ( !wp_verify_nonce( woomotiv()->request->post( 'nonce', null ), 'woomotiv' ) ) {
        response( false );
    }
}

/**
 * Return json response and die
 */
function response( $success, $data = array() )
{
    if ( $success ) {
        wp_send_json_success( $data );
    }
    wp_send_json_error( $data );
}
