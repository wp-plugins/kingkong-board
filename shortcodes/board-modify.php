<?php
  
  $entry_id    = sanitize_text_field($_GET['id']);
  $content     = get_post_field('post_content', $entry_id);
  $type        = get_kingkong_board_meta_value($entry_id, 'type');
  $board_editor = get_post_meta($board_id, 'kingkongboard_editor', true);
  $board_sections = get_post_meta($board_id, 'board_sections', true);

  if($board_sections){
    $sections = unserialize($board_sections);
  }

  if($type == 1){
    $notice_checked = "checked";
  } else {
    $notice_checked = "";
  }

  $entry_secret   = get_post_meta($entry_id, 'kingkongboard_secret', true);
  $entry_password = get_post_meta($entry_id, 'kingkongboard_entry_password', true);

  if($entry_secret){
    $entry_secret_checked = "checked";
  } else {
    $entry_secret_checked = "";
  }

  $kkbContent .= '<form id="writeForm" method="post" enctype="multipart/form-data">';
  $kkbContent .= '<table id="kingkongboard-write-table">';
  $kkbContent .= '<tr>';
  $kkbContent   .= '<th class="kingkongboard-write-title">'.__('제목', 'kingkongboard').'</th>';
  $kkbContent   .= '<td class="kingkongboard-write-title"><input type="text" name="entry_title" value="'.get_the_title($entry_id).'"></td>';
  $kkbContent .= '</tr>';
  if( current_user_can('edit_post', get_the_ID()) ){
    $kkbContent .= '<tr>';
    $kkbContent   .= '<th>'.__('공지사항', 'kingkongboard').'</th>';
    $kkbContent   .= '<td><input type="checkbox" name="entry_notice" value="notice" '.$notice_checked.'></td>';
    $kkbContent .= '</tr>';
  }
  $kkbContent .= '<tr>';
  $kkbContent   .= '<th>'.__('비밀글', 'kingkongboard').'</th>';
  $kkbContent   .= '<td><input type="checkbox" name="entry_secret" '.$entry_secret_checked.'></td>';
  $kkbContent .= '</tr>';

  if($sections){
    $selected_section = get_kingkong_board_meta_value($entry_id, 'section');
    $section_value = "<option value='0'>".__('분류를 선택하세요', 'kingkongboard')."</option>";

    foreach($sections as $section){
      if($selected_section == $section){
        $section_value .= '<option value="'.$section.'" selected>'.$section.'</option>';
      } else {
        $section_value .= '<option value="'.$section.'">'.$section.'</option>';
      }
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
      $kkbContent   .= '<textarea name="entry_content">'.$content.'</textarea>';
    break;

    case "wp_editor" :
      $settings = array( 'teeny' => true, 'textarea_name' => 'entry_content' );
      ob_start();
      wp_editor($content, 'entry_content', $settings);
      $editor = ob_get_contents();
      ob_end_clean();
      $kkbContent   .= $editor;
    break;

    default :
      if (file_exists(TEMPLATEPATH."/kingkongboard/editor/".$board_editor."/kingkongboard-editor.php")) {
        require_once(TEMPLATEPATH."/kingkongboard/editor/".$board_editor."/kingkongboard-editor.php");
      } else {
        $kkbContent   .= '<textarea name="entry_content">'.$content.'</textarea>';
      }
    break;
  }
  
  $kkbContent   .= '</td>';
  $kkbContent .= '</tr>';

  $attached         = get_post_meta($entry_id, 'kingkongboard_attached', true );
  $thumbnail_image  = get_the_post_thumbnail( $entry_id, array(80,80) );
  $thumbnail_url    = wp_get_attachment_image_src( get_post_thumbnail_id($entry_id, 'thumbnail'));
    if($thumbnail_image){
      $kkbContent .= '<tr>';
      $kkbContent .= '<td colspan="2">';
      $kkbContent .= '<div class="entry_attachment_title">'.__('등록된 썸네일', 'kingkongboard').'</div>';
      $kkbContent .= '<div class="entry_each_attach"><div style="display:inline-block; border:1px solid #e0e0e0; margin-right:10px; vertical-align:top">'.$thumbnail_image.'</div><div style="display:inline-block; font-size:11px; color:gray">'.$thumbnail_url[0].'<br>'.__('하단에 새롭게 썸네일을 업로드 하시면 변경 됩니다.', 'kingkongboard').'</div><div class="entry_attach_remove"><img src="'.KINGKONGBOARD_PLUGINS_URL.'/assets/images/icon-close.png" style="width:18px; height:auto"></div><input type="hidden" name="entry_each_thumbnail" value="'.get_post_thumbnail_id($entry_id, 'thumbnail').'"></div>';
      $kkbContent .= '</td>';
      $kkbContent .= '</tr>';
    }
    if($attached){
        $attached = unserialize($attached);

        $kkbContent .= '<tr>';
        $kkbContent .= '<td colspan="2">';
        $kkbContent .= '<div class="entry_attachment_title">'.__('첨부파일', 'kingkongboard').' ('.count($attached).')</div>';
        
        foreach($attached as $attach){
            $filename = get_kingkongboard_uploaded_filename($attach);
            $typeIcon = "<img src='".kingkongboard_get_icon_for_attachment($attach)."' style='width:15px; height:auto; margin-right:5px'>";
            $kkbContent .= '<div class="entry_each_attach">'.$typeIcon.'<a href="'.wp_get_attachment_url($attach).'" download>'.$filename.'</a> <span style="color:gray">'.kingkongboard_attached_getSize($attach).'</span><div class="entry_attach_remove"><img src="'.KINGKONGBOARD_PLUGINS_URL.'/assets/images/icon-close.png" style="width:18px; height:auto"></div><input type="hidden" name="entry_each_attached_id[]" value="'.$attach.'"></div>';
        }
        $kkbContent .= '</td>';
        $kkbContent .= '</tr>';
    }
  $kkbContent .= '<tr class="thumbnail-tr">';
  $kkbContent   .= '<th>'.__('썸네일', 'kingkongboard').'</th>';
  $kkbContent   .= '<td style="position:relative"><input type="file" name="thumbnail_file[]"></td>';
  $kkbContent .= '</tr>';
  $kkbContent .= '<tr class="attachment-tr">';
  $kkbContent   .= '<th>'.__('첨부파일', 'kingkongboard').'</th>';
  $kkbContent   .= '<td style="position:relative"><input type="file" name="entry_file[]"> <div class="attachment-controller"><img src="'.KINGKONGBOARD_PLUGINS_URL.'/assets/images/button-add.png" style="width:20px; height:auto"></div></td>';
  $kkbContent .= '</tr>';
  $kkbContent .= '</table>';
  $kkbContent .= '<div class="kingkongboard-controller">';
  $kkbContent .= '<a href="'.get_the_permalink().'" class="kingkongboard-button button-prev">돌아가기</a> <a class="kingkongboard-button button-save">수정하기</a>';
  $kkbContent .= '</div>';
  $kkbContent .= '<input type="hidden" name="page_uri" value="'.get_the_permalink().'">';
  $kkbContent .= '<input type="hidden" name="pfx" value="'.$prm_pfx.'">';
  $kkbContent .= '<input type="hidden" name="entry_id" value="'.$entry_id.'">';
  $kkbContent .= '<input type="hidden" name="board_id" value="'.$board_id.'">';
  $kkbContent .= '<input type="hidden" name="post_id" value="'.get_the_ID().'">';
  $kkbContent .= '<input type="hidden" name="page_id" value="'.$page_id.'">';
  $kkbContent .= '<input type="hidden" name="editor_style" value="'.$board_editor.'">';
  $kkbContent .= '</form>';

?>