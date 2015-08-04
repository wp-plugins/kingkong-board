<?php
(isset($_POST['kkb_keyword'])) ? $keyword  = sanitize_text_field($_POST['kkb_keyword']) : $keyword = null;
(isset($_POST['kkb_section'])) ? $section  = sanitize_text_field($_POST['kkb_section']) : $section = null;
(isset($_GET['kkb_keyword']) && empty($keyword)) ? $keyword = sanitize_text_field($_GET['kkb_keyword']) : $keyword = $keyword;

if(isset($_GET['kkb_section'])) $section = sanitize_text_field($_GET['kkb_section']);

(empty($section)) ? $section_all_checked = 'active' : $section_all_checked = null;
(empty($section)) ? $all_url = null : $all_url = 'href="'.get_the_permalink().'"';

$board_sections = get_post_meta($board_id, 'board_sections', true);
  if($board_sections){
    $board_sections = maybe_unserialize($board_sections);
?>
  <div class="kkb-section-button-wrapper">
    <a <?php echo $all_url;?> class="<?php echo kkb_button_classer($board_id);?> <?php echo $section_all_checked;?>"><?php _e('전체', 'kingkongboard');?></a>
<?php
    foreach($board_sections as $esection){
      $link_url = add_query_arg( array( 'kkb_section' => $esection), get_the_permalink());
      if($section == $esection){
?>
    <a class="<?php echo kkb_button_classer($board_id);?> active"><?php echo $esection;?></a>
<?php
      } else {
?>
    <a href="<?php echo $link_url;?>" class="<?php echo kkb_button_classer($board_id);?>"><?php echo $esection;?></a>
<?php
      }
    }
?>
  </div>
<?php
  }
?>