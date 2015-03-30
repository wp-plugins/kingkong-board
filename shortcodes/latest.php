<?php

add_shortcode("kingkong_board_latest","kingkong_board_latest");
function kingkong_board_latest($attr){
  $title           = $attr['title'];
  $skin            = $attr['skin'];
  $number          = $attr['number'];
  $length          = $attr['length'];
  $board_id        = $attr['board_id'];

  $header          = apply_filters('kingkongboard_latest_list_before', '<thead><tr><th>'.__('제목', 'kingkongboard').'</th><th style="text-align:center; width:100px">'.__('작성일', 'kingkongboard').'</th></tr></thead>');

  $latest_content  = '<div id="kingkongboard-latest-wrapper">';

  $table_before = apply_filters('kingkongboard_latest_table_before', $board_id);

  if($table_before != $board_id){
    $latest_content .= $table_before;
  }

  $latest_content .= '<table id="kingkongboard-latest-table">';

  $latest_content .= $header;
  $kingkongboard  = new KKB_Latest($board_id);
  $latests        = $kingkongboard->kkb_get_latest_list($number);

  $latest_priority = apply_filters('kingkongboard_latest_priority', array('title', 'date'));
  $count = 1;
  foreach($latests as $latest){
    $latest_content .= '<tr>';
      foreach($latest_priority as $priority){

        switch($priority){
          case "title" :
            $title = kingkongboard_text_cut(get_the_title($latest->post_id), $length, "...");
            $latest_content .= '<td class="kingkongboard-latest-td-'.$priority.'">';
            $latest_content .= '<a href="'.get_the_permalink($latest->post_id).'">'.$title.'</a>';
            $latest_content .= '</td>';
          break;

          case "date" :
            $latest_content .= '<td class="kingkongboard-latest-td-'.$priority.'">';
            $latest_content .= get_the_date('Y-m-d', $latest->post_id);
            $latest_content .= '</td>';
          break;
        }
        $latest_filter = apply_filters('kingkongboard_latest_priority_case', $count, $latest, $priority);
        if($latest_filter != $count){
          $latest_content .= $latest_filter;
        }
      }
    $latest_content .= '</tr>';
    $count++;
  }

  $latest_content   .= '</table>';
  $latest_content   .= '</div>';

  return $latest_content;

}

?>