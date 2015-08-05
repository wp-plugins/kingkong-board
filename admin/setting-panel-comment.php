<?php
  function KingkongBoard_Setting_Panel_Comment(){

    $thumbnail                = "";
    $background_color         = "";
    $background_border        = "";
    $background_border_color  = "";
    $writer_color             = "";
    $writer_font_weight       = "";
    $writer_font_size         = "";
    $date_format              = "";
    $date_color               = "";
    $date_font_size           = "";
    $content_color            = "";
    $comment_options          = null;
    if(isset($_GET['id'])){
      $board_id = $_GET['id'];
      $board_comment = get_post_meta($board_id, 'board_comment', true);
      $comment_options = get_post_meta($board_id, 'kingkongboard_comment_options', true);
      if($comment_options){
        $comment_options = unserialize($comment_options);
      }
    } else {
      $board_comment = "T";
    }

    if(!$comment_options){
      $comment_options = array(
        'thumbnail' => 'T',
        'background' => array(
          'color'         => '#f9f9f9',
          'border'        => '1px',
          'border_color'  => '#f1f1f1'
        ),
        'writer'     => array(
          'color'       => '#424242',
          'font_weight' => 'bold',
          'font_size'   => '12px'
        ),
        'date'       => array(
          'format'      => 'Y/m/d H:i',
          'color'       => '#666666',
          'font_size'   => '11px'
        ),
        'content'    => array(
          'color'       => '#424242'
        )
      );      
    }

    if($comment_options['thumbnail'] == "T"){
      $thumbnail = "checked";
    }

    if($comment_options['writer']['font_weight'] == "bold"){
      $writer_font_weight = "checked";
    } else {
      $writer_font_weight = "";
    }

    $comment_enable   = "";
    $comment_disable  = "";

    switch($board_comment){
      case "T" :
        $comment_enable = "checked";
      break;

      case "F" :
        $comment_disable = "checked";
      break;

      default :
        $comment_enable = "checked";
      break;
    }
?>

    <table>
      <?php do_action('kkb_admin_panel_comment_use_before'); ?>
      <tr>
        <th><?php echo __('댓글 사용', 'kingkongboard');?> :</th>
        <td>
          <input type="radio" name="board_comment" value="T" <?php echo $comment_enable;?>> <?php echo __('사용함', 'kingkongboard');?> <input type="radio" name="board_comment" value="F" <?php echo $comment_disable;?>> <?php echo __('사용안함', 'kingkongboard');?>
          <div class="description-container">
            <span class="description"><?php echo __('게시판에 댓글기능 사용 여부를 지정합니다.', 'kingkongboard');?></span>
          </div>                
        </td>
      </tr>
      <?php do_action('kkb_admin_panel_comment_use_after'); ?>
    </table>
<script>
  jQuery(document).ready(function(){
    jQuery("[name=setting_comment_date_format]").val("<?php echo $comment_options['date']['format'];?>");
  });
</script>
<?php
  }
?>