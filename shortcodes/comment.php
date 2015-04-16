<?php

add_shortcode("kingkong_board_comment","kingkong_board_comment");
function kingkong_board_comment($attr){

  $entry_id           = $attr['id'];
  $board_id           = get_board_id_by_entry_id($entry_id);
  $board_skin         = get_post_meta($board_id, 'board_skin', true);  
  $skin_path          = get_kingkongboard_skin_path('board', $board_skin);

  if(is_user_logged_in()){
    $user_status = 1;
  } else {
    $user_status = 0;
  }

  $args = array('post_id' => $entry_id, 'status'=>'approve', 'count' => true);
  $comments = new KKB_Comments();
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

  $cmContent = "";
  $cmContent .= '<div class="comments-count">'.__('덧글', 'kingkongboard').' <span class="total-comments-count">'.$comment_count.'</span>'.__('개', 'kingkongboard').'</div>';
  $cmContent .= '<div class="comments-sorting">'.$sorting_content.'</div>';
  $cmContent .= '<div class="kingkongboard-comment-wrapper" style="background:'.$comment_options['background']['color'].'; border:'.$comment_options['background']['border'].' solid '.$comment_options['background']['border_color'].';">';
  $cmContent .= '<div class="comments-list">'.$comment_list.'</div>';
  $cmContent .= '<div class="comments-add">';
  if($user_status == 0){
    $cmContent .= '<ul class="comments-add-ul">';
    $cmContent .= '<li class="comments-add-writer" style="margin-right:10px">'.__('작성자', 'kingkongboard').'</li>';
    $cmContent .= '<li><input class="kkb-comment-input kkb-comment-writer" type="text" name="kkb_comment_writer"></li>';
    $cmContent .= '<li class="comments-add-email" style="margin:0 10px">'.__('이메일', 'kingkongboard').'</li>';
    $cmContent .= '<li><input class="kkb-comment-input kkb-comment-email" type="text" name="kkb_comment_email"></li>';
    $cmContent .= '</ul>';
  }
  $cmContent .= '<ul class="kkb-comment-input-ul">';
  $cmContent .= '<li class="kkb-comment-content-li"><table class="kkb-comment-content-table"><tr><td class="kkb-comment-content-td"><textarea class="kkb-comment-input kkb-comment-content" height="30px" name="kkb_comment_content"></textarea></td><td class="kkb-comment-button-td"><input type="image" src="'.KINGKONGBOARD_PLUGINS_URL.'/assets/images/button-ok.png" class="button-comment-save" style="border:0;margin-left:6px"></td></tr></table></li>';
  $cmContent .= '</ul>';
  $cmContent .= '</div>';
  $cmContent .= '<input type="hidden" name="user_status" value="'.$user_status.'">';
  $cmContent .= '<input type="hidden" name="entry_id" value="'.$entry_id.'">';
  $cmContent .= '</div>';

  return $cmContent;
}

?>