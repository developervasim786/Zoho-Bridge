<?php
/**
 * Plugin main actions file.
 * 
 * @since   2.0.0
 */

function zb_create_property_lead_in_crm(){
    
    if( $_REQUEST["action"] == "houzez_property_agent_contact" ){

        $name           = $_POST["name"];
        $full_name      = explode(" ",$name);
        $first_name     = $full_name[0];
        $last_name      = @$full_name[1];

        if( empty( $last_name ) ){
            $first_name = "";
            $last_name  = $name;
        }

        $mobile         = $_POST["mobile"];
        $email          = $_POST["email"];
        $user_type      = $_POST["user_type"];
        $message        = $_POST["message"];
        $privacy_policy = $_POST["privacy_policy"];

        $property_permalink = $_POST["property_permalink"];
        $property_title     = $_POST["property_title"];
        $property_id        = $_POST["property_id"];

        $listing_id         = $_POST["listing_id"];
        $is_listing_form    = $_POST["is_listing_form"];
        $agent_id           = $_POST["agent_id"];

        //if( isset( $_GET["pid"] ) ){

        $property_category      = "";
        $property_status        = "";
        
        // get property "Property Type"
        $property_types = get_the_terms( $property_id, "property_type" );
        foreach ( $property_types as $p_type ) {
            $property_category = $p_type->name;
        }

        // get property "Property Status"
        $property_statuses = get_the_terms( $property_id, "property_status" );
        foreach ( $property_statuses as $p_status ) {
            $property_status = $p_status->name;
        }
        
        $property_price             = get_post_meta( $property_id,"fave_property_price",true );
        $property_price_postfix     = get_post_meta( $property_id,"fave_property_price_postfix",true );
        $property_size              = get_post_meta( $property_id,"fave_property_size",true );
        $property_size_prefix       = get_post_meta( $property_id,"fave_property_size_prefix",true );
        $property_land_postfix      = get_post_meta( $property_id,"fave_property_land_postfix",true );
        $property_bedrooms          = get_post_meta( $property_id,"fave_property_bedrooms",true );
        $property_bathrooms         = get_post_meta( $property_id,"fave_property_bathrooms",true );
        $property_garage            = get_post_meta( $property_id,"fave_property_garage",true );
        $additional_features        = get_post_meta( $property_id,"additional_features",true );
        $floor_plans                = get_post_meta( $property_id,"floor_plans",true );
        $multi_units                = get_post_meta( $property_id,"fave_multi_units",true );

        $lead_data = array(
            "data" => array(
                array(
                    "First_Name"            => $first_name,
                    "Last_Name"             => $last_name,
                    "Phone"                 => $mobile,
                    "Email"                 => $email,
                    "Lead_Source"           => "Website",
                    "Lead_Status"           => "Enquiry Received",
                    "Property_Type"         => "Residential",
                    "Property_ID"           => $property_id,
                    "Property_Category"     => $property_category,
                    "Max_Price"             => $property_price,
                    "No_Bedrooms"           => $property_bedrooms,
                    "No_Bathroom"           => $property_bathrooms,
                    "Size_SQM"              => $property_size,
                    "Description"           => $message
                )
            )
        );
        
        $crm_lead = zcrm_create_record( "Leads", $lead_data );

        if( isset( $crm_lead["data"][0]["code"] ) && $crm_lead["data"][0]["code"] == "SUCCESS" ){
            // crm lead id
            $lead_id = $crm_lead["data"][0]["details"]["id"];

            // add notes to lead
            $note_content = "Property ID : ".$property_id."\n";
            $note_content .="Price : ".$property_price." QAR/Per month \n";
            $note_content .="Property Size : ".$property_size." $property_size_prefix \n";
            $note_content .="Bedrooms : ".$property_bedrooms."\n";
            $note_content .="Bathrooms : ".$property_bathrooms."\n";
            $note_content .="Property Type : ".$property_category."\n";
            $note_content .="Property Status : ".$property_status."\n";

            $data = zcrm_create_record_notes( "Leads", $note_content, $lead_id );
        }
    }
}

//add_action("houzez_property_agent_contact","zb_create_property_lead_in_crm",99999999);
add_action("init","zb_create_property_lead_in_crm",99999999);


// handle webhook
function zb_create_crm_property_to_website(){
    if( isset( $_POST["zcrm_property_id"] ) ){

        $args = array(
            'post_type' => 'property',
            'taxonomy'  => 'property_feature'
        );
        
        $features = get_terms( $args );
        $feature_terms = array();

        foreach( $features as $feature ) {
            $feature_terms[$feature->name] = $feature->term_id;
        }

        $property_id = $_POST["zcrm_property_id"];
        //$property_id = "5660331000003631019";
        $property = zcrm_get_record_by_id("Products",$property_id);

        $property_name              = $property["Product_Name"];
        $property_desc              = $property["Description"];
        $property_addr              = $property["Address"];
        $property_code              = $property["Product_Code"];
        $property_furnishing_type   = $property["Property_Furnishing_Type"];
        $property_status            = $property["Property_Status"];
        $rental_period              = $property["Rental_Period"];
        $unit_price                 = $property["Unit_Price"];
        $size_sqm                   = $property["Size_SQM"];
        $bedrooms                   = $property["No_Bedrooms"];
        $bathrooms                  = $property["No_Bathrooms"];

        $listing_category           = $property["Listing_Category"];
        $listing_type               = $property["Listing_Type"];
        $property_category          = $property["Product_Category"];

        $property_amenitiess        = $property["Amenitiess"];
        $property_other_amenitiess  = $property["Other_amenities"];
        $wp_property_id             = $property["WP_Property_ID"];
        
        if( empty( $wp_property_id ) ){
            // first create property
            $property_args = array(
                'post_title'    => $property_name." ".$property_code,
                'post_content'  => $property_desc,
                'post_type'     => 'property'
            );
            
            $wp_property_id = wp_insert_post( $property_args );

            // update property id to crm

            $up_args = array(
                "data" => array(
                    array(
                        "id"             => $property_id,
                        "WP_Property_ID" => (string)$wp_property_id
                    )
                )
            );
            
            zcrm_update_record( "Products", $up_args );
        }

        

        $wp_property_type = array(
            "Apartment" => 16,
            "Office"    => 65,
            "Store"     => 66,
            "Villa"     => 56,
        );

        $property_cats = array();

        if( isset( $wp_property_type[$property_category] ) ){
            $property_cats[] = $wp_property_type[$property_category];
        }
        
        wp_set_post_terms( $wp_property_id, $property_cats, "property_type" );

        // for property status
        $wp_property_status = array(
            "Rent"  => 28,
            "Sale"  => 29
        );

        $property_status = array();

        if( isset( $wp_property_status[$listing_category] ) ){
            $property_status[] = $wp_property_status[$listing_category];
        }

        wp_set_post_terms( $wp_property_id, $property_status, "property_status" );

        // property labels
        $wp_property_labels = array(
            "Residential" => 284,
            "Commercial"  => 283
        );

        $property_labels = array();

        if( isset( $wp_property_labels[$listing_type] ) ){
            $property_labels[] = $wp_property_labels[$listing_type];
        }

        wp_set_post_terms( $wp_property_id, $property_labels, "property_label" );

        // feature update

        $feature_cats = array();

        foreach ( $property_amenitiess as $p_amentiess ) {
            if( isset( $feature_terms[$p_amentiess] ) ){
                $feature_term_id    = $feature_terms[$p_amentiess];
                $feature_cats[]     = $feature_term_id;
            }
        }

        foreach ( $property_other_amenitiess as $p_other_amentiess ) {
            if( isset( $feature_terms[$p_other_amentiess] ) ){
                $feature_term_id    = $feature_terms[$p_other_amentiess];
                $feature_cats[]     = $feature_term_id;
            }
        }

        wp_set_post_terms( $wp_property_id, $feature_cats, "property_feature" );

        // update property meta
        update_post_meta( $wp_property_id,"fave_property_id",$wp_property_id );
        update_post_meta( $wp_property_id,"fave_property_map_address",$property_addr );
        update_post_meta( $wp_property_id,"fave_property_price",$unit_price );
        //update_post_meta( $wp_property_id,"fave_property_sec_price","" );
        //update_post_meta( $wp_property_id,"fave_property_price_prefix","" );
        //update_post_meta( $wp_property_id,"fave_property_price_postfix","" );
        update_post_meta( $wp_property_id,"fave_property_size",$size_sqm );
        update_post_meta( $wp_property_id,"fave_property_size_prefix","m²" );
        //update_post_meta( $wp_property_id,"fave_property_land","" );
        update_post_meta( $wp_property_id,"fave_property_land_postfix","m²" );
        update_post_meta( $wp_property_id,"fave_property_bedrooms",$bedrooms );
        //update_post_meta( $wp_property_id,"fave_property_rooms",$property_id );
        update_post_meta( $wp_property_id,"fave_property_bathrooms",$bathrooms );
        //update_post_meta( $wp_property_id,"fave_property_garage","" );
        //update_post_meta( $wp_property_id,"fave_property_garage_size","" );
        //update_post_meta( $wp_property_id,"fave_property_year","" );

        // attach property images
        $attachments = zcrm_get_attachments("Products",$property_id);

        // delete post meta
        delete_post_meta($wp_property_id,"_thumbnail_id");
        delete_post_meta($wp_property_id,"fave_property_images");

        foreach( $attachments as $key=>$att ) {
            $attach_file_name   = $property_id."-".$att["File_Name"];
            $attachment_id      = $att["id"];
            $attachment_data    = zcrm_download_attachment("Products",$property_id,$attachment_id);
            $file_url           = site_url().'/wp-content/uploads/zcrm-property-images/'.$attach_file_name;
            $attched_file       = fopen('wp-content/uploads/zcrm-property-images/'.$attach_file_name, "w");
            fwrite( $attched_file, $attachment_data );
            fclose( $attched_file );
            $wp_attachment_id = zb_upload_file_from_url($file_url);
            if( $key == 0 ){
                // set thumbnail id
                update_post_meta( $wp_property_id,"_thumbnail_id",$wp_attachment_id );
            }

            // add images
            add_post_meta( $wp_property_id,"fave_property_images",$wp_attachment_id );
        }
        echo "Success";
        die;
    }
}

add_action( "init","zb_create_crm_property_to_website",9999 );

function zb_upload_file_from_url( $image_url ) {

	// it allows us to use download_url() and wp_handle_sideload() functions
	require_once( ABSPATH . 'wp-admin/includes/file.php' );

	// download to temp dir
	$temp_file = download_url( $image_url );

	if( is_wp_error( $temp_file ) ) {
		return false;
	}

	// move the temp file into the uploads directory
	$file = array(
		'name'     => basename( $image_url ),
		'type'     => mime_content_type( $temp_file ),
		'tmp_name' => $temp_file,
		'size'     => filesize( $temp_file ),
	);
	$sideload = wp_handle_sideload(
		$file,
		array(
			'test_form'   => false // no needs to check 'action' parameter
		)
	);

	if( ! empty( $sideload[ 'error' ] ) ) {
		// you may return error message if you want
		return false;
	}

	// it is time to add our uploaded image into WordPress media library
	$attachment_id = wp_insert_attachment(
		array(
			'guid'           => $sideload[ 'url' ],
			'post_mime_type' => $sideload[ 'type' ],
			'post_title'     => basename( $sideload[ 'file' ] ),
			'post_content'   => '',
			'post_status'    => 'inherit',
		),
		$sideload[ 'file' ]
	);

	if( is_wp_error( $attachment_id ) || ! $attachment_id ) {
		return false;
	}

	// update medatata, regenerate image sizes
	require_once( ABSPATH . 'wp-admin/includes/image.php' );

	wp_update_attachment_metadata(
		$attachment_id,
		wp_generate_attachment_metadata( $attachment_id, $sideload[ 'file' ] )
	);

	return $attachment_id;
}
?>