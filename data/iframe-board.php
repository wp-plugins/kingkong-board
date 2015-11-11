<?php if(!defined('ABSPATH')) exit;?>
<!DOCTYPE html>
<html <?php language_attributes()?>>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width">
  <title><?php echo __('WordPress')?> Kingkong Board</title>
  <?php wp_head(); ?>
  <style>
  html{margin-top:0px !important;}
  </style>
</head>
  <body>
<?php
  add_filter('show_admin_bar', '__return_false');
  do_action('kingkongboard_loop_before', $board_id, $board_skin, $board_extension);

  $board_captcha      = get_post_meta($board_id, 'board_captcha', true);
  $board_captcha_key  = get_post_meta($board_id, 'board_captcha_key', true);
  $board_reply_use    = get_post_meta($board_id, 'kkb_reply_use', true);
  $captcha_site_key   = null;
  $captcha_secret_key = null;

  if($board_captcha_key){
    $keys = unserialize($board_captcha_key);
    $captcha_site_key   = $keys['site_key'];
    $captcha_secret_key = $keys['secret_key'];
  }

  if($board_captcha == "T" && $captcha_site_key != null && $captcha_secret_key != null){
    wp_register_script( "recaptcha", 'https://www.google.com/recaptcha/api.js' );
    wp_enqueue_script( "recaptcha" );    
  }
    
  if($board_id){
    include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    require_once( KINGKONGBOARD_ABSPATH.'class/class.board.view.php' );
    $boardView          = new kkboard_view($board_id);
      
    ob_start();
    require_once( KINGKONGBOARD_ABSPATH.'includes/view.php' );
    $kkbContent = ob_get_contents();
    ob_end_clean();
    echo $kkbContent;
  } else {
    return apply_filters('kkb_not_exist_board_message', __('킹콩보드 해당 게시판이 존재하지 않습니다.', 'kingkongboard'));
  }
?>
  <script>
  function kingkongboard_iframe_resize(){
    var kingkongboard = document.getElementById('kingkongboard-wrapper');
    if(kingkongboard.offsetHeight != 0 && parent.document.getElementById("kingkongboard-iframe")){
      parent.document.getElementById("kingkongboard-iframe").style.height = kingkongboard.offsetHeight + "px";
    }
  }
  setInterval(function(){
    kingkongboard_iframe_resize();
  }, 100);
  </script>
<?php
  wp_footer();
?>
  </body>
<html>
