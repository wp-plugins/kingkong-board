<?php
  $comment = get_comment($cid);
?>
<div class="kkb-comment-modify-wrapper">
  <form method="post" action="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/includes/view.read.comment.save.modify.php">
    <textarea name="kkb_comment_modify_textarea"><?php echo $comment->comment_content;?></textarea>
    <button class="<?php echo kkb_button_classer($this->board_id);?>">수정완료</button>
    <input type="hidden" name="entry_id" value="<?php echo $this->entry_id;?>">
    <input type="hidden" name="cid" value="<?php echo $cid;?>">
  </form>
</div>