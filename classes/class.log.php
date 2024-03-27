<?php
/**
 * This is the main class for log the error and response of zoho api v2.0
 * 
 */

if( !class_exists( 'Zoho_Log' ) ){

    class Zoho_Log{

        public $log_file;

        public function __construct(){

            $this->log_file = $this->getLogFile();
        }

        /**
         * Write log on every crm request.
         * 
         * @since   2.0.0
         * @access  public
         * @return  void
         */

        public function create( $input_data = array(), $response, $module, $email = false ){

            /* Sample response look like this
            
            $data = '{
                "data": [
                   {
                        "message": "record added",
                        "details": {
                            "created_by": {
                                "id": "4108880000086001",
                                "name": "Patricia Boyle"
                            },
                            "id": "4108880000478027",
                            "modified_by": {
                                "id": "4108880000086001",
                                "name": "Patricia Boyle"
                            },
                            "modified_time": "2016-04-28T17:59:21+05:30",
                            "created_time": "2016-04-28T17:59:21+05:30"
                        },
                        "status": "success",
                        "code": "SUCCESS"
                    }
                ]
            }';*/
            
            if( isset( $response['data'][0]['status'] ) ){

                $crm_response = $response['data'][0];
            }else{
                $crm_response = $response;
            }

            if( isset( $crm_response['status'] ) ){

                $file = $this->openLogFile();

                $log_content = $this->prepareLogContent( $input_data, $crm_response, $module );

                $this->writeLog( $file, $log_content );

                $this->closeLogFile( $file );
            }

            if( isset( $response['data'][0]['status'] ) && $response['data'][0]['status'] == 'error' ){

                $email = true;
            }elseif( isset( $response['status'] ) && $response['status'] == 'error' ){
                $email = true;
            }

            if( $email ){

                /**
                 * Send email to developers
                 */

                $headers[] = 'From: PIL <'.get_option("admin_email").'>';

                $body = "Input Data - ".json_encode( $input_data );

                $body .= "\n\n Response Data - ".json_encode( $response );

                wp_mail( 'vasimkhan338@gmail.com', 'PIL Error Mail', $body, $headers );
            }
        }

        /**
         * Get log file path
         * 
         * @since   2.0.0
         * @access  public
         * @return  string  "log file full path."
         */

        function getLogFile(){

            $upload_paths   = wp_upload_dir();
            $upload_dir     = $upload_paths['basedir'];

            /**
             * Check log directory exists or not
             * 
             */

            if( !is_dir( $upload_dir.'/zoho-bridge' ) ){

                /**
                 * If log directory not exists then create it.
                 * 
                 */

                mkdir( $upload_dir.'/zoho-bridge', 0777 );
            }

            /**
             * Setup log file name day wise.
             * 
             */

            $log_file_path  = $upload_dir.'/zoho-bridge/'.current_time( 'Ymd' ).'_log.txt';

            return $log_file_path;
        }

        /**
         * Open log file using 
         * 
         * @since   2.0.0
         * @access  public
         * @return  file link
         */

        function openLogFile(){

            /**
             * Open log file.
             */

            $fo = fopen( $this->log_file, 'a' );

            return $fo;
        }

        /**
         * Prepare log content.
         * 
         * @since   2.0.0
         * @access  public
         * @return  string
         */

        function prepareLogContent( $input_data = array(), $data, $module ){

            $details = $data['details'];

            $log_content = "\n\n**********************************************".current_time( 'd-m-Y h:i:a' )."************************************************************";

            $log_content .= "\n\n ".$module;
            
            if( isset( $details['id'] ) ){

                $log_content .= "\n\n {$module} added successfully with id - ".$details['id'];

            }

            $log_content .= "\n\n Input Data -- ".json_encode( $input_data );

            $log_content .= "\n\n ZOHO Response -- ".json_encode( $data );

            return $log_content;
        }

        /**
         * Write log content to log file.
         * 
         * @since   2.0.0
         * @access  public
         * @return  void
         */

        function writeLog( $file, $log_content ){

            fwrite( $file, $log_content );
        }

        /**
         * Close the open log file.
         * 
         * @since   2.0.0
         * @access  public
         * @return  void
         */

        function closeLogFile( $file ){

            fclose( $file );
        }
    }
}