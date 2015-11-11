<?php
  $searchContent  = null;
  $searchClass    = apply_filters('kkb_search_extra_class', null, $board_id);
  $board_sections = get_post_meta($board_id, 'board_sections', true);
  $section_value  = null;
  (isset($_GET['pageid'])) ? $page = sanitize_text_field($_GET['pageid']) : $page = 1;
  (isset($_POST['kkb_keyword'])) ? $keyword      = sanitize_text_field($_POST['kkb_keyword']) : $keyword     = null;
  (isset($_POST['kkb_section'])) ? $section      = sanitize_text_field($_POST['kkb_section']) : $section     = null;
  (isset($_POST['srchtype']))  ? $search_type  = sanitize_text_field($_POST['srchtype'])    : $search_type = 'content';
  (isset($_GET['kkb_keyword']) && empty($keyword)) ? $keyword = sanitize_text_field($_GET['kkb_keyword']) : $keyword = $keyword;
  (isset($_GET['srchtype']) && empty($search_type)) ? $search_type = sanitize_text_field($_GET['srchtype']) : $search_type = $search_type;

  if(isset($_GET['kkb_section'])) $section = sanitize_text_field($_GET['kkb_section']);

  if($board_sections){
    $board_sections = maybe_unserialize($board_sections);
    $section_value .= '<option value="all">'.__('전체', 'kingkongboard').'</option>';
    foreach($board_sections as $esection){
      ($section == $esection) ? $section_value .= '<option value="'.$esection.'" selected>분류:'.$esection.'</option>' : $section_value .= '<option value="'.$esection.'">분류:'.$esection.'</option>';
    }
  }
  $page_args = apply_filters('kkb_read_arg_after', array(), $board_id);
  $back_args = apply_filters('kkb_read_arg_after', array('pageid' => $page), $board_id);
  $page_path = add_query_arg($page_args, get_the_permalink($post->ID));
  $backPath  = add_query_arg($back_args, get_the_permalink());
?>
<div class="kingkongboard-search <?php echo $searchClass;?>">
  <form method="POST" action="<?php echo $page_path;?>">
    <div class="kkb-search-buttons">
<?php
  if($keyword || $section){
?>
      <a href="<?php echo $backPath;?>" class="<?php echo kkb_button_classer($board_id);?>"><?php _e('돌아가기', 'kingkongboard');?></a>
<?php
  }
?>
<?php echo apply_filters('kkb_board_loop_search_box_before', null, $board_id); ?>
      <span class="btn-kkb-search <?php echo kkb_button_classer($board_id);?>">
        <button type="button" class="kkb-list-icon kkblc-search" style="text-indent:-100px; border:0; position:relative; top:-2px; z-index:2; outline:0; cursor:pointer"></button>
        <label class="btn-kkb-search-label"><?php _e('검색', 'kingkongboard');?></label>
        <input type="text" name="kkb_keyword" class="kkb-keyword">
      </span>
      <span class="kkb-section-span">
        <select name="srchtype" class="styled">
          <option value="content"><?php _e('제목+내용', 'kingkongboard');?></option>
          <option value="writer"><?php _e('작성자명', 'kingkongboard');?></option>
          <option value="id"><?php _e('작성자 아이디', 'kingkongboard');?></option>
          <option value="tag"><?php _e('태그검색', 'kingkongboard');?></option>
        </select>
      </span>
      <input type="hidden" name="kkb_section" value="<?php echo $section;?>">
    </div>
  </form>
</div>
<input type="hidden" name="page_permalink" value="<?php echo $page_path;?>">