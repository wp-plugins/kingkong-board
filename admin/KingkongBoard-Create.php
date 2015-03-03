  <div class="head-area">
    <div style="float:left; position:relative; top:10px; margin-right:10px">
      <a href="?page=KingkongBoard"><img src="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/assets/images/logo-kingkongboard.png" style="width:220px; height:auto"></a>
    </div>
    <div style="float:left; font-size:18px; margin-top:14px; margin-left:20px">신규 게시판 생성</div>
    <div style="float:right; position:relative; top:8px">
      <div class="fb-like" data-href="https://facebook.com/superrocketer" data-layout="button_count" data-action="like" data-show-faces="true" data-share="true" style="position:relative; top:-10px; margin-right:10px"></div>
      <a href="http://superrocket.io" target="_blank"><img src="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/assets/images/superrocket-symbol.png" style="height:34px; width:auto" class="superrocket-logo" alt="superrocket.io"></a>
      <a href="https://www.facebook.com/superrocketer" target="_blank"><img src="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/assets/images/icon-facebook.png" style="height:34px; width:auto" class="superrocket-logo" alt="facebook"></a>
      <a href="https://instagram.com/superrocketer/" target="_blank"><img src="<?php echo KINGKONGBOARD_PLUGINS_URL;?>/assets/images/icon-instagram.png" style="height:34px; width:auto" class="superrocket-logo" alt="instagram"></a>
    </div>
  </div>
  <div class="notice-toolbox-wrapper"></div>
  <form id="kkb-create-board-form">
    <input type="hidden" name="kkb_type" value="create">
    <input type="hidden" name="board_id">
    <div class="settings-panel">
      <div class="settings-panel-left">
        <div class="settings-title">Board Settings</div>
        <div class="settings-table-wrapper">
          <table>
            <tr>
              <th>게시판 명:</th>
              <td>
                <input type="text" class="kkb-input" name="kkb_board_name"> <span class="kkb-required">*</span>
                <div class="description-container">
                  <span class="description">보여질 게시판의 이름입니다. 예 : 공지사항</span>
                </div>
              </td>
            </tr>
            <tr>
              <th>게시판 슬러그:</th>
              <td>
                <input type="text" class="kkb-input" name="kkb_board_slug"> <span class="kkb-required">*</span>
                <div class="description-container">
                  <span class="description">게시판 슬러그는 게시판을 붙여넣을 때 필요합니다. 영문으로 작성하시기 바랍니다. 예 : notice</span>
                </div>
              </td>
            </tr>
            <tr>
              <th>게시판 숏코드:</th>
              <td>
                <input type="text" class="kkb-input" name="kkb_board_shortcode">
                <div class="description-container">
                  <span class="description">이 숏코드를 원하시는 Post 나 Page 에 붙여넣으면 노출 됩니다.</span>
                </div>
              </td>
            </tr>
            <tr>
              <th>게시물 표시:</th>
              <td>
                <input type="text" class="kkb-input" name="kkb_board_rows">
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
                <input type="checkbox" name="kkb_board_thumbnail_display" value="display"> 리스트에 썸네일을 노출 합니다.
                <div class="description-container">
                  <span class="description">게시판 리스트에 썸네일 노출 여부를 지정합니다.</span>
                </div>                
              </td>
            </tr>
            <tr>
              <th>썸네일 본문</th>
              <td>
                <input type="radio" name="kkb_board_thumbnail_input_content" value="T" checked> 본문포함 <input type="radio" name="kkb_board_thumbnail_input_content" value="F"> 본문미포함
                <div class="description-container">
                  <span class="description">본문포함을 체크할 경우 썸네일이 본문 상단에 자동으로 삽입 됩니다.</span>
                </div>                
              </td>
            </tr>
            <tr>
              <th>검색 필터링:</th>
              <td>
                <input type="radio" name="kkb_board_search_filter" value="T" checked>포함 <input type="radio" name="kkb_board_search_filter" value="F">미포함
                <div class="description-container">
                  <span class="description">워드프레스 기본 검색 포함여부를 설정합니다.</span>
                </div>
              </td>
            </tr>
            <tr>
              <th>첨부파일 설정:</th>
              <td>
                <input type="checkbox" name="kkb_board_thumbnail_upload" value="T" checked>썸네일 업로드 사용 <input type="checkbox" name="kkb_board_file_upload" value="T" checked>첨부파일 업로드 사용
                <div class="description-container">
                  <span class="description">첨부파일 업로드 (썸네일 포함) 노출 여부를 지정합니다.</span>
                </div>                
              </td>
            </tr>
          </table>
          <br><br>
          <button type="button" class="button-kkb kkbgreen button-kkb-create-board"><i class="kkb-icon kkb-icon-setting"></i>게시판 생성</button>
          <a href="?page=KingkongBoard" class="button-kkb kkbred"><i class="kkb-icon kkb-icon-close" style="position:relative; top:5px"></i>취소</a>
        </div>
      </div>
      <div class="settings-panel-right">
        <?php do_action('kingkong_board_columns'); ?>
      </div>
    </div>
  </form>


