<?php
  $entry_id = $board_id;
  $controller = new kkbList();
  (get_post_meta($entry_id, 'kingkongboard_secret')) ? $secret = __('비밀글 설정됨', 'kingkongboard') : $secret = __('일반글', 'kingkongboard');
  ($controller->getMeta($entry_id, 'type') == 1) ? $type = __('공지글', 'kingkongboard') : $type = __('일반 게시글', 'kingkongboard');
  ($controller->getMeta($entry_id, 'login_id') != 0) ? $user_type = __('회원', 'kingkongboard') : $user_type = __('비회원', 'kingkongboard');
?>
  <div class="head-area">
    <div style="float:left; position:relative; top:10px; margin-right:10px">
      <a href="?page=KingkongBoard"><img src="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/assets/images/logo-kingkongboard.png" style="width:220px; height:auto"></a>
    </div>
    <div style="float:left; font-size:18px; margin-top:14px; margin-left:20px"><?php echo get_the_title($entry_id);?></div>
    <div style="float:right; position:relative; top:8px">
      <a href="#" class="button">Help</a>
    </div>
  </div>
  <div class="notice-toolbox-wrapper"></div>
  <div class="kkb-entry-wrapper">
    <table class="wp-list-table kkb-entry-view-table widefat fixed unite_table_items" style="padding:10px 10px">
      <tr>
        <th width="150"><?php echo __('제목', 'kingkongboard');?></th>
        <td><?php echo get_the_title($entry_id);?></td>
      </tr>
      <tr>
        <th width="150"><?php _e('작성자', 'kingkongboard');?></th>
        <td><?php echo $controller->getMeta($entry_id, 'writer').' ('.$user_type.')';?></td>
      </tr>
      <tr>
        <th width="150"><?php _e('종류', 'kingkongboard');?></th>
        <td><?php echo $type;?></td>
      </tr>
      <tr>
        <th width="150"><?php _e('비밀글 설정여부', 'kingkongboard');?></th>
        <td><?php echo $secret;?></td>
      </tr>
      <tr>
        <th width="150"><?php echo __('내용', 'kingkongboard');?></th>
        <td><?php echo nl2br(get_post_field('post_content', $entry_id));?></td>
      </tr>
      <tr>
        <th width="150"><?php echo __('첨부', 'kingkongboard');?></th>
        <td>
<?php
  $entry_attachment = unserialize(get_post_meta($entry_id, 'kingkongboard_attached', true));

  if($entry_attachment){
    foreach($entry_attachment as $attachment_id){
      echo "<div class='entry-attachment-div'><a href='".wp_get_attachment_url( $attachment_id )."' target='_blank'>".preg_replace( '/^.+[\\\\\\/]/', '', get_attached_file( $attachment_id ) )."</a></div>";
    }
  } else {
    echo __("없음", "kingkongboard");
  }

  if(isset($_GET['prnt'])){
    $parent = sanitize_text_field($_GET['prnt']);
    if($parent != ''){
      $parent_id  = $parent;
      $parent_prm = '&parent='.$parent_id;
    }
  } else {
    $parent_id    = sanitize_text_field($_GET['id']);
    $parent_prm   = '';
  }

?>
        </td>
      </tr>
      <tr>
        <th style="background:none"></th>
        <td class="entry-file-list"></td>
      </tr>
      <?php do_action('kingkong_board_admin_entry_input_after'); ?>
    </table>
    <br><a href="javascript:history.back();" class="button-kkb kkbblue"><i class="kkb-icon kkb-icon-list"></i><?php echo __('목록보기', 'kingkongboard');?></a> <a href="?page=KingkongBoard&view=entry-modify&id=<?php echo $entry_id;?>" class="button-kkb kkbred"><i class="kkb-icon kkb-icon-modify" style="margin-right:5px;"></i><?php echo __('수정하기', 'kingkongboard');?></a> <a href="?page=KingkongBoard&view=entry-reply&id=<?php echo $entry_id;?><?php echo $parent_prm;?>" class="button-kkb kkbgreen"><i class="kkb-icon kkb-icon-pen"></i><?php echo __('답변하기', 'kingkongboard');?></a>
  </div>
