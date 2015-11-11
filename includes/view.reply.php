<?php
  $config     = new kkbConfig();
  $config     = $config->getBoard($this->board_id);
  $tableID    = apply_filters('kkb_write_table_id', 'kkb-write-wrapper', $this->board_id);
  $tableClass = apply_filters('kkb_write_table_class', 'kkb-write-wrapper', $this->board_id);
  ($config->must_secret == "T") ? $board_must_secret_text = "onclick='return false;' checked='checked'" : $board_must_secret_text = null;
  ($board_must_secret_text != null) ? $secret_description = '<span style="display:inline-block; margin-left:25px">'.__('비밀글만 허용 됩니다.', 'kingkongboard').'</span>' : $secret_description = null;
  (isset($_GET['pageid'])) ? $page = sanitize_text_field($_GET['pageid']) : $page = 1;
  (isset($_GET['prnt'])) ? $parent_id = sanitize_text_field($_GET['prnt']) : $parent_id = $this->entry_id;
  $parent_section = parent::getMeta($parent_id, 'section');
  (!$parent_section) ? $parent_section = '0' : $parent_section = $parent_section;
?>
<form id="writeForm" method="post" enctype="multipart/form-data" action="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/includes/view.save.php">
  <input type="hidden" name="entry_section" value="<?php echo $parent_section;?>">
  <div id="<?php echo $tableID;?>" class="<?php echo $tableClass;?>">
<?php
  echo apply_filters("kingkongboard_write_title_before", null, $this->board_id);
?>
    <div class="write-box">
      <label for="entry-title" class="kingkongboard-write-title"><span><?php echo apply_filters('kkb_write_title_text', __('제목', 'kingkongboard'), $this->board_id);?><span class="label-required">*</span></span></label>
      <span class="write-span"><input id="entry-title" type="text" name="entry_title" style="width:100%" value="Re: <?php echo $this->get_title($parent_id);?>" title="<?php echo apply_filters('kkb_write_title_text', __('제목', 'kingkongboard'), $this->board_id);?>"></span>
    </div>
    <div class="write-box-devider"></div>
<?php
  echo apply_filters("kingkongboard_write_secret_before", null, $this->board_id);
  ob_start();
?> 
    <div class="write-box check-box">
      <label for="input-checkbox-secret"><span><?php _e('비밀글', 'kingkongboard');?></span></label>
      <span class="write-span"><span class="toggle-checkbox-image"></span><input id="input-checkbox-secret" type="checkbox" class="input-checkbox" name="entry_secret" title="<?php _e('비밀글', 'kingkongboard');?>" <?php echo $board_must_secret_text;?>> <?php echo $secret_description;?></span>
    </div>
    <div class="write-box-devider"></div>
<?php
  $scContent = ob_get_contents();
  ob_end_clean();
  echo apply_filters('kkb_write_secret_row', $scContent, $this->board_id);

  echo apply_filters("kingkongboard_write_writer_before", null, $this->board_id);

  if( !is_user_logged_in() ){
?>
    <div class="write-box">
      <label for="entry-writer"><span><?php _e('작성자', 'kingkongboard');?><span class="label-required">*</span></span></label>
      <span class="write-span"><input id="entry-writer" type="text" name="entry_writer" title="<?php _e('작성자', 'kingkongboard');?>"></span>
    </div>
    <div class="write-box-devider"></div>
<?php
  }
  echo apply_filters("kingkongboard_write_password_before", null, $this->board_id);

  if( !is_user_logged_in() ){
?>
    <div class="write-box">
      <label for="entry-password"><span><?php _e('비밀번호', 'kingkongboard');?><span class="label-required">*</span></span></label>
      <span class="write-span"><input id="entry-password" type="password" name="entry_password" title="<?php _e('비밀번호', 'kingkongboard');?>"></span>
    </div>
    <div class="write-box-devider"></div>
<?php
  }

  if($config->captcha == "T" && $config->captchaKey['site_key'] != null && $config->captchaKey['secret_key'] != null){  
?>  
    <div class="write-box write-captcha" style="height:auto">
      <label for="g-recaptcha" class="captcha-title-th"><span><?php _e('자동 글 방지', 'kingkongboard');?></span></label>
      <span class="write-span"><div id="g-recaptcha" class="g-recaptcha" data-sitekey="<?php echo $config->captchaKey['site_key'];?>"></div></span>
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
?>
        <textarea name="entry_content" rows="10" title="<?php _e('내용입력', 'kingkongboard');?>"></textarea>
<?php
    break;

    case "wp_editor" :
      $settings = array( 'teeny' => false, 'textarea_name' => 'entry_content', 'textarea_rows' => 10 );
      ob_start();
      wp_editor('', 'entry_content', $settings);
      $editor = ob_get_contents();
      ob_end_clean();
      echo $editor;
    break;

    default :
?>
          <textarea name="entry_content" rows="10" title="<?php _e('내용입력', 'kingkongboard');?>"></textarea>
<?php
    break;
  }
?>
      </span>
    </div>
    <div class="write-box-devider"></div>
    <div class="write-box">
      <label for="entry-tags"><span><?php _e('태그설정', 'kingkongboard');?></span></label>
      <span class="write-span">
        <input id="entry-tags" title="<?php _e('태그설정', 'kingkongboard');?>" name="entry_tags" type="text" style="display:block; min-width:auto; width:100%" placeholder="<?php _e('콤마(,) 로 분리 합니다.', 'kingkongboard');?>">
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
          <input title="<?php printf(__('태그: %s', 'kingkongboard'), $term->name);?>" type="checkbox" class="check-each-tag" value="<?php echo $term->name;?>">
          <label><?php echo $term->name;?></label>
        </span>
<?php
  }
?>
    </div>
    <div class="rows-more popular-tags">
      <a href="#" class="btn-popular-tags" data-fold="<?php _e('접기', 'kingkongboard');?>" data-open="<?php _e('자주쓰는태그', 'kingkongboard');?>"><?php _e('자주쓰는태그', 'kingkongboard');?></a>
    </div>
<?php
  }
?>
    <div class="write-box-devider"></div>
<?php
  echo apply_filters("kingkongboard_write_content_after", null, $this->board_id);
  if($config->thumbUpload == "T"){
?>
    <div class="write-box">
      <label for="entry-thumbnail"><span><?php _e('썸네일', 'kingkongboard');?></span></label>
      <span class="write-span" style="position:relative; width:300px">
        <div class="file-upload">
          <input type="text" class="text thumbnail-file" title="<?php _e('썸네일 첨부하기', 'kingkongboard');?>" readonly="readonly" style="font-size:12px">
          <div class="upload-btn">
            <button type="button" class="img-upload <?php echo kkb_button_classer($this->board_id);?>" title="<?php _e('썸네일 찾아보기', 'kingkongboard');?>"><span><?php _e('찾아보기', 'kingkongboard');?></span></button>
            <input id="entry-thumbnail" type="file" id="find" class="file" name="thumbnail_file[]" title="<?php _e('썸네일 찾아보기', 'kingkongboard');?>">
          </div>
        </div>
      </span>
    </div>
    <div class="write-box-devider"></div>
<?php
  }
  if($config->fileUpload == "T"){
    $addIcon = apply_filters('kkb_write_attach_add_icon', '<img src="'.KINGKONGBOARD_PLUGINS_URL.'/assets/images/button-add.png" style="width:20px; height:auto">', $this->board_id);
    $attach_number = get_post_meta($this->board_id, 'kkb_attach_number', true);
?>
    <div class="write-box attach-box attach-box-1">
      <label for="entry-file"><span data-title="<?php _e('첨부파일', 'kingkongboard');?>"><?php _e('첨부파일', 'kingkongboard');?></span></label>
      <span class="write-span" style="position:relative; width:300px">
        <div class="file-upload">
          <input type="text" class="text" title="파일 첨부하기" readonly="readonly" style="font-size:12px">
          <div class="upload-btn">
            <button type="button" class="img-upload <?php echo kkb_button_classer($this->board_id);?>" title="<?php _e('파일 찾아보기', 'kingkongboard');?>"><span><?php _e('찾아보기', 'kingkongboard');?></span></button>
            <input id="entry-file" type="file" id="find" class="file" name="entry_file[]" title="<?php _e('파일 찾아보기', 'kingkongboard');?>">
          </div>
        </div>
      </span>
    </div>
    <div class="rows-more attach-more">
      <a href="#" class="btn-attach-more" data-limit="<?php echo $attach_number;?>" data-error="<?php printf(__('최대 %d개 까지 업로드 할 수 있습니다.', 'kingkongboard'), $attach_number);?>"><?php _e('첨부파일 추가', 'kingkongboard');?></a>
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
<?php
  $back_args = apply_filters('kkb_read_arg_after', array(), $this->board_id);
  $back_path = add_query_arg($back_args, get_the_permalink());
?>
    <a href="<?php echo $back_path;?>" class="<?php echo kkb_button_classer($this->board_id);?> button-prev">
      <?php echo kkb_button_text($this->board_id, 'back');?>
    </a> 
    <button type="button" class="<?php echo kkb_button_classer($this->board_id);?> button-save" style="float:right">
      <?php echo kkb_button_text($this->board_id, 'save');?>
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
  <input type="hidden" name="post_id" value="<?php echo get_the_ID();?>">
  <input type="hidden" name="page_uri" value="<?php echo get_the_permalink();?>">
  <input type="hidden" name="page_id" value="<?php echo $page;?>">
  <input type="hidden" name="editor_style" value="<?php echo $config->editor;?>">
  <input type="hidden" name="write_type" value="reply">
  <input type="hidden" name="origin" value="<?php echo $this->entry_id;?>">
  <input type="hidden" name="parent" value="<?php echo $parent_id;?>">
</form>