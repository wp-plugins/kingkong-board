<?php
  do_action('kkb_loop_before');
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
    $board_sections = unserialize($board_sections);
    $section_value .= '<option value="all">'.__('전체', 'kingkongboard').'</option>';
    foreach($board_sections as $section){
      if($search_section == $section){
        $section_value .= '<option value="'.$section.'" selected>'.$section.'</option>';
      } else {
        $section_value .= '<option value="'.$section.'">'.$section.'</option>';
      }
    }
  } 
  $kkbContent .= '<div class="kingkongboard-search">';
  $kkbContent .= '<form method="POST" action="'.get_the_permalink($post->ID).'">';

  $search_box_before = apply_filters('kkb_board_loop_search_box_before', $board_id);
  if($search_box_before != $board_id){
    $kkbContent .= $search_box_before;
  }
  $kkbContent .= '<div class="kingkongboard-search-inner-div">';
  $kkbContent .= '<table class="kingkongboard-search-table">';
  $kkbContent .= '<tr>';

  if($board_sections){
    $kkbContent .= '<td class="kingkongboard-search-td-section">';
    $kkbContent .= '<select name="kkb_section">';
    $kkbContent .= $section_value;
    $kkbContent .= '</select>';
    $kkbContent .= '</td>';
  }
  $kkbContent .= '<td class="kingkongboard-search-td-input">';
  $kkbContent .= '<input type="text" name="kkb_keyword" value="'.$search_keyword.'" style="max-height:26px">';
  $kkbContent .= '</td>';
  $kkbContent .= '<td style="text-align:left; padding-right:0;padding-left:10px">';
  $kkbContent .= '<button type="submit" class="kingkongboard-search-button '.kkb_button_classer($board_id).'" style="height:26px">'.kkb_button_text($board_id, 'search').'</button>';
  $kkbContent .= '</td>';
  $kkbContent .= '</tr>';
  $kkbContent .= '</table>';
  $kkbContent .= '</div>';
  $kkbContent .= '</form>';
  $kkbContent .= '</div><input type="hidden" name="page_permalink" value="'.get_the_permalink($post->ID).'">';

  $table_id    = apply_filters('kkb_loop_table_id', 'kingkongboard-table', $board_id);
  $table_class = apply_filters('kkb_loop_table_class', 'kingkongboard-table', $board_id);

  $kkbContent .= '<table id="'.$table_id.'" class="'.$table_class.'">';
  $kkbContent .= '<thead>';
  $kkbContent .= '<tr>';

  $board_sections = get_post_meta($board_id, 'board_sections', true);

  foreach($entries as $entry){
    if($entry['value'] == "section" && !$board_sections){
      $kkbContent .= null;
    } else {
      $kkbContent .= "<th class='entry-th-".$entry['value']."'>".apply_filters('kkb_loop_header_text', $entry['label'], $entry)."</th>";
    }
  }

  $kkbContent .= '</tr>';
  $kkbContent .= '</thead>';

  if(isset($_GET['pageid'])){
    $page = sanitize_text_field($_GET['pageid']);
  } else {
    $page = 1;
  }

  $KKB_List = new KKB_List($board_id);

  if($search_keyword || $search_section){
    $data['kkb_search_keyword'] = $search_keyword;
    $data['kkb_search_section'] = $search_section;
    //var_dump($data);
    $bResults     = $KKB_List->kkb_get_search_list($data, $page);
    $totalCount   = $KKB_List->kkb_get_search_count($data);
    $pageCount    = $KKB_List->kkb_search_pagination($data);
  } else {
    $bResults     = $KKB_List->kkb_get_basic_list($page);
    $totalCount   = $KKB_List->kkb_get_list_count('all');
    $pageCount    = $KKB_List->kkb_pagination();
  }

  $nResults       = $KKB_List->kkb_get_notice_list();
  $noticeCount    = $KKB_List->kkb_get_list_count('notice');

  $basic_rows = '';
  $notice_rows = '';
  $pagenation = '';
  $param_keyword = null;
  $param_section = null;

  if($pageCount > 1){
    if(!empty($search_keyword) || !empty($search_section)){ 
      if($search_keyword){
        $param_keyword = "&kkb_keyword=".$search_keyword;
      }
      if($search_section){
        $param_section = "&kkb_section=".$search_section;
      }
    }


    if( $page > 3){
      $page_path = add_query_arg('pageid', '1', get_the_permalink());
      $pagenation .= '<a href="'.$page_path.'"><span>1</span></a>';
      $pagenation .= '<span class="none-select">...</span>';
      for ($i=($page-3); $i < $pageCount; $i++) { 
        if($page == ($i+1)){
          $pagenation .= '<span>'.($i+1).'</span>';
        } else {
          if(($i+1) > $page + 2){
            $pagenation .= '<span class="none-select">...</span>';
            $page_path = add_query_arg( 'pageid', ($pageCount.$param_section.$param_keyword), get_the_permalink());
            $pagenation .= '<a href="'.$page_path.'"><span>'.$pageCount.'</span></a>';
            break;
          } else {
            $page_path = add_query_arg( 'pageid', (($i+1).$param_section.$param_keyword), get_the_permalink());
            $pagenation .= '<a href="'.$page_path.'"><span>'.($i+1).'</span></a>';
          }
        }
      }
    } else {
      for ($i=0; $i < $pageCount; $i++) { 
        if($i > 3){
            $pagenation .= '<span class="none-select">...</span>';
            $page_path = add_query_arg( 'pageid', ($pageCount.$param_section.$param_keyword), get_the_permalink());
            $pagenation .= '<a href="'.$page_path.'"><span>'.$pageCount.'</span></a>';
            break;          
        } else {
          if($page == ($i+1)){
            $pagenation .= '<span>'.($i+1).'</span>';
          } else {
            $page_path = add_query_arg( 'pageid', (($i+1).$param_section.$param_keyword), get_the_permalink());
            $pagenation .= '<a href="'.$page_path.'"><span>'.($i+1).'</span></a>';
          }
        }
      }
    }
  }

  foreach($nResults as $nResult){
    $notice_rows .= '<tr class="entry-notice">';
      foreach($entries as $entry){
        $notice_rows  .= apply_filters('kkb_loop_column_'.$entry['value'], $result=null, $entry, $nResult->post_id, $cn=null);
      }
    $notice_rows .= '</tr>';
  }

  $cnt = ($totalCount-$noticeCount) - (($page * $KKB_List->config->board_rows) - ($KKB_List->config->board_rows));
  //var_dump($bResults);
  if($bResults){
    foreach($bResults as $bResult){
      $basic_rows .= '<tr>';
      foreach($entries as $entry){
        $basic_rows  .= apply_filters('kkb_loop_column_'.$entry['value'], $result=null, $entry, $bResult->post_id, $cnt);
      }
      $basic_rows .= '</tr>';
      $cnt--;
    }
  }

  $kkbEntries = $notice_rows.$basic_rows;

  $kkbContent .= $kkbEntries.'</table>';
  $kkbContent .= '<nav class="kingkong-board-entry-nav">'; 

  $activate_page = 'class="activated"';

  $kkbContent .= $pagenation;
  
  $kkbContent .= '</nav>';
  $kkbContent .= '<div class="kingkongboard-controller">';
  if($permission_write_status == "checked"){
    $write_path = add_query_arg('view', 'write', get_the_permalink($post->ID));
    $kkbContent .= '<a href="'.$write_path.'" class="'.kkb_button_classer($board_id).'">'.kkb_button_text($board_id, 'write').'</a>';
  }
  $kkbContent .= '</div>';
  $kkbContent .= '<div class="kingkongboard-copyrights"><a href="http://superrocket.io" target="_blank">Powered by Kingkong Board</a></div>';


?>