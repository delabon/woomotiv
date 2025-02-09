<?php

namespace Woomotiv;

use Automattic\WooCommerce\Utilities\OrderUtil;
use DateTime;

/**
 * Returns DateTime object of the current time with the WP timezone
 */
function date_now(): DateTime
{
    return new DateTime("now", Timezone::getWpTimezone());
}

/**
 * Convert a date to the WordPress timezone
 */
function convert_timezone($date = null)
{
    $date = $date ?? new DateTime();
    $date = is_string($date) ? new DateTime($date) : $date;
    $date->setTimezone(Timezone::getWpTimezone());

    return $date;
}

/**
 * Get user's avatar
 */
function mod_avatar($id_or_email, $default = '', $alt = ''): string
{
    $file = get_avatar_url($id_or_email, array('size' => 150, 'default' => 404));
    $file_headers = @get_headers($file, 0);

    if (!$file_headers || strpos($file_headers[0], '404 Not Found') !== false) {
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
function days_in_month($month, $year): int
{
    return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
}

/**
 * Return stats rows
 */
function get_statistics(int $year, int $month = 0, int $day = 0): array
{
    global $wpdb;

    // Stats for products
    $query_parts = [
        "SELECT product_id, SUM( clicks ) AS totalClicks, post_title",
        "FROM {$wpdb->prefix}woomotiv_stats",
        "LEFT JOIN {$wpdb->prefix}posts ON {$wpdb->prefix}woomotiv_stats.product_id = {$wpdb->prefix}posts.ID",
        "WHERE {$wpdb->prefix}woomotiv_stats.the_year = %d AND popup_type IN ('order', 'review')"
    ];

    $query_params = [$year];

    if ($month > 0) {
        $query_parts[] = "AND {$wpdb->prefix}woomotiv_stats.the_month = %d";
        $query_params[] = $month;
    }

    if ($day > 0) {
        $query_parts[] = "AND {$wpdb->prefix}woomotiv_stats.the_day = %d";
        $query_params[] = $day;
    }

    $query_parts[] = "GROUP BY {$wpdb->prefix}woomotiv_stats.product_id";
    $products_stats = $wpdb->get_results($wpdb->prepare(implode(' ', $query_parts), ...$query_params)); // phpcs:ignore

    // Stats for custom popups
    $query_parts = [
        "SELECT product_id, SUM( clicks ) AS totalClicks, image_id, content, link",
        "FROM {$wpdb->prefix}woomotiv_stats",
        "LEFT JOIN {$wpdb->prefix}woomotiv_custom_popups AS CPOPUP",
        "ON {$wpdb->prefix}woomotiv_stats.product_id = CPOPUP.id",
        "WHERE {$wpdb->prefix}woomotiv_stats.the_year = %d AND popup_type = 'custom'"
    ];

    $query_params = [$year];

    if ($month > 0) {
        $query_parts[] = "AND {$wpdb->prefix}woomotiv_stats.the_month = %d";
        $query_params[] = $month;
    }

    if ($day > 0) {
        $query_parts[] = "AND {$wpdb->prefix}woomotiv_stats.the_day = %d";
        $query_params[] = $day;
    }

    $query_parts[] = "GROUP BY {$wpdb->prefix}woomotiv_stats.product_id";
    $custom_stats = $wpdb->get_results($wpdb->prepare(implode(' ', $query_parts), ...$query_params)); // phpcs:ignore

    return [
        'products' => $products_stats,
        'custom_popups' => $custom_stats,
    ];
}

/**
 * Clear cookies
 */
function clear_cookies(): void
{
    $cookie_key = 'woomotiv_seen_products_' . woomotiv()->get_site_hash();
    unset($_COOKIE[$cookie_key]);
    setcookie($cookie_key, '', -1, '/');

    $cookie_key = 'woomotiv_seen_reviews_' . woomotiv()->get_site_hash();
    unset($_COOKIE[$cookie_key]);
    setcookie($cookie_key, '', -1, '/');

    $cookie_key = 'woomotiv_seen_custompop_' . woomotiv()->get_site_hash();
    unset($_COOKIE[$cookie_key]);
    setcookie($cookie_key, '', -1, '/');
}

/**
 * Get see order items
 */
function get_seen_order_items(): array
{
    $cookie_key = 'woomotiv_seen_products_' . woomotiv()->get_site_hash();
    $excluded = isset($_COOKIE[$cookie_key]) ? $_COOKIE[$cookie_key] : '';

    return array_filter(explode(',', $excluded));
}

/**
 * Get seen reviews
 */
function get_seen_reviews(): array
{
    $cookie_key = 'woomotiv_seen_reviews_' . woomotiv()->get_site_hash();
    $excluded = isset($_COOKIE[$cookie_key]) ? $_COOKIE[$cookie_key] : '';

    return array_filter(array_map('intval', explode(',', $excluded))); // force int to prevent SQL injection
}

/**
 * Get seen custom popups
 */
function get_seen_custom_popups(): array
{
    $cookie_key = 'woomotiv_seen_custompop_' . woomotiv()->get_site_hash();
    $excluded = isset($_COOKIE[$cookie_key]) ? $_COOKIE[$cookie_key] : '';

    return array_filter(array_map('intval', explode(',', $excluded)));
}

/**
 * Get orders
 */
function get_products(): array
{
    global $wpdb;

    $is_random = woomotiv()->config->woomotiv_display_order === 'random_sales' ? true : false;

    if (OrderUtil::custom_orders_table_usage_is_enabled()) {
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

        $raw = excludeOutOfStock($raw);
        $raw = excludeOrderItems($raw);
        $raw = excludeProducts($raw);

        // exclude current user orders
        if (is_user_logged_in()) {
            if (current_user_can('manage_options')) {
                if ((int)woomotiv()->config->woomotiv_admin_popups == 0) {
                    $raw .= ' AND D.customer_id != ' . get_current_user_id();
                }
            } else {
                if ((int)woomotiv()->config->woomotiv_logged_own_orders == 0) {
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

        $raw = excludeOutOfStock($raw);
        $raw = excludeOrderItems($raw);
        $raw = excludeProducts($raw);

        // exclude current user orders
        if (is_user_logged_in()) {
            if (current_user_can('manage_options')) {
                if ((int)woomotiv()->config->woomotiv_admin_popups == 0) {
                    $raw .= ' AND F.meta_value != ' . get_current_user_id();
                }
            } else {
                if ((int)woomotiv()->config->woomotiv_logged_own_orders == 0) {
                    $raw .= ' AND F.meta_value != ' . get_current_user_id();
                }
            }
        }
    }

    // random or recent sales
    if ($is_random) {
        $raw .= " ORDER BY RAND()";
    } else {
        $raw .= " ORDER BY A.order_item_id DESC";
    }

    // limit
    $raw .= " LIMIT " . (int) woomotiv()->config->woomotiv_limit;

    $products = array();

    foreach ($wpdb->get_results($raw) as $data) { // phpcs:ignore
        $order = wc_get_order((int)$data->order_id);

        $products[] = [
            'id' => $data->product_id,
            'order' => $order,
            'order_id' => (int)$data->order_id,
            'order_item_id' => (int)$data->order_item_id,
            'product' => wc_get_product((int)$data->product_id),
        ];
    }

    // Mysql ORDER BY RAND() returns a cached query after the first time
    if ($is_random) {
        shuffle($products);
    }

    return $products;
}

function excludeOutOfStock(string $raw): string
{
    if (! woomotiv()->config->woomotiv_filter_out_of_stock == 1) {
        $raw .= " AND J.meta_value != 'outofstock'";
    }

    return $raw;
}

function excludeProducts(string $raw): string
{
    $excludedProducts = [];

    if (woomotiv()->config->woomotiv_filter_products !== '' && woomotiv()->config->woomotiv_filter_products !== '0') {
        $excludedProducts = woomotiv()->config->woomotiv_filter_products;
        $excludedProducts = array_filter(array_map('intval', explode(',', $excludedProducts)));
    }

    if (!empty($excludedProducts)) {
        $excluded_products_str = implode(',', $excludedProducts);
        $raw .= " AND C.ID NOT IN ({$excluded_products_str})";
    }

    return $raw;
}

function excludeOrderItems(string $raw): string
{
    $excluded_order_items = get_seen_order_items();
    $excluded_order_items = array_filter(array_map('intval', $excluded_order_items)); // force int to prevent SQL injection

    if (!empty($excluded_order_items)) {
        $excluded_str = implode(',', $excluded_order_items);
        $raw .= " AND A.order_item_id NOT IN ($excluded_str)";
    }

    return $raw;
}

/**
 * Get reviews
 */
function get_reviews()
{
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
    if (count($excluded_reviews)) {
        $excluded_reviews_str = implode(',', $excluded_reviews);
        $raw .= " AND COMMENT.comment_ID NOT IN ({$excluded_reviews_str})";
    }

    // random or recent sales
    if ($is_random) {
        $raw .= " ORDER BY RAND()";
    } else {
        $raw .= " ORDER BY COMMENT.comment_date DESC";
    }

    // LIMIT
    $raw .= " LIMIT " . (int) woomotiv()->config->woomotiv_limit;

    // Return
    $reviews = array();

    // Excluded products
    $excludedProducts = [];
    if (woomotiv()->config->woomotiv_filter_products !== '' && woomotiv()->config->woomotiv_filter_products !== '0') {
        $excludedProducts = woomotiv()->config->woomotiv_filter_products;
        $excludedProducts = array_map('intval', explode(',', $excludedProducts));
    }

    foreach ($wpdb->get_results($raw) as $data) { // phpcs:ignore
        $product = wc_get_product($data->comment_post_ID);

        if (!$product) {
            continue;
        }

        // exclude products
        if (in_array($product->get_id(), $excludedProducts)) {
            continue;
        }

        $rating = array(
            'id' => $data->comment_ID,
            'rating' => $data->meta_value,
            'product_id' => $data->comment_post_ID,
            'product_name' => $product->get_name(),
            'product_url' => get_the_permalink($data->comment_post_ID),
            'date' => $data->comment_date,
            'date_gmt' => $data->comment_date_gmt,
            'user_id' => $data->user_id,
            'username' => $data->comment_author,
        );

        if ($rating['user_id'] != 0) {
            $usermeta = get_user_meta($data->user_id);

            if (!empty($usermeta)) {
                $rating['user_first_name'] = isset($usermeta['first_name'], $usermeta['first_name'][0]) ? $usermeta['first_name'][0] : '';
                $rating['user_last_name'] = isset($usermeta['last_name'], $usermeta['last_name'][0]) ? $usermeta['last_name'][0] : '';
            }
        }

        $reviews[] = (object)$rating;
    }

    // Mysql ORDER BY RAND() returns a cached query after the first time
    if ($is_random) {
        shuffle($reviews);
    }

    return $reviews;
}

/**
 * Get custom popups
 */
function get_custom_popups()
{
    global $wpdb;

    $excluded_popups = get_seen_custom_popups();
    $now = convert_timezone(new DateTime());
    $today = convert_timezone($now->format('F d, Y'));
    $date = $today->format('Y-m-d H:i:s');

    $query_parts = [
        "SELECT *",
        "FROM {$wpdb->prefix}woomotiv_custom_popups",
        "WHERE date_ends >= %s"
    ];

    $query_params = [$date];

    // Handle excluded popups
    if (!empty($excluded_popups)) {
        // Create placeholders for each excluded ID
        $placeholders = array_fill(0, count($excluded_popups), '%d');
        $placeholder_string = implode(',', $placeholders);

        $query_parts[] = "AND id NOT IN ($placeholder_string)";

        // Add each excluded ID to the parameters
        $query_params = array_merge($query_params, $excluded_popups);
    }

    $query_parts[] = "ORDER BY id DESC";
    $query_parts[] = "LIMIT %d";
    $query_params[] = (int) woomotiv()->config->woomotiv_limit;

    return $wpdb->get_results($wpdb->prepare(implode(' ', $query_parts), ...$query_params)); // phpcs:ignore
}

/**
 * Check for valid nonce
 */
function validateNounce(): void
{
    if (!wp_verify_nonce(woomotiv()->request->post('nonce'), 'woomotiv')) {
        response(false);
    }
}

/**
 * Return json response and die
 */
function response($success, $data = array()): void
{
    if ($success) {
        wp_send_json_success($data);
    }

    wp_send_json_error($data);
}
