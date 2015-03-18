<?php

  $entries = apply_filters('kingkong_board_entry_columns', 'kingkong_board_entry_columns', $entry = null, $board_id); 
  $board_sections = get_post_meta($board_id, 'board_sections', true);
  if(get_option('permalink_structure')){
    $prm_pfx = "?";
  } else {
    $prm_pfx = "&";
  }

  $section_value  = null;


  if(isset($_POST['kkb_search_section'])){
    $search_section = sanitize_text_field($_POST['kkb_search_section']);  
  } else {
    $search_section = null;
  }

  if(isset($_POST['kkb_search_keyword'])){
    $search_keyword = sanitize_text_field($_POST['kkb_search_keyword']);
  } else {
    $search_keyword = null;
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
  $kkbContent .= '<form method="post" action="'.get_the_permalink().$prm_pfx.'mode=search">';
  $kkbContent .= '<div class="kingkongboard-search-inner-div">';
  $kkbContent .= '<table class="kingkongboard-search-table">';
  $kkbContent .= '<tr>';

  if($board_sections){
    $kkbContent .= '<td class="kingkongboard-search-td-section">';
    $kkbContent .= '<select name="kkb_search_section">';
    $kkbContent .= $section_value;
    $kkbContent .= '</select>';
    $kkbContent .= '</td>';
  }
  $kkbContent .= '<td class="kingkongboard-search-td-input">';
  $kkbContent .= '<input type="text" name="kkb_search_keyword" value="'.$search_keyword.'" style="max-height:26px">';
  $kkbContent .= '</td>';
  $kkbContent .= '<td style="text-align:left; padding-right:0;padding-left:10px">';
  $kkbContent .= '<button type="submit" class="kingkongboard-button" style="height:26px">검색</button>';
  $kkbContent .= '</td>';
  $kkbContent .= '</tr>';
  $kkbContent .= '</table>';
  $kkbContent .= '</div>';
  $kkbContent .= '</form>';
  $kkbContent .= '</div>';

  $kkbContent .= '<table id="kingkongboard-table" class="kingkongboard-table">';
  $kkbContent .= '<thead>';
  $kkbContent .= '<tr>';

  $board_sections = get_post_meta($board_id, 'board_sections', true);

  foreach($entries as $entry){
    if($entry['value'] == "section" && !$board_sections){
      $kkbContent .= null;
    } else {
      $kkbContent .= "<th class='entry-th-".$entry['value']."'>".$entry['label']."</th>";
    }
  }

  $kkbContent .= '</tr>';
  $kkbContent .= '</thead>';

  if(isset($_GET['pageid'])){
    $page = sanitize_text_field($_GET['pageid']);
  } else {
    $page = 1;
  }

  if(isset($_GET['mode'])){
    $mode = sanitize_text_field($_GET['mode']);
  } else {
    $mode = null;
  }

  if($mode == "search"){
    $bResults     = $KingkongEntries->Get_Kingkong_Search_Entries($_POST, $page);
    $totalCount   = $KingkongEntries->Get_Kingkong_Board_Search_Total_Count($_POST);
    $pageCount    = $KingkongEntries->Search_Pagination($_POST);
  } else {
    $bResults     = $KingkongEntries->Get_Kingkong_Basic_Entries($page);
    $totalCount   = $KingkongEntries->Get_Kingkong_Board_Total_Count();
    $pageCount    = $KingkongEntries->Pagination();
  }

  $nResults   = $KingkongEntries->Get_Kingkong_Notice_Entries();
  $noticeCount = $KingkongEntries->Get_Kingkong_Board_Notice_Count();

  $basic_rows = '';
  $notice_rows = '';
  $pagenation = '';

  if($pageCount > 1){
    if( $page > 3){
      $pagenation .= '<a href="'.get_the_permalink().$prm_pfx.'pageid=1"><span>1</span></a>';
      $pagenation .= '<span class="none-select">...</span>';
      for ($i=($page-3); $i < $pageCount; $i++) { 
        if($page == ($i+1)){
          $pagenation .= '<span>'.($i+1).'</span>';
        } else {
          if(($i+1) > $page + 2){
            $pagenation .= '<span class="none-select">...</span>';
            $pagenation .= '<a href="'.get_the_permalink().$prm_pfx.'pageid='.$pageCount.'"><span>'.$pageCount.'</span></a>';
            break;
          } else {
            $pagenation .= '<a href="'.get_the_permalink().$prm_pfx.'pageid='.($i+1).'"><span>'.($i+1).'</span></a>';
          }
        }
      }
    } else {
      for ($i=0; $i < $pageCount; $i++) { 
        if($i > 3){
            $pagenation .= '<span class="none-select">...</span>';
            $pagenation .= '<a href="'.get_the_permalink().$prm_pfx.'pageid='.$pageCount.'"><span>'.$pageCount.'</span></a>';
            break;          
        } else {
          if($page == ($i+1)){
            $pagenation .= '<span>'.($i+1).'</span>';
          } else {
            $pagenation .= '<a href="'.get_the_permalink().$prm_pfx.'pageid='.($i+1).'"><span>'.($i+1).'</span></a>';
          }
        }
      }
    }
  }

  foreach($nResults as $nResult){
    $notice_rows .= '<tr class="entry-notice">';
    $notice_rows .= apply_filters('kingkong_board_manage_entry_column', $entries, $nResult->post_id, $cnt=null );
    $notice_rows .= '</tr>';
  }

  $cnt = ($totalCount-$noticeCount) - (($page * $KingkongEntries->board_rows) - ($KingkongEntries->board_rows));
  //var_dump($bResults);

  foreach($bResults as $bResult){
    $basic_rows .= '<tr>';
    $basic_rows .= apply_filters('kingkong_board_manage_entry_column', $entries, $bResult->post_id, $cnt );
    $basic_rows .= '</tr>';
    $cnt--;
  }

  $kkbEntries = $notice_rows.$basic_rows;

  $kkbContent .= $kkbEntries.'</table>';
  $kkbContent .= '<nav class="kingkong-board-entry-nav">'; 

  $activate_page = 'class="activated"';

  $kkbContent .= $pagenation;
  
  $kkbContent .= '</nav>';
  $kkbContent .= '<div class="kingkongboard-controller">';
  if($permission_write_status == "checked"){
    $kkbContent .= '<a href="'.get_the_permalink($post->ID).$prm_pfx.'view=write" class="kingkongboard-button">'.__('글쓰기', 'kingkongboard').'</a>';
  }
  $kkbContent .= '</div>';
  $kkbContent .= '<div class="kingkongboard-copyrights"><a href="http://superrocket.io" target="_blank">Powered by Kingkong Board</a></div>';


?>