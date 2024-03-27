<?php
/**
 * Plugin main actions file.
 * 
 * @since   2.0.0
 */

// Lets check lead check

add_action("init",function(){

    if( isset( $_GET["sample_lead"] ) ){
        $data = array(
            "data" => array(
                array(
                    "First_Name"    => "Developer",
                    "Last_Name"     => "Rajesh"
                )
            )
        );

        $lead = zcrm_create_record( "Leads", $data );

        echo "<pre>";
        print_r($lead);
        echo "</pre>";
        die;
    }
},999);
?>