<?php

add_shortcode("kingkong_board_comment","kingkong_board_comment");
function kingkong_board_comment($attr){

  if(is_user_logged_in()){
    $user_status = 1;
  } else {
    $user_status = 0;
  }

  $entry_id = $attr['id'];

  $args = array('post_id' => $entry_id, 'status'=>'approve', 'count' => true);
  $comments = get_comments($args);
  $board_id = get_board_id_by_entry_id($entry_id);
  $comment_options = get_post_meta($board_id, 'kingkongboard_comment_options', true);

/*
    $origin_options = array(
      'thumbnail' => 'T',
      'background' => array(
        'color'         => '#f9f9f9',
        'border'        => '1px',
        'border_color'  => '#f1f1f1'
      ),
      'writer'     => array(
        'color'       => '#424242',
        'font_weight' => 'bold',
        'font_size'   => '12px'
      ),
      'date'       => array(
        'format'      => 'Y/m/d H:i',
        'color'       => '#666666',
        'font_size'   => '11px'
      ),
      'content'    => array(
        'color'       => '#424242'
      )
    );
*/


  if($comment_options){
    $comment_options = unserialize($comment_options);
  }

  $cmContent = "";
  $cmContent .= '<div class="comments-count">'.__('덧글', 'kingkongboard').' <span class="total-comments-count">'.$comments.'</span>'.__('개', 'kingkongboard').'</div>';
  $cmContent .= '<div class="kingkongboard-comment-wrapper" style="background:'.$comment_options['background']['color'].'; border:'.$comment_options['background']['border'].' solid '.$comment_options['background']['border_color'].';">';
  $cmContent .= '<div class="comments-list">loading...</div>';
  $cmContent .= '<div class="comments-add">';
  if($user_status == 0){
    $cmContent .= '<ul class="comments-add-ul">';
    $cmContent .= '<li class="comments-add-writer">'.__('작성자', 'kingkongboard').'</li>';
    $cmContent .= '<li><input class="kkb-comment-input kkb-comment-writer" type="text" name="kkb_comment_writer"></li>';
    $cmContent .= '<li class="comments-add-email">'.__('이메일', 'kingkongboard').'</li>';
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