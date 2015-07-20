<?php
  $table_id    = apply_filters('kkb_loop_table_id', 'kingkongboard-table', $board_id);
  $table_class = apply_filters('kkb_loop_table_class', 'kingkongboard-table', $board_id);
?>

  <table id="<?php echo $table_id;?>" class="<?php echo $table_class;?>">
    <thead>
      <tr>
<?php
  $board_sections = get_post_meta($board_id, 'board_sections', true);

  foreach($entries as $entry){
    if($entry['value'] == "section" && !$board_sections){

    } else {
?>
        <th class="entry-th-<?php echo $entry['value'];?>"><?php echo apply_filters('kkb_loop_header_text', $entry['label'], $entry);?></th>
<?php
    }
  }
?>
      </tr>
    </thead>
<?php
  (isset($_GET['pageid'])) ? $page = sanitize_text_field($_GET['pageid']) : $page = 1;
  require_once KINGKONGBOARD_ABSPATH.'/class/class.list.php';
  require_once KINGKONGBOARD_ABSPATH.'/class/class.pagenation.php';
  $kkb        = new kkbList();
  $pnation    = new kkbPagenation();
  $config     = new kkbConfig();
  $config     = $config->getBoard($board_id);

  if($search_keyword || $search_section){
    $data['kkb_search_keyword'] = $search_keyword;
    $data['kkb_search_section'] = $search_section;
    $bResults     = $kkb->getSearch($board_id, $data, $page, null);
    $totalCount   = $kkb->getSearch($board_id, $data, $page, 'count');
    $pageCount    = $kkb->searchCount($board_id, $data);
  } else {
    $bResults     = $kkb->getBasic($board_id, $page);
    $totalCount   = $kkb->getCount($board_id, 'basic');
    $pageCount    = $kkb->pageCount($board_id);
  }

  $nResults       = $kkb->getNotice($board_id);
  $noticeCount    = $kkb->getCount($board_id, 'notice');
  $pagenation     = '';
  $param_keyword  = null;
  $param_section  = null;

  $pagenation    = $pnation->getPagenation($board_id, $page, $pageCount, $search_keyword, $search_section);

  foreach($nResults as $nResult){
?>
      <tr class="entry-notice">
<?php
      foreach($entries as $entry){
?>
        <?php echo apply_filters('kkb_loop_column_'.$entry['value'], $result=null, $entry, $nResult->post_id, $cn=null);?>
<?php
      }
?>
      </tr>
<?php
  }
  $cnt = ($totalCount) - (($page * $config->rows) - ($config->rows));
  if($bResults){
    foreach($bResults as $bResult){
?>
      <tr>
<?php
      foreach($entries as $entry){
?>
        <?php echo apply_filters('kkb_loop_column_'.$entry['value'], $result=null, $entry, $bResult->post_id, $cnt);?>
<?php
      }
?>
      </tr>
<?php
      $cnt--;
    }
  }
?>
  </table>