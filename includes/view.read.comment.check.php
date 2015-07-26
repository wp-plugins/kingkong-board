<?php
  $validation = false;
  if(isset($_POST['inputpwd'])){
    $inputpwd = sanitize_text_field($_POST['inputpwd']);
    if($mod == 'modify' && !empty($cid)){
      $pwd = get_comment_meta($cid, 'kkb_comment_password', true);
      if($pwd == md5($inputpwd)){
        $validation = true;
      } else {
        echo "<script>alert('".__('비밀번호가 일치하지 않습니다.', 'kingkongboard')."');</script>";
      }
    }
  }

  if(is_user_logged_in() || $validation == true){
    switch($mod){
      case 'modify' :
      //require_once(KINGKONGBOARD_ABSPATH."includes/view.read.comment.check.form.php");
      require_once(KINGKONGBOARD_ABSPATH."includes/view.read.comment.modify.php");
      break;
    }
  } else {
    require_once(KINGKONGBOARD_ABSPATH."includes/view.read.comment.check.form.php");
  }
?>