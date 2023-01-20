<?php 

/**
 * Admin alert when woocommerce is not installed
 */
add_action( 'admin_notices', function () {
    if( ! class_exists('Woocommerce') ) {
        ?>
            <div class="notice notice-error">
                <p><?php _e( 'Woomotiv requires Woocommerce.', 'woomotiv' ); ?></p>
            </div>
        <?php
    }
});

/**
 * Fix for "WP User Avatar" Plugin
 * get_avatar_url is not supported by the plugin, so i made a filter for this matter
 */
add_filter( 'get_avatar_url', function( $url, $id_or_email, $args ){

    if( ! function_exists('has_wp_user_avatar') ) return $url;

    if( is_string( $id_or_email ) && is_email( $id_or_email ) ) {
        $user = get_user_by( 'email', $id_or_email );
        $user_id = $user->ID;
    } 
    
    else {
        $user_id = $id_or_email;
    }
    
    if ( has_wp_user_avatar( $user_id ) ) {
        return get_wp_user_avatar_src( $user_id, 100 );
    }
    
    return $url;
    
}, 10, 3);    

/**
 * Load Plugin Text Domain
 */
add_action( 'plugins_loaded', function() {
    // wp-content/plugins/plugin-name/languages/textdomain-de_DE.mo
	load_plugin_textdomain( 'woomotiv', FALSE,  'woomotiv/languages/' );
});
