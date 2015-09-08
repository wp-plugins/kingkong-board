<?php
  if(is_numeric($cid)){
?>
<form method="post">
<div class="comment-checker-wrapper">
  <div class="comment-checker">
    <label><span>비밀번호를 입력하세요.</span></label>
    <div class="comment-checker-input">
      <input type="password" name="inputpwd">

<?php
  if(!is_user_logged_in() && $mod == 'delete'){
?>
  <button type="button" class="kkb-comment-delete <?php echo kkb_button_classer($this->board_id);?>" data-cid="<?php echo $cid;?>">입력</button>
<?php
  } else {
?>
  <button class="<?php echo kkb_button_classer($this->board_id);?>">입력</button>
<?php
  }
?>

      
    </div>
  </div>
</div>
</form>
<?php
  } else {
    _e('존재하지 않는 댓글 입니다.', 'kingkongboard');
  }
?>