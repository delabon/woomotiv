<?php 
/**
 * Envato API class.
 *
 */

namespace DLBV2;

if( ! class_exists( Envato_API::class ) ){

    class Envato_API{

	    /**
		 * The single class instance.
		 *
		 * @var object
		 */
		private static $_instance = null;

		/**
		 * The Envato API personal token.
		 *
		 * @var string
		 */
		public $token;

		/**
		 * Main Instance
		 *
		 * Ensures only one instance of this class exists in memory at any one time.
		 *
		 * @return object The one true Envato_API.
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}

		/**
		 * A private constructor to prevent this class from being loaded more than once.
         *
         * @param string $token
         */
		private function __construct( $token ) {

            $this->token = $token;

        }

		/**
		 * Query the Envato API.
		 *
		 * @uses wp_remote_get() To perform an HTTP request.
		 *
		 * @param  string $endpoint API endpoint.
		 * @param  array  $args The arguments passed to `wp_remote_get`.
		 * @return array|WP_Error  The HTTP response.
		 */
		public function request( $endpoint, $args = array() ) {

            $url = 'https://api.envato.com' . $endpoint;

			$defaults = array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $this->token,
					'User-Agent'    => 'Mozilla/5.0 (compatible; Delabon Envato API APP V1)',
				),
				'timeout' => 14,
            );
            
			$args = wp_parse_args( $args, $defaults );

            $token = trim( str_replace( 'Bearer', '', $args['headers']['Authorization'] ) );
            
			if ( empty( $token ) ) {
				return new WP_Error( 'api_token_error', 'An API token is required.' );
			}

			// Make an API request.
			$response = wp_remote_get( esc_url_raw( $url ), $args );

			// Check the response code.
			$response_code    = wp_remote_retrieve_response_code( $response );
			$response_message = wp_remote_retrieve_response_message( $response );

			// API connectivity issue.
			if ( ! empty( $response->errors ) && isset( $response->errors['http_request_failed'] ) ) {
                return new WP_Error( 'http_error', esc_html( current( $response->errors['http_request_failed'] ) ) );
			}

			if ( 200 !== $response_code && ! empty( $response_message ) ) {
				return new WP_Error( $response_code, $response_message );
            } 
            elseif ( 200 !== $response_code ) {
				return new WP_Error( $response_code, 'An unknown API error occurred.' );
            } 
            else {
                $return = json_decode( wp_remote_retrieve_body( $response ), true );
                
                if ( null === $return ) {
					return new WP_Error( 'api_error', 'An unknown API error occurred.' );
                }
                
				return $return;
			}
		}
        
        /**
         * Verify Purchase
         * ( It requires the “View your items’ sales history" permission )
         * 
         * @param string $purchase_code
         * @param string $item_id Item envato id
         * @return mixed
         */
        public function verify_purchase( $purchase_code, $item_id ){

            if( empty( $purchase_code ) ){
				return new WP_Error( 'api_error', 'Empty Purchase Code' );
            }

            $body = $this->request('/v3/market/author/sale?code=' . $purchase_code );

            if( $body->item->id !== $item_id ){
				return new WP_Error( 'api_error', 'The purchase code provided is for a different item' );
            }

            return true;
        }

    }

}
