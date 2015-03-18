<?php
if(isset($_POST['entry_title'])){
  $entry_title = sanitize_text_field($_POST['entry_title']);
} else {
  $entry_title = null;
}
  if($entry_title){
    $Board = new KingkongBoard_Entries(sanitize_text_field($_GET['id']));
    $Board->Kingkong_Board_Insert_Entry($_POST);
  }
?>
  <div class="head-area">
    <div style="float:left; position:relative; top:10px; margin-right:10px">
      <a href="?page=KingkongBoard"><img src="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/assets/images/logo-kingkongboard.png" style="width:220px; height:auto"></a>
    </div>
    <div style="float:left; font-size:18px; margin-top:14px; margin-left:20px"><?php echo __('게시판 글쓰기', 'kingkongboard');?> : <?php echo get_the_title($board_id);?></div>
    <div style="float:right; position:relative; top:8px">
      <a href="#" class="button">Help</a>
    </div>
  </div>
  <div class="notice-toolbox-wrapper"></div>
  <div class="kkb-entry-wrapper">
    <form id="kingkongboard-entry-write-form" method="post">
    <input type="hidden" name="page_id" value="<?php echo sanitize_text_field($_GET['id']);?>">
    <table class="wp-list-table widefat fixed unite_table_items" style="padding:10px 10px">
      <tr>
        <th width="150"><?php echo __('제목', 'kingkongboard');?></th>
        <td><input type="text" name="entry_title" class="kkb-input" style="max-width:500px; width:100%"></td>
      </tr>
      <tr>
        <th width="150"><?php echo __('종류', 'kingkongboard');?></th>
        <td><input type="checkbox" name="entry_notice" value="notice"> <?php echo __('공지사항', 'kingkongboard');?></td>
      </tr>
      <tr>
        <th width="150"><?php echo __('설정', 'kingkongboard');?></th>
        <td><input type="checkbox" name="entry_secret"> <?php echo __('비밀글', 'kingkongboard');?></td>
      </tr>
      <tr>
        <th width="150"><?php echo __('비밀번호', 'kingkongboard');?></th>
        <td><input type="password" name="entry_password" class="kkb-input" style="max-width:300px; width:100%"></td>
      </tr>
      <tr>
        <th width="150"><?php echo __('내용', 'kingkongboard');?></th>
        <td><textarea class="kkb-textarea" name="entry_content" style="max-width:500px; width:100%; height:200px"></textarea></td>
      </tr>
      <tr>
        <th width="150"><?php echo __('첨부', 'kingkongboard');?></th>
        <td><button type="button" class="button-kkb kkbblue button-entry-file-upload"><i class="kkb-icon kkb-icon-file"></i><?php echo __('첨부파일 업로드', 'kingkongboard');?></button></td>
      </tr>
      <tr>
        <th></th>
        <td class="entry-file-list"></td>
      </tr>
      <?php do_action('kingkong_board_admin_entry_input_after'); ?>
    </table>
    <br><button type="submit" class="button-kkb kkbgreen"><i class="kkb-icon kkb-icon-pen"></i><?php echo __('작성 완료', 'kingkongboard');?></button> <button class="button-kkb kkbred"><i class="kkb-icon kkb-icon-close"></i><?php echo __('취소', 'kingkongboard');?></button>
    </form>
  </div>
