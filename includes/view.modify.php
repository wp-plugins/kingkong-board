<?php
  $config           = new kkbConfig();
  $config           = $config->getBoard($this->board_id);
  $entry_id         = $this->entry_id;
  $tableID          = apply_filters('kkb_write_table_id', 'kkb-write-wrapper', $this->board_id);
  $tableClass       = apply_filters('kkb_write_table_class', 'kkb-write-wrapper', $this->board_id);
  $type             = parent::getMeta($entry_id, 'type');
  $selected_section = parent::getMeta($entry_id, 'section');
  $writer           = parent::getMeta($entry_id, 'writer');

  $attached         = maybe_unserialize(get_post_meta($entry_id, 'kingkongboard_attached', true ));
  $thumbnail_image  = get_the_post_thumbnail( $entry_id, array(80,80) );
  $thumbnail_url    = wp_get_attachment_image_src( get_post_thumbnail_id($entry_id, 'thumbnail'));

  $entry_secret     = get_post_meta($entry_id, 'kingkongboard_secret', true);
  ($config->must_secret == "T") ? $board_must_secret_text = "onclick='return false;' checked='checked'" : $board_must_secret_text = null;
  ($board_must_secret_text != null) ? $secret_description = __('비밀글만 허용 됩니다.', 'kingkongboard') : $secret_description = null;
  (isset($_GET['pageid'])) ? $page = sanitize_text_field($_GET['pageid']) : $page = 1;
  ($type == 1) ? $notice_checked = "checked" : $notice_checked = null;
  ($entry_secret) ? $entry_secret_checked = "checked" : $entry_secret_checked = null;
?>
<form id="writeForm" method="post" enctype="multipart/form-data" action="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/includes/view.save.php">
  <div id="<?php echo $tableID;?>" class="<?php echo $tableClass;?>">
<?php 
  echo apply_filters("kingkongboard_write_title_before", null, $this->board_id);
?>
    <div class="write-box">
      <label class="kingkongboard-write-title"><span><?php echo apply_filters('kkb_write_title_text', __('제목', 'kingkongboard'), $this->board_id);?><span class="label-required">*</span></span></label>
      <span class="write-span"><input type="text" name="entry_title" style="width:100%" value="<?php echo $this->get_title($entry_id);?>"></span>
    </div>
    <div class="write-box-devider"></div>
<?php
  echo apply_filters("kingkongboard_write_notice_before", null, $this->board_id);
  if( current_user_can('manage_options') || parent::checkManagers($this->board_id) == true ){
    ob_start();
?>
    <div class="write-box check-box">
      <label><span><?php _e('공지사항', 'kingkongboard');?></span></label>
      <span class="write-span"><input type="checkbox" name="entry_notice" value="notice" <?php echo $notice_checked;?>></span>
    </div>
    <div class="write-box-devider"></div>
<?php
    $noticeContent = ob_get_contents();
    ob_end_clean();
    echo apply_filters('kkb_write_notice_row', $noticeContent, $this->board_id);    
  }
  echo apply_filters("kingkongboard_write_secret_before", null, $this->board_id);

  ob_start();
?>
    <div class="write-box check-box">
      <label><span><?php _e('비밀글', 'kingkongboard');?></span></label>
      <span class="write-span"><input type="checkbox" name="entry_secret" <?php echo $entry_secret_checked;?> <?php echo $board_must_secret_text;?>> <?php echo $secret_description;?></span>
    </div>
    <div class="write-box-devider"></div>
<?php
  $scContent = ob_get_contents();
  ob_end_clean();
  echo apply_filters('kkb_write_secret_row', $scContent, $this->board_id);
  echo apply_filters("kingkongboard_write_section_before", null, $this->board_id);

  if($config->sections){
    $section_value = "<option value='0'>".__('분류를 선택하세요', 'kingkongboard')."</option>";
    foreach($config->sections as $section){
      if($selected_section == $section){
        $section_value .= '<option value="'.$section.'" selected>'.$section.'</option>';
      } else {
        $section_value .= '<option value="'.$section.'">'.$section.'</option>';
      }
    }
    ob_start();
?>
    <div class="write-box">
      <label><span><?php _e('분류선택', 'kingkongboard');?></span></label>
      <span class="write-span"><?php echo apply_filters('kkb_write_section', '<select name="entry_section">'.$section_value.'</select>', $this->board_id);?></span>
    </div>
    <div class="write-box-devider"></div>
<?php
    $secContent = ob_get_contents();
    ob_end_clean();
    echo apply_filters('kkb_write_section_row', $secContent, $this->board_id);
  }
  echo apply_filters("kingkongboard_write_writer_before", null, $this->board_id);

  if( !is_user_logged_in() ){
?>
    <div class="write-box">
      <label><span><?php _e('작성자', 'kingkongboard');?><span class="label-required">*</span></span></label>
      <span class="write-span"><input type="text" name="entry_writer" value="<?php echo $writer;?>"></span>
    </div>
    <div class="write-box-devider"></div>
<?php
  }
  echo apply_filters("kingkongboard_write_password_before", null, $this->board_id);

  if( !is_user_logged_in() ){
?>
    <div class="write-box">
      <label><span><?php _e('비밀번호', 'kingkongboard');?><span class="label-required">*</span></span></label>
      <span class="write-span"><input type="password" name="entry_password"></span>
    </div>
    <div class="write-box-devider"></div>
<?php
  }
?>
    <div class="textarea-box">
      <span class="write-span">
<?php

  switch($config->editor){

    case "textarea" :
      $content = nl2br(get_post_field('post_content', $entry_id));
?>
        <textarea name="entry_content" rows="10"><?php echo $content;?></textarea>
<?php
    break;

    case "wp_editor" :
      $content = get_post_field('post_content', $entry_id);
      $settings = array( 'teeny' => false, 'textarea_name' => 'entry_content', 'textarea_rows' => 10 );
      ob_start();
      wp_editor($content, 'entry_content', $settings);
      $editor = ob_get_contents();
      ob_end_clean();
      echo $editor;
    break;

    default :
      $content = nl2br(get_post_field('post_content', $entry_id));
?>
          <textarea name="entry_content" rows="10"><?php echo $content;?></textarea>
<?php
    break;
  }

  $tags = wp_get_post_terms($entry_id, 'kkb_tag', array('fields' => 'names'));
  $tag_names = join(',', $tags);
?>
      </span>
    </div>
    <div class="write-box-devider"></div>
    <div class="write-box">
      <label><span><?php _e('태그설정', 'kingkongboard');?></span></label>
      <span class="write-span">
        <input name="entry_tags" type="text" style="display:block; min-width:auto; width:100%" placeholder="<?php _e('콤마(,) 로 분리 합니다.', 'kingkongboard');?>" value="<?php echo $tag_names;?>">
      </span>
    </div>
<?php
  $tags = parent::getTags($this->board_id);
  if($tags){
?>
    <div class="popular-tag-list">
<?php
  foreach($tags as $tag){
    $term = get_term($tag, 'kkb_tag');
?>
        <span class="each-tag">
          <input type="checkbox" class="check-each-tag" value="<?php echo $term->name;?>">
          <label><?php echo $term->name;?></label>
        </span>
<?php
  }
?>
    </div>
    <div class="rows-more popular-tags">
      <span class="btn-popular-tags" data-fold="<?php _e('접기', 'kingkongboard');?>" data-open="<?php _e('자주쓰는태그', 'kingkongboard');?>"><?php _e('자주쓰는태그', 'kingkongboard');?></span>
    </div>
<?php
  }
?>
    <div class="write-box-devider"></div>
<?php

  echo apply_filters("kingkongboard_write_content_after", null, $this->board_id);

  if($thumbnail_image){
?>
    <div class="added-file">
      <div class="added-file-box">
        <span class="added-file-label"><?php _e('등록된 썸네일', 'kingkongboard');?></span>
        <span class="added-file-remove"><img src="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/assets/images/icon-close.png" style="width:11px; height:auto"></span>
        <span class="added-file-thumbnail"><?php echo $thumbnail_image;?></span>
        <span class="added-file-content"><span style="font-size:11px; color:gray"><?php echo basename($thumbnail_url[0]);?></span><br><?php _e('하단에 새롭게 썸네일을 업로드 하면 변경 됩니다.', 'kingkongboard');?></span>
        <input type="hidden" name="entry_each_thumbnail" value="<?php echo get_post_thumbnail_id($entry_id, 'thumbnail');?>">
      </div>
    </div>
<?php
  }
  if($attached){
    $cnt = 1;
    foreach($attached as $attach){
      $filename = get_kingkongboard_uploaded_filename($attach);
      $typeIcon = "";
?>
    <div class="added-file">
      <div class="added-file-box">
        <span class="added-file-label"><?php _e('등록된 첨부파일', 'kingkongboard');?> <?php echo $cnt;?></span>
        <span class="added-file-remove"><img src="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/assets/images/icon-close.png" style="width:11px; height:auto"></span>
        <span class="added-file-content" style="padding:0"><a href="<?php echo wp_get_attachment_url($attach);?>" download><?php echo $filename;?></a> <span style="color:gray"><?php echo kingkongboard_attached_getSize($attach);?></span></span>
      </div>
      <input type="hidden" name="entry_each_attached_id[]" value="<?php echo $attach;?>">
    </div>
<?php
    $cnt++;
    }
  }

  echo apply_filters("kingkongboard_write_content_after", null, $this->board_id);

  if($config->thumbUpload == "T"){
?>
    <div class="write-box">
      <label><span><?php _e('썸네일', 'kingkongboard');?></span></label>
      <span class="write-span" style="position:relative; width:300px">
        <div class="file-upload">
          <input type="text" class="text" title="파일 첨부하기" readonly="readonly" style="font-size:12px">
          <div class="upload-btn">
            <button type="button" class="img-upload <?php echo kkb_button_classer($this->board_id);?>" title="찾아보기"><span>찾아보기</span></button>
            <input type="file" id="find" class="file" name="thumbnail_file[]" title="찾아보기">
          </div>
        </div>
      </span>
    </div>
    <div class="write-box-devider"></div>
<?php
  }
  /* 기존에 올라가 있는 첨부파일 개수와 신규 첨부파일 개수의 토탈 수가 지정된 수 이하이어야만 한다. */
  if($config->fileUpload == "T"){
    $attach_number = get_post_meta($this->board_id, 'kkb_attach_number', true);
?>
    <div class="write-box attach-box attach-box-1">
      <label><span data-title="<?php _e('첨부파일', 'kingkongboard');?>"><?php _e('첨부파일', 'kingkongboard');?></span></label>
      <span class="write-span" style="position:relative; width:300px">
        <div class="file-upload">
          <input type="text" class="text" title="파일 첨부하기" readonly="readonly" style="font-size:12px">
          <div class="upload-btn">
            <button type="button" class="img-upload <?php echo kkb_button_classer($this->board_id);?>" title="찾아보기"><span>찾아보기</span></button>
            <input type="file" id="find" class="file" name="entry_file[]" title="찾아보기">
          </div>
        </div>
      </span>
    </div>
    <div class="rows-more attach-more">
      <span class="btn-attach-more" data-limit="<?php echo $attach_number;?>" data-error="<?php printf(__('최대 %d개 까지 업로드 할 수 있습니다.', 'kingkongboard'), $attach_number);?>"><?php _e('첨부파일 추가', 'kingkongboard');?></span>
    </div>
    <div class="write-box-devider"></div>
<?php
  }
  if($config->thumbUpload == "T" || $config->fileUpload == "T"){
?>
    <div class="attach-message-box">
      <span class="write-span"><?php printf(__('썸네일/첨부파일은 한번에 %s 이하만 업로드가 가능합니다.', 'kingkongboard'), kingkong_formatBytes(kingkong_file_upload_max_size()));?></span>
    </div>
<?php
  }
?>

  </div>
  <div class="kingkongboard-controller">
    <a href="<?php echo get_the_permalink();?>" class="<?php echo kkb_button_classer($this->board_id);?> button-prev">
      <?php echo kkb_button_text($this->board_id, 'back');?>
    </a> 
    <button type="button" class="<?php echo kkb_button_classer($this->board_id);?> button-save" style="float:right">
      <?php echo kkb_button_text($this->board_id, 'modified');?>
    </button>
    <div style="float:right; margin-right:10px; position:relative; opacity:0" class="write-save-loading">
      <div style="float:left; margin-right:10px">
        <?php _e('저장중입니다.잠시만 기다려주세요.', 'kingkongboard');?>
      </div>
      <div style="float:left">
        <img src="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/assets/images/ajax-loader3.gif">
      </div>
    </div>
  </div>
  <input type="hidden" name="board_id" value="<?php echo $this->board_id;?>">
  <input type="hidden" name="entry_id" value="<?php echo $entry_id;?>">
  <input type="hidden" name="post_id" value="<?php echo get_the_ID();?>">
  <input type="hidden" name="page_uri" value="<?php echo get_the_permalink();?>">
  <input type="hidden" name="page_id" value="<?php echo $page;?>">
  <input type="hidden" name="editor_style" value="<?php echo $config->editor;?>">
  <input type="hidden" name="write_type" value="modify">
</form>