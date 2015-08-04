<?php
  if(isset($_POST['validate_pwd'])){
    _e('비밀번호가 일치하지 않습니다.' ,'kingkongboard');
  } else {
?>
<div>
  <label><?php _e('아래에 비밀번호를 입력하시기 바랍니다.', 'kingkongboard');?></label>
</div>
<form method="post" id="kingkongboard_verify_form">
  <div class="kingkongboard-read-verifying-wrapper">
    <table class="kingkongboard-read-verifying-table">
      <tr>
        <td style="width:300px">
          <input type="password" name="validate_pwd" style="width:100%">
        </td>
        <td style="padding-left:5px">
          <button  class="<?php echo kkb_button_classer($this->board_id);?>" style="height:26px;"><?php _e('확인', 'kingkongboard');?></button>
        </td>
      </tr>
    </table>
  </div>
</form>
<?php
  }
?>