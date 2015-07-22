<?php
list($path) = explode(DIRECTORY_SEPARATOR.'wp-content', dirname(__FILE__).DIRECTORY_SEPARATOR);
include $path.DIRECTORY_SEPARATOR.'wp-load.php';

$referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';

//header("Content-Type: text/html; charset=UTF-8");
if(!stristr($referer, $host)) wp_die('KINGKONG BOARD : '.__('지금 페이지는 외부 접근이 차단되어 있습니다.', 'kingkongboard'));
if(!isset($_GET['aid']) or !isset($_GET['eid'])) wp_die('KINGKONG BOARD : '.__('잘못된 접근 입니다.', 'kingkongboard'));

$attach_id   = $_GET['aid'];
$entry_id    = $_GET['eid'];
require_once KINGKONGBOARD_ABSPATH.'class/class.download.php';
$downloader  = new kkbDownloader($entry_id, $attach_id);
echo $downloader->download();
?>