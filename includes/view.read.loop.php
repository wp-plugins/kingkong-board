<?php
  $table_id       = apply_filters('kkb_loop_table_id', 'kingkongboard-table', $this->board_id);
  $table_class    = apply_filters('kkb_loop_table_class', 'kingkongboard-table', $this->board_id);
  $entries        = apply_filters('kingkong_board_entry_columns', 'kingkong_board_entry_columns', $entry = null, $this->board_id); 
  $board_sections = get_post_meta($this->board_id, 'board_sections', true);
  (isset($_GET['pageid'])) ? $page = sanitize_text_field($_GET['pageid']) : $page = 1;
  (isset($_POST['kkb_keyword'])) ? $keyword = sanitize_text_field($_POST['kkb_keyword']) : $keyword = null;
  (isset($_POST['kkb_section'])) ? $section = sanitize_text_field($_POST['kkb_section']) : $section = null;
  (isset($_GET['kkb_keyword']) && empty($keyword)) ? $keyword = sanitize_text_field($_GET['kkb_keyword']) : $keyword = $keyword;
   
  if(isset($_GET['srchtype'])) $search_type = sanitize_text_field($_GET['srchtype']);
  if(isset($_GET['kkb_section'])) $section = sanitize_text_field($_GET['kkb_section']);

?>
 
  <table id="<?php echo $table_id;?>" class="<?php echo $table_class;?>">
    <thead>
      <tr>
<?php
  
  foreach($entries as $entry){
    if($entry['value'] == "section" && !$board_sections){

    } else {
?>
        <th class="entry-th-<?php echo $entry['value'];?>"><span><label><?php echo apply_filters('kkb_loop_header_text', $entry['label'], $entry);?></label></span></th>
<?php
    }
  }
?>
      </tr>
    </thead>
<?php
  (isset($_GET['pageid'])) ? $page = sanitize_text_field($_GET['pageid']) : $page = 1;
  require_once KINGKONGBOARD_ABSPATH.'class/class.list.php';
  $kkb        = new kkbList();
  $config     = new kkbConfig();
  $config     = $config->getBoard($this->board_id);

  if($keyword || $section){
    $data['kkb_search_keyword'] = $keyword;
    $data['kkb_search_section'] = $section;
    $data['kkb_search_type']    = $search_type;

    $bResults     = $kkb->searchResult($this->board_id, $data, $page, null);
    $totalCount   = $kkb->getSearch($this->board_id, $data, $page, 'count');
  } else {
    $bResults     = $kkb->getBasic($this->board_id, $page);
    $totalCount   = $kkb->getCount($this->board_id, 'basic');
  }
  
  $nResults       = $kkb->getNotice($this->board_id);

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