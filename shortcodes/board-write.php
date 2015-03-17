<?php
  $sections           = null;
  $board_editor       = get_post_meta($board_id, 'kingkongboard_editor', true);
  $board_sections     = get_post_meta($board_id, 'board_sections', true);
  $board_thumb_upload = get_post_meta($board_id, 'thumbnail_upload', true);
  $board_file_upload  = get_post_meta($board_id, 'file_upload', true);

  if($board_sections){
    $sections = unserialize($board_sections);
  }

  $kkbContent .= '<form id="writeForm" method="post" enctype="multipart/form-data">';
  $kkbContent .= '<table id="kingkongboard-write-table">';

  apply_filters("kingkongboard_write_title_before", $kkbContent);

  $kkbContent .= '<tr>';
  $kkbContent   .= '<th class="kingkongboard-write-title">'.__('제목', 'kingkongboard').'</th>';
  $kkbContent   .= '<td class="kingkongboard-write-title"><input type="text" name="entry_title" style="width:100%"></td>';
  $kkbContent .= '</tr>';

  apply_filters("kingkongboard_write_notice_before", $kkbContent);

  if( current_user_can('edit_post', get_the_ID()) ){
    $kkbContent .= '<tr>';
    $kkbContent   .= '<th>'.__('공지사항', 'kingkongboard').'</th>';
    $kkbContent   .= '<td><input type="checkbox" name="entry_notice" value="notice"></td>';
    $kkbContent .= '</tr>';
  }

  apply_filters("kingkongboard_write_secret_before", $kkbContent); 

  $kkbContent .= '<tr>';
  $kkbContent   .= '<th>'.__('비밀글', 'kingkongboard').'</th>';
  $kkbContent   .= '<td><input type="checkbox" name="entry_secret"></td>';
  $kkbContent .= '</tr>';
  
  apply_filters("kingkongboard_write_section_before", $kkbContent);

  if($sections){
    $section_value = "<option value='0'>".__('분류를 선택하세요', 'kingkongboard')."</option>";
    foreach($sections as $section){
      $section_value .= '<option value="'.$section.'">'.$section.'</option>';
    }

    $kkbContent .= '<tr>';
    $kkbContent   .= '<th>'.__('분류선택', 'kingkongboard').'</th>';
    $kkbContent   .= '<td>';
    $kkbContent   .= '<select name="entry_section">';
    $kkbContent   .= $section_value;
    $kkbContent   .= '</select>';
    $kkbContent   .= '</td>';
    $kkbContent .= '</tr>';
  }

  apply_filters("kingkongboard_write_writer_before", $kkbContent);

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

  apply_filters("kingkongboard_write_password_before", $kkbContent);

  if(!is_user_logged_in()){
    $kkbContent .= '<tr class="password-tr">';
    $kkbContent   .= '<th>'.__('비밀번호', 'kingkongboard').'</th>';
    $kkbContent   .= '<td><input type="password" name="entry_password"></td>';
    $kkbContent .= '</tr>';
  }

  if($board_captcha == "T" && $captcha_site_key != null && $captcha_secret_key != null){    
    $kkbContent .= '<tr>';
    $kkbContent .= '<th class="captcha-title-th">'.__('자동 글 방지', 'kingkongboard').'</th>';
    $kkbContent .= '<td><div class="g-recaptcha" data-sitekey="'.$captcha_site_key.'"></div></td>';
    $kkbContent .= '</tr>';
  }

  $kkbContent .= '<tr>';
  $kkbContent   .= '<td colspan="2">';

  switch($board_editor){

    case "textarea" :
      $kkbContent   .= '<textarea name="entry_content" rows="10"></textarea>';
    break;

    case "wp_editor" :
      $settings = array( 'teeny' => true, 'textarea_name' => 'entry_content', 'textarea_rows' => 10 );
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

  if($board_thumb_upload == "T"){
    $kkbContent .= '</tr>';
    $kkbContent .= '<tr class="thumbnail-tr">';
    $kkbContent .= '<th>'.__('썸네일', 'kingkongboard').'</th>';
    $kkbContent .= '<td style="position:relative">';
    $kkbContent .= '<input type="file" name="thumbnail_file[]">';
    $kkbContent .= '</td>';
    $kkbContent .= '</tr>';
  }

  if($board_file_upload == "T"){
    $kkbContent .= '<tr class="attachment-tr">';
    $kkbContent   .= '<th>'.__('첨부파일', 'kingkongboard').'</th>';
    $kkbContent   .= '<td style="position:relative"><input type="file" name="entry_file[]"> <div class="attachment-controller"><img src="'.KINGKONGBOARD_PLUGINS_URL.'/assets/images/button-add.png" style="width:20px; height:auto"></div></td>';
    $kkbContent .= '</tr>';
  }

  if($board_thumb_upload == "T" or $board_file_upload == "T"){
    $kkbContent .= '<tr>';
    $kkbContent .= '<td colspan="2" style="border-bottom:0px; font-size:12px; color:gray">* '.__('썸네일/첨부파일은 한번에 ','kingkongboard').kingkong_formatBytes(kingkong_file_upload_max_size()).__('이하만 업로드가 가능합니다. 클 경우 정상적으로 업로드 되지 않습니다.','kingkongboard').'</td>';
    $kkbContent .= '</tr>';
  }
  $kkbContent .= '</table>';
  $kkbContent .= '<div class="kingkongboard-controller">';
  $kkbContent .= '<a href="'.get_the_permalink().'" class="kingkongboard-button button-prev">돌아가기</a> <a class="kingkongboard-button button-save">저장하기</a><div style="float:right; margin-right:10px; position:relative; opacity:0" class="write-save-loading"><div style="float:left; margin-right:10px">'.__('저장중입니다.잠시만 기다려주세요.', 'kingkongboard').'</div><div style="float:left"><img src="'.KINGKONGBOARD_PLUGINS_URL.'/assets/images/ajax-loader3.gif"></div></div>';
  $kkbContent .= '</div>';
  $kkbContent .= '<input type="hidden" name="board_id" value="'.$board_id.'">';
  $kkbContent .= '<input type="hidden" name="post_id" value="'.get_the_ID().'">';
  $kkbContent .= '<input type="hidden" name="page_uri" value="'.get_the_permalink().'">';
  $kkbContent .= '<input type="hidden" name="pfx" value="'.$prm_pfx.'">';
  $kkbContent .= '<input type="hidden" name="page_id" value="'.$page_id.'">';
  $kkbContent .= '<input type="hidden" name="editor_style" value="'.$board_editor.'">';

if(is_user_logged_in()){
  $user_status = 1;
} else {
  $user_status = 0;
}

  $kkbContent .= '<input type="hidden" name="user_status" value="'.$user_status.'">';
  $kkbContent .= '</form>';

?>