<?php

add_shortcode("kingkong_board_comment","kingkong_board_comment");
function kingkong_board_comment($attr){

  $entry_id           = $attr['id'];
  $board_id           = get_board_id_by_entry_id($entry_id);
  $board_skin         = get_post_meta($board_id, 'board_skin', true);  
  $skin_path          = get_kingkongboard_skin_path('board', $board_skin);

  ob_start();
    require_once KINGKONGBOARD_ABSPATH.'includes/view.read.comment.php';
    $cmContent = ob_get_contents();
  ob_end_clean();
  return apply_filters('kkb_comment_list_after', $cmContent, $attr);
}

?>