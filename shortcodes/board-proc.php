<?php
  $board_id     = sanitize_text_field($_POST['board_id']);
  $post_id      = sanitize_text_field($_POST['post_id']);
  if(isset($_POST['g-recaptcha-response'])){
    $cpt_response = sanitize_text_field($_POST['g-recaptcha-response']);
    $response     = kingkongboard_captcha_initialize($board_id, $cpt_response);
  } else {
    $response = true;
  }
  if($response == false){
    //echo "<script>console.log('".$cpt_response."');</script>";
  } else {
    //$Board      = new KKB_Controller($board_id);
    //$entry_id   = $Board->kkb_entry_write($_POST, "front-end");
    $Board      = new kkbController();
    $entry_id   = $Board->writeEntry($board_id, $_POST, 'basic');

    if($entry_id && is_numeric($entry_id)){
      $upload   = $Board->fileUploader($entry_id, $_POST, $_FILES);
      if($upload){
        if(is_array($upload)) {
          $message = $upload['message'];
          $kkbContent .= "<script>alert('".$message."');</script>";
        } else {
          $kkbContent .= "<script>parent.location.href='".get_the_permalink($post_id)."';</script>";
        }
      }
    } else {
      (is_array($entry_id)) ? $message = $entry_id['message'] : $message = $entry_id;
      $kkbContent .= "<script>alert('".$message."');</script>";
    }
  }

?> 