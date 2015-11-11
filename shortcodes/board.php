<?php
if(!isset($_SESSION)){
  session_start();
}
add_shortcode("kingkong_board","kingkong_board");
function kingkong_board($attr){
  global $post, $current_user;

  do_action('kingkong_board_before', $attr);
  
  $slug               = $attr[0];
  $board_id           = get_kingkong_board_id_by_slug($slug);
  $board_skin         = get_post_meta($board_id, 'board_skin', true);
  $board_extension    = get_post_meta($board_id, 'board_extension', true);
  $iframe_use         = get_post_meta($board_id, 'kkb_iframe_use', true);
  (isset($attr[1])) ? $iframe_mode = 'iframe' : $iframe_mode = null;
  if($iframe_use == 'T'){

    do_action('kingkong_board_iframe_before');

    if(isset($_GET['kkb_mod'])){
      $mod = sanitize_text_field($_GET['kkb_mod']);
      if($mod == 'iframe'){
        ob_start();
        require_once( KINGKONGBOARD_ABSPATH.'data/iframe-board.php');
        $content = ob_get_contents();
        ob_get_clean();
      }
      return $content;      
    } else {
    ob_start();
    require_once( KINGKONGBOARD_ABSPATH.'includes/iframe.php');
    $content = ob_get_contents();
    ob_get_clean();
    return $content;
    }
  } else {
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
      return $kkbContent;
    } else {
      return apply_filters('kkb_not_exist_board_message', __('킹콩보드 해당 게시판이 존재하지 않습니다.', 'kingkongboard'));
    }
  }
}
?>