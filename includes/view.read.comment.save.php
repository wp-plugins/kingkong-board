<?php
list($path) = explode(DIRECTORY_SEPARATOR.'wp-content', dirname(__FILE__).DIRECTORY_SEPARATOR);
include $path.DIRECTORY_SEPARATOR.'wp-load.php';

$referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';

if(!stristr($referer, $host)) wp_die('KINGKONG BOARD : '.__('지금 페이지는 외부 접근이 차단되어 있습니다.', 'kingkongboard'));
if(!isset($_POST)) wp_die('KINGKONG BOARD : '.__('잘못된 접근 입니다.', 'kingkongboard'));

include_once(ABSPATH . 'wp-includes/pluggable.php');
  $kkb_comment = new kkbComment();
  $kkb_comment->kkb_comment_save($_POST);

  $controller  = new kkbController();
  $post_id     = $controller->getMeta($_POST['entry_id'], 'guid');
  $return_path = add_query_arg( array('view' => 'read', 'id' => $_POST['entry_id']), get_the_permalink($post_id) );
  header( "Location: ".$return_path );
?>