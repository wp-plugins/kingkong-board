<?php
  do_action('kkb_loop_before', $board_id);
  $entries = apply_filters('kingkong_board_entry_columns', 'kingkong_board_entry_columns', $entry = null, $board_id); 
  $board_sections = get_post_meta($board_id, 'board_sections', true);

  $section_value  = null;

  if(isset($_POST['kkb_keyword'])){
    $search_keyword = sanitize_text_field($_POST['kkb_keyword']);
  } else {
    $search_keyword = null;
  }

  if(isset($_POST['kkb_section'])){
    $search_section = sanitize_text_field($_POST['kkb_section']);  
  } else {
    $search_section = null;
  }

  if(isset($_GET['kkb_keyword']) && $search_keyword == null){
    $search_keyword = sanitize_text_field($_GET['kkb_keyword']);
  }

  if(isset($_GET['kkb_section']) && $search_section == null){
    $search_section = sanitize_text_field($_GET['kkb_section']);
  }

  if($board_sections){
    $board_sections = maybe_unserialize($board_sections);
    $section_value .= '<option value="all">'.__('전체', 'kingkongboard').'</option>';
    foreach($board_sections as $section){
      if($search_section == $section){
        $section_value .= '<option value="'.$section.'" selected>'.$section.'</option>';
      } else {
        $section_value .= '<option value="'.$section.'">'.$section.'</option>';
      }
    }
  }

  $loopContent = null;

  ob_start();
  require_once KINGKONGBOARD_ABSPATH.'/shortcodes/board-loop-search.php';
  $searchContent .= ob_get_contents();
  ob_end_clean();

  ob_start();
  require_once KINGKONGBOARD_ABSPATH.'/shortcodes/board-loop-content.php';
  $loopContent .= ob_get_contents();
  ob_end_clean();

  $loopContent     = apply_filters('kkb_loop_after', $loopContent, $board_id, $nResults, $bResults);
  $pageNavClass    = apply_filters('kkb_loop_pagination_class', 'kingkong-board-entry-nav', $board_id);
  $pageContent     = '<nav class="'.$pageNavClass.'">'; 
  $activate_page   = 'class="activated"';
  $pageContent    .= $pagenation;
  $pageContent    .= '</nav>';

  $controllerClass = apply_filters('kkb_loop_controller_extra_class', null, $board_id);

  $controllerContent = '<div class="kingkongboard-controller '.$controllerClass.'">';
  if($permission_write_status == "checked"){
    $write_path = add_query_arg('view', 'write', get_the_permalink($post->ID));
    $controllerContent .= '<a href="'.$write_path.'" class="'.kkb_button_classer($board_id).'">'.kkb_button_text($board_id, 'write').'</a>';
  }
  $controllerContent .= '</div>';
  $copyContent = '<div class="kingkongboard-copyrights"><a href="http://superrocket.io" target="_blank">Powered by Kingkong Board</a></div>';
  $display = $searchContent.$loopContent.$pageContent.$controllerContent.$copyContent;
  $kkbContent .= apply_filters('kkb_loop_display', $display, $searchContent, $loopContent, $pageContent, $controllerContent, $copyContent, $board_id);

?>