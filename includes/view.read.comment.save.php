<?php
list($path) = explode(DIRECTORY_SEPARATOR.'wp-content', dirname(__FILE__).DIRECTORY_SEPARATOR);
include $path.DIRECTORY_SEPARATOR.'wp-load.php';

$referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';

if(!stristr($referer, $host)) wp_die('KINGKONG BOARD : '.__('지금 페이지는 외부 접근이 차단되어 있습니다.', 'kingkongboard'));
if(!isset($_POST)) wp_die('KINGKONG BOARD : '.__('잘못된 접근 입니다.', 'kingkongboard'));

include_once(ABSPATH . 'wp-includes/pluggable.php');

$controller  = new kkbController();
$post_id     = $controller->getMeta($_POST['entry_id'], 'guid');
$board_id    = $controller->getMeta($_POST['entry_id'], 'board_id');

if(isset($_POST['g-recaptcha-response'])){
  $cpt_response = sanitize_text_field($_POST['g-recaptcha-response']);
  $response     = kingkongboard_captcha_initialize($board_id, 'comment', $cpt_response);
} else {
  $response = true;
}

if($response == true){
  $kkb_comment = new kkbComment();
  $kkb_comment->kkb_comment_save($_POST);
}

$iframe_use  = get_post_meta($board_id, 'kkb_iframe_use', true);
$return_args = array('view' => 'read', 'id' => $_POST['entry_id']);
if($iframe_use == 'T'){
  $return_args['kkb_mod'] = 'iframe';
}

$return_path = add_query_arg( $return_args, get_the_permalink($post_id) );
header( "Location: ".$return_path );
?> 