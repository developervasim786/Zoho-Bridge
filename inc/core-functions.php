<?php

/**

 * Plugin Main Core function File.

 * 

 */



/**

 * Zoho_Bridge class object retun core funtion.

 * 

 * @since   2.0.0

 * @access  public

 * @return  instace of 'Zoho_Bridge' class object

 */



function ZB(){



    global $zb;



    return $zb;

}





function zb_refreseToken(){



    $token_data = get_option( 'zv2_token' );



    $api_info   = get_option( 'zb_api_info' );



    if( current_time( 'timestamp' ) > $token_data['token_expire_timestamp'] || current_time( 'timestamp' ) == $token_data['token_expire_timestamp'] ){ 



        $url        = 'https://accounts.zoho.'.ZB_DOMAIN.'/oauth/v2/token';

        $param      = 'refresh_token='.$token_data["refresh_token"].'&client_id='.$api_info["client_id"].'&client_secret='.$api_info["client_secret"].'&grant_type=refresh_token';



        $headers = array( 'Content Type: text/xml' );



        $ch_fetch_pot = curl_init();

        curl_setopt($ch_fetch_pot, CURLOPT_URL, $url);

        curl_setopt($ch_fetch_pot, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($ch_fetch_pot, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch_fetch_pot, CURLOPT_POSTFIELDS, $param);

        

        $result_fetch_pot = curl_exec($ch_fetch_pot);

        curl_close($ch_fetch_pot);

        

        $response = json_decode( $result_fetch_pot, true );



        $after_1hour = strtotime(date( 'd-m-Y h:i:s', strtotime( current_time( 'd-m-Y h:i:s' )." + 1 hours" ) ));



        if( isset( $response['access_token'] ) ){



            $response['token_expire_timestamp'] = $after_1hour;

            $response['refresh_token'] = $token_data["refresh_token"];

            update_option( 'zv2_token',$response );

        }

    }

}



function zb_get_token(){

    $token_data = get_option( 'zv2_token' );

    return $token_data["access_token"];

}



function zcrm_get_record_by_id( $module, $id ){

    zb_refreseToken();
    $access_token = zb_get_token();
    $url = 'https://www.zohoapis.'.ZB_DOMAIN.'/crm/v3/'.$module.'/'.$id;
    $curl = curl_init();
    curl_setopt( $curl, CURLOPT_URL,$url) ;
    curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Zoho-oauthtoken '.$access_token ) );
    curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
    $result = curl_exec( $curl );
    curl_close($curl);

    $response = json_decode($result,true);
    if( isset( $response["data"] ) ){
        return $response["data"][0];
    }
}

function zcrm_get_records( $module ){

    zb_refreseToken();
    $access_token = zb_get_token();

    $url = 'https://www.zohoapis.'.ZB_DOMAIN.'/crm/v3/'.$module."?fields=id";
    $curl = curl_init();
    curl_setopt( $curl, CURLOPT_URL,$url) ;
    curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Zoho-oauthtoken '.$access_token ) );
    curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
    $result = curl_exec( $curl );
    curl_close($curl);
    $response = json_decode($result,true);

    if( isset( $response["data"] ) ){
        return $response["data"];
    }
}

function zcrm_get_attachments( $module, $id ){
    zb_refreseToken();
    $access_token = zb_get_token();
    $url = 'https://www.zohoapis.'.ZB_DOMAIN.'/crm/v3/'.$module.'/'.$id.'/Attachments?fields=id,Owner,File_Name,Created_Time,Parent_Id';
    $curl = curl_init();
    curl_setopt( $curl, CURLOPT_URL,$url) ;
    curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Zoho-oauthtoken '.$access_token ) );
    curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
    $result = curl_exec( $curl );
    curl_close($curl);

    $response = json_decode($result,true);
    if( isset( $response["data"] ) ){
        return $response["data"];
    }
}

function zcrm_download_attachment( $module, $record_id, $attachment_id ){
    zb_refreseToken();
    $access_token = zb_get_token();
    $url = 'https://www.zohoapis.'.ZB_DOMAIN.'/crm/v6/'.$module.'/'.$record_id.'/Attachments/'.$attachment_id;
    $curl = curl_init();
    curl_setopt( $curl, CURLOPT_URL,$url) ;
    curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Zoho-oauthtoken '.$access_token ) );
    curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
    $result = curl_exec( $curl );
    curl_close($curl);

    return $result;
}

function zcrm_get_users(){

    zb_refreseToken();
    $access_token = zb_get_token();

    $url = 'https://www.zohoapis.'.ZB_DOMAIN.'/crm/v3/users?type=AllUsers';
    $curl = curl_init();
    curl_setopt( $curl, CURLOPT_URL,$url) ;
    curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Zoho-oauthtoken '.$access_token ) );
    curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
    $result = curl_exec( $curl );
    curl_close($curl);
    $response = json_decode($result,true);

    if( isset( $response["users"] ) ){
        return $response["users"];
    }
}

function zcrm_search_record( $module, $field_name, $field_value ){



    zb_refreseToken();

    $access_token = zb_get_token();



    $url = "https://www.zohoapis.".ZB_DOMAIN."/crm/v3/".$module."/search?criteria=(".$field_name.":equals:".$field_value.")";

    

    $curl = curl_init();

    curl_setopt( $curl, CURLOPT_URL,$url) ;

    curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Zoho-oauthtoken '.$access_token ) );

    curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );

    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

    $result = curl_exec( $curl );

    curl_close($curl);

    

    

    $response = json_decode($result,true);



    if( isset( $response["data"] ) ){

        

        return $response["data"];

    }

}



function zcrm_get_related_record( $module, $id, $relation_api_name, $related_fields = array() ){



    zb_refreseToken();

    $access_token = zb_get_token();



    if( !empty( $related_fields ) ){



        $fields = implode( ",", $related_fields );

        $url    = 'https://www.zohoapis.'.ZB_DOMAIN.'/crm/v3/'.$module.'/'.$id.'/'.$relation_api_name.'?fields='.$fields;

    }else{

        $url    = 'https://www.zohoapis.'.ZB_DOMAIN.'/crm/v3/'.$module.'/'.$id.'/'.$relation_api_name.'?fields=Owner';

    }



    $curl = curl_init();

    curl_setopt( $curl, CURLOPT_URL,$url) ;

    curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Zoho-oauthtoken '.$access_token ) );

    curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );

    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

    $result = curl_exec( $curl );

    curl_close($curl);

    

    $response = json_decode($result,true);



    if( isset( $response["data"] ) ){

        

        return $response["data"];

    }

}



/**

 * Create record in ZOHO CRM

 */



function zcrm_create_record( $module, $data ){

    zb_refreseToken();
    $access_token = zb_get_token();

    $url  = 'https://www.zohoapis.'.ZB_DOMAIN.'/crm/v3/'.$module;
    $curl = curl_init();
    curl_setopt( $curl, CURLOPT_URL,$url ) ;
    curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Zoho-oauthtoken '.$access_token ) );
    curl_setopt( $curl, CURLOPT_POST, true );
    curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode( $data ) );
    curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
    $result = curl_exec( $curl );
    curl_close($curl);
    $response = json_decode($result,true);
    return $response;
}

function zcrm_create_record_notes( $module, $content, $rec_id ){

    zb_refreseToken();
    $access_token = zb_get_token();

    $data = array(
        "data" => array(
            array(
                "Note_Content" => $content
            )
        )
    );

    $url  = 'https://www.zohoapis.'.ZB_DOMAIN.'/crm/v3/'.$module.'/'.$rec_id.'/Notes';
    $curl = curl_init();
    curl_setopt( $curl, CURLOPT_URL,$url ) ;
    curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Zoho-oauthtoken '.$access_token ) );
    curl_setopt( $curl, CURLOPT_POST, true );
    curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode( $data ) );
    curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
    $result = curl_exec( $curl );
    curl_close($curl);
    $response = json_decode($result,true);
    return $response;
}

function zcrm_update_record( $module, $data ){

    zb_refreseToken();
    $access_token = zb_get_token();
    $url = 'https://www.zohoapis.'.ZB_DOMAIN.'/crm/v3/'.$module;
    $curl = curl_init();
    curl_setopt( $curl, CURLOPT_URL,$url) ;
    curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Authorization: Zoho-oauthtoken '.$access_token ) );
    curl_setopt( $curl, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt( $curl, CURLOPT_POSTFIELDS, json_encode( $data ) );
    curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );
    curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );
    $result = curl_exec( $curl );
    curl_close($curl);
    $response = json_decode($result,true);
    return $response;

}

function zb_is_connected(){
    $crm_users = zcrm_get_users();
    if( isset( $crm_users[0] ) ){
        return true;
    }
    return false;
}

/*function zb_add_flight_to_crm( $data ){



    if( $data["Trip_Type"] == "Round trip" ){



        $crm_data = array(

            "data" => array(

                array(

                    "First_Name"            => $data["First_Name"],

                    "Last_Name"             => $data["Last_Name"],

                    "Lead_Source"           => "Web site",

                    "Trip_Type"             => $data["Trip_Type"],

                    "First_Name_Crm"        => $data["First_Name"],

                    "Last_Name_Crm"         => $data["Last_Name"],

                    "Cabin"                 => $data["Ticket_Type"],

                    "Phone"                 => $data["Phone"],

                    "Email"                 => $data["Email"],

                    "Number_of_passengers"      => $data["Number_of_passengers"],

                    "Departure_city"            => $data["Flight"][0]["Departure_city"],

                    "Destination_City"          => $data["Flight"][0]["Destination_city"],

                    "Destination_Data"          => $data["Flight"][0]["Departure_date"],

                    "Departure_date"            => $data["Return_date"],

                    "Flight" => array(

                        array(

                            "Departure_city"    => $data["Flight"][0]["Departure_city"],

                            "Destination_city"  => $data["Flight"][0]["Destination_city"],

                            "Departure_date"    => date("Y-m-d",strtotime($data["Flight"][0]["Departure_date"])),

                            "Return_date"       => date("Y-m-d",strtotime($data["Return_date"])),

                        )

                    )

                )

            )

        );

    }



    if( $data["Trip_Type"] == "One way" ){

        

        $crm_data = array(

            "data" => array(

                array(

                    "First_Name"            => $data["First_Name"],

                    "Last_Name"             => $data["Last_Name"],

                    "Lead_Source"           => "Web site",

                    "Trip_Type"             => $data["Trip_Type"],

                    "First_Name_Crm"        => $data["First_Name"],

                    "Last_Name_Crm"         => $data["Last_Name"],

                    "Cabin"                 => $data["Ticket_Type"],

                    "Phone"                 => $data["Phone"],

                    "Email"                 => $data["Email"],

                    "Number_of_passengers"      => $data["Number_of_passengers"],

                    "Departure_city"            => $data["Flight"][0]["Departure_city"],

                    "Destination_City"          => $data["Flight"][0]["Destination_city"],

                    "Destination_Data"          => $data["Flight"][0]["Departure_date"],

                    "Flight" => array(

                        array(

                            "Departure_city"    => $data["Flight"][0]["Departure_city"],

                            "Destination_city"  => $data["Flight"][0]["Destination_city"],

                            "Departure_date"    => date("Y-m-d",strtotime($data["Flight"][0]["Departure_date"]))

                        )

                    )

                )

            )

        );

    }



    if( $data["Trip_Type"] == "Multi city" ){

        

        $flight_subform = array();



        foreach( $data["Flight"] as $flight ){

            $flight_subform[] = array(

                "Departure_city"    => $flight["Departure_city"],

                "Destination_city"  => $flight["Destination_city"],

                "Departure_date"    => date("Y-m-d",strtotime($flight["Departure_date"]))

            );

        }



        $crm_data = array(

            "data" => array(

                array(

                    "First_Name"            => $data["First_Name"],

                    "Last_Name"             => $data["Last_Name"],

                    "Lead_Source"           => "Web site",

                    "Trip_Type"             => $data["Trip_Type"],

                    "First_Name_Crm"        => $data["First_Name"],

                    "Last_Name_Crm"         => $data["Last_Name"],

                    "Cabin"                 => $data["Ticket_Type"],

                    "Phone"                 => $data["Phone"],

                    "Email"                 => $data["Email"],

                    "Number_of_passengers"  => $data["Number_of_passengers"],

                    "Flight"                => $flight_subform

                )

            )

        );

    }



    $lead = zcrm_create_record( "Leads", $crm_data );

}*/

?>