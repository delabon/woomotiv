<?php 

namespace WooMotiv;

use WooMotiv\Framework\Helper;

class Frontend{

    private $popup_shape;

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
        $excluded = Helper::excludedListToArray(woomotiv()->config->woomotiv_filter_pages);
        $isUrlQueryIgnored = (bool)(int)woomotiv()->config->woomotiv_filter_keep_query_pages;

        if( woomotiv()->config->woomotiv_filter === 'show' ){
            if( ! empty( $excluded ) ){
                if( Helper::isExcluded( woomotiv()->request->url(), $excluded, $isUrlQueryIgnored) ){
                    return true;
                }
            }
        } else {
            // hide on all except
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
    
        $shape = woomotiv()->config->woomotiv_shape;

        if( $shape === 'random' ){
            $shapes = array( 'rectangle', 'rectangle_2', 'rounded', 'rounded_2', 'bordered', 'bordered_2' );
            $shape = $shapes[ array_rand( $shapes ) ];
        }

        $template_content = trim( woomotiv()->config->woomotiv_content_content );
        $template_review = trim( woomotiv()->config->woomotiv_template_review );

        if( empty( $template_content ) ){
            $template_content = woomotiv()->config->default( 'woomotiv_content_content' );
        }

        if( empty( $template_review ) ){
            $template_review = woomotiv()->config->default( 'woomotiv_template_review' );
        }

        wp_localize_script( 'woomotiv', 'woomotivObj', array(
            'site_hash'     => woomotiv()->get_site_hash(),
            'nonce'         => wp_create_nonce('woomotiv'),
            'ajax_url'      => admin_url( 'admin-ajax.php' ),
            'limit'         => (int)woomotiv()->config->woomotiv_limit,
            'interval'      => (int)woomotiv()->config->woomotiv_interval,
            'hide'          => (int)woomotiv()->config->woomotiv_hide,
            'position'      => woomotiv()->config->woomotiv_position,
            'animation'     => woomotiv()->config->woomotiv_animation,
            'shape'         => $shape, 
            'size'          => woomotiv()->config->woomotiv_style_size, 
            'hide_mobile'   => woomotiv()->config->woomotiv_hide_on_mobile,
            'user_avatar'   => woomotiv()->config->woomotiv_user_avatar,
            'disable_link'  => woomotiv()->config->woomotiv_disable_link,
            'hide_close_button' => (int)woomotiv()->config->woomotiv_hide_close_button,
            'is_no_repeat_enabled' => (int)woomotiv()->config->woomotiv_no_repeat_sales_reviews,
        ));
    }

    /**
     * Get popups
     */
	function ajax_get_items(){
        
        validateNounce();

        $country_list = require WC()->plugin_path() . '/i18n/countries.php';
        $date_now = date_now();
        $products = get_products();
        $reviews = get_reviews();
        $custom_popups = get_custom_popups();
        $notifications = array();
        $counter = 1;
        $max = count( $products );
        
        if ($max < count($reviews)){
            $max = count( $reviews );
        } elseif ($max < count($custom_popups)){
            $max = count( $custom_popups );
        }

        // Clear cookies
        if (count($products) === 0 && count($reviews) === 0 && count($custom_popups) === 0){
            clear_cookies();
        }

        while ( $counter <= $max ) {

            if( count( $products ) ){
                $popup = new Popup( $products[0], $country_list, $date_now );
                $notifications[] = array( 'type' => 'product', 'popup' => $popup->toArray() );
                array_shift( $products );
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

        // Render
        $rendered = $this->render( array_slice( $notifications, 0, (int)woomotiv()->config->woomotiv_limit ) );

        // Response
        response( true, $rendered );
    }

    /**
     * Render by type
     * 
     * @param array $notifications
     * @return array
     */
    private function render( $notifications ){

        // Get settings
        $this->popup_user_avatar = woomotiv()->config->woomotiv_user_avatar;
        $this->popup_size = woomotiv()->config->woomotiv_style_size;
        $this->popup_animation = woomotiv()->config->woomotiv_animation;
        $this->popup_position = woomotiv()->config->woomotiv_position;
        $this->popup_hide_on_mobile = woomotiv()->config->woomotiv_hide_on_mobile;
        $this->popup_disable_link = woomotiv()->config->woomotiv_disable_link;
        $this->popup_hide_close_button = woomotiv()->config->woomotiv_hide_close_button;

        $this->popup_shape = woomotiv()->config->woomotiv_shape;
        
        if( $this->popup_shape === 'random' ){
            $shapes = array( 'rectangle', 'rectangle_2', 'rounded', 'rounded_2', 'bordered', 'bordered_2' );
            $this->popup_shape = $shapes[ array_rand( $shapes ) ];
        }
        
        $rendered = [];
        $index = 0;
        
        foreach( $notifications as $notification ){
            
            // fix first name & last name
            if( in_array( $notification['type'], [ 'product', 'review' ] ) ){

                // no first name, use username
                if( ! $notification['popup']['user']['first_name'] || $notification['popup']['user']['first_name'] === '' ){
                    $notification['popup']['user']['first_name'] = $notification['popup']['user']['username'];
                }
                
                // no last name, use empty string
                if( ! $notification['popup']['user']['last_name'] || $notification['popup']['user']['last_name'] === '' ){
                    $notification['popup']['user']['last_name'] = '';
                }
                
            }

            if( $notification['type'] === 'product' ){
                $rendered[] = [
                    'type' => 'product',
                    'markup' => $this->renderProduct( $notification, $index ),
                ];
            }
            elseif( $notification['type'] === 'review' ){
                $rendered[] = [
                    'type' => 'review',
                    'markup' => $this->renderReview( $notification, $index ),
                ];
            }
            else{
                $rendered[] = [
                    'type' => 'custom',
                    'markup' => $this->renderCustom( $notification, $index ),
                ];
            }

            $index += 1;
        }

        return $rendered;
    }

    /**
     * Render popup type product
     *
     * @param array $notification
     * @param int $index
     * @return string
     */
    private function renderProduct( $notification, $index ){

        // get template
        $template_content = trim( woomotiv()->config->woomotiv_content_content );

        if( empty( $template_content ) ){
            $template_content = woomotiv()->config->default( 'woomotiv_content_content' );
        }

        $template_content = $this->replace_shortcodes( $template_content, $notification );
        $image = $this->renderImage($notification);

        $output = '
            <div data-orderitemid="' . $notification['popup']['order_item_id'] . '" data-id="' . $notification['popup']['product']['id'] . '" data-type="product" data-product="' .$notification['popup']['product']['id']. '" class="woomotiv-popup" data-size="' .$this->popup_size. '" data-shape="' .$this->popup_shape. '" data-animation="' .$this->popup_animation. '" data-position="' .$this->popup_position. '" data-hideonmobile="' .$this->popup_hide_on_mobile. '">
                <div class="woomotiv-image" >' . $image . '</div>
                <p>
                    '.$template_content.'
                    <a class="woomotiv-link"' . ( $this->popup_disable_link == 1 ? '' : ' href="' .$notification['popup']['product']['url']. '"' ).'></a>
                    <a class="woomotiv-close ' . ( $this->popup_hide_close_button == 1 ? '__hidden__' : '' ) . '">&times</a>
                </p>
            </div>';

        return $output;
    }

    /**
     * Render popup type review
     *
     * @param array $notification
     * @param int $index
     * @return string
     */
    private function renderReview( $notification, $index ){

        // get template
        $template_review = trim( woomotiv()->config->woomotiv_template_review );

        if( empty( $template_review ) ){
            $template_review = woomotiv()->config->default( 'woomotiv_template_review' );
        }

        $template_review = $this->replace_shortcodes( $template_review, $notification );
        $image = $this->renderImage($notification);

        $output = '
            <div data-review="' . $notification['popup']['id'] . '" data-type="review" data-product="' .$notification['popup']['product']['id']. '" class="woomotiv-popup " data-size="' .$this->popup_size. '" data-shape="' .$this->popup_shape. '" data-animation="' .$this->popup_animation. '" data-position="' .$this->popup_position. '" data-hideonmobile="' .$this->popup_hide_on_mobile. '">
                <div class="woomotiv-image" >' . $image . '</div>
                <p>
                    ' .$template_review. '<br>
                    <span class="wmt-stars">
                        <span style="width:' . ( $notification['popup']['stars'] / 5 ) * 100 . '%"></span>
                    </span>
                    <a class="woomotiv-link"' . ( $this->popup_disable_link == 1 ? '' : ' href="' .$notification['popup']['product']['url']. '"' ).'></a>
                    <a class="woomotiv-close ' . ( $this->popup_hide_close_button == 1 ? '__hidden__' : '' ) . '">&times</a>
                </p>
            </div>';

        return $output;
    }

    /**
     * Render popup type custom
     *
     * @param array $notification
     * @param int $index
     * @return string
     */
    private function renderCustom( $notification, $index ){

        $content = $notification['popup']['content'];
        $content = str_ireplace('{new_line}', '<br>', $content);
        $content = str_ireplace('{', '<strong>', $content);
        $content = str_ireplace('}', '</strong>', $content);

        $output = '
            <div data-type="custom" data-id="' .$notification['popup']['id']. '" class="woomotiv-popup" data-size="' .$this->popup_size. '" data-shape="' .$this->popup_shape. '" data-animation="' .$this->popup_animation. '" data-position="' .$this->popup_position. '" data-hideonmobile="' .$this->popup_hide_on_mobile. '">
                <div class="woomotiv-image" >
                    ' .$notification['popup']['image']. '
                </div>
                <p>
                    ' .$content. '
                    <a class="woomotiv-link"' . ( $this->popup_disable_link == 1 ? '' : ' href="' .$notification['popup']['link']. '"' ).'></a>
                    <a class="woomotiv-close ' . ( $this->popup_hide_close_button == 1 ? '__hidden__' : '' ) . '">&times</a>
                </p>
            </div>';

        return $output;
    }

    /**
     * Render product/review image
     *
     * @param array $notification
     * @return string
     */
    private function renderImage( $notification ){

        $image = $this->popup_user_avatar == 0 ? $notification['popup']['product']['thumbnail_img'] : $notification['popup']['user']['avatar_img'];

        if( empty($image) ){
            $image_url = WC()->plugin_url() . '/assets/images/placeholder.png';
            $image = '<img width="150" height="150" src="'.$image_url.'" class="attachment-thumbnail size-thumbnail" alt="Product thumbnail" loading="lazy">';
        }

        return $image;
    }

    /**
     * Replace shortcode
     *
     * @param string $template_content
     * @param array $notification
     * @return string
     */
    private function replace_shortcodes( $template_content, $notification ){

        $country = isset($notification['popup']['country']) ? $notification['popup']['country'] : '';
        $city = isset($notification['popup']['city']) ? $notification['popup']['city'] : '';
        $state = isset($notification['popup']['state']) ? $notification['popup']['state'] : '';
        $country = isset($notification['popup']['country']) ? $notification['popup']['country'] : '';
        $country = isset($notification['popup']['country']) ? $notification['popup']['country'] : '';
        $country = isset($notification['popup']['country']) ? $notification['popup']['country'] : '';
        $country = isset($notification['popup']['country']) ? $notification['popup']['country'] : '';

        $template_content = str_ireplace('{date}', '<span class="wmt-date">' . $notification['popup']['date_completed'] . '</span>', $template_content);
        $template_content = str_ireplace('{new_line}', '<br>', $template_content);
        $template_content = str_ireplace('{buyer}', '<strong class="wmt-buyer">' .$notification['popup']['user']['first_name']. ' ' .$notification['popup']['user']['last_name']. '</strong>', $template_content);
        $template_content = str_ireplace('{product}', '<strong class="wmt-product">' .$notification['popup']['product']['name']. '</strong>', $template_content);
        $template_content = str_ireplace('{by_woomotiv}', '<strong class="wmt-by">by <span>woomotiv</span></strong>', $template_content);
        $template_content = str_ireplace('{city}', '<strong class="wmt-city">' . $city . '</strong>', $template_content);
        $template_content = str_ireplace('{country}', '<strong class="wmt-country">' . $country . '</strong>', $template_content);
        $template_content = str_ireplace('{state}', '<strong class="wmt-state">' . $state . '</strong>', $template_content);
        $template_content = str_ireplace('{buyer_first_name}', '<strong class="wmt-buyer-first-name">' .$notification['popup']['user']['first_name']. '</strong>', $template_content);
        $template_content = str_ireplace('{buyer_last_name}', '<strong class="wmt-buyer-last-name">' .$notification['popup']['user']['last_name']. '</strong>', $template_content);
        $template_content = str_ireplace('{buyer_username}', '<strong class="wmt-buyer-username">' .$notification['popup']['user']['username']. '</strong>', $template_content);

        return $template_content;
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
        $type = trim($_POST['type']);

        if( $type === 'review' || $type === 'product' ){

            $product_id = (int)$_POST['product_id'];
            $product = wc_get_product( $product_id );

            if( ! $product ){
                response( false );
            }

            if( $type === 'product' ){
                $type = 'order';
            }

            $stats = $wpdb->get_row( "
                SELECT 
                    * 
                FROM 
                    {$wpdb->prefix}woomotiv_stats 
                WHERE 
                    product_id = {$product_id} 
                    AND 
                        popup_type IN ('order' , 'review')
                    AND 
                        the_day=$day 
                    AND 
                        the_month=$month 
                    AND 
                        the_year=$year
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
