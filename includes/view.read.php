<?php
if($this->entry_id && is_numeric($this->entry_id) && $this->board_id && is_numeric($this->board_id)){
  (isset($_GET['pageid'])) ? $pageid = sanitize_text_field($_GET['pageid']) : $pageid = 1;
  $config                 = new kkbConfig();
  $config                 = $config->getBoard($this->board_id);
  $board_read_under       = $config->board_under;
  $entry_id               = $this->entry_id;
  $writer                 = parent::getMeta($entry_id, 'writer');
  $date                   = parent::getMeta($entry_id, 'date');
  $currentListNumber      = parent::getMeta($entry_id, 'list_number');
  $prevListNumber         = parent::getPrev($this->board_id, $currentListNumber);
  $nextListNumber         = parent::getNext($this->board_id, $currentListNumber);
  $attached               = get_post_meta($entry_id, 'kingkongboard_attached', true);
  $hit                    = $this->updateHit($this->board_id, $entry_id);  
  $board_thumbnail_input  = get_post_meta($this->board_id, 'kingkongboard_thumbnail_input', true);
  $board_reply_use        = get_post_meta($this->board_id, 'kkb_reply_use', true);

  if(isset($_GET['prnt'])){ 
    $parent = sanitize_text_field($_GET['prnt']);          
    if($parent != ''){
      $parent_id  = $parent;
      $parent_prm = '&prnt='.$parent_id;
    }
  } else {
    $parent_id    = $entry_id;
    $parent_prm   = '';
  }

  ($config->editor == 'wp_editor') ? $content = nl2br(get_post_field('post_content', $entry_id)) : $content = wpautop(get_post_field('post_content', $entry_id), true);  
  if($config->auto_link == 'T') : $content = preg_replace('/((http|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?)/', '<a href="\1" target="_blank">\1</a>', $content); endif;

  $content = kingkongboard_xssfilter($content);
  $content = html_entity_decode($content);
  $content = apply_filters('the_content', $content);
  $content = do_shortcode($content);
  
  if($board_thumbnail_input == "T"){
    $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($entry_id), "full" );
    ($thumbnail && !empty($thumbnail)) ? $thumbnail_image = '<img src="'.$thumbnail[0].'" style="max-width:70%; height:auto"><br><br>' : $thumbnail_image = null;
  } else {
    $thumbnail          = null;
    $thumbnail_image    = null;
  }
  $table_id = apply_filters('kkb_read_table_id', 'kingkongboard-read-table', $this->board_id); 
?>

<div id="<?php echo $table_id;?>">
<?php 
  ob_start();
?>
  <div class="title-section">
    <h1 class="kkb-read-h1"><?php echo $this->get_title($entry_id); ?></h1>
    <div class="regist-date"><h2 class="kkb-read-h2"><?php echo date("Y.m.d H:i", $date);?></h2></div>
  </div>
<?php
  $title_section = ob_get_contents();
  ob_end_clean();
  echo apply_filters('kkb_read_title_section', $title_section, $this->board_id, $entry_id);
  ob_start();
?>
  <div class="writer-section">
    <div class="regist-writer"><h2 class="kkb-read-h2"><?php echo $writer;?></h2></div>
    <div class="regist-info">
      <span><?php _e('조회 수', 'kingkongboard');?><b><?php echo $hit;?></b></span>
    </div>
  </div>
<?php
  $writer_section = ob_get_contents();
  ob_end_clean();
  echo apply_filters('kkb_read_writer_section', $writer_section, $this->board_id, $entry_id);
  echo apply_filters('kkb_read_content_outer_before', null, $this->board_id, $entry_id);
  ob_start();
?>
  <div class="content-section">
<?php
  echo apply_filters('kkb_read_content_inner_before', null, $this->board_id, $entry_id);
  if($thumbnail_image != null){
  echo $thumbnail_image.$content;
  } else {
    echo $content;
  }
  echo apply_filters('kkb_read_content_inner_after', null, $this->board_id, $entry_id);

  $tags = wp_get_post_terms($entry_id, 'kkb_tag', array('fields' => 'names'));

  if($tags){
?>
    <div class="content-tags">
      <span class="kkb-list-icon kkblc-tag"></span>
<?php
  foreach($tags as $tag){
    $tag_url = add_query_arg(array('kkb_keyword' => $tag, 'srchtype' => 'tag'), get_the_permalink());
?>
      <a href="<?php echo $tag_url;?>"><span class="each-tag"><?php echo $tag;?></span></a>
<?php
  }
?>
    </div>
<?php
  }
  if($attached){
?>
    <div class="content-section-attach">
<?php
    ob_start();
    require_once KINGKONGBOARD_ABSPATH.'includes/view.read.attached.php';
    $attach_content = ob_get_contents();
    ob_end_clean();
    echo apply_filters('kkb_read_attach_content', $attach_content, $this->board_id, $entry_id, $attached);
?>
    </div>
<?php
  }
?>
  </div>
<?php
  $entry_content = ob_get_contents();
  ob_get_clean();
  echo apply_filters('kkb_read_content_section', $entry_content, $this->board_id, $entry_id);  
  echo apply_filters('kkb_read_content_outer_after', null, $this->board_id, $entry_id);
  ob_start();
  require_once KINGKONGBOARD_ABSPATH.'includes/view.read.buttons.php';
  $buttons = ob_get_contents();
  ob_end_clean();
  echo apply_filters('kkb_read_buttons', $buttons, $entry_id);

  echo apply_filters('kkb_read_comment_before', null, $this->board_id, $entry_id);
  if($config->commentuse == 'T') : echo do_shortcode('[kingkong_board_comment id='.$entry_id.']'); endif;

?>
  <div style="padding:10px 0 50px 0">
<?php
  if($board_read_under == 'T'){
    require_once(KINGKONGBOARD_ABSPATH . "includes/view.read.loop.php");
  } 
?>
  </div>
</div>
<?php
}
?>