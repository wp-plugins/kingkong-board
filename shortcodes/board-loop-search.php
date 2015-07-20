<?php
  $searchContent = null;
  $searchClass = apply_filters('kkb_search_extra_class', null, $board_id);
?>
<div class="kingkongboard-search <?php echo $searchClass;?>">
  <form method="POST" action="<?php echo get_the_permalink($post->ID);?>">
<?php echo apply_filters('kkb_board_loop_search_box_before', null, $board_id); ?>

  <div class="kingkongboard-search-inner-div">
    <table class="kingkongboard-search-table">
      <tr>
<?php
  if($board_sections){
?>
        <td class="kingkongboard-search-td-section">
          <div class="select_box">
            <select name="kkb_section">
              <?php echo $section_value; ?>
            </select>
          </div>
        </td>
<?php
  }
?>
        <td class="kingkongboard-search-td-input">
          <input type="text" name="kkb_keyword" value="<?php echo $search_keyword;?>" style="max-height:26px">
        </td>
        <td style="text-align:left; padding-right:0;padding-left:10px">
          <button type="submit" class="kingkongboard-search-button <?php echo kkb_button_classer($board_id);?>" style="height:26px"><?php echo kkb_button_text($board_id, 'search');?></button>
        </td>
      </tr>
    </table>
  </div>
  </form>
</div>
<input type="hidden" name="page_permalink" value="'.get_the_permalink($post->ID).'">