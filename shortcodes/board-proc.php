<?php
  $board_id     = sanitize_text_field($_POST['board_id']);
  $post_id      = sanitize_text_field($_POST['post_id']);
  $cpt_response = sanitize_text_field($_POST['g-recaptcha-response']);
  $response     = kingkongboard_captcha_initialize($board_id, $cpt_response);

  if($response == false){
    echo "<script>console.log('".$cpt_response."');</script>";
  } else {

    $Board    = new KingkongBoard_Entries($board_id);
    $entry_id = $Board->Kingkong_Board_Insert_Entry($_POST);

    do_action('kingkongboard_entry_save_after', $board_id, $entry_id);

    if($entry_id){

      if( ! function_exists( 'wp_handle_upload' ) ){
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
      }
      require_once( ABSPATH . 'wp-admin/includes/image.php' );

      if($_FILES['thumbnail_file']){
        do_action('kingkongboard_thumbnail_save_before', $board_id );
        $thumbnail = kingkongboard_reArrayFiles($_FILES['thumbnail_file']);
        for ($i=0; $i < count($thumbnail); $i++) { 
          if($thumbnail[$i]['name']){
            $uploadedfile = $thumbnail[$i];
            $upload_overrides = array( 'test_form' => false );
            $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
            if( $movefile ){
              $filename       = $movefile['file'];
              $parent_post_id = $entry_id;
              $filetype       = wp_check_filetype( basename($filename), null );
              $wp_upload_dir  = wp_upload_dir();
              $attachment     = array(
                'guid'            => $wp_upload_dir['url'] . '/' . basename( $filename ),
                'post_mime_type'  => $filetype['type'],
                'post_title'      => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                'post_content'   => '',
                'post_status'    => 'inherit'
              );

              $thumbnail_id      = wp_insert_attachment( $attachment, $filename, $parent_post_id );

              // Generate the metadata for the attachment, and update the database record.
              $attach_data = wp_generate_attachment_metadata( $thumbnail_id, $filename );
              wp_update_attachment_metadata( $thumbnail_id, $attach_data );
              set_post_thumbnail( $entry_id, $thumbnail_id);
            }
          }
        }
      }

      if($_FILES['entry_file']){

        add_filter('intermediate_image_sizes_advanced', 'kingkong_file_upload_crop_remove');

        $Files = kingkongboard_reArrayFiles($_FILES['entry_file']);
        $attached = array();

        for ($i=0; $i < count($Files); $i++) { 
          if($Files[$i]['name']){
            $uploadedfile = $Files[$i];
            $upload_overrides = array( 'test_form' => false );
            $movefile = wp_handle_upload( $uploadedfile, $upload_overrides );
            if( $movefile ){
              $filename       = $movefile['file'];
              $parent_post_id = $entry_id;
              $filetype       = wp_check_filetype( basename($filename), null );
              $wp_upload_dir  = wp_upload_dir();
              $attachment     = array(
                'guid'            => $wp_upload_dir['url'] . '/' . basename( $filename ),
                'post_mime_type'  => $filetype['type'],
                'post_title'      => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
                'post_content'   => '',
                'post_status'    => 'inherit'
              );

              $attach_id      = wp_insert_attachment( $attachment, $filename, $parent_post_id );

              // Generate the metadata for the attachment, and update the database record.
              $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
              wp_update_attachment_metadata( $attach_id, $attach_data );

              if(!is_wp_error($attach_id) && $attach_id != ""){
                $attached[]     = $attach_id;
              }
            }
          }
        }

        if($attach_id != ""){
          update_post_meta($entry_id, 'kingkongboard_attached', serialize($attached) );
        } else {
          delete_post_meta($entry_id, 'kingkongboard_attached');
        }
      } else {
        delete_post_meta($entry_id, 'kingkongboard_attached');
      }

      $kkbContent .= "<script>parent.location.href='".get_the_permalink($post_id)."';</script>";
      
    }
  }

  function kingkong_file_upload_crop_remove( $sizes ){
    unset( $sizes['medium']);
    unset( $sizes['large']);
    return $sizes;
  }

?>