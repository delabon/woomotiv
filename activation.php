<?php 


/**
 * Fires after plugin installation
 */
register_activation_hook( __DIR__ . '/index.php', function (){
    global $wpdb;

    $charset_collate = $wpdb->get_charset_collate();
    
    /** Stats Table */
    $table = $wpdb->prefix . 'woomotiv_stats';

    if( $wpdb->get_var( "show tables like '{$table}'" ) != $table ) {

        $sql = "CREATE TABLE $table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            popup_type  VARCHAR(30) NOT NULL,
            product_id bigint(20) NOT NULL,
            clicks bigint(20) NOT NULL,
            the_day int(2) NOT NULL,
            the_month int(2) NOT NULL,
            the_year int(4) NOT NULL,
            PRIMARY KEY (id)
            ) $charset_collate;";
            
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
    else {

        $result = $wpdb->query("SHOW COLUMNS FROM {$table} LIKE 'popup_type'");

        if( ! $result ){
            $wpdb->query( "ALTER TABLE $table ADD COLUMN popup_type VARCHAR(30);" );
        }
           
    }

    /** Custom Popups Table */
    $table = $wpdb->prefix . 'woomotiv_custom_popups';

    if( $wpdb->get_var( "show tables like '{$table}'" ) != $table ) {

        $sql = "CREATE TABLE $table (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            image_id bigint(20) NOT NULL,
            content varchar(250) NOT NULL,
            link varchar(250) NOT NULL,
            date_ends DATETIME NOT NULL,
            date_created DATETIME NOT NULL,
            date_updated DATETIME NOT NULL,
            PRIMARY KEY (id)
            ) $charset_collate;";
            
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    update_option( 'woomotiv_do_activation_redirect', true );
    update_option('woomotiv_version', WOOMOTIV_VERSION );
});


/**
 * Redirect after installation
 */
add_action( 'admin_init', function (){
    if ( get_option( 'woomotiv_do_activation_redirect', false ) ) {

        delete_option( 'woomotiv_do_activation_redirect' );
    
        if( ! isset( $_GET['activate-multi'] ) ) {

            wp_redirect( "admin.php?page=woomotiv" );
        
        }
    }
});

