<?php 

namespace WooMotiv;

class Popup_Custom{

    private $item;
    private $data = array();

    function __construct( $item ){

        $this->item = $item;
        $this->data = (array)$item;

        $this->setImage();
    }

    /**
     * Set image tag
     */
    private function setImage(){

        $this->data['image'] = '<img src="'.woomotiv()->url.'/img/150.png" alt="id '.$this->item->id.'" >';

        if( $this->item->image_id ){
    
            $src = wp_get_attachment_image_src( $this->item->image_id );
    
            $this->data['image'] = '<img src="'.$src[0].'" alt="id '.$this->item->id.'" >';
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
