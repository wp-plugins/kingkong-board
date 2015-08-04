<?php
/**
* File Name : 킹콩보드 글읽기 첨부파일 영역
*
* 주의 ! 반드시 <tr> 태그로 감싸야 하며 <td> 는 1개만 존재 해야 합니다.
* <tr> 태그로 감싸지 않으면 스타일이 깨집니다.
*/
$attached = maybe_unserialize($attached);
$mimeCons = new kkbMimeIcons();

?>
<div class="attachment-title"><?php echo apply_filters('kkb_read_attach_title', sprintf(__('첨부파일 %d', 'kingkongboard'), count($attached)), count($attached));?></div>
<div class="attachment-list">
<?php
  foreach($attached as $attach){
    $filename   = get_kingkongboard_uploaded_filename($attach);
    $filetypes  = wp_check_filetype($filename);
    $filetype   = $filetypes['ext'];
    $typeIcon   = apply_filters('kkb_read_attach_icon', $mimeCons->getImage($filename), $this->board_id, $entry_id, $attach);
?>
    <div class="each-attach">
      <span class="each-attach-span"><?php echo $typeIcon;?></span>
      <span class="each-attach-span">
        <a href="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/data/download.php?eid=<?php echo $entry_id;?>&aid=<?php echo $attach;?>">
          <?php echo get_the_title($attach).'.'.$filetype;?> <span style="color:gray"><?php echo kingkongboard_attached_getSize($attach);?></span>
        </a>
      </span>
    </div>
<?php
  }
?>
</div>