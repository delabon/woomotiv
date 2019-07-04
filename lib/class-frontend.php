<?php 

namespace WooMotiv;

use WooMotiv\Framework\Helper;

class Frontend{

    /**
     * Constructor
     */
    function __construct(){

        # load assets
        add_action( 'wp_enqueue_scripts', array( $this, 'load_assets' ) );

        # Ajax: get popups
        add_action( 'wp_ajax_woomotiv_get_items', array( $this, 'ajax_get_items' ) );
        add_action( 'wp_ajax_nopriv_woomotiv_get_items', array( $this, 'ajax_get_items' ) );

        # Ajax: update report
        add_action( 'wp_ajax_woomotiv_update_stats', array( $this, 'ajax_update_stats' ) );
        add_action( 'wp_ajax_nopriv_woomotiv_update_stats', array( $this, 'ajax_update_stats' ) );
        
    }

    /**
     * Assets
     * Filters are here
     */
    public function load_assets(){

        global $post, $wp_query;
            
        if( ! class_exists('Woocommerce') ) return;

        // hide on all articles ( posts )
        $hide_on_posts = woomotiv()->config->woomotiv_filter_posts;

        if( is_a( $post, 'WP_Post' ) ){
            if( in_array( $post->post_type, array( 'attachment', 'post' ) ) && $hide_on_posts ){
                return true;
            }
        }

        // Show only on these woo categories
        $woo_cats = woomotiv()->config->woomotiv_woocategories;
        
        if( $woo_cats !== '' ){

            $woo_cats = array_map(function( $cat ){
                return (int)trim( $cat );
            }, explode( ',' , $woo_cats ) );

            if( is_product_category() ){
    
                if( ! in_array( $wp_query->get_queried_object()->term_id, $woo_cats ) ){
                    return;
                }

            }

            elseif( is_product() ){
                
                $cat_found = false; 

                $pro_cats = array_map(function( $cat ){
                    return $cat->term_id;
                }, get_the_terms( $post->ID, 'product_cat' ) );

                foreach ( $woo_cats as $cat ) {
                    if( in_array( $cat, $pro_cats ) ){
                        $cat_found = true; 
                        break;
                    }
                }

                if( ! $cat_found ){
                    return;
                }

            }

            // hide on all pages
            else{
                return;
            }

        }
        
        // fitlers
        $excluded = Helper::excludedListToArray( woomotiv()->config->woomotiv_filter_pages );

        if( woomotiv()->config->woomotiv_filter === 'show' ){
            if( ! empty( $excluded ) ){
                if( Helper::isExcluded( woomotiv()->request->url(), $excluded ) ){
                    return true;
                }
            }
        }
        // hide on all except
        else{
            if( ! empty( $excluded ) ){ 
                if( ! Helper::isExcluded( woomotiv()->request->url(), $excluded ) ){
                    return true;
                }
            }
            else{
                return true;
            }
        }

        // CSS
        wp_enqueue_style( 'woomotiv', woomotiv()->url . '/css/front.min.css', array(), woomotiv()->version );

        if( is_rtl() ){

            wp_enqueue_style( 
                'woomotiv_front_rtl', 
                woomotiv()->url . '/css/front-rtl.min.css', 
                array('woomotiv'), 
                woomotiv()->version 
            );
    
        }

        $custom_css = require ( woomotiv()->dir . '/views/custom-css.php' );
        wp_add_inline_style( 'woomotiv', $custom_css );

        // JS
        wp_enqueue_script( 'woomotiv', woomotiv()->url . '/js/front.min.js', array('jquery'), woomotiv()->version, true );
    
        wp_localize_script( 'woomotiv', 'woomotivObj', array(
            'nonce'         => wp_create_nonce('woomotiv'),
            'ajax_url'      => admin_url( 'admin-ajax.php' ),
            'limit'         => (int)woomotiv()->config->woomotiv_limit,
            'interval'      => (int)woomotiv()->config->woomotiv_interval,
            'hide'          => (int)woomotiv()->config->woomotiv_hide,
            'position'      => woomotiv()->config->woomotiv_position,
            'animation'     => woomotiv()->config->woomotiv_animation,
            'shape'         => woomotiv()->config->woomotiv_shape, 
            'size'          => woomotiv()->config->woomotiv_style_size, 
            'hide_mobile'   => woomotiv()->config->woomotiv_hide_on_mobile,
            'user_avatar'   => woomotiv()->config->woomotiv_user_avatar,
            'disable_link'  => woomotiv()->config->woomotiv_disable_link,
            'template_content' => woomotiv()->config->woomotiv_content_content,
            'template_review' => woomotiv()->config->woomotiv_template_review,
            'hide_close_button'  => (int)woomotiv()->config->woomotiv_hide_close_button,
        ));

    }

    /**
     * Get Orders
     */
	function ajax_get_items(){
        
        validateNounce();
        
        $country_list = require WC()->plugin_path() . '/i18n/countries.php';
        $date_now = date_now();
        $orders = get_orders();
        $reviews = get_reviews();
        $custom_popups = get_custom_popups();
        $notifications = array();
        $counter = 1;
        $max = count( $orders );

        if( $max < count( $reviews ) ){
            $max = count( $reviews );
        }
        elseif( $max < count( $custom_popups )  ){
            $max = count( $custom_popups );
        }

        while ( $counter <= $max ) {

            if( count( $orders ) ){
                $popup = new Popup( $orders[0], $country_list, $date_now );
                $notifications[] = array( 'type' => 'order', 'popup' => $popup->toArray() );
                array_shift( $orders );
            }

            if( count( $reviews ) && (bool)woomotiv()->config->woomotiv_display_reviews ){
                $popup = new Popup_Review( $reviews[0], $date_now );
                $notifications[] = array( 'type' => 'review', 'popup' => $popup->toArray() );
                array_shift( $reviews );
            }

            if( count( $custom_popups ) ){
                $popup = new Popup_Custom( $custom_popups[0] );
                $notifications[] = array( 'type' => 'custom', 'popup' => $popup->toArray() );
                array_shift( $custom_popups );
            }

            $counter += 1;
        }
        
        response( true, array_slice( $notifications, 0, (int)woomotiv()->config->woomotiv_limit ) );
    }

    /**
     * Update stats by product id
     */
    function ajax_update_stats(){

        global $wpdb;

        validateNounce();

        if( ! isset( $_POST['type'] ) ){
            response( false );
        }

        $now = new \DateTime();
        $day = (int)$now->format('d');
        $month = (int)$now->format('m');
        $year = (int)$now->format('Y');
        $type = $_POST['type'];

        if( $type === 'review' || $type === 'order' ){

            $product_id = (int)$_POST['product_id'];
            $product = wc_get_product( $product_id );

            if( ! $product ){
                response( false );
            }

            $stats = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}woomotiv_stats 
                WHERE product_id={$product_id} AND popup_type IN ('order' , 'review')
                AND the_day=$day 
                AND the_month=$month 
                AND the_year=$year
            ");

            // insert
            if( ! $stats ){

                $wpdb->insert( 
                    $wpdb->prefix.'woomotiv_stats', 
                    array( 
                        'popup_type'    => $type,
                        'product_id'    => $product->get_id(), 
                        'the_day'       => (int)$now->format('d'),
                        'the_month'     => (int)$now->format('m'),
                        'the_year'      => (int)$now->format('Y'),
                        'clicks'        => 1,
                    ), 
                    array( 
                        '%s',                        
                        '%d',
                        '%d',
                        '%d',
                        '%d',
                        '%d',
                    ) 
                );

            }
            // update
            else{

                $wpdb->update( 
                    $wpdb->prefix.'woomotiv_stats', 
                    array( 'clicks' => (int)$stats->clicks + 1 ), 
                    array( 'id' => (int)$stats->id ), 
                    array( '%d' ), 
                    array( '%d' ) 
                );

            }

        }

        // custom popup
        else{

            $popup_id = empty( $_POST['id'] ) ? 0 : (int)$_POST['id'];

            if( ! $popup_id ){
                response( false );
            }

            $stats = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}woomotiv_stats 
                WHERE product_id={$popup_id} AND popup_type = 'custom'
                AND the_day=$day 
                AND the_month=$month 
                AND the_year=$year
            ");

            // insert
            if( ! $stats ){

                $wpdb->insert( 
                    $wpdb->prefix.'woomotiv_stats', 
                    array( 
                        'popup_type'    => $type,
                        'product_id'    => $popup_id, 
                        'the_day'       => (int)$now->format('d'),
                        'the_month'     => (int)$now->format('m'),
                        'the_year'      => (int)$now->format('Y'),
                        'clicks'        => 1,
                    ), 
                    array( 
                        '%s',                        
                        '%d',
                        '%d',
                        '%d',
                        '%d',
                        '%d',
                    ) 
                );

            }
            // update
            else{

                $wpdb->update( 
                    $wpdb->prefix.'woomotiv_stats', 
                    array( 'clicks' => (int)$stats->clicks + 1 ), 
                    array( 'id' => (int)$stats->id ), 
                    array( '%d' ), 
                    array( '%d' ) 
                );

            }

        }

        // add the year if it does not exist ( used for filter )
        $years = get_option( 'woomotiv_report_years', array() );

        if( ! isset( $years[ $year ] ) ){
            $years[ $year ] = $year;

            update_option( 'woomotiv_report_years', $years );
        }

        response( true );
    }

}
