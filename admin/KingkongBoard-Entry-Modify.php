<?php
$entry_id = $board_id;
$Board    = new kkbController();
$board_id = $Board->getMeta($entry_id, 'board_id');
if(isset($_POST['entry_title'])){
  $entry_title = sanitize_text_field($_POST['entry_title']);
} else {
  $entry_title = null;
}
  if($entry_title){
    $Board->writeModify($_POST, 'admin');
  }

  $current_user   = wp_get_current_user();
  $hour_options   = null;
  $minute_options = null;
  $second_options = null;

  for ($i = 0; $i < 24; $i++) { 
    if($i == date( 'H', $Board->getMeta($entry_id, 'date') ) ){
      $hour_options .= '<option selected>'.$i.'</option>';
    } else {
      $hour_options .= '<option>'.$i.'</option>';
    }
  }

  for ($i = 0; $i < 60; $i++) {
    if($i == date( 'i', $Board->getMeta($entry_id, 'date') ) ){
      $minute_options .= '<option selected>'.$i.'</option>';
    } else {
      $minute_options .= '<option>'.$i.'</option>';
    }
  }

  for ($i = 0; $i < 60; $i++) {
    if($i == date( 's', $Board->getMeta($entry_id, 'date') ) ){
      $second_options .= '<option selected>'.$i.'</option>';
    } else {
      $second_options .= '<option>'.$i.'</option>';
    }
  }

  $post_list  = null;
  $board_slug = get_post_meta( $board_id, 'kingkongboard_slug', true );
  $board_slug = "kingkong_board ".$board_slug;
  $added_post_lists = check_board_shortcode_using( $board_slug );

  foreach($added_post_lists as $list){
    $post_list .= '<option value="'.$list.'">'.get_the_title($list).'</option>';
  }

  $guest_mode = $Board->getMeta($entry_id, 'login_id');
  $notice     = $Board->getMeta($entry_id, 'type');
  ($guest_mode == 0) ? $guest_checked = 'checked' : $gust_checked = null;
  ($notice == 1) ? $notice_checked = 'checked' : $notice_checked = null;
  (get_post_meta($entry_id, 'kingkongboard_secret')) ? $secret_checked = 'checked' : $secret_checked = null;
  $content = get_post_field('post_content', $entry_id);
?>
  <div class="head-area">
    <div style="float:left; position:relative; top:10px; margin-right:10px">
      <a href="?page=KingkongBoard"><img src="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/assets/images/logo-kingkongboard.png" style="width:220px; height:auto"></a>
    </div>
    <div style="float:left; font-size:18px; margin-top:14px; margin-left:20px"><?php echo __('게시판 글수정', 'kingkongboard');?> : <?php echo get_the_title($entry_id);?></div>
    <div style="float:right; position:relative; top:8px">
      <a href="#" class="button">Help</a>
    </div>
  </div>

<?php

  if($added_post_lists){

?>
  <div class="notice-toolbox-wrapper"></div>
  <div class="kkb-entry-wrapper">
    <form id="kingkongboard-entry-write-form" method="post">
    <input type="hidden" name="page_id" value="<?php echo sanitize_text_field($_GET['id']);?>">
    <table class="wp-list-table widefat fixed unite_table_items" style="padding:10px 10px">
      <tr>
        <th width="150"><?php echo __('제목', 'kingkongboard');?></th>
        <td><input type="text" name="entry_title" class="kkb-input" style="max-width:500px; width:100%" value="<?php echo get_the_title($entry_id);?>"></td>
      </tr>
      <tr>
        <th width="150"><?php echo __('작성자', 'kingkongboard');?></th>
        <td>
          <input type="text" name="entry_writer" class="kkb-input" style="max-width:150px; width:100%" value="<?php echo $Board->getMeta($entry_id, 'writer');?>">
          <div class="description-container">
            <span class="description"><?php echo __('기본값은 현재 관리자 display name 입니다. 변경하실 경우 수정하시면 됩니다.', 'kingkongboard');?></span>
          </div>  
        </td>
      </tr>
      <tr>
        <th width="150"><?php _e('작성분류', 'kingkongboard');?></th>
        <td>
          <input type="checkbox" name="entry_write_guest" value="T" <?php echo $guest_checked;?>> 비회원으로 작성합니다.
          <div class="description-container">
            <span class="description"><?php echo __('체크시 비회원 모드로 글을 작성합니다.', 'kingkongboard');?></span>
          </div> 
        </td>
      </tr>
      <tr>
        <th width="150"><?php echo __('작성일시', 'kingkongboard');?></th>
        <td>
          년-월-일 : <input type="text" id="entry_date" class="kkb-input" name="entry_ymd" value="<?php echo date( 'Y-m-d', $Board->getMeta($entry_id, 'date') );?>" style="max-width:100px; width:100%" /> <select class="kkb-input" name="entry_h"><?php echo $hour_options;?></select>시 <select class="kkb-input" name="entry_i"><?php echo $minute_options;?></select>분 <select class="kkb-input" name="entry_s"><?php echo $second_options;?></select>초
          <div class="description-container">
            <span class="description"><?php echo __('기본값은 게시글 등록 시간입니다. 변경하실 경우 지정된 일시로 저장됩니다.', 'kingkongboard');?></span>
          </div> 
        </td>
      </tr>
      <tr>
        <th width="150"><?php echo __('동록된 페이지/포스트', 'kingkongboard');?></th>
        <td>
          <select class="kkb-input" name="entry_guid" style="max-width:400px; width:100%"><?php echo $post_list;?></select>
          <div class="description-container">
            <span class="description"><?php echo __('관리자에서 임의로 글을 등록하기 위해서는 해당 게시판이 등록되어 있는 페이지 또는 포스트의 ID 값이 필요합니다.', 'kingkongboard');?></span>
          </div>           
        </td>
      </tr>
      <tr>
        <th width="150"><?php echo __('종류', 'kingkongboard');?></th>
        <td><input type="checkbox" name="entry_notice" value="notice" <?php echo $notice_checked;?>> <?php echo __('공지사항', 'kingkongboard');?></td>
      </tr>
      <tr>
        <th width="150"><?php echo __('설정', 'kingkongboard');?></th>
        <td><input type="checkbox" name="entry_secret" <?php echo $secret_checked;?>> <?php echo __('비밀글', 'kingkongboard');?></td>
      </tr>
      <tr>
        <th width="150"><?php echo __('비밀번호', 'kingkongboard');?></th>
        <td>
          <input type="password" name="entry_password" class="kkb-input" style="max-width:300px; width:100%">
          <div class="description-container">
            <span class="description"><?php echo __('기존 비밀글일 경우 입력하시면 입력하신 비밀번호로 변경 되오니 유의하시기 바랍니다.', 'kingkongboard');?></span>
          </div> 
        </td>
      </tr>
      <tr>
        <th width="150"><?php echo __('내용', 'kingkongboard');?></th>
        <td><textarea class="kkb-textarea" name="entry_content" style="max-width:500px; width:100%; height:200px"><?php echo $content;?></textarea></td>
      </tr>
      <tr>
        <th width="150"><?php _e('썸네일', 'kingkongboard');?></th>
        <td><button type="button" class="button-kkb kkbblue button-entry-thumbnail-upload"><i class="kkb-icon kkb-icon-file"></i><?php echo __('썸네일 업로드', 'kingkongboard');?></button></td>
      </tr>
      <tr>
        <th></th>
        <td class="entry-thumbnail-list">
<?php
  if(has_post_thumbnail($entry_id)){
    $thumbnail_id = get_post_thumbnail_id($entry_id);
    $url          = wp_get_attachment_image_src( $thumbnail_id, 'full' );
    $filename     = basename($url[0]);
?>
          <div class="entry-attachment-div" data="<?php echo $thumbnail_id;?>">
            <?php echo $filename;?>
            <input type="hidden" name="entry_thumbnail" value="<?php echo $thumbnail_id;?>">
            <div class="entry-thumbnail-remove"></div>
          </div>
<?php
  }
?>
        </td>
      </tr>
      <tr>
        <th width="150"><?php echo __('첨부', 'kingkongboard');?></th>
        <td><button type="button" class="button-kkb kkbblue button-entry-file-upload"><i class="kkb-icon kkb-icon-file"></i><?php echo __('첨부파일 업로드', 'kingkongboard');?></button></td>
      </tr>
      <tr>
        <th></th>
        <td class="entry-file-list">
<?php
  $attached = get_post_meta($entry_id, 'kingkongboard_attached', true);
  if($attached){
    $attached = maybe_unserialize($attached);
    foreach($attached as $attach){
      $url = wp_get_attachment_url( $attach );
      $filename = basename($url);
?>
          <div class="entry-attachment-div" data="<?php echo $attach;?>">
            <?php echo $filename;?>
            <input type="hidden" name="entry_attachment[]" value="<?php echo $attach;?>">
            <div class="entry-attachment-remove"></div>
          </div>
<?php
    }
  }
?>
        </td>
      </tr>
      <?php do_action('kingkong_board_admin_entry_input_after'); ?>
    </table>
    <br><button type="submit" class="button-kkb kkbgreen"><i class="kkb-icon kkb-icon-pen"></i><?php echo __('작성 완료', 'kingkongboard');?></button> <a href="javascript:history.back();" class="button-kkb kkbred"><?php echo __('취소', 'kingkongboard');?></a>
    <input type="hidden" name="entry_id" value="<?php echo $entry_id;?>">
    </form>
  </div>

<?php 
  } else {
?>
  <div class="kkb-entry-wrapper">
    <?php echo __('관리자 모드에서 글을 등록하기 위해서는 해당 게시판 숏코드를 페이지 또는 포스트에 등록을 먼저 해주셔야 합니다.', 'kingkongboard');?>
  </div>
<?php
  }
?>
<script>
kingkongboard_entry_remove_button_enable();
</script>
