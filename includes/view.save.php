<?php
list($path) = explode(DIRECTORY_SEPARATOR.'wp-content', dirname(__FILE__).DIRECTORY_SEPARATOR);
include $path.DIRECTORY_SEPARATOR.'wp-load.php';

$referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
$host = isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'';

if(!stristr($referer, $host)) wp_die('KINGKONG BOARD : '.__('지금 페이지는 외부 접근이 차단되어 있습니다.', 'kingkongboard'));
if(!isset($_POST)) wp_die('KINGKONG BOARD : '.__('잘못된 접근 입니다.', 'kingkongboard'));

$board_id     = sanitize_text_field($_POST['board_id']);
$post_id      = sanitize_text_field($_POST['post_id']);
$iframe_use   = get_post_meta($board_id, 'kkb_iframe_use', true);

(isset($_POST['page_id'])) ? $pageid = sanitize_text_field($_POST['page_id']) : $pageid = 1; 
$return_args = array('pageid' => $pageid);

if($iframe_use == 'T'){
  $return_args['kkb_mod'] = 'iframe';
}

$return_path = add_query_arg( $return_args, get_the_permalink($post_id));

if(isset($_POST['g-recaptcha-response'])){
  $cpt_response = sanitize_text_field($_POST['g-recaptcha-response']);
  $response     = kingkongboard_captcha_initialize($board_id, 'entry', $cpt_response);
} else {
  $response = true;
}

if($response == false){
  $result['status'] = 'failed';
  $result['message'] = __('자동글 방지에 체크하셔야 합니다.', 'kingkongboard');
} else {

  $Board = new kkbController();

  if($_POST['write_type'] == 'write' || $_POST['write_type'] == 'reply'){
    $entry_id   = $Board->writeEntry($board_id, $_POST, 'basic');

    if($entry_id && is_numeric($entry_id)){
      $upload   = $Board->fileUploader($entry_id, $_POST, $_FILES);
      if($upload){
        if(is_array($upload)) {
          $message = $upload['message'];
          $result['status']   = 'failed';
          $result['message']  = $message;
        } else {
          $result['status']   = 'success';
          $result['url']      = get_the_permalink($post_id);
        }
      } else {
        $result['status']     = 'success';
        $result['url']        = get_the_permalink($post_id);
      }
    } else {
      (is_array($entry_id)) ? $message = $entry_id['message'] : $message = $entry_id;
      $result['status'] = 'failed';
      $result['message'] = $message;
    }
  }

  if($_POST['write_type'] == 'modify'){
    $entry_id = $Board->writeModify($_POST, 'basic');
    if($entry_id && is_numeric($entry_id)){
      wp_reset_postdata();
      $upload = $Board->fileUploader($entry_id, $_POST, $_FILES);
      if($upload){
        if(is_array($upload)) {
          $message = $upload['message'];
          $result['status']   = 'failed';
          $result['message']  = $message;
        } else {
          $result['status']   = 'success';
          $result['url']      = get_the_permalink($post_id);
        }
      } else {
        $result['status']     = 'success';
        $result['url']        = get_the_permalink($post_id);
      }
    } else {
      (is_array($entry_id)) ? $message = $entry_id['message'] : $message = $entry_id;
      $result['status'] = 'failed';
      $result['message'] = $message;   
    }
  }
}

if($result['status'] == 'failed'){
?>
  <script>
  alert("<?php echo $result['message'];?>");
  history.back();
  </script>
<?php
} else {
  //echo '<script>alert("'.$return_path.'");</script>';
  header( "Location: ".$return_path );
}

?>