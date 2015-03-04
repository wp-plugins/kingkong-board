<?php

  $board_id = sanitize_text_field($_GET['id']);

  if($board_id){

    $board_title        = get_the_title($board_id);
    $board_slug         = get_post_meta($board_id, 'kingkongboard_slug', true);
    $board_shortcode    = get_post_meta($board_id, 'kingkongboard_shortcode', true);
    $board_rows         = get_post_meta($board_id, 'kingkongboard_rows', true);
    $board_search       = get_post_meta($board_id, 'kingkongboard_search', true);
    $board_editor       = get_post_meta($board_id, 'kingkongboard_editor', true);
    $board_thumb_dp     = get_post_meta($board_id, 'kingkongboard_thumbnail_dp', true);
    $board_thumb_input  = get_post_meta($board_id, 'kingkongboard_thumbnail_input', true);
    $board_thumb_upload = get_post_meta($board_id, 'thumbnail_upload', true);
    $board_file_upload  = get_post_meta($board_id, 'file_upload', true);

    $thumb_con_include  = "";
    $thumb_con_exclude  = "";

    if($board_thumb_dp == "display"){
      $thumb_dp_checked = "checked";
    } else {
      $thumb_dp_checked = "";
    }

    switch($board_thumb_input){
      case "T" :
        $thumb_con_include = "checked";
      break;

      case "F" :
        $thumb_con_exclude = "checked";
      break;

      default :
        $thumb_con_include = "checked";
      break;
    }

?>
  <div class="head-area">
    <div style="float:left; position:relative; top:10px; margin-right:10px">
      <a href="?page=KingkongBoard"><img src="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/assets/images/logo-kingkongboard.png" style="width:220px; height:auto"></a>
    </div>
    <div style="float:left; font-size:18px; margin-top:14px; margin-left:20px">게시판 설정 변경</div>
    <div style="float:right; position:relative; top:8px">
      <div class="fb-like" data-href="https://facebook.com/superrocketer" data-layout="button_count" data-action="like" data-show-faces="true" data-share="true" style="position:relative; top:-10px; margin-right:10px"></div>
      <a href="http://superrocket.io" target="_blank"><img src="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/assets/images/superrocket-symbol.png" style="height:34px; width:auto" class="superrocket-logo" alt="superrocket.io"></a>
      <a href="https://www.facebook.com/superrocketer" target="_blank"><img src="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/assets/images/icon-facebook.png" style="height:34px; width:auto" class="superrocket-logo" alt="facebook"></a>
      <a href="https://instagram.com/superrocketer/" target="_blank"><img src="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/assets/images/icon-instagram.png" style="height:34px; width:auto" class="superrocket-logo" alt="instagram"></a>
    </div>
  </div>
  <div class="notice-toolbox-wrapper"></div>
  <form id="kkb-create-board-form">
    <input type="hidden" name="kkb_type" value="modify">
    <input type="hidden" name="board_id" value="<?php echo $board_id;?>">
    <div class="settings-panel">
      <div class="settings-panel-left">
        <div class="settings-title">Board Settings</div>
        <div class="settings-table-wrapper">
          <table>
            <tr>
              <th>게시판 명:</th>
              <td>
                <input type="text" class="kkb-input" name="kkb_board_name" value="<?php echo $board_title;?>"> <span class="kkb-required">*</span>
                <div class="description-container">
                  <span class="description">보여질 게시판의 이름입니다. 예 : 공지사항</span>
                </div>
              </td>
            </tr>
            <tr>
              <th>게시판 슬러그:</th>
              <td>
                <input type="text" class="kkb-input" name="kkb_board_slug" value="<?php echo $board_slug;?>" disabled> <span class="kkb-required">*</span>
                <div class="description-container">
                  <span class="description">게시판 슬러그는 초기 설정값에서 변경할 수 없습니다.</span>
                </div>
              </td>
            </tr>
            <tr>
              <th>게시판 숏코드:</th>
              <td>
                <input type="text" class="kkb-input" name="kkb_board_shortcode" value="<?php echo $board_shortcode;?>" disabled>
                <div class="description-container">
                  <span class="description">이 숏코드를 원하시는 Post 나 Page 에 붙여넣으면 노출 됩니다.</span>
                </div>
              </td>
            </tr>
            <tr>
              <th>게시물 표시:</th>
              <td>
                <input type="text" class="kkb-input" name="kkb_board_rows" value="<?php echo $board_rows;?>">
                <div class="description-container">
                  <span class="description">한 페이지에 보여지는 게시물 숫자를 의미합니다.</span>
                </div>
              </td>
            </tr>
            <tr>
              <th>글 작성 에디터:</th>
              <td>
                <select class="kkb-input-select" name="kkb_board_editor">
                  <option value="textarea">Textarea 사용</option>
                  <option value="wp_editor">WP 내장 에디터 사용</option>
<?php

  $path = TEMPLATEPATH."/kingkongboard/editor";
  $dirs = array_filter(glob($path . '/*' , GLOB_ONLYDIR), 'is_dir');
  foreach($dirs as $dir){
    if (file_exists($dir."/kingkongboard-editor.php")) {
      include($dir."/kingkongboard-editor.php");
      echo "<option value='".$Slug."'>".$EditorName."</option>";
    }
  } 

?>
                </select>
                <div class="description-container">
                  <span class="description">본문 작성 에디터를 설정 합니다.</span>
                </div>
              </td>
            </tr>
            <tr>
              <td colspan="2"><hr></td>
            </tr>
            <tr>
              <th>썸네일</th>
              <td>
                <input type="checkbox" name="kkb_board_thumbnail_display" value="display" <?php echo $thumb_dp_checked;?>> 리스트에 썸네일을 노출 합니다.
                <div class="description-container">
                  <span class="description">게시판 리스트에 썸네일 노출 여부를 지정합니다.</span>
                </div>                
              </td>
            </tr>
            <tr>
              <th>썸네일 본문</th>
              <td>
                <input type="radio" name="kkb_board_thumbnail_input_content" value="T" <?php echo $thumb_con_include;?>> 본문포함 <input type="radio" name="kkb_board_thumbnail_input_content" value="F" <?php echo $thumb_con_exclude;?>> 본문미포함
                <div class="description-container">
                  <span class="description">본문포함을 체크할 경우 썸네일이 본문 상단에 자동으로 삽입 됩니다.</span>
                </div>                
              </td>
            </tr>
            <tr>
              <th>검색 필터링:</th>
              <td>
<?php
  if($board_search){

    $board_filter_on  = "";
    $board_filter_off = "";

    switch($board_search){
      case "T" :
        $board_filter_on = "checked";
      break;

      case "F" :
        $board_filter_off = "checked";
      break;
    }
  }
  $board_thumb_checked = null;
  $board_file_checked  = null;
  if($board_thumb_upload){
    if($board_thumb_upload == "T"){
      $board_thumb_checked = "checked";
    }
  }

  if($board_file_upload){
    if($board_file_upload == "T"){
      $board_file_checked = "checked";
    }
  }
?>
                <input type="radio" name="kkb_board_search_filter" value="T" <?php echo $board_filter_on;?>>포함 <input type="radio" name="kkb_board_search_filter" value="F" <?php echo $board_filter_off;?>>미포함
                <div class="description-container">
                  <span class="description">워드프레스 기본 검색 포함여부를 설정합니다.</span>
                </div>
              </td>
            </tr>
            <tr>
              <th>첨부파일 설정:</th>
              <td>
                <input type="checkbox" name="kkb_board_thumbnail_upload" value="T" <?php echo $board_thumb_checked;?>>썸네일 업로드 사용 <input type="checkbox" name="kkb_board_file_upload" value="T" <?php echo $board_file_checked;?>>첨부파일 업로드 사용
                <div class="description-container">
                  <span class="description">첨부파일 업로드 (썸네일 포함) 노출 여부를 지정합니다.</span>
                </div>                
              </td>
            </tr>
          </table>
          <br><br>`
          <button type="button" class="button-kkb kkbgreen button-kkb-create-board"><i class="kkb-icon kkb-icon-setting"></i>수정 완료</button>
          <a href="?page=KingkongBoard" class="button-kkb kkbred"><i class="kkb-icon kkb-icon-close" style="position:relative; top:5px"></i>취소</a>
        </div>
      </div>
      <div class="settings-panel-right">
        <?php do_action('kingkong_board_columns'); ?>
      </div>
    </div>
  </form>
  <script>
    jQuery(document).ready(function(){
      jQuery("[name=kkb_board_editor]").val("<?php echo $board_editor;?>");
    });
  </script>
<?php
  }
?>