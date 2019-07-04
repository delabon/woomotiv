<?php 

namespace WooMotiv;

class Autoload{
        
    /**
     * Load called classes
     */
    static function init(){

        spl_autoload_register( function( $className ){

            if( strpos( $className, 'WooMotiv' ) === false ) return;

            preg_match('/.*\\\(.*)$/', $className, $match);
            $filename = $match[1];
            
            $className = str_replace( 'WooMotiv', '', $className );
            $className = str_replace( $filename, '', $className );
            $className = str_replace( '\\', '/', $className );                
            $path = __DIR__ . $className . 'class-' . str_replace( '_', '-', strtolower( $filename ) ) . '.php';

            require_once $path;
        });

    }

}

Autoload::init();

