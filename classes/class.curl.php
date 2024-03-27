<?php
/**
 * This is the main class for the curl request.
 * 
 * @since   2.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if( !class_exists( 'Zoho_Curl' ) ){

    class Zoho_Curl{

        private $token;

        protected function request( $url, $data = array() ){

            /**
             * Refrese token if expire.
             */
            $this->validateToken();

            $token_data  = get_option( 'zv2_token' );
            $this->token = $token_data['access_token'];

            $ch     = curl_init();
            curl_setopt( $ch, CURLOPT_URL, $url );
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content Type: text/xml','Authorization: Zoho-oauthtoken '.$this->token ) );
            curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );

            /**
             * Only on update record.
             */

            if( !empty( $data ) && isset( $data['data'][0]['id'] ) ){

                curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "PUT" );
            }

            if( strpos( $url, 'ids') !== false ) {

                curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, "DELETE" );
            }

            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
            
            if( !empty( $data ) ){

                curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $data ) );
            }

            $result = curl_exec( $ch );
            
            curl_close( $ch );

            return json_decode( $result, true );
        }

        /**
         * Refrese token of expire
         * 
         * @since   2.0.0
         * @access  public
         * @return  void
         */

        function validateToken(){

            $token_data = get_option( 'zv2_token' );
            $api_info   = get_option( 'zb_api_info' );
        
            if( current_time( 'timestamp' ) > $token_data['token_expire_timestamp'] || current_time( 'timestamp' ) == $token_data['token_expire_timestamp'] ){ 
        
                $url        = 'https://accounts.zoho.com/oauth/v2/token';
                $param      = 'refresh_token='.$token_data["refresh_token"].'&client_id='.$api_info["client_id"].'&client_secret='.$api_info["client_secret"].'&grant_type=refresh_token';
        
                $headers = array( 'Content Type: text/xml' );
        
                $ch_fetch_pot = curl_init();
                curl_setopt($ch_fetch_pot, CURLOPT_URL, $url);
                curl_setopt($ch_fetch_pot, CURLOPT_HTTPHEADER, $headers );
                curl_setopt($ch_fetch_pot, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch_fetch_pot, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch_fetch_pot, CURLOPT_POSTFIELDS, $param);
               
                $result_fetch_pot = curl_exec($ch_fetch_pot);
                curl_close($ch_fetch_pot);
                
                $response = json_decode( $result_fetch_pot, true );
        
                $after_1hour = current_time( 'timestamp' ) + 3600;
        
                if( isset( $response['access_token'] ) ){
        
                    $response['token_expire_timestamp'] = $after_1hour;
                    $response['refresh_token'] = $token_data["refresh_token"];
                    
                    update_option( 'zv2_token',$response );
                }

            }
        }
    }
}
?>