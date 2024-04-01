<?php

/**

 * Plugin admin template file.

 * 

 * @since   2.0.0

 */



// Exit if call this file directly.

if( !defined( 'ABSPATH' ) ) exit;



if( !class_exists( 'ZB_Dashboard' ) ){



    class ZB_Dashboard{



        /**

         * Class constructer

         * 

         * @since   2.0.0

         */

        public function __construct(){



            /**

             *  Admin template action and filters.

             */

            add_action( 'admin_menu', array( $this, 'zb_menu' ) );



            add_action( 'before_zb_from', array( $this, 'zb_save_api_details' ) );



            add_action( 'init', array( $this, 'zb_generate_token' ) );



            add_action( 'wp_ajax_zi_get_log', array( $this, 'zb_getLog' ) );

        }



        /**

         * Create plugin admin menu

         * 

         * @since   2.0.0

         * @access  public

         * @return  void

         */



        function zb_menu(){

            add_menu_page(
                __( 'Zoho Bridge', 'textdomain' ),
                __( 'Zoho Bridge', 'textdomain' ),
                'manage_options',
                'zoho-bridge',
                array( $this, 'zb_setting_page' )
            );  
        }



        /**

         * Plugin admin menu setting page.

         * 

         * @since   2.0.0

         * @access  public

         * @return  void

         */



        function zb_setting_page(){

            

            $token_data = get_option("zv2_token");

            ?>

            <div class="wrap">

                <h1>ZOHO Bridge <label></label></h1>

            <?php

             do_action( 'before_zb_from' );

             

             $api_info = get_option( 'zb_api_info' );

             

             ?>

             <form action="" method="post">

             <table class="form-table" >

                <tbody>

                    <tr>

                        <th>

                            <label>Client ID<label>

                        </th>

                        <td>

                            <input type="text" name="api_info[client_id]" value="<?php if( isset( $api_info['client_id'] ) ){ echo $api_info['client_id']; } ?>" class="client_id regular-text" >

                        </td>

                    </tr>

                    <tr>

                        <th>

                            <label>Client Secret<label>

                        </th>

                        <td>

                            <input type="text" name="api_info[client_secret]" value="<?php if( isset( $api_info['client_secret'] ) ){ echo $api_info['client_secret']; } ?>" class="client_secret regular-text" >

                        </td>

                    </tr>

                    <tr>

                        <th>

                            <label>Redirect URL<label>

                        </th>

                        <td>

                            <input type="text" name="api_info[redirect_url]" value="<?php if( isset( $api_info['redirect_url'] ) ){ echo $api_info['redirect_url']; } ?>" class="redirect_url regular-text" >

                        </td>

                    </tr>

                </tbody>

             </table>

                <p>

                    <input type="hidden" value="zb" name="zb" />

                    <input class="button-primary button" type="submit" value="Submit" name="submit">

                </p>

                <p>

                    <a href="#" class="auth">Authorize App</a>

                </p>

             </form>

             </div>

            

                <?php
                    if( zb_is_connected() ){
                        echo "<span class='crm-connected'>CRM connected</span>";
                    }else{
                        echo "<span class='crm-not-connected'>CRM not connected</span>";
                    }
                ?>



             <script>

                <?php if( !empty( $api_info ) ){ ?>

                    jQuery('.auth').click(function(){

                        window.open('https://accounts.zoho.<?php echo ZB_DOMAIN; ?>/oauth/v2/auth?scope=ZohoCRM.users.ALL,ZohoCRM.modules.ALL,ZohoSearch.securesearch.READ&client_id=<?php echo $api_info["client_id"]; ?>&response_type=code&access_type=offline&prompt=consent&redirect_uri=<?php echo $api_info["redirect_url"]; ?>','ZOHO App Authorization','width=700,height=700');

                    });

                <?php } ?>

             </script>
            <style>
                .crm-connected{
                    background: mediumseagreen;
                    color: #fff;
                    padding: 2px 5px;
                    line-height: 0px;
                    vertical-align: middle;
                    border-radius: 5px;
                }
                .crm-not-connected{
                    background: red;
                    color: #fff;
                    padding: 2px 5px;
                    line-height: 0px;
                    vertical-align: middle;
                    border-radius: 5px;
                }
            </style>
             <?php

        }



        /**

         * Save plugin api information.

         * 

         * @since   2.0.0

         * @access  public

         * @return  void

         */



        function zb_save_api_details(){



            if( isset( $_REQUEST['zb'] ) ){

        

                $info = $_REQUEST['api_info'];

        

                update_option( 'zb_api_info', $info );

        

                echo '<p style="color:mediumseagreen;"><b>API Credentials Saved</b></p>';

            }

        }



        /**

         * Genrate zoho api auth-token

         * 

         * @since   2.0.0

         * @access  public

         * @return  void

         */



        function zb_generate_token(){

            

            if( isset( $_REQUEST['code'] ) && isset( $_REQUEST['accounts-server'] ) ){

        

                update_option( 'zv2_grand_code', $_REQUEST['code'] );

        

                $api_info = get_option( 'zb_api_info' );

                

                $url    = 'https://accounts.zoho.'.ZB_DOMAIN.'/oauth/v2/token';

                $param     = 'code='.$_REQUEST["code"].'&redirect_uri='.$api_info["redirect_url"].'&client_id='.$api_info["client_id"].'&client_secret='.$api_info["client_secret"].'&grant_type=authorization_code';

        

                $response = $this->zb_curl( $url, $param );

        

                $after_1hour = strtotime(date( 'd-m-Y h:i:s', strtotime( current_time( 'd-m-Y h:i:s' )." + 1 hours" ) ));

        

                if( isset( $response['access_token'] ) ){

        

                    $response['token_expire_timestamp'] = $after_1hour;

                    update_option( 'zv2_token',$response );

                }

            }

        

        }



        /**

         * Make curl request.

         * 

         * @since   2.0.0

         * @access  public

         * @return  array

         */

        

        function zb_curl( $url, $param = '', $token = false ){



            //$this->is_token_valid();

        

            $token_data = get_option( 'zv2_token' );

            //$headers = array( 'Content Type: text/xml' );

        

            if( $token ){

                $headers[] = 'Authorization: Zoho-oauthtoken '.$token_data['access_token'];

            }

        

            $ch_fetch_pot = curl_init();

            curl_setopt($ch_fetch_pot, CURLOPT_URL, $url);

            //curl_setopt($ch_fetch_pot, CURLOPT_HTTPHEADER, $headers );

            curl_setopt($ch_fetch_pot, CURLOPT_SSL_VERIFYPEER, false);

            curl_setopt($ch_fetch_pot, CURLOPT_RETURNTRANSFER, true);

        

            if( !empty( $param ) ){

        

                curl_setopt($ch_fetch_pot, CURLOPT_POSTFIELDS, $param);

            }

        

            $result_fetch_pot = curl_exec($ch_fetch_pot);

            curl_close($ch_fetch_pot);

            

            return json_decode( $result_fetch_pot, true );

        }

        

        /**

         * Check is current token is valid or not.

         * 

         * @since   2.0.0

         * @access  public

         * @return  void

         */



        function is_token_valid(){

        

            $token_data = get_option( 'zv2_token' );

            $api_info   = get_option( 'zb_api_info' );

        

            if( current_time( 'timestamp' ) > $token_data['token_expire_timestamp'] || current_time( 'timestamp' ) == $token_data['token_expire_timestamp'] ){ 

        

                $url        = 'https://accounts.zoho.'.ZB_DOMAIN.'/oauth/v2/token';

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

        

                $after_1hour = strtotime(date( 'd-m-Y h:i:s', strtotime( current_time( 'd-m-Y h:i:s' )." + 1 hours" ) ));

        

                if( isset( $response['access_token'] ) ){

        

                    $response['token_expire_timestamp'] = $after_1hour;

                    update_option( 'zv2_token',$response );

                }

            }

        }

    } // class end

} // class exists check end

new ZB_Dashboard();

?>