<?php 

namespace WooMotiv;

use Automattic\WooCommerce\Utilities\OrderUtil;

/**
 * Returns DateTime object of the current time with the WP timezone
 *
 * @return DateTime
 */
function date_now(){
    return new \DateTime( "now", Timezone::getWpTimezone() );
}

/**
 * Convert a date to the wordpress timezone
 *@param string|DateTime $date
 * @return DateTime
 */
function convert_timezone( $date = null ){

    $timezone = Timezone::getWpTimezone();

    if( ! $date ){
        $date = new \DateTime();
    }

    if( is_string( $date ) ) {
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
function mod_avatar( $id_or_email, $default = '', $alt = '' ){

    $file = get_avatar_url( $id_or_email, array( 'size' => 150, 'default' => 404 ) );
    $file_headers = @get_headers( $file, 0  );

    if( !$file_headers || strpos( $file_headers[0], '404 Not Found') !== false ) {
        return '<img src="'.$default.'" alt="'.$alt.'">';        
    }

    return '<img src="'.$file.'" alt="'.$alt.'">';        
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
function days_in_month($month, $year) { 
    // calculate number of days in a month 
    return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31); 
}

/**
 * Return stats rows
 * @return array
 */
function get_statistics( $year, $month = 0, $day = 0 ){

    global $wpdb;

    # Stats for products
    $sql = "SELECT product_id, SUM( clicks ) AS totalClicks, post_title
            FROM {$wpdb->prefix}woomotiv_stats 
            LEFT JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}woomotiv_stats.product_id = {$wpdb->prefix}posts.ID
            WHERE {$wpdb->prefix}woomotiv_stats.the_year={$year} AND popup_type IN ('order', 'review')";
    
    if( $month > 0 ){
        $sql .= " AND {$wpdb->prefix}woomotiv_stats.the_month={$month}";
    }

    if( $day > 0 ){
        $sql .= " AND {$wpdb->prefix}woomotiv_stats.the_day={$day}";
    }

    $sql .= " GROUP BY {$wpdb->prefix}woomotiv_stats.product_id";
    $products_stats = $wpdb->get_results( $sql );

    # stats for custom popups
    $sql = "SELECT product_id, SUM( clicks ) AS totalClicks, image_id, content, link
            FROM {$wpdb->prefix}woomotiv_stats 
            LEFT JOIN {$wpdb->prefix}woomotiv_custom_popups AS CPOPUP 
                ON {$wpdb->prefix}woomotiv_stats.product_id = CPOPUP.id
            WHERE 
                {$wpdb->prefix}woomotiv_stats.the_year={$year} 
                AND popup_type = 'custom'";

    if( $month > 0 ){
        $sql .= " AND {$wpdb->prefix}woomotiv_stats.the_month={$month}";
    }

    if( $day > 0 ){
        $sql .= " AND {$wpdb->prefix}woomotiv_stats.the_day={$day}";
    }

    $sql .= " GROUP BY {$wpdb->prefix}woomotiv_stats.product_id";
    $custom_stats = $wpdb->get_results( $sql );

    return array(
        'products' => $products_stats,
        'custom_popups' => $custom_stats,
    );    
}

/**
 * Get orders
 * 
 * @param array $excluded_orders
 * @return array
 */
function get_orders( $excluded_orders = [] ){

    global $wpdb;

    $excluded_orders = ! is_array($excluded_orders) ? [] : $excluded_orders;
    $is_random = woomotiv()->config->woomotiv_display_order === 'random_sales' ? true : false;

    $raw = "
        SELECT 
            POSTS.ID, POSTS.post_status, POSTS.post_type, 
            POSTMETA.meta_value AS 'customer_id'
        
        FROM 
            {$wpdb->prefix}posts AS POSTS
        
        LEFT JOIN
            {$wpdb->prefix}postmeta AS POSTMETA
                ON 
                    POSTMETA.post_id = POSTS.ID
                AND
                    POSTMETA.meta_key = '_customer_user'
    ";

    // This IF block will be auto removed from the Free version.
    if( woomotiv()->config->woomotiv_display_processing_orders == 1 ){
        $raw .= "
            WHERE
                POSTS.post_status IN ('wc-completed','wc-processing')
        ";
    }
    else{
        $raw .= "
            WHERE
                POSTS.post_status = 'wc-completed' 
        ";
    }

    $raw .= " AND POSTS.post_type = 'shop_order'";

    // Make sure it is a parent order
    $raw .= " AND POSTS.post_parent = 0";

    // Only if has products
    $raw .= " AND (SELECT COUNT(*) AS total_products FROM {$wpdb->prefix}woocommerce_order_items AS WOI where WOI.order_id = POSTS.ID) > 0";

    // Excluded orders
    if( count($excluded_orders) ){
        $excluded_orders_str = implode(',', $excluded_orders);
        $raw .= " AND POSTS.ID NOT IN ({$excluded_orders_str})";
    }

    // exclude current user orders
    if( is_user_logged_in() ){
        if( current_user_can('manage_options') ){
            if( (int)woomotiv()->config->woomotiv_admin_popups == 0 ){
                $raw .= ' AND POSTMETA.meta_value != ' . get_current_user_id();                    
            }
        } 
        else{
            if( (int)woomotiv()->config->woomotiv_logged_own_orders == 0 ){
                $raw .= ' AND POSTMETA.meta_value != ' . get_current_user_id();
            }
        }
    }

    // random or recent sales
    if( $is_random ){
        $raw .= " ORDER BY RAND()"; 
    }
    else{
        $raw .= " ORDER BY POSTS.post_date DESC"; 
    }

    // limit
    $raw .= " LIMIT " . woomotiv()->config->woomotiv_limit;
    
    $orders = array();

    // exlcuded products
    $excludedProducts = [];
    if( woomotiv()->config->woomotiv_filter_products !== '' && woomotiv()->config->woomotiv_filter_products !== '0' ){
        $excludedProducts = woomotiv()->config->woomotiv_filter_products;
        $excludedProducts = explode( ',', $excludedProducts );
    }

    foreach ( $wpdb->get_results( $raw ) as $data ) {

        $order = wc_get_order( $data->ID );
        $items = $order->get_items();
        $products = array();

        // only keep the published products
        foreach( $items as $item ){
            if( $item->get_product() ){
                $product = $item->get_product();

                // exlcude products
                if( ! in_array( $product->get_id(), $excludedProducts ) ){
                    $products[] = $product;
                }
            }
        } 

        if( ! count( $products ) ){
            continue;
        }

        // select a random product
        $random = array_rand( $products, 1 );
        $product = $products[ $random ];

        $orders[] = (object)array( 
            'id' => $data->ID, 
            'order' => $order,
            'product' => $product,
        );
    }

    // Mysql ORDER BY RAND() returns a cached query after the first time
    if( $is_random ){
        shuffle($orders);
    }

    return $orders;
}

/**
 * Clear cookies
 *
 * @return void
 */
function clear_cookies(){
    $cookie_key = 'woomotiv_seen_products_' . woomotiv()->get_site_hash();
    unset($_COOKIE[$cookie_key]); 
    setcookie($cookie_key, null, -1, '/'); 

    $cookie_key = 'woomotiv_seen_reviews_' . woomotiv()->get_site_hash();
    unset($_COOKIE[$cookie_key]); 
    setcookie($cookie_key, null, -1, '/'); 

    $cookie_key = 'woomotiv_seen_custompop_' . woomotiv()->get_site_hash();
    unset($_COOKIE[$cookie_key]); 
    setcookie($cookie_key, null, -1, '/'); 
}

/**
 * Get see order items
 *
 * @return array
 */
function get_seen_order_items(){
    $cookie_key = 'woomotiv_seen_products_' . woomotiv()->get_site_hash();
    $excluded = isset($_COOKIE[$cookie_key]) ? $_COOKIE[$cookie_key] : '';
    $excluded = array_filter(explode(',', $excluded));

    return $excluded;
}

/**
 * Get seen reviews
 *
 * @return array
 */
function get_seen_reviews(){
    $cookie_key = 'woomotiv_seen_reviews_' . woomotiv()->get_site_hash();
    $excluded = isset($_COOKIE[$cookie_key]) ? $_COOKIE[$cookie_key] : '';
    $excluded = array_filter(explode(',', $excluded));

    return $excluded;
}

/**
 * Get seen custom popups
 *
 * @return array
 */
function get_seen_custom_popups(){
    $cookie_key = 'woomotiv_seen_custompop_' . woomotiv()->get_site_hash();
    $excluded = isset($_COOKIE[$cookie_key]) ? $_COOKIE[$cookie_key] : '';
    $excluded = array_filter(explode(',', $excluded));

    return $excluded;
}

/**
 * Get orders
 * 
 * @param array $excluded_order_items
 * @return array
 */
function get_products(){

    global $wpdb;

    $excluded_order_items = get_seen_order_items();
    $is_outofstock_visible = woomotiv()->config->woomotiv_filter_out_of_stock == 1 ? true : false;
    $is_random = woomotiv()->config->woomotiv_display_order === 'random_sales' ? true : false;

    if( woomotiv()->config->woomotiv_filter_products !== '' && woomotiv()->config->woomotiv_filter_products !== '0' ){
        $excluded_products = woomotiv()->config->woomotiv_filter_products;
        $excluded_products = array_filter(explode( ',', $excluded_products ));
    }


    if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
        // HPOS usage is enabled.
        $raw = "
            SELECT 
                C.ID AS product_id, 
                A.order_id,
                A.order_item_id,
                D.status AS order_status,
                D.customer_id,
                J.meta_value AS stock_status
            FROM 
                {$wpdb->prefix}woocommerce_order_items AS A
            INNER JOIN
                {$wpdb->prefix}woocommerce_order_itemmeta AS B
                    ON
                        A.order_item_id = B.order_item_id
                    AND
                        B.meta_key = '_product_id'
            INNER JOIN
                {$wpdb->prefix}posts AS C
                    ON
                        C.ID = B.meta_value
                    AND
                        C.post_status = 'publish'
            INNER JOIN
                {$wpdb->prefix}wc_orders AS D
                    ON
                        A.order_id = D.id
            LEFT JOIN
                {$wpdb->prefix}postmeta AS F
                    ON 
                        F.post_id = D.ID
                    AND
                        F.meta_key = '_customer_user'
            INNER JOIN
                {$wpdb->prefix}postmeta AS J
                ON
                    J.post_id = C.ID
                AND
                    J.meta_key = '_stock_status'
            WHERE
                A.order_item_type = 'line_item'
        ";

        if (woomotiv()->config->woomotiv_display_processing_orders == 1) {
            $raw .= " AND D.status IN ('wc-completed','wc-processing')";
        } else {
            $raw .= " AND D.status = 'wc-completed'";
        }

        // Make sure it is a parent order
        $raw .= " AND D.parent_order_id = 0";

        // Is out of stock enabled?
        if( ! $is_outofstock_visible ){
            $raw .= " AND J.meta_value != 'outofstock'";
        }

        // Excluded order items ( No-repeat functionality)
        if( count($excluded_order_items) ){
            $excluded_order_items_str = implode(',', $excluded_order_items);
            $raw .= " AND A.order_item_id NOT IN ({$excluded_order_items_str})";
        }

        // Excluded products
        if( isset($excluded_products) && count($excluded_products) ){
            $excluded_products_str = implode(',', $excluded_products);
            $raw .= " AND C.ID NOT IN ({$excluded_products_str})";
        }

        // exclude current user orders
        if( is_user_logged_in() ){
            if( current_user_can('manage_options') ){
                if( (int)woomotiv()->config->woomotiv_admin_popups == 0 ){
                    $raw .= ' AND D.customer_id != ' . get_current_user_id();
                }
            }
            else{
                if( (int)woomotiv()->config->woomotiv_logged_own_orders == 0 ){
                    $raw .= ' AND D.customer_id != ' . get_current_user_id();
                }
            }
        }
    } else {
        $raw = "
            SELECT 
                C.ID AS product_id, 
                A.order_id,
                A.order_item_id,
                D.post_status AS order_status,
                F.meta_value AS customer_id,
                J.meta_value AS stock_status
            FROM 
                {$wpdb->prefix}woocommerce_order_items AS A
            INNER JOIN
                {$wpdb->prefix}woocommerce_order_itemmeta AS B
                    ON
                        A.order_item_id = B.order_item_id
                    AND
                        B.meta_key = '_product_id'
            INNER JOIN
                {$wpdb->prefix}posts AS C
                    ON
                        C.ID = B.meta_value
                    AND
                        C.post_status = 'publish'
            INNER JOIN
                {$wpdb->prefix}posts AS D
                    ON
                        A.order_id = D.ID
                    AND
                        (
                            D.post_type = 'shop_order'
                            OR
                            D.post_type = 'shop_order_placehold'    
                        )
            LEFT JOIN
                {$wpdb->prefix}postmeta AS F
                    ON 
                        F.post_id = D.ID
                    AND
                        F.meta_key = '_customer_user'
            INNER JOIN
                {$wpdb->prefix}postmeta AS J
                ON
                    J.post_id = C.ID
                AND
                    J.meta_key = '_stock_status'
            WHERE
                A.order_item_type = 'line_item'
        ";

        if (woomotiv()->config->woomotiv_display_processing_orders == 1) {
            $raw .= " AND D.post_status IN ('wc-completed','wc-processing')";
        } else {
            $raw .= " AND D.post_status = 'wc-completed'";
        }

        // Make sure it is a parent order
        $raw .= " AND D.post_parent = 0";

        // Is out of stock enabled?
        if( ! $is_outofstock_visible ){
            $raw .= " AND J.meta_value != 'outofstock'";
        }

        // Excluded order items ( No-repeat functionality)
        if( count($excluded_order_items) ){
            $excluded_order_items_str = implode(',', $excluded_order_items);
            $raw .= " AND A.order_item_id NOT IN ({$excluded_order_items_str})";
        }

        // Excluded products
        if( isset($excluded_products) && count($excluded_products) ){
            $excluded_products_str = implode(',', $excluded_products);
            $raw .= " AND C.ID NOT IN ({$excluded_products_str})";
        }

        // exclude current user orders
        if( is_user_logged_in() ){
            if( current_user_can('manage_options') ){
                if( (int)woomotiv()->config->woomotiv_admin_popups == 0 ){
                    $raw .= ' AND F.meta_value != ' . get_current_user_id();
                }
            }
            else{
                if( (int)woomotiv()->config->woomotiv_logged_own_orders == 0 ){
                    $raw .= ' AND F.meta_value != ' . get_current_user_id();
                }
            }
        }
    }

    // random or recent sales
    if( $is_random ){
        $raw .= " ORDER BY RAND()";
    }
    else{
        $raw .= " ORDER BY A.order_item_id DESC";
    }

    // limit
    $raw .= " LIMIT " . woomotiv()->config->woomotiv_limit;

    $products = array();

    foreach ( $wpdb->get_results( $raw ) as $data ) {

        $order = wc_get_order( (int)$data->order_id );

        $products[] = [
            'id' => $data->product_id, 
            'order' => $order,
            'order_id' => (int)$data->order_id,
            'order_item_id' => (int)$data->order_item_id,
            'product' => wc_get_product( (int)$data->product_id ),
        ];
    }

    // Mysql ORDER BY RAND() returns a cached query after the first time
    if( $is_random ){
        shuffle($products);
    }

    return $products;
}

/**
 * Get reviews
 *
 * @param array $excluded
 * @return array
 */
function get_reviews(){

    global $wpdb;

    $excluded_reviews = get_seen_reviews();
    $is_random = woomotiv()->config->woomotiv_display_order === 'random_sales' ? true : false;

    $raw = "
        SELECT 
            *
        FROM 
            {$wpdb->prefix}comments AS COMMENT

        INNER JOIN
            {$wpdb->prefix}posts AS PTS 
                ON  
                    PTS.ID = COMMENT.comment_post_ID
                AND
                    PTS.post_type = 'product'
                AND
                    PTS.post_status = 'publish'
                    
        LEFT JOIN
            {$wpdb->prefix}commentmeta AS CMETA 
                ON 
                    CMETA.comment_id = COMMENT.comment_ID
                AND 
                    CMETA.meta_key = 'rating'
    ";

    // WHERE
    $raw .= " 
        WHERE 
            COMMENT.comment_author != 'WooCommerce' 
            AND COMMENT.comment_type = 'review' 
            AND COMMENT.comment_approved = 1 
            AND CMETA.meta_value > 3
    ";

    // Excluded orders
    if( count($excluded_reviews) ){
        $excluded_reviews_str = implode(',', $excluded_reviews);
        $raw .= " AND COMMENT.comment_ID NOT IN ({$excluded_reviews_str})";
    }

    // random or recent sales
    if( $is_random ){
        $raw .= " ORDER BY RAND()"; 
    }
    else{
        $raw .= " ORDER BY COMMENT.comment_date DESC"; 
    }
    
    // LIMIT
    $raw .= " LIMIT " . woomotiv()->config->woomotiv_limit;
    
    // Return
    $reviews = array();

    // exlcuded products
    $excludedProducts = [];
    if( woomotiv()->config->woomotiv_filter_products !== '' && woomotiv()->config->woomotiv_filter_products !== '0' ){
        $excludedProducts = woomotiv()->config->woomotiv_filter_products;
        $excludedProducts = explode( ',', $excludedProducts );
    }

    foreach ( $wpdb->get_results( $raw ) as $data ) {

        $product = wc_get_product( $data->comment_post_ID );
        
        if( ! $product ){
            continue;
        }

        // exlcude products
        if( in_array( $product->get_id(), $excludedProducts ) ){
            continue;
        }
        
        $rating = array( 
            'id'            => $data->comment_ID, 
            'rating'        => $data->meta_value,
            'product_id'    => $data->comment_post_ID,
            'product_name'  => $product->get_name(),
            'product_url'   => get_the_permalink( $data->comment_post_ID ),
            'date'          => $data->comment_date,
            'date_gmt'      => $data->comment_date_gmt,
            'user_id'       => $data->user_id,
            'username'      => $data->comment_author,
        );

        if( $rating['user_id'] != 0 ){
            $usermeta = get_user_meta( $data->user_id );

            if (!empty($usermeta)) {
                $rating['user_first_name'] = isset($usermeta['first_name'], $usermeta['first_name'][0]) ? $usermeta['first_name'][0] : '';
                $rating['user_last_name'] = isset($usermeta['last_name'], $usermeta['last_name'][0]) ? $usermeta['last_name'][0] : '';
            }
        }
        
        $reviews[] = (object)$rating;
    }

    // Mysql ORDER BY RAND() returns a cached query after the first time
    if( $is_random ){
        shuffle($reviews);
    }

    return $reviews;
}

/**
 * Get custom popups
 *
 * @return array
 */
function get_custom_popups(){
    
    global $wpdb;

    $excluded_popups = get_seen_custom_popups();
    $now = convert_timezone( new \DateTime() );
    $today = convert_timezone( $now->format('F d, Y') );
    $date = $today->format('Y-m-d H:i:s');

    $raw = "
        SELECT 
            *
        FROM 
            {$wpdb->prefix}woomotiv_custom_popups
        WHERE 
            date_ends >= '$date'
    ";

    // Excluded
    if( count($excluded_popups) ){
        $excluded_popups_str = implode(',', $excluded_popups);
        $raw .= " AND id NOT IN ({$excluded_popups_str})";
    }

    $raw .= " ORDER BY id DESC";
    
    // LIMIT
    $raw .= " LIMIT " . woomotiv()->config->woomotiv_limit;
    
    return $wpdb->get_results( $raw );
}

/**
 * Check for valid nonce
 * @return bool
 */
function validateNounce(){
    if( ! wp_verify_nonce( woomotiv()->request->post( 'nonce', null ), 'woomotiv' ) ) {
        response( false );
    }
}

/**
 * Return json response and die
 */
function response( $success, $data = array() ){
    if( $success ){
        wp_send_json_success( $data );
    }
    
    wp_send_json_error( $data );
}
