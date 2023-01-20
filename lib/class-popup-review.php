<?php 

namespace WooMotiv;

use DLBV2\Helper;

class Popup_Review{

    private $review;
    private $data = array();
    private $date_now;

    function __construct( $review, $date_now ){

        $this->review = $review;
        $this->date_now = $date_now;
        $this->data['id'] = $review->id;
        $this->data['stars'] = $review->rating;

        $this->setProducts();
        $this->setUserData();
        $this->setDates();
    }

    /**
     * Set customer date
     */
    private function setUserData(){

        $this->data['user'] = array(
            'id' => $this->review->user_id,
            'username' => $this->review->username,
            'first_name' => isset($this->review->user_first_name) ? $this->review->user_first_name : '',
            'last_name' => isset($this->review->user_first_name) ? $this->review->user_first_name : '',
            'avatar' => get_avatar_url( $this->review->user_id, array( 'size' => 150 ) ),
            'avatar_img' => mod_avatar( $this->review->user_id, $this->data['product']['thumbnail_src'][0], $this->review->username ),
        );

    }

    /**
     * Set Dates
     */
    private function setDates(){

        $date = convert_timezone( $this->review->date_gmt );

        $this->data[ 'date_completed' ] = human_time_diff( 
            $date->format('U'), 
            $this->date_now->getTimestamp() 
        ) . ' ' . __('ago', 'woomotiv');
    }

    /**
     * Set products
     */
    private function setProducts(){

        $this->data[ 'product' ] = array(
            'id' => $this->review->product_id,
            'name' => wp_trim_words( $this->review->product_name, (int)woomotiv()->config->woomotiv_product_max_words ),
            'url' => $this->review->product_url,
            'thumbnail_id' => get_post_thumbnail_id( $this->review->product_id ),
            'thumbnail_src' => wp_get_attachment_image_src( get_post_thumbnail_id( $this->review->product_id ), 'thumbnail' ),
            'thumbnail_img' => wp_get_attachment_image( get_post_thumbnail_id( $this->review->product_id ), 'thumbnail' ),                
        );

        if( woomotiv()->config->woomotiv_tracking == 1 ){
            $this->data[ 'product' ]['url'] = add_query_arg( array(
                'utm_source' => 'woomotiv',
                'utm_medium' => 'notification',
                'utm_campaign' => 'salesnotification',
            ), $this->data[ 'product' ]['url'] );
        }

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
