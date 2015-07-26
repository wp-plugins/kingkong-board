<?php
  if(isset($_POST['validate_pwd'])){
    $ipwd = $_POST['validate_pwd'];
    if(parent::checkPassword($this->entry_id, $ipwd) == true){
      ob_start();
      require_once(KINGKONGBOARD_ABSPATH."includes/view.read.php");
      $content  = ob_get_contents();
      ob_end_clean();
    } else {
      ob_start();
      require_once(KINGKONGBOARD_ABSPATH."includes/view.read.validation.php");
      $content  = ob_get_contents();
      ob_end_clean();
    }
  } else {
    ob_start();
    require_once(KINGKONGBOARD_ABSPATH."includes/view.read.validation.php");
    $content  = ob_get_contents();
    ob_end_clean();
  }
  echo $content;
?>

