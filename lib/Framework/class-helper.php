<?php 

namespace WooMotiv\Framework;

class Helper {

    /**
     * Dumper
     */
    static function dump( $d, $die = true, $var_dump = false ){

        if( $var_dump ){
            echo '<pre>', var_dump( $d ), '</pre>';
        }
        else{
            echo '<pre>', print_r( $d, true ), '</pre>';
        }

        if( $die ) die;
    }

    /**
     * Return a sanitized url
     * @param string $url
     * @return string
     */
    static function sanitize( $url ){
        $url = str_replace( array('_','-'), '', $url );
        $url = trim( $url, '/' );
        $url = str_replace( '/', '_', $url );
        return preg_replace( '`[^A-Za-z0-9\_]`', '', $url );
    }

    /**
     * Sanitize path
     * @param string $path
     * @return string
     */
    static function sanitizedPath( $path ){
        $name = sanitize( $path );
        if( empty( $name ) ) $name = 'index';
        return $name;
    }

    /**
     * is user logged in from cookies
     * @return bool
     */
    static function is_loggedin(){
        foreach( $_COOKIE as $cookie => $value ) {
            if( strpos( $cookie, '_logged_in_') !== false ){
                return true;
            }
        }
        return false;
    }

    /**
     * Return excluded array from string
     * @return array
     */
    static function excludedListToArray( $value ){
        if( empty( $value ) ) return array();

        $value = preg_replace('/\s?\n/', ',', $value);
        $value = str_replace( ' ', ',', $value );
        $value = trim( preg_replace( '/,,/', ',', $value ), ',' );

        if( strpos( $value , ',' ) !== false ){
            return explode( ',', $value );
        }

        return array( $value );
    }

    /**
     * is asset expluded ?
     * @param string $link
     * @param array|string $list
     * @return bool
     */
    static function isExcluded( $link, $list, $ignoreQuery = false ){

        if( ! is_array( $list ) ){
            $list = excludedListToArray( $list );
        }
    
        $link = preg_replace( '/.*?https?:\/\//', '', $link );

        if ($ignoreQuery && strpos($link, '?') !== false) {
            return false;
        }

        foreach ($list as $excluded) {
            $excluded_link = preg_replace( '/.*?https?:\/\//', '', $excluded );

            if( strpos( $excluded_link, '*' ) !== false ){
                
                $excluded_parts = explode( '*', $excluded_link );
    
                if( strpos( $link, $excluded_parts[0] ) !== false ){
                    return true;
                }
            } elseif( $link === $excluded_link ){
                return true;
            }
        }
    
        return false;
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
    static function days_in_month($month, $year) { 
        // calculate number of days in a month 
        return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31); 
    } 


}
