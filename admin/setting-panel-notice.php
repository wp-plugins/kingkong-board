<?php
  function KingkongBoard_Setting_Panel_Notice(){

    $kkb_notice_entry     = "";
    $kkb_notice_comment   = "";
    $kkb_notice_emails    = "";

    if(isset($_GET['id'])){
      $board_id = sanitize_text_field($_GET['id']);
      $kkb_notice_entry   = get_post_meta($board_id, 'kingkongboard_notice_entry', true);
      $kkb_notice_comment = get_post_meta($board_id, 'kingkongboard_notice_comment', true);
      $kkb_notice_emails  = get_post_meta($board_id, 'kingkongboard_notice_emails', true);
    }

?>

    <table>
      <tr>
        <th>알림 요소 :</th>
        <td style="vertical-align:top; padding-top:0">
          <table>
            <tr>
              <td style="padding-top:0">
                <input type="checkbox" name="kkb_notice_entry" value="checked" <?php echo $kkb_notice_entry;?>>신규 글
                <div class="description-container">
                  <span class="description">글이 등록되면 등록된 이메일로 알림을 보냅니다.</span>
                </div>
              </td>
            </tr>
            <tr>
              <td>
                <input type="checkbox" name="kkb_notice_comment" value="checked" <?php echo $kkb_notice_comment;?>>신규 댓글
                <div class="description-container">
                  <span class="description">댓글이 등록되면 등록된 이메일로 알림을 보냅니다.</span>
                </div>
              </td>
            </tr>
          </table>
        </td>
      </tr>
      <tr>
        <td colspan="2"><hr></td>
      </tr>
      <tr>
        <th>이메일 알림 :</th>
        <td>
          <input type="text" class="kkb-input" name="kkb_notice_emails" style="max-width:300px; width:100%" value="<?php echo $kkb_notice_emails;?>">
          <div class="description-container">
            <span class="description">최신글이 등록되면 입력된 이메일로 알려드립니다. 여러명을 입력하실 경우 콤마(,)로 구분됩니다. 서버 환경에 따라서 메일이 전송되지 못 할 수도 있습니다.</span>
          </div>                
        </td>
      </tr>
    </table>

<?php
  }
?>