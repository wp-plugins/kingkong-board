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

  $backPath = add_query_arg( array('pageid' => $page), get_the_permalink());
?>
<div class="kingkongboard-search <?php echo $searchClass;?>">
  <form method="POST" action="<?php echo get_the_permalink($post->ID);?>">
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
        <button class="kkb-list-icon kkblc-search" style="text-indent:-100px; border:0; position:relative; top:-2px; z-index:2; outline:0; cursor:pointer"></button>
        <label class="btn-kkb-search-label">검색</label>
        <input type="text" name="kkb_keyword" class="kkb-keyword">
      </span>
      <span class="kkb-section-span">
        <select name="srchtype">
          <option value="content">제목+내용</option>
          <option value="writer">작성자명</option>
          <option value="id">작성자아이디</option>
          <option value="tag">태그검색</option>
        </select>
      </span>
      <input type="hidden" name="kkb_section" value="<?php echo $section;?>">
    </div>
  </form>
</div>
<input type="hidden" name="page_permalink" value="'.get_the_permalink($post->ID).'">