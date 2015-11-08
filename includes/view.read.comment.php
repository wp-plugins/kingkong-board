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
if(isset($matches[1])){
  $user_avatar = $matches[1];
}

if($user->user_login == null){
  $user_alt = __("비회원 프로필 이미지", "kingkongboard");
} else {
  $user_alt = $user->user_login;
}

(is_user_logged_in()) ? $readOnly = 'readonly' : $readOnly = null;
(is_user_logged_in()) ? $userStatus = 1 : $userStatus = 0;
?>
<div class="comment-section">
  <form method="post" enctype="multipart/form-data" action="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/includes/view.read.comment.save.php" onsubmit="return kkb_comment_submit();">
<?php
  $controller = new kkbController();
  if($controller->actionCommentPermission($board_id, null, 'write') == true){
?>    
    <div class="comment-editor">
      <div class="comment-editor-top">
        <span class="kkb-list-icon kkblc-people"></span>
        <span style="width:auto"><h2 class="kkb-read-h2"><strong><?php _e('댓글 쓰기', 'kingkongboard');?></strong></h2></span>
      </div>
      <div class="comment-editor-content">
        <span class="comment-editor-avatar">
<?php
  if(isset($matches[1])){
?>
          <img src="<?php echo $user_avatar;?>" alt="<?php echo $user_alt;?>">
<?php
  } else {
    echo $user_avatar;
  }
?>
        </span>
        <div class="comment-editor-text">
          <textarea name="kkb_comment_content" class="comment-editor-textarea" title="<?php _e('댓글 내용 입력', 'kingkongboard');?>"></textarea>
        </div>
        <button type="submit" class="button-comment-write <?php echo kkb_button_classer($board_id);?>">등록</button>
      </div>
      <?php
        $config     = new kkbConfig();
        $config     = $config->getBoard($board_id); 
        if($config->cmtcaptcha == "T" && $config->captchaKey['site_key'] != null && $config->captchaKey['secret_key'] != null){  
      ?>  
          <div class="write-comment-box write-comment-captcha" style="height:auto">
            <label for="g-recaptcha" class="captcha-title-th"><span><?php _e('자동 글 방지', 'kingkongboard');?></span></label>
            <span class="write-span"><div id="g-recaptcha" class="g-recaptcha" data-sitekey="<?php echo $config->captchaKey['site_key'];?>"></div></span>
          </div>
      <?php
        }
      ?> 
<?php
  if(!is_user_logged_in()){
?>
      <div class="comment-editor-content-input">
        <input type="text" name="writer" title="<?php _e('글쓴이', 'kingkongboard');?>" placeholder="<?php _e('글쓴이', 'kingkongboard');?>" <?php echo $readOnly;?>>
        <input type="password" name="password" title="<?php _e('비밀번호', 'kingkongboard');?>" placeholder="<?php _e('비밀번호', 'kingkongboard');?>" <?php echo $readOnly;?>>
        <input type="text" name="email" title="<?php _e('이메일', 'kingkongboard');?>" placeholder="<?php _e('이메일', 'kingkongboard');?>" <?php echo $readOnly;?>>        
      </div>
<?php
  }
?>
    </div>
<?php
  }
?>
    <input type="hidden" name="user_status" value="<?php echo $userStatus;?>">
    <input type="hidden" name="entry_id" value="<?php echo $entry_id;?>">
  </form>
  <div class="kkb-comment-list">
    <div class="list-header">
      <span><?php _e('댓글', 'kingkongboard');?> <strong><?php echo $comment_count;?></strong></span>
      <span style="float:right"><?php echo $sorting_content;?></span>
    </div>
<?php
  if($controller->actionCommentPermission($board_id, null, 'read') == true){
?>
    <div class="list-wrapper">
      <?php echo $comment_list;?>
    </div>
<?php
  } else {
?>
      <span class="comment-read-denied"><?php _e('댓글 읽기 권한이 없습니다.', 'kingkongboard');?></span>
<?php
  }
?>
  </div>
</div>