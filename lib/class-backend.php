<?php 

namespace WooMotiv;

use WooMotiv\Framework\Alert;
use WooMotiv\Framework\Panel;
use WooMotiv\Framework\Helper;

class Backend{

    /**
     * Constructor
     */
    function __construct(){
        add_action( 'init', array( $this, 'init' ) );
    }

    /**
     * init
     */
    function init(){
        if( ! current_user_can( 'level_8' ) ) return;

        add_action( 'admin_menu', array( $this, 'adminMenuAction' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'loadAssets' ) );
        add_action( 'admin_footer', array( $this, 'printTemplates' ) );

        # Ajax: custom popup
        add_action('wp_ajax_woomotiv_custom_popup_add_form', array( $this, 'ajax_custom_popup_add_form') );
        add_action('wp_ajax_woomotiv_custom_popup_edit_form', array( $this, 'ajax_custom_popup_edit_form') );
        add_action('wp_ajax_woomotiv_custom_popup_save', array( $this, 'ajax_custom_popup_save') );
        add_action('wp_ajax_woomotiv_custom_popup_delete', array( $this, 'ajax_custom_popup_delete') );
    }

    /**
     * Add admin menu
     */
    function adminMenuAction(){

        /** save when post request */
        if( woomotiv()->request->post('woomotiv_nonce') ){

            foreach( woomotiv()->request->queries()['post'] as $key => $value ){
                update_option( $key, $value );
            }
            
            echo Alert::success( __('Options Saved Successfuly','woomotiv') );
        }

        add_menu_page( 
            'Woomotiv', 
            'Woomotiv', 
            'level_8', 
            'woomotiv', 
            array( $this, 'generalPage'), 
            'dashicons-money', 
            58 
        );
    }

    /**
     * Add Admin PAge
     */
    function generalPage(){       
        
        $panel = new Panel( 'woomotiv', woomotiv()->dir . '/views/admin-settings.php' );

        $panel->addTab( 
            __('General', 'woomotiv'), 
            'general', 
            woomotiv()->dir . '/views/tabs/general.php'
        );

        $panel->addTab( 
            __('Content Template','woomotiv'), 
            'content-template', 
            woomotiv()->dir . '/views/tabs/content-template.php' 
        );

        $panel->addTab( 
            __('Custom Popups', 'woomotiv') . '<span>'.__('new','woomotiv').'</span>', 
            'custom-popups', 
            woomotiv()->dir . '/views/tabs/custom-popups.php' 
        );

        $panel->addTab( 
            __('Advanced','woomotiv'), 
            'advanced', 
            woomotiv()->dir . '/views/tabs/advanced.php' 
        );

        $panel->addTab( 
            __('Filters','woomotiv'), 
            'filters', 
            woomotiv()->dir . '/views/tabs/filters.php' 
        );

        $panel->addTab( 
            __('Appearance','woomotiv'), 
            'style', 
            woomotiv()->dir . '/views/tabs/style.php' 
        );

        $panel->addTab( 
            __('Report','woomotiv'), 
            'report', 
            woomotiv()->dir . '/views/tabs/report.php' 
        );

        $panel->addTab( 
            __('Discover','woomotiv') . '<span style="background: orange;">3</span>', 
            'discover',  
            woomotiv()->dir . '/views/tabs/discover.php' 
        );

        $panel->addTab( 
            __('Change Log','woomotiv'), 
            'changelog',  
            woomotiv()->dir . '/views/tabs/changelog.php' 
        );
        
        $panel->print();
    }


    /**
     * Checkbox js helper
     */
    function loadAssets( $hook ){

        if( strpos( $hook, 'woomotiv' ) === false ) return;

        Panel::load_assets( woomotiv()->url . '/lib/Framework/' );

        wp_enqueue_media();
        
        wp_enqueue_style( 
            'woomotiv_jquery_ui', 
            woomotiv()->url . '/css/jquery-ui.min.css', 
            array(), 
            woomotiv()->version 
        );

        wp_enqueue_style( 
            'woomotiv_admin', 
            woomotiv()->url . '/css/admin.css', 
            array(), 
            woomotiv()->version 
        );

        wp_enqueue_script( 
            'woomotiv_admin_tablesorter', 
            woomotiv()->url . '/js/jquery.tablesorter.min.js', 
            array('jquery'), 
            woomotiv()->version, 
            true 
        );

        wp_enqueue_script( 
            'woomotiv_admin', 
            woomotiv()->url . '/js/admin.js', 
            array('jquery', 'wp-color-picker', 'jquery-ui-datepicker', 'woomotiv_admin_tablesorter'), 
            woomotiv()->version, 
            true 
        );

        wp_localize_script( 'woomotiv_admin', 'woomotiv_params', array( 
            'panel_url' => admin_url( 'admin.php?page=woomotiv' ),
            'delete_text' => __('Are you sure ?', 'woomotiv'),
        ));

        wp_enqueue_style( 
            'woomotiv_front', 
            woomotiv()->url . '/css/front.min.css', 
            array(), 
            woomotiv()->version 
        );

        if( is_rtl() ){

            wp_enqueue_style( 
                'woomotiv_front_rtl', 
                woomotiv()->url . '/css/front-rtl.min.css', 
                array('woomotiv_admin'), 
                woomotiv()->version 
            );
    
        }

    }

    /**
     * Print Templates
     */
    function printTemplates(){

        if( isset($_GET['tab']) && $_GET['tab'] == 'style' ){

            $style = require ( woomotiv()->dir . '/views/custom-css.php' );

            echo '<style>' . $style . '</style>
                <div data-size="'.woomotiv()->config->woomotiv_style_size.'" data-shape="'.woomotiv()->config->woomotiv_shape.'" data-position="'.woomotiv()->config->woomotiv_position.'" data-animation="'.woomotiv()->config->woomotiv_animation.'" class="woomotiv-popup wmt-index-0 wmt-current" data-index="0">
                    <div class="woomotiv-container">
                        <div class="woomotiv-image">
                            <img src="'.woomotiv()->url.'/img/150.png">
                        </div>
                        <div class="woomotiv-content">
                            <p>
                                <strong class="wmt-buyer">John D</strong> . recently purchased <br>
                                <strong class="wmt-product">Hoodie With Logo</strong> <br>
                                <span class="wmt-by">By <span>Woomotiv</span></span>
                            </p>
                        </div>                    
                    </div>
                    <a class="woomotiv-link" href="http://woomotiv.delabon.com"></a>
                    <div class="woomotiv-close" style="display:inline-block;">
                        <svg viewBox="0 0 24 24" width="12" height="12" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 11.293l10.293-10.293.707.707-10.293 10.293 10.293 10.293-.707.707-10.293-10.293-10.293 10.293-.707-.707 10.293-10.293-10.293-10.293.707-.707 10.293 10.293z"/>
                        </svg>
                    </div>
                </div>
            ';

        }

    }

    /**
     * Returns custom popup add modal
     */
    function ajax_custom_popup_add_form(){
        validateNounce();
        require __DIR__ . '/../views/custom-popup/add.php';
        die;
    }

    /**
     * Returns custom popup add modal
     */
    function ajax_custom_popup_edit_form(){
        global $wpdb;
        
        validateNounce();
    
        $id = empty( $_POST['id'] ) ? 0 : (int)$_POST['id'];
        $table = $wpdb->prefix.'woomotiv_custom_popups';

        if( $id ){

            $result = $wpdb->get_row( "SELECT * FROM {$table} WHERE id = " . $id );     

            if( $result ){

                $image = wp_get_attachment_image_src( $result->image_id );

                if( ! $image ){
                    $image_url = woomotiv()->url . '/img/150.png';
                }
                else{
                    $image_url = $image[0];
                }

                $expiry_date = convert_timezone( $result->date_ends );
                
                require __DIR__ . '/../views/custom-popup/edit.php';
            }

        }

        die;
    }

    /**
     * Delete custom popup by id
     */
    function ajax_custom_popup_delete(){
        global $wpdb;
        
        validateNounce();
    
        $id = empty( $_POST['id'] ) ? 0 : (int)$_POST['id'];
        $table = $wpdb->prefix.'woomotiv_custom_popups';

        if( $id ){
            $wpdb->delete( $table, array( 'id' => $id ), array( '%d' ) );
        }

        # decrease counter
        $total = (int)get_option('woomotiv_total_custom_popups', 0 );

        if( $total != 0 ){
            update_option('woomotiv_total_custom_popups', $total - 1 );
        }

        response( true );        
    }

    /**
     * Saves the custom popup data
     */
    function ajax_custom_popup_save(){
        global $wpdb;

        validateNounce();

        $table = $wpdb->prefix.'woomotiv_custom_popups';
        $id = empty( $_POST['id'] ) ? 0 : (int)$_POST['id'];
        $now = convert_timezone( new \DateTime() );
        $image_id = empty( $_POST['image_id'] ) ? 0 : (int)$_POST['image_id'];
        $content = empty( $_POST['content'] ) ? 'Visit delabon.com for powerful Woocommerce plugins.' : $_POST['content'] ;
        $link = empty( $_POST['link'] ) ? 'https://delabon.com' : $_POST['link'];
        $expiry_date = empty( $_POST['expiry_date'] ) ? $now->format('Y-m-d H:i:s') : $_POST['expiry_date'];

        $expiry_date_obj = convert_timezone( new \DateTime( $expiry_date ) );
        $expiry_date = $expiry_date_obj->format('Y-m-d H:i:s');

        // add new
        if( ! $id ){

            $wpdb->insert( $table, 
                array( 
                    'image_id'      => $image_id, 
                    'content'       => $content,
                    'link'          => $link,
                    'date_ends'     => $expiry_date,
                    'date_created'  => $now->format('Y-m-d H:i:s'),
                    'date_updated'  => $now->format('Y-m-d H:i:s'),
                ), 
                array( 
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                ) 
            );

            # increase counter
            $total = (int)get_option('woomotiv_total_custom_popups', 0 );
            update_option('woomotiv_total_custom_popups', $total +1 );
        }

        // update
        else {
            $wpdb->update( $table, 
                array( 
                    'image_id'      => $image_id, 
                    'content'       => $content,
                    'link'          => $link,
                    'date_ends'     => $expiry_date,
                    'date_updated'  => $now->format('Y-m-d H:i:s'),
                ), 
                array( 'id' => $id ), 
                array( 
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                    '%s',
                ),
                array( '%d' ) 
            );
        }

        response( true );
    }

}
