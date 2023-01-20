<?php 

namespace WooMotiv\Framework;

class Config{

    private $default;
    private $data = [];

    function __construct( $default ){
        $this->default = $default;
    }

    function default( $key ){
        if( isset( $this->default[ $key ] ) ){
            return $this->default[ $key ];
        }

        return null;
    }

    /**
     * Set
     */
    function set( $key, $value ){
        update_option( $key, $value );
    }

    function __set( $key, $value ){
        $this->set( $key, $value );
    }
    
    /**
     * Get
     */
    function get( $key, $default = null ){
        
        if( isset( $_POST[ $key ] ) ){
            return $_POST[ $key ];
        }
        elseif( isset( $_GET[ $key ] ) ){
            return $_GET[ $key ];
        }
        elseif( isset( $this->data[ $key ] ) ){
            return $this->data[ $key ];
        }
        elseif( isset( $this->default[ $key ] ) ){
            $this->data[ $key ] = get_option( $key, $this->default[ $key ] );
            return $this->data[ $key ];
        }
        else{
            $this->data[ $key ] = get_option( $key, $default );
            return $this->data[ $key ];
        }

        return $default;
    }

    function __get( $key ){
        return $this->get( $key );
    }

    /**
     * Delete
     */
    static function delete( $key ){
        delete_option( $key );
    }

}
