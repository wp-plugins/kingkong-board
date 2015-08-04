<?php

  if(is_user_logged_in()){
    $user_status = 1;
  } else {
    $user_status = 0;
  }

  $args = array('post_id' => $entry_id, 'status'=>'approve', 'count' => true);
  $comments = new kkbComment();
  $comment_count   = $comments->kkb_get_comment_count($entry_id);
  $comments_array  = $comments->kkb_get_comment_list($entry_id);
  $comment_list    = $comments->kkb_comment_display($entry_id, $comments_array);

  
  $comment_options = get_post_meta($board_id, 'kingkongboard_comment_options', true);

  if($comment_options){
    $comment_options = maybe_unserialize($comment_options);
  }
  $comment_sorting = apply_filters('kingkongboard_comment_sorting', $entry_id);
  if($comment_sorting != $entry_id){
    $sorting_content = $comment_sorting;
  } else {
    $sorting_content = null;
  }

$comment_class = apply_filters('kkb_comment_wrapper_class', 'kingkongboard-comment-wrapper', $board_id, $entry_id);
$user          = wp_get_current_user();
$user_avatar   = get_avatar($user->ID, 50);
preg_match("/src='(.*?)'/i", $user_avatar, $matches);
$user_avatar = $matches[1];

(is_user_logged_in()) ? $readOnly = 'readonly' : $readOnly = null;
(is_user_logged_in()) ? $userStatus = 1 : $userStatus = 0;
?>
<div class="comment-section">
  <form method="post" action="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/includes/view.read.comment.save.php" onsubmit="return kkb_comment_submit();">
    <div class="comment-editor">
      <div class="comment-editor-top">
        <span class="kkb-list-icon kkblc-people"></span>
        <span style="width:auto"><strong><?php _e('댓글 쓰기', 'kingkongboard');?></strong></span>
      </div>
      <div class="comment-editor-content">
        <span class="comment-editor-avatar">
          <img src="<?php echo $user_avatar;?>">
        </span>
        <div class="comment-editor-text">
          <textarea name="kkb_comment_content" class="comment-editor-textarea"></textarea>
        </div>
        <button type="submit" class="button-comment-write <?php echo kkb_button_classer($board_id);?>">등록</button>
      </div>
<?php
  if(!is_user_logged_in()){
?>
      <div class="comment-editor-content-input">
        <input type="text" name="writer" placeholder="<?php _e('글쓴이', 'kingkongboard');?>" <?php echo $readOnly;?>>
        <input type="password" name="password" placeholder="<?php _e('비밀번호', 'kingkongboard');?>" <?php echo $readOnly;?>>
        <input type="text" name="email" placeholder="<?php _e('이메일', 'kingkongboard');?>" <?php echo $readOnly;?>>
      </div>
<?php
  }
?>
    </div>
    <input type="hidden" name="user_status" value="<?php echo $userStatus;?>">
    <input type="hidden" name="entry_id" value="<?php echo $entry_id;?>">
  </form>
  <div class="kkb-comment-list">
    <div class="list-header">
      <span><?php _e('댓글', 'kingkongboard');?> <strong><?php echo $comment_count;?></strong></span>
      <span style="float:right"><?php echo $sorting_content;?></span>
    </div>
    <div class="list-wrapper">
      <?php echo $comment_list;?>
    </div>
  </div>
</div>