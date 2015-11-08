<div>
  <label><?php _e('삭제 하시려면 아래에 게시글 비밀번호를 입력하시기 바랍니다.', 'kingkongboard');?></label>
</div>
<div class="kingkongboard-read-verifying-wrapper">
  <table class="kingkongboard-read-verifying-table">
    <tr>
      <td style="width:300px">
        <input type="password" name="validate_pwd" style="width:100%">
      </td>
      <td style="padding-left:5px">
        <button  class="<?php echo kkb_button_classer($this->board_id);?> kkb-delete-validation" data-id="<?php echo $this->entry_id;?>" data-board="<?php echo $this->board_id;?>" style="height:26px;"><?php _e('확인', 'kingkongboard');?></button>
      </td>
    </tr>
  </table>
</div>