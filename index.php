<?php

/*
Plugin Name: Woomotiv - Sales Popups for Woocommerce
Description: Laverage social proof to increase truct, traffic and sales.
Version: 2.6.1
Author: Sabri Taieb
Author Uri: https://delabon.com
Text Domain: woomotiv
Domain Path: /languages
*/

defined( 'ABSPATH' ) or die( 'Mmmmm Funny ?' );

# Defined
define( 'WOOMOTIV_VERSION', '2.6.1' );
define( 'WOOMOTIV_URL', plugins_url( '', __FILE__ ) );
define( 'WOOMOTIV_DIR', __DIR__ );

# Activation ( before anything )
require_once __DIR__ . '/activation.php';

# Autoloader
require_once __DIR__ . '/lib/class-autoload.php';

# Freemius init
require_once __DIR__ . '/freemius-init.php';

class Woomotiv {

    /**
     * @var string
     */
    public $version = WOOMOTIV_VERSION;

    /**
     * @var string
     */
    public $url = WOOMOTIV_URL;

    /**
     * @var string
     */
    public $dir = WOOMOTIV_DIR;

    /**
     * Instance
     *
     * @var Woomotiv
     */         
    private static $_instance;

    /**
     * @var Config
     */
    public $config;

    /**
     * @var Request
     */
    public $request;

    /**
     * Return instance
     */
    public static function instance(){

        if( null === self::$_instance ){
            self::$_instance = new Woomotiv;
        }

        return self::$_instance;
    }

    /**
     * Init
     */
    function __construct(){

        require_once __DIR__ . '/lib/functions.php';
        require_once __DIR__ . '/lib/hooks.php';
        $defaultConfig = require_once __DIR__ . '/lib/config.php';

        $this->config = new WooMotiv\Framework\Config( $defaultConfig );
        $this->request = new WooMotiv\Framework\Request();
        new WooMotiv\Backend;
        new WooMotiv\Frontend;
    }

}

/**
 * Main Function
 *
 * @return Woomotiv
 */
function woomotiv(){
    return Woomotiv::instance();
}

woomotiv();
