<?php

(is_user_logged_in()) ? $user_status = 1 : $user_status = 0;
if($comments){
  foreach($comments as $cmmt){
    $depth   = $cmmt->depth;
    if($depth > 1){
      $depth_padding = "margin-left:".$this->kkb_comment_depth_padding($depth)."px";
      $depth_div     = '<div style="float:left"><img src="'.KINGKONGBOARD_PLUGINS_URL.'/assets/images/icon-comment-reply.png" style="height:16px; width:auto"></div>';
    } else {
      $depth_padding = null;
      $depth_div     = null;
    }
    $comment = get_comment($cmmt->cid);
    if($comment){ 

      echo apply_filters('kingkongboard_comment_list_before', null, $board_id, $comment);
      if($comment->comment_approved == 1){
        $user_avatar   = get_avatar($comment->comment_author_email, 50);
        preg_match("/src='(.*?)'/i", $user_avatar, $matches);
        $user_avatar = $matches[1];
?>
<div id="comment-<?php echo $comment->comment_ID;?>" class="each-comment comment-<?php echo $comment->comment_ID;?>" style="<?php echo $depth_padding;?>">
<?php
  if($depth > 1){
?>
  <div class="comment-reply-icon-wrapper">
    <span class="kkb-list-icon kkblc-comment-reply02"></span>
  </div>
<?php
  }
?>
  <div class="comment-box">
    <span class="comment-avatar">
      <img src="<?php echo $user_avatar;?>" alt="<?php echo $comment->comment_author;?>">
    </span>
    <div class="comment-content">
      <div class="comment-content-writer">
        <h2 class="kkb-read-h2"><span class="author"><?php echo $comment->comment_author;?></span></h2>
        <h2 class="kkb-read-h2"><span class="date"><?php echo get_comment_date( 'Y.m.d H:i', $comment->comment_ID);?></span></h2>
      </div>
<?php
  echo apply_filters('kingkongboard_comment_content_before', null, $comment->comment_ID); 
?>
      <div class="comment-content-text">
        <?php
          echo apply_filters('kkb_comment_content_inner_before', null, $board_id, $entry_id, $comment->comment_ID);
          echo "<h2 class='kkb-read-h2'>".nl2br($comment->comment_content)."</h2>";
          echo apply_filters('kkb_comment_content_inner_after', null, $board_id, $entry_id, $comment->comment_ID);
        ?>
      </div>
    </div>
<?php
  $comment_after = apply_filters('kingkongboard_comment_after', $board_id, $entry_id, $comment->comment_ID );
  if($comment_after != $board_id) echo $comment_after;
?>
  </div>
  <div class="comment-controller">
<?php

  $controller = new kkbController();
  $controllers = null;
  if($controller->actionCommentPermission($board_id, $comment->comment_ID, 'modify') == true){
    $modify_args = apply_filters('kkb_read_arg_after', array('view' => 'cmtcheck', 'cid' => $comment->comment_ID, 'id' => $entry_id, 'mod' => 'modify'), $board_id);
    $controllers['modify'] = array(
      'label'  => __('수정', 'kingkongboard'),
      'class'  => 'kkblc-comment-modify',
      'aclass' => null,
      'ahref'  => add_query_arg( $modify_args, get_the_permalink()),
      'data'   => null
    );
  }

  if($controller->actionCommentPermission($board_id, $comment->comment_ID, 'delete') == true){
    $controllers['delete'] = array(
      'label'  => __('삭제', 'kingkongboard'),
      'class'  => 'kkblc-comment-delete',
      'aclass' => 'kkb-check-comment-delete',
      'ahref'  => null,
      'data'   => $comment->comment_ID
    );
  }

  if($controller->actionCommentPermission($board_id, $comment->comment_ID, 'write') == true){
    $controllers['write'] = array(
      'label'  => __('댓글', 'kingkongboard'),
      'class'  => 'kkblc-comment-reply',
      'aclass' => 'btn-kkb-comment-reply',
      'ahref'  => null,
      'data'   => null
    );
  }

  if(isset($controllers)){
    foreach($controllers as $controller){
      ($controller['ahref'] != null)  ? $ahref  = 'href="'.$controller['ahref'].'"'    : $ahref   = null;
      ($controller['aclass'] != null) ? $aclass = 'class="'.$controller['aclass'].'"' : $aclass   = null;
      ($controller['data'] != null)   ? $data   = 'data-id="'.$controller['data'].'"'   : $data   = null;
?>
  <span><a <?php echo $ahref;?> <?php echo $aclass;?> <?php echo $data;?>><span class="kkb-list-icon <?php echo $controller['class'];?>"></span><span><?php echo $controller['label'];?></span></a></span>
<?php
    }
  }
?>
  </div>
  <div class="comment-reply comment-reply-<?php echo $comment->comment_ID;?>">
    <div class="comment-reply-header">
      <span><span class="kkb-list-icon kkblc-comment-reply02"></span> <strong>댓글 쓰기</strong></span>
      <span style="float:right"><a class="btn-kkb-comment-reply-close"><span class="kkb-list-icon kkblc-comment-close"></span><span>닫기</span></a></span>
    </div>
    <form method="POST" action="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/includes/view.read.comment.save.php" onsubmit="return kkb_comment_reply_submit(<?php echo $comment->comment_ID;?>);">
      <textarea name="kkb_comment_content"></textarea>
      <div class="comment-reply-input">
<?php
  if(!is_user_logged_in()){
?>
        <input type="text" name="writer" placeholder="글쓴이">
        <input type="password" name="password" placeholder="비밀번호">
        <input type="text" name="email" placeholder="이메일">
<?php
  }
?>
        <button class="<?php echo kkb_button_classer($board_id);?>">등록</button>
      </div>
      <input type="hidden" name="user_status" value="<?php echo $user_status;?>">
      <input type="hidden" name="comment_parent" value="<?php echo $comment->comment_ID;?>">
      <input type="hidden" name="entry_id" value="<?php echo $entry_id;?>">
    </form>
  </div>
</div>
<?php
      }
    }
  }
}
?>