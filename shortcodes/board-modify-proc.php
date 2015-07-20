<?php

  $board_id = sanitize_text_field($_POST['board_id']);
  $post_id  = sanitize_text_field($_POST['post_id']);
  //$Board    = new KKB_Controller($board_id);
  //$entry_id = $Board->kkb_entry_modify($_POST, "front-end");

    $Board      = new kkbController();
    $entry_id   = $Board->writeModify($_POST);

  if($entry_id && is_numeric($entry_id)){
    wp_reset_postdata();
    $upload = $Board->fileUploader($entry_id, $_POST, $_FILES);
    $kkbContent .= "<script>parent.location.href='".get_the_permalink($post_id)."';</script>";
  } else {
    (is_array($entry_id)) ? $message = $entry_id['message'] : $message = $entry_id;
    $kkbContent .= "<script>alert('".$message."');</script>";    
  }

?>