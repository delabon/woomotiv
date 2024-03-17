<?php 

namespace WooMotiv;

use WooMotiv\Framework\Alert;
use WooMotiv\Framework\Panel;

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

        add_action( 'admin_notices', array( $this, 'adminNotices' ) );
        add_action( 'admin_menu', array( $this, 'adminMenuAction' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'loadAssets' ), 2 );
        add_action( 'admin_footer', array( $this, 'printTemplates' ) );
        add_action( 'network_admin_menu', array( $this, 'adminMenuMultisite' ));

        add_action( 'woocommerce_order_status_completed', array( $this, 'wc_order_status_completed'), 10, 1 );
        add_action( 'admin_footer', array( $this, 'review_popup'), 0 );

        # Ajax
        add_action('wp_ajax_woomotiv_custom_popup_add_form', array( $this, 'ajax_custom_popup_add_form') );
        add_action('wp_ajax_woomotiv_custom_popup_edit_form', array( $this, 'ajax_custom_popup_edit_form') );
        add_action('wp_ajax_woomotiv_custom_popup_save', array( $this, 'ajax_custom_popup_save') );
        add_action('wp_ajax_woomotiv_custom_popup_delete', array( $this, 'ajax_custom_popup_delete') );
        add_action('wp_ajax_woomotiv_cancel_review', array( $this, 'ajax_cancel_review'));
    }

    /**
     * Add top level menu when Multisite is enabled
     *
     * @return void
     */
    function adminMenuMultisite() {
        add_menu_page( 
            "Woomotive Mulsite", 
            "Woomotive Mulsite", 
            'manage_options', 
            'woomotiv', 
            function(){
                ?>
                    <div class="wrap">
                        <h1 class="wp-heading-inline">Woomotiv Multisite</h1>
                        <p>
                            <strong>Each site has its Woomotiv settings page. And Woomotiv will display sales notification by site.</strong>
                            <br>
                            <a href="<?php echo admin_url('admin.php?page=woomotiv') ?>">Main site settings page.</a>
                        </p>
                    </div>
                <?php 
            } 
        );  
    } 

    /**
     * Admin notices
     * Using this hook prevents headers already sent warning
     */
    function adminNotices() {

        if( ! woomotiv()->request->post('woomotiv_nonce') ) return;

        echo Alert::success( __('Options Saved Successfuly','woomotiv') );
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
            
            // Clear cookies
            clear_cookies();
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

        add_submenu_page( 
            'woomotiv', 
            __('Get Support', 'woomotiv'), 
            __('Get Support', 'woomotiv'), 
            'manage_options',
            'woomotiv_contact', 
            array( $this, 'render_contact_page' )
        );
    }

    /**
     * Render contact page
     */
    function render_contact_page(){
        echo '<script>location.href="https://delabon.com/support";</script>';
        die;
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
            __('Style','woomotiv'), 
            'style', 
            woomotiv()->dir . '/views/tabs/style.php' 
        );

        $panel->addTab( 
            __('Report','woomotiv'), 
            'report', 
            woomotiv()->dir . '/views/tabs/report.php' 
        );

        // $panel->addTab( 
        //     __('Discover','woomotiv') . '<span style="background: orange;">3</span>', 
        //     'discover',  
        //     woomotiv()->dir . '/views/tabs/discover.php' 
        // );

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

        // load it on all pages
        wp_enqueue_style( 
            'woomotiv_admin_review_popup', 
            woomotiv()->url . '/css/admin-review-popup.css', 
            array(), 
            woomotiv()->version 
        );

        wp_enqueue_script( 
            'woomotiv_admin_review_popup', 
            woomotiv()->url . '/js/admin-review-popup.js', 
            array('jquery'), 
            woomotiv()->version, 
            true 
        );

        wp_localize_script( 'woomotiv_admin_review_popup', 'woomotiv_admin_review_popup', array(
            'nonce' => wp_create_nonce('woomotiv'),
        ));

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
            $link = "https://delabon.com/store/sales-notification-for-woocommerce";

            echo '<style>' . $style . '</style>

                <div data-size="'.woomotiv()->config->woomotiv_style_size.'" data-shape="'.woomotiv()->config->woomotiv_shape.'" data-position="'.woomotiv()->config->woomotiv_position.'" data-animation="'.woomotiv()->config->woomotiv_animation.'" class="woomotiv-popup wmt-index-0 wmt-current" data-index="0">

                    <div class="woomotiv-image">
                        <img src="'.woomotiv()->url.'/img/150.png">
                    </div>

                    <p>
                        <strong class="wmt-buyer">John D</strong> . recently purchased <br>
                        <strong class="wmt-product">Hoodie With Logo</strong> <br>
                        <span class="wmt-by">By <span>Woomotiv</span></span>

                        <a class="woomotiv-link" href="https://delabon.com/store/sales-notification-for-woocommerce"></a>
                        
                        <span class="woomotiv-close" style="display:inline-block;">&times;</span>
                    </p>

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

    /**
     * Fires when an order is completed
     *
     * @param [type] $order_id
     * @return void
     */
    function wc_order_status_completed( $order_id ) {

        $count = (int)get_option('woomotiv_sales_count_after_install', 0 );
        $count += 1;

        update_option('woomotiv_sales_count_after_install', $count);
    }

    /**
     * Display a review popup
     *
     * @return void
     */
    function review_popup(){

        $count = (int)get_option('woomotiv_sales_count_after_install', 0 );
        $cancel_review_count = (int)get_option('woomotiv_cancel_review_count', 0 );
        
        if( ! in_array( $count, [ 5, 20, 100, 500, 1000 ] ) ) return;
        if( $cancel_review_count === $count ) return;

        ?>
            <div class="woomotiv-reviews-popup">
                <div class="woomotiv-reviews-popup-content">

                    <img src="<?php echo WOOMOTIV_URL . '/img/trophy.png'; ?>" alt="Congrat" >
                    
                    <h1>Congratulations!</h1>
                    <p>You have got <strong><?php echo $count ?> SALES</strong> since installing <strong>Woomotiv</strong>!</p>
                    <p>Help Woomotiv by giving it 5 stars review!</p>
                    <a href="<?php echo WOOMOTIV_REVIEW_URL; ?>" target="_blank" class="__go_review">Yes, I Will Help Now!</a>
                    <br>
                    <a href="#" class="__cancel_review">
                        No, I don't want to help.
                    </a>
                </div>
            </div>
        <?php
    }

    /**
     * Cancel review
     *
     * @return void
     */
    function ajax_cancel_review(){

        validateNounce();

        $count = (int)get_option('woomotiv_sales_count_after_install', 0 );
        update_option('woomotiv_cancel_review_count', $count );

        die;
    }
}
