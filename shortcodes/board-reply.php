<?php
  
  $board_editor = get_post_meta($board_id, 'kingkongboard_editor', true);

  if(isset($_GET['parent'])){
    $parent = sanitize_text_field($_GET['parent']);
    if($parent != ''){
      $parent_id = $parent;
    }
  } else {
    $parent_id  = sanitize_text_field($_GET['id']);
  }

  $kkbContent .= '<form id="writeForm" method="post" enctype="multipart/form-data">';
  $kkbContent .= '<table id="kingkongboard-write-table">';
  $kkbContent .= '<tr>';
  $kkbContent   .= '<th class="kingkongboard-write-title">'.__('제목', 'kingkongboard').'</th>';
  $kkbContent   .= '<td class="kingkongboard-write-title"><input type="text" name="entry_title" value="re: '.get_the_title($_GET['id']).'"></td>';
  $kkbContent .= '</tr>';
  $kkbContent .= '<tr>';
  $kkbContent   .= '<th>'.__('비밀글', 'kingkongboard').'</th>';
  $kkbContent   .= '<td><input type="checkbox" name="entry_secret"></td>';
  $kkbContent .= '</tr>';
  
  if( !is_user_logged_in() ){
    $kkbContent .= '<tr>';
    $kkbContent   .= '<th>'.__('작성자', 'kingkongboard').'</th>';
    $kkbContent   .= '<td><input type="text" name="entry_writer"></td>';
    $kkbContent .= '</tr>';
  }

  if(get_option('permalink_structure')){
    $prm_pfx = "?";
  } else {
    $prm_pfx = "&";
  }

  $kkbContent .= '<tr>';
  $kkbContent   .= '<th>'.__('비밀번호', 'kingkongboard').'</th>';
  $kkbContent   .= '<td><input type="password" name="entry_password"></td>';
  $kkbContent .= '</tr>';
  $kkbContent .= '<tr>';
  $kkbContent   .= '<td colspan="2">';

  switch($board_editor){

    case "textarea" :
      $kkbContent   .= '<textarea name="entry_content"></textarea>';
    break;

    case "wp_editor" :
      $settings = array( 'teeny' => true, 'textarea_name' => 'entry_content' );
      ob_start();
      wp_editor(null, 'entry_content', $settings);
      $editor = ob_get_contents();
      ob_end_clean();
      $kkbContent   .= $editor;
    break;

    default :
      if (file_exists(TEMPLATEPATH."/kingkongboard/editor/".$board_editor."/kingkongboard-editor.php")) {
        require_once(TEMPLATEPATH."/kingkongboard/editor/".$board_editor."/kingkongboard-editor.php");
      } else {
        $kkbContent   .= '<textarea name="entry_content"></textarea>';
      }
    break;
  }

  $kkbContent   .= '</td>';
  $kkbContent .= '</tr>';
  $kkbContent .= '<tr class="attachment-tr">';
  $kkbContent   .= '<th>'.__('첨부파일', 'kingkongboard').'</th>';
  $kkbContent   .= '<td style="position:relative"><input type="file" name="entry_file[]"> <div class="attachment-controller"><img src="'.KINGKONGBOARD_PLUGINS_URL.'/assets/images/button-add.png" style="width:20px; height:auto"></div></td>';
  $kkbContent .= '</tr>';
  $kkbContent .= '</table>';
  $kkbContent .= '<div class="kingkongboard-controller">';
  $kkbContent .= '<a href="'.get_the_permalink().'" class="kingkongboard-button button-prev">돌아가기</a> <button type="submit" class="kingkongboard-button button-save">저장하기</button>';
  $kkbContent .= '</div>';
  $kkbContent .= '<input type="hidden" name="board_id" value="'.$board_id.'">';
  $kkbContent .= '<input type="hidden" name="post_id" value="'.get_the_ID().'">';
  $kkbContent .= '<input type="hidden" name="page_uri" value="'.get_the_permalink().'">';
  $kkbContent .= '<input type="hidden" name="pfx" value="'.$prm_pfx.'">';
  $kkbContent .= '<input type="hidden" name="page_id" value="'.$page_id.'">';
  $kkbContent .= '<input type="hidden" name="origin" value="'.$_GET['id'].'">';
  $kkbContent .= '<input type="hidden" name="parent" value="'.$parent_id.'">';
  $kkbContent .= '<input type="hidden" name="editor_style" value="'.$board_editor.'">';

  $kkbContent .= '</form>';

?>