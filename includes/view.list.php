<?php
  $board_id       = $this->board_id;
  do_action('kkb_loop_before', $board_id);
  $entries        = apply_filters('kingkong_board_entry_columns', 'kingkong_board_entry_columns', $entry = null, $board_id); 

  ob_start();
  require_once KINGKONGBOARD_ABSPATH.'/includes/view.list.search.php';
  $searchContent = ob_get_contents();
  $searchContent = apply_filters('kkb_list_search_content', $searchContent, $board_id);
  ob_end_clean();

  ob_start();
  require_once KINGKONGBOARD_ABSPATH.'/includes/view.list.section.php';
  $sectionContent = ob_get_contents();
  $sectionContent = apply_filters('kkb_list_top_sections', $sectionContent, $board_id);
  ob_end_clean();

  ob_start();
  require_once KINGKONGBOARD_ABSPATH.'/includes/view.list.loop.php';
  $loopContent = ob_get_contents();
  $loopContent = apply_filters('kkb_list_loop', $loopContent, $board_id);
  ob_end_clean();

  ob_start();
  require_once KINGKONGBOARD_ABSPATH.'includes/view.list.pagenation.php';
  $pageContent = ob_get_contents();
  $pageContent = apply_filters('kkb_list_pagenation', $pageContent, $board_id);
  ob_end_clean();

  $loopContent     = apply_filters('kkb_loop_after', $loopContent, $board_id, $nResults, $bResults);
  $controllerClass = apply_filters('kkb_loop_controller_extra_class', null, $board_id);

  $controllerContent = '<div class="kingkongboard-controller '.$controllerClass.'">';
  if(parent::actionPermission($board_id, null, 'write') == true){
    $write_args = apply_filters('kkb_read_arg_after', array('view' => 'write'), $board_id);
    $write_path = add_query_arg($write_args, get_the_permalink($post->ID));
    $controllerContent .= $searchContent;
    $controllerContent .= '<a href="'.$write_path.'" class="'.kkb_button_classer($board_id).' write-button"><span class="kkb-list-icon kkblc-write" style="margin-right:5px"></span><span style="vertical-align:middle">'.kkb_button_text($board_id, 'write').'</span></a>';
  }
  $controllerContent .= '</div>';
  $copyContent = '<div class="kingkongboard-copyrights"><a href="http://superrocket.io" target="_blank">Powered by Kingkong Board</a></div>';
  $display = $sectionContent.$loopContent.$controllerContent.$pageContent.$copyContent;
  $kkbContent = apply_filters('kkb_loop_display', $display, $searchContent, $loopContent, $pageContent, $controllerContent, $copyContent, $board_id);
  echo $kkbContent;
?>