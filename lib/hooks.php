<?php 

/**
 * Admin notices process
 */

use WooMotiv\Framework\Helper;

add_action( 'init', function () {

    if( woomotiv()->request->get('woomotiv_hide_review_notice') ){

        update_option('woomotiv_hide_review_notice', 1 );
        set_transient('woomotiv_hide_notices', 1, 345600 ); // 4 days
        echo '<script>location.href = "https://wordpress.org/support/plugin/woomotiv/reviews/?rate=5#rate-response";</script>';
    }

    if( woomotiv()->request->get('woomotiv_hide_premium_notice') ){

        set_transient('woomotiv_hide_notices', 1, 345600 ); // 4 days
        echo '<script>location.href = "'.admin_url('admin.php?page=woomotiv-pricing').'";</script>';
    }

    if( woomotiv()->request->get('woomotiv_hide_notices') ){

        set_transient('woomotiv_hide_notices', 1, 345600 ); // 4 days
    }

});

/**
 * Admin alert when woocommerce is not installed
 */
add_action( 'admin_notices', function () {

    if( ! class_exists('Woocommerce') ) {
        ?>
            <div class="notice notice-error">
                <p><?php _e( 'Woomotiv needs Woocommerce to be installed.', 'woomotiv' ); ?></p>
            </div>
        <?php
    }

    /** Only Free */
    if( wmv_fs()->is_free_plan() ){

        $hide_review_notice = get_option( 'woomotiv_hide_review_notice', 0 );

        if( get_transient('woomotiv_hide_notices') ){
            return ;
        }

        ?>
            <div class="notice notice-warning">

                <p style="font-size: 15px;">
                    <?php _e("Hi there! you have been using <strong>Woomotiv</strong> for few days. I hope it is helpful."); ?>
                    <br>

                    <?php if( $hide_review_notice == 0 ): ?>
                        <?php _e("Would you mind give it 5-stars review to help spread the word? And to keep me updating it & adding more features to it!", 'woomotiv' ); ?>
                    <?php else: ?>
                        <?php _e("Would you want to get awesome features?", 'woomotiv' ); ?>
                        <br>
                        <strong><?php _e("Review popups (Very powerful).", 'woomotiv' ); ?></strong>
                        <br>
                        <strong><?php _e("Custom popups.", 'woomotiv' ); ?></strong> 
                        <br>
                        <strong><?php _e("Display orders popups which still processing (Very powerful).", 'woomotiv' ); ?></strong> 
                        <br>
                        <strong><?php _e("And more.", 'woomotiv' ); ?></strong>        
                    <?php endif; ?>

                </p>

                <p>

                    <?php if( $hide_review_notice == 0 ): ?>
                        <a class="button button-primary" href="<?php echo add_query_arg( 'woomotiv_hide_review_notice', 'true', woomotiv()->request->url() ); ?>">Give it 5-stars</a>
                    <?php endif; ?>
                    
                    <a class="button button-primary" href="<?php echo add_query_arg( 'woomotiv_hide_premium_notice', 'true', woomotiv()->request->url() ); ?>">Try premium version</a>
                    <a class="button" href="<?php echo add_query_arg( 'woomotiv_hide_notices', 'true', woomotiv()->request->url() ); ?>">Thanks, later</a>
                </p>

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

    if ( is_email( $id_or_email ) ) {
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