<?php
  $parent_id = '';
  $entry_title = sanitize_text_field($_POST['entry_title']);
  if($entry_title){
    $ParentBoard = get_board_id_by_entry_id(sanitize_text_field($_GET['id']));
    $Board = new KingkongBoard_Entries($ParentBoard);
    $Board->Kingkong_Board_Insert_Entry($_POST);
  }

  $parent = sanitize_text_field($_GET['parent']);

  if($parent){
    if($parent != ''){
      $parent_id = $parent;
    }
  } else {
    $parent_id  = sanitize_text_field($_GET['id']);
  }

?>
  <div class="head-area">
    <div style="float:left; position:relative; top:10px; margin-right:10px">
      <a href="?page=KingkongBoard"><img src="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/assets/images/logo-kingkongboard.png" style="width:220px; height:auto"></a>
    </div>
    <div style="float:left; font-size:18px; margin-top:14px; margin-left:20px">답변하기 : <?php echo get_the_title($parent_id);?></div>
    <div style="float:right; position:relative; top:8px">
      <a href="#" class="button">Help</a>
    </div>
  </div>
  <div class="notice-toolbox-wrapper"></div>
  <div class="kkb-entry-wrapper">
    <form id="kingkongboard-entry-write-form" method="post">
    <input type="hidden" name="origin" value="<?php echo sanitize_text_field($_GET['id']);?>">
    <input type="hidden" name="parent" value="<?php echo $parent_id;?>">
    <table class="wp-list-table widefat fixed unite_table_items" style="padding:10px 10px">
      <tr>
        <th width="150">제목</th>
        <td><input type="text" name="entry_title" class="kkb-input" style="max-width:500px; width:100%" value="<?php echo get_the_title($board_id);?>"></td>
      </tr>
      <tr>
        <th width="150">내용</th>
        <td><textarea class="kkb-textarea" name="entry_content" style="max-width:500px; width:100%; height:200px"></textarea></td>
      </tr>
      <tr>
        <th width="150">첨부</th>
        <td><button type="button" class="button-kkb kkbblue button-entry-file-upload"><i class="kkb-icon kkb-icon-file"></i>첨부파일 업로드</button></td>
      </tr>
      <tr>
        <th></th>
        <td class="entry-file-list"></td>
      </tr>
      <?php do_action('kingkong_board_admin_entry_input_after'); ?>
    </table>
    <br><button type="submit" class="button-kkb kkbgreen"><i class="kkb-icon kkb-icon-pen"></i>작성 완료</button> <button class="button-kkb kkbred"><i class="kkb-icon kkb-icon-close"></i>취소</button>
    </form>
  </div>
