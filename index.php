<?php

/**
 * Plugin Name: Woomotiv - Live Sales Notification for Woocommerce
 * Description: Laverage social proof to increase trust, traffic and sales.
 * Version: 3.4.3
 * Author: Sabri Taieb
 * Author Uri: https://delabon.com
 * Text Domain: woomotiv
 * Domain Path: /languages
 *
 * WC requires at least: 3.0
 * WC tested up to: 8.3.1
 *
**/

defined( 'ABSPATH' ) or die( 'Mmmmm Funny ?' );

# Defined
define( 'WOOMOTIV_VERSION', '3.4.3' );
define( 'WOOMOTIV_URL', plugins_url( '', __FILE__ ) );
define( 'WOOMOTIV_DIR', __DIR__ );
define( 'WOOMOTIV_REVIEW_URL', 'https://wordpress.org/support/plugin/woomotiv/reviews/?rate=5#rate-response');

# Activation ( before anything )
require_once __DIR__ . '/activation.php';

# Autoloader
require_once __DIR__ . '/lib/class-autoload.php';

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
     * @var String
     */
    private static $_site_hash;

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

    /**
     * Get site hash
     *
     * @return void
     */
    function get_site_hash(){

        if (!self::$_site_hash){
            self::$_site_hash = md5(get_home_url());
        }

        return self::$_site_hash;
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
