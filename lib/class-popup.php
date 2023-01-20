<?php 

namespace WooMotiv;

class Popup{

    private $id;
    private $order;
    private $product;
    private $data = array();
    private $country_list;
    private $date_now;

    function __construct( $data, $country_list, $date_now ){

        $order = $data['order'];
        $product = $data['product'];

        $this->id = $product->get_id();
        $this->product = $product;
        $this->order = $order;
        $this->date_now = $date_now;
        $this->country_list = $country_list;

        $this->data['order_id'] = $order->get_id();
        $this->data['order_item_id'] = $data['order_item_id'];

        $this->setProduct();
        $this->setUserData();
        $this->setGeoData();
        $this->setDates();
    }

    /**
     * Set products
     */
    private function setProduct(){

        $thumbnail_size = apply_filters('woomotiv_product_image_size_filter', 'thumbnail');
        $this->data[ 'product' ] = array();

        $product = array(
            'id' => $this->product->get_id(),
			'parent_id' => $this->product->get_parent_id(),
            'name' => wp_trim_words( $this->product->get_name(), (int)woomotiv()->config->woomotiv_product_max_words ),
            'slug' => $this->product->get_slug(),
            'url' => get_permalink( $this->product->get_id() ),
            'thumbnail_id' => get_post_thumbnail_id( $this->product->get_id() ),
            'thumbnail_src' => wp_get_attachment_image_src( get_post_thumbnail_id( $this->product->get_id() ), $thumbnail_size ),
            'thumbnail_img' => wp_get_attachment_image( get_post_thumbnail_id( $this->product->get_id() ), $thumbnail_size ),                
        );

		// if a variation
		if( $this->product->get_parent_id() ){
            $product['thumbnail_id'] = get_post_thumbnail_id( $this->product->get_parent_id() );
            $product['thumbnail_src'] = wp_get_attachment_image_src( get_post_thumbnail_id( $this->product->get_parent_id() ), $thumbnail_size );
            $product['thumbnail_img'] = wp_get_attachment_image( get_post_thumbnail_id( $this->product->get_parent_id() ), $thumbnail_size );
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
        $this->data[ 'address' ] = $this->order->get_address();

        // get from shippping
        if( empty( $this->data['country'] ) ){
            $this->data['country']= strtolower( @$this->country_list[ $this->order->get_shipping_country() ]);
        }

        if( empty( $this->data['state'] ) ){
            $this->data['state']= $this->order->get_shipping_state();
        }

        if( empty( $this->data['city'] ) ){
            $this->data['city']= $this->order->get_shipping_city();
        }

        // get from address
        if( empty( $this->data['country'] ) ){
            $this->data['country']= strtolower( @$this->country_list[ $this->data['address']['country'] ]);
        }

        if( empty( $this->data['state'] ) ){
            $this->data['state']= $this->data['address']['state'];
        }

        if( empty( $this->data['city'] ) ){
            $this->data['city']= $this->data['address']['city'];
        }
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
