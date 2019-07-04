<?php 

namespace WooMotiv;

class Popup{

    private $id;
    private $order;
    private $product;
    private $data = array();
    private $country_list;
    private $date_now;

    function __construct( $order, $country_list, $date_now ){

        $this->id = $order->id;
        $this->order = $order->order;
        $this->product = $order->product;
        $this->date_now = $date_now;
        $this->country_list = $country_list;

        $this->data['order_id'] = $this->id;

        $this->setProduct();
        $this->setUserData();
        $this->setGeoData();
        $this->setDates();
    }

    /**
     * Set products
     */
    private function setProduct(){

        $this->data[ 'product' ] = array();

        $product = array(
            'id' => $this->product->get_id(),
			'parent_id' => $this->product->get_parent_id(),
            'name' => wp_trim_words( $this->product->get_name(), (int)woomotiv()->config->woomotiv_product_max_words ),
            'slug' => $this->product->get_slug(),
            'url' => get_permalink( $this->product->get_id() ),
            'thumbnail_id' => get_post_thumbnail_id( $this->product->get_id() ),
            'thumbnail_src' => wp_get_attachment_image_src( get_post_thumbnail_id( $this->product->get_id() ), 'thumbnail' ),
            'thumbnail_img' => wp_get_attachment_image( get_post_thumbnail_id( $this->product->get_id() ), 'thumbnail' ),                
        );

		// if a variation
		if( $this->product->get_parent_id() ){
            $product['thumbnail_id'] = get_post_thumbnail_id( $this->product->get_parent_id() );
            $product['thumbnail_src'] = wp_get_attachment_image_src( get_post_thumbnail_id( $this->product->get_parent_id() ), 'thumbnail' );
            $product['thumbnail_img'] = wp_get_attachment_image( get_post_thumbnail_id( $this->product->get_parent_id() ), 'thumbnail' );
		}
		
        if( woomotiv()->config->woomotiv_tracking == 1 ){
            $product['url'] = add_query_arg( array(
                'utm_source' => 'woomotiv',
                'utm_medium' => 'notification',
                'utm_campaign' => 'salesnotification',
            ), $product['url'] );
        }

        $this->data[ 'product' ] = $product;
    }

    /**
     * Set customer date
     */
    private function setUserData(){

        $customer = get_userdata( $this->order->get_customer_id() );

		if( ! $customer ){
			$this->data['user'] = array(
				'id' => 0,
				'username' => $this->order->get_billing_first_name(),
				'first_name' => $this->order->get_billing_first_name(),
				'last_name' => $this->order->get_billing_last_name(),
				'avatar_img' => $this->data['product']['thumbnail_img'],
			);
		}
		else{			
			$this->data['user'] = array(
				'id' => $this->order->get_customer_id(),
				'username' => $customer->display_name,
				'first_name' => $this->order->get_billing_first_name(),
				'last_name' => $this->order->get_billing_last_name(),
				'avatar_img' => mod_avatar( $this->order->get_customer_id(), $this->data['product']['thumbnail_src'][0], $customer->display_name ),
			);
		}

    }

    /**
     * Set country, city & state
     */
    private function setGeoData(){
        $this->data[ 'city' ] = $this->order->get_billing_city();
        $this->data[ 'country' ] = strtolower( @$this->country_list[ $this->order->get_billing_country() ] );
        $this->data[ 'state' ] = $this->order->get_billing_state();        
    }

    /**
     * Set Dates
     */
    private function setDates(){

        $date_order = $this->order->get_date_created();

        if( $this->order->get_date_completed() ){
            $date_order = $this->order->get_date_completed();
        }

        $this->data[ 'date_completed' ] = human_time_diff( 
            $date_order->format('U'), 
            $this->date_now->getTimestamp() 
        ) . ' ' . __('ago', 'woomotiv');
    }

    /**
     * Returns object properties as an array
     *
     * @return array
     */
    function toArray(){
        return $this->data;
    }

}
