 
  <div class="head-area">
    <div style="float:left; position:relative; top:10px; margin-right:10px">
      <a href="?page=KingkongBoard"><img src="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/assets/images/logo-kingkongboard.png" style="width:220px; height:auto"></a>
    </div>
    <div style="float:left; font-size:18px; margin-top:14px; margin-left:20px"><?php echo __('게시판 글목록', 'kingkongboard');?> : <?php echo get_the_title($board_id);?></div>
    <div style="float:right; position:relative; top:8px">
      <div class="fb-like" data-href="https://facebook.com/superrocketer" data-layout="button_count" data-action="like" data-show-faces="true" data-share="true" style="position:relative; top:-10px; margin-right:10px"></div>
      <a href="http://superrocket.io" target="_blank"><img src="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/assets/images/superrocket-symbol.png" style="height:34px; width:auto" class="superrocket-logo" alt="superrocket.io"></a>
      <a href="https://www.facebook.com/superrocketer" target="_blank"><img src="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/assets/images/icon-facebook.png" style="height:34px; width:auto" class="superrocket-logo" alt="facebook"></a>
      <a href="https://instagram.com/superrocketer/" target="_blank"><img src="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/assets/images/icon-instagram.png" style="height:34px; width:auto" class="superrocket-logo" alt="instagram"></a>
    </div>
  </div>
  <div class="notice-toolbox-wrapper"></div>
  <div class="kkb-entry-wrapper">
<?php 
  $KingkongEntries = new KingkongBoard_Entries($board_id);
  $entries = apply_filters('admin_kingkong_board_entry_columns', 'admin_kingkong_board_entry_columns', $entry = null, $board_id); 
?>
    <input type="hidden" class="board_id" value="<?php echo $board_id;?>">
    <div style="float:right; margin-right:5px; padding-bottom:5px"><?php echo __('등록된 총 글 수', 'kingkongboard');?> : <span style="background:#616161; color:#fff; border-radius:10px; padding:0 5px"><?php echo $KingkongEntries->entry_count;?></span></div>
    <table class="wp-list-table widefat fixed unite_table_items">
      <tr>
<?php
    echo "<th class='entry-th-checkbox'><input type='checkbox' class='select-all-entry'></th>";
    echo "<th class='entry-th-id'>".__('번호', 'kingkongboard')."</th>";
  foreach($entries as $entry){
    echo "<th class='entry-th-".$entry['value']."'>".$entry['label']."</th>";
  }
?>
      </tr>

<?php
  if(isset($_GET['paged'])){
    $page = sanitize_text_field($_GET['paged']);
  } else {
    $page = 1;
  }
  $bResults    = $KingkongEntries->Get_Kingkong_Basic_Entries($page);
  $nResults   = $KingkongEntries->Get_Kingkong_Notice_Entries();

  $totalCount = $KingkongEntries->Get_Kingkong_Board_Total_Count();
  $noticeCount = $KingkongEntries->Get_Kingkong_Board_Notice_Count();

  $pageCount = $KingkongEntries->Pagination();
  $basic_rows = '';
  $notice_rows = '';

  foreach($nResults as $nResult){
    $notice_rows .= '<tr class="entry-notice"><td><input type="checkbox"></td><td><span class="label-notice">'.__('공지', 'kingkongboard').'</span></td>';
    $notice_rows .= apply_filters('admin_kingkong_board_manage_entry_column', $entries, $nResult->post_id );
    $notice_rows .= '</tr>';
  }

  $cnt = ($totalCount-$noticeCount) - (($page * $KingkongEntries->board_rows) - ($KingkongEntries->board_rows));

  foreach($bResults as $bResult){
    $basic_rows .= '<tr><td><input type="checkbox"></td><td>'.$cnt.'</td>';
    $basic_rows .= apply_filters('admin_kingkong_board_manage_entry_column', $entries, $bResult->post_id );
    $basic_rows .= '</tr>';
    $cnt--;
  }

    echo $notice_rows.$basic_rows;
?>

    </table>
    <nav class='kingkong-board-entry-nav'>
 <?php 

      $activate_page = 'class="activated"';
      $pagination    = '';

      if($pageCount > 1){
        for ($i=0; $i < $pageCount; $i++) { 
          if($page == ($i+1)){
            $pagination .= '<span>'.($i+1).'</span>';
          } else {
            $pagination .= '<a href="?page=KingkongBoard&view=entry&id='.$board_id.'&paged='.($i+1).'"><span>'.($i+1).'</span></a>';
          }
        }

        echo $pagination;

      }
  ?>
    </nav>
    <br><a href="?page=KingkongBoard&view=entry-write&id=<?php echo $board_id;?>" class="button-kkb kkbblue"><i class="kkb-icon kkb-icon-pen"></i><?php echo __('글 쓰기', 'kingkongboard');?></a> <a href="?page=KingkongBoard&view=modify&id=<?php echo $board_id;?>" class="button-kkb kkbgreen"><i class="kkb-icon kkb-icon-setting"></i><?php echo __('게시판 옵션설정', 'kingkongboard');?></a> <a href="?page=KingkongBoard" class="button-kkb kkbred"><?php echo __('이전으로', 'kingkongboard');?></a>
  </div>