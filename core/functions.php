<?php

  function add_kingkong_board_column( $class, $title, $FucName = '' ){

    ob_start();
      $FucName();
      $FucResult = ob_get_contents();
    ob_end_clean();

    $content  = '<div class="settings-panel-'.$class.'">';
    $content .= '<div class="settings-title noselect">';
    $content .= $title;
    $content .= KingkongBoard_settings_panel_column_close();
    $content .= '<input type="hidden" class="thisHeight"></div>';
    $content .= '<div class="inside">';
    $content .= '<div class="settings-table-wrapper">';
    $content .= $FucResult;
    $content .= '</div>';
    $content .= '</div></div>';

    echo $content;

  }
 
  function KingkongBoard_settings_panel_column_close(){
    $content = '<div class="postbox-onoff" style="float:right">◀</div>';
    return $content;
  }

  add_action( 'wp_ajax_nopriv_kkb_comment_delete', 'kkb_comment_delete');
  add_action( 'wp_ajax_kkb_comment_delete', 'kkb_comment_delete');

  function kkb_comment_delete(){
    global $current_user;
    $result     = array();
    $user_id    = $current_user->ID;
    $comment_id = sanitize_text_field($_POST['cid']);
    
    (isset($_POST['password'])) ? $pwd = sanitize_text_field($_POST['password']) : $pwd = null;

    $comment    = new kkbComment();
    $status     = $comment->kkb_comment_delete($comment_id, $pwd);

    header( "Content-Type: application/json" );
    echo json_encode($status);
    //echo $status;
    exit();
  }
 
  add_action( 'wp_ajax_nopriv_kkb_entry_delete_before_validation', 'kkb_entry_delete_before_validation');
  add_action( 'wp_ajax_kkb_entry_delete_before_validation', 'kkb_entry_delete_before_validation');

  function kkb_entry_delete_before_validation(){
    $board_id   = sanitize_text_field($_POST['board_id']);
    $entry_id   = sanitize_text_field($_POST['entry_id']);
    $pwd        = sanitize_text_field($_POST['pwd']);

    $controller = new kkbController();
    $kkberror   = new kkbError(); 
    if($controller->checkPassword($entry_id, $pwd) == true){
      $guid       = $controller->getMeta($entry_id, 'guid');
      $status     = $controller->deleteEntry($board_id, $entry_id);
      if($status == true){
        $result['status']  = 'success';
        $result['url']     = get_the_permalink($guid);
      } else {
        $error             = $kkberror->Error(016);
        $result['status']  = 'failed';
        $result['message'] = $error['message'];
      }
    } else {
      $error             = $kkberror->Error(008);
      $result['status']  = 'failed';
      $result['message'] = $error['message'];
    }
    header( "Content-Type: application/json" );
    echo json_encode($result);  
    exit();    
  }

  add_action( 'wp_ajax_nopriv_kkb_entry_delete', 'kkb_entry_delete');
  add_action( 'wp_ajax_kkb_entry_delete', 'kkb_entry_delete');

  function kkb_entry_delete(){
    $kkberror   = new kkbError();
    $board_id   = sanitize_text_field($_POST['board_id']);
    $entry_id   = sanitize_text_field($_POST['entry_id']);
    if(!empty($board_id) && !empty($entry_id)){
      $controller = new kkbController();
      $guid       = $controller->getMeta($entry_id, 'guid');
      $status     = $controller->deleteEntry($board_id, $entry_id);
      if($status == true){
        $result['status']  = 'success';
        $result['url']     = get_the_permalink($guid);
      } else {
        $error             = $kkberror->Error(016);
        $result['status']  = 'failed';
        $result['message'] = $error['message'];
      }
    } else {
      $error             = $kkberror->Error(015);
      $result['status']  = 'failed';
      $result['message'] = $error['message'];
    }

    header( "Content-Type: application/json" );
    echo json_encode($result);  
    exit();
  }

  // email validation
  function kingkongboard_email_valid($email) { 
    return preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $email);
  } 

  add_action( 'wp_ajax_nopriv_kingkongboard_entry_validation', 'kingkongboard_entry_validation');
  add_action( 'wp_ajax_kingkongboard_entry_validation', 'kingkongboard_entry_validation');

  function kingkongboard_entry_validation(){

    parse_str($_POST['data'], $data);

    $result               = array();
    $entry_id             = sanitize_text_field($_POST['entry_id']);
    $entry_pwd            = null;
    $entry_title          = null;
    $entry_content        = null;

    if(isset($data['entry_password'])){
      $entry_pwd            = sanitize_text_field($data['entry_password']);
    }
    if(isset($data['entry_title'])){
      $entry_title          = $data['entry_title'];
    }
    
    if(isset($data['entry_writer'])){
      $entry_writer         = $data['entry_writer'];
    }
    
    if(isset($data['entry_content'])){
      $entry_content        = sanitize_text_field($data['entry_content']);
    }
    
    $board_id             = sanitize_text_field($data['board_id']);
    $added_user           = get_kingkong_board_meta_value($entry_id, 'login_id');
    $entry_secret         = get_post_meta($entry_id, 'kingkongboard_entry_password', true);
    $entry_password       = get_post_meta($entry_id, 'kingkongboard_entry_password', true);
    $board_managers       = get_post_meta($board_id, 'board_managers', true);
    $exclude_keyword      = get_post_meta($board_id, 'kkb_exclude_keyword', true);
    $board_must_thumbnail = get_post_meta($board_id, 'kkb_must_thumbnail', true);
    $board_must_section   = get_post_meta($board_id, 'kkb_must_section', true);
    (get_post_meta($board_id, 'board_sections')) ? $board_sections = get_post_meta($board_id, 'board_sections') : $board_sections = null;
    $board_sections       = maybe_unserialize($board_sections);

    $not_allow_keyword    = null;
    $not_allow_keyword_title = null;
    $result_keyword       = null;

    if($exclude_keyword){
      $keywords = explode(",", $exclude_keyword);
      foreach($keywords as $keyword){
        if(preg_match("/".$keyword."/i", $entry_content)){
          $not_allow_keyword[] = $keyword;
        }
        if(preg_match("/".$keyword."/i", $entry_title)){
          $not_allow_keyword_title[] = $keyword;
        }
      }
    }

    $result['status']     = "failed";
    $error                = new kkbError();
    if(!$entry_title){
      $result['message']  = $error->getMessage(001);
    } else if (!is_user_logged_in() && !$entry_writer){
      $result['message']  = $error->getMessage(002);
    } else if(!is_user_logged_in() && !$entry_pwd){
      $result['message']  = $error->getMessage(003);
    } else if(!$entry_content){
      $result['message']  = $error->getMessage(006);
    } else if(is_array($not_allow_keyword_title)){
      $cnt = 1;
      foreach($not_allow_keyword_title as $ntkeyword){
        if(count($not_allow_keyword_title) == $cnt){
          $result_keyword .= $ntkeyword;
        } else {
          $result_keyword .= $ntkeyword.",";
        }
        $cnt++;
      }
      $result['message']  = __('제목', 'kingkongboard').'['.$result_keyword.'] '.__('단어는 포함 할 수 없습니다.', 'kingkongboard');
    } else if(is_array($not_allow_keyword)){
      $cnt = 1;
      foreach($not_allow_keyword as $nkeyword){
        if(count($not_allow_keyword) == $cnt){
          $result_keyword .= $nkeyword;
        } else {
          $result_keyword .= $nkeyword.",";
        }
        $cnt++;
      }
      $result['message']  = __('본문', 'kingkongboard').'['.$result_keyword.'] '.__('단어는 포함 할 수 없습니다.', 'kingkongboard');
    } else if ( ($board_must_thumbnail == "T") && ($_POST['thumbnail'] == null) ){
      $result['message']  = __('썸네일을 반드시 등록하셔야 글이 등록 됩니다.', 'kingkongboard');
    } else if ( (count($board_sections) > 0) && ($board_must_section == "T") && ($data['entry_section'] == '0') ){
      $result['message']  = __('분류를 반드시 선택하셔야 합니다.', 'kingkongboard');
    } else {
      $result['status']   = "success";
    }


    $result = apply_filters('kkb_entry_validation_after', $result, $data );

    header( "Content-Type: application/json" );
    echo json_encode($result); 
    //echo $result;
    exit();

  }

  add_action( 'wp_ajax_nopriv_kingkongboard_entry_password_check', 'kingkongboard_entry_password_check');
  add_action( 'wp_ajax_kingkongboard_entry_password_check', 'kingkongboard_entry_password_check');

  function kingkongboard_entry_password_check(){

    global $current_user;

    $result         = array();
    $entry_id       = sanitize_text_field($_POST['entry_id']);
    $entry_pwd      = sanitize_text_field($_POST['entry_pwd']);
    $entry_pwd      = md5($entry_pwd);
    $board_id       = get_kingkong_board_meta_value($entry_id, 'board_id');
    $added_user     = get_kingkong_board_meta_value($entry_id, 'login_id');
    $entry_secret   = get_post_meta($entry_id, 'kingkongboard_entry_password', true);
    $entry_password = get_post_meta($entry_id, 'kingkongboard_entry_password', true);
    $board_managers = get_post_meta($board_id, 'board_managers', true);

    if($board_managers){
      $board_managers = unserialize($board_managers);
    } else {
      $board_managers = array();
    }

    if(is_user_logged_in()){
      $user_login = $current_user->user_login;
    } else {
      $user_login = null;
    }

    if( (in_array($user_login, $board_managers)) or current_user_can('manage_options') or ( ($added_user == $current_user->ID) and ($added_user != 0) )){
      $result['status'] = "success";
    } else if($entry_pwd == $entry_secret){
      $result['status'] = "success";
    } else {
      $result['status'] = "failed";
      $error = new kkbError();
      if(($added_user != $current_user->ID) && ($added_user != 0)){
        $result['message'] = $error->getMessage(007);
      } else {
        $result['message'] = $error->getMessage(008);
      }
    }

    header( "Content-Type: application/json" );
    echo json_encode($result); 

    exit();

  }

  add_action( 'wp_ajax_nopriv_kingkongboard_comment_list', 'kingkongboard_comment_list');
  add_action( 'wp_ajax_kingkongboard_comment_list', 'kingkongboard_comment_list');

  function kingkongboard_comment_list(){

    $entry_id        = sanitize_text_field($_POST['entry_id']);
    $board_id        = get_board_id_by_entry_id($entry_id);
    $board_comment   = get_post_meta($board_id, 'board_comment', true);

    do_action('kingkongboard_comment_list_before', $board_id, $entry_id);
    
    if($board_comment == "T"){
      $kkb_comment = new kkbComment();
      $comments   = $kkb_comment->kkb_get_comment_list($entry_id);
      $comments_display = $kkb_comment->kkb_comment_display($entry_id, $comments);
    } else {
      $comments_display = false;
    }

    echo $comments_display;

    do_action('kingkongboard_comment_list_after', $board_id, $entry_id);

    exit();

  }


  add_action( 'wp_ajax_nopriv_kingkongboard_comment_save', 'kingkongboard_comment_save');
  add_action( 'wp_ajax_kingkongboard_comment_save', 'kingkongboard_comment_save');

  function kingkongboard_comment_save(){
    include_once(ABSPATH . 'wp-includes/pluggable.php');

    $kkb_comment = new kkbComment();
    $kkb_comment->kkb_comment_save($_POST);

    exit();
  }

  add_action( 'wp_ajax_get_all_kingkong_boards', 'get_all_kingkong_boards' );

  function get_all_kingkong_boards(){
    global $wpdb;
    $content = "";
    $results = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE post_type = 'kkboard' AND post_status = 'publish' ");

    if($results){
      foreach($results as $result){
        $slug = get_post_meta($result->ID, 'kingkongboard_slug', true);
        $content .= "<option value='".$slug."'>".$result->post_title."</option>";
      }
    }
    echo $content;
    exit();
  }

  add_action( 'wp_ajax_remove_kingkong_board_all_entry', 'remove_kingkong_board_all_entry' );

  function remove_kingkong_board_all_entry(){

    $result     = array();
    $board      = new kkbController();
    $entry_ids  = sanitize_text_field($_POST['entry_id']);
    $board_id   = sanitize_text_field($_POST['board_id']);
    $entry_ids  = explode(",", $entry_ids);

    foreach($entry_ids as $entry_id){
      if( ($entry_id != null) and ($entry_id != '')){
        $board->deleteEntry($board_id, $entry_id);
      }
    } 

    exit();    

  }  

  add_action( 'wp_ajax_remove_kingkong_board_each_entry', 'remove_kingkong_board_each_entry' );

  function remove_kingkong_board_each_entry(){
    $result   = array();
    $entry_id = sanitize_text_field($_POST['entry_id']);
    $board_id = sanitize_text_field($_POST['board_id']);
    $board    = new kkbController($board_id);
    $status   = $board->deleteEntry($board_id, $entry_id);
 
    if(is_wp_error($status)){
      $result['status']   = 'cancel';
      $result['message']  = __('삭제도중 오류가 발생하였습니다.', 'kingkongboard');
    } else {
      $result['status']   = 'success';
      $result['message']  = __('정상적으로 삭제 되었습니다.', 'kingkongboard');      
    }

    header( "Content-Type: application/json" );
    echo json_encode($result);  

    exit();

  }

  add_action( 'wp_ajax_remove_kingkong_board', 'remove_kingkong_board');

  function remove_kingkong_board(){
    global $wpdb;

    $WillRemoves = array();
    $board_id = sanitize_text_field($_POST['board_id']);
    $result = array();

    $Board = new kkbConfig();
    $Board = $Board->getBoard($board_id);

    $args = array(
      'post_type'       => $Board->slug,
      'posts_per_page'  => -1
    );

    $WillRemoves = get_posts($args);

    foreach($WillRemoves as $Remove){
      wp_delete_post($Remove->ID);
    }

    $meta_table = $wpdb->prefix."kingkongboard_meta";

    $wpdb->delete($meta_table, array('board_id' => $board_id));

    $status = wp_delete_post($board_id);

    if(is_wp_error($status)){
      $result['status']   = 'cancel';
      $result['message']  = __('삭제도중 오류가 발생하였습니다.', 'kingkongboard');
    } else {
      $result['status']   = 'success';
      $result['message']  = __('정상적으로 삭제 되었습니다.', 'kingkongboard');      
    }

    header( "Content-Type: application/json" );
    echo json_encode($result);  

    exit();
  }

  add_action( 'wp_ajax_create_kingkong_board', 'create_kingkong_board');


/*
* - Function Name
*   create_kingkong_board
* - Description
*   관리자 패널의 쿱 보드 신규 게시판 생성을 위한 함수로 Board Setting 정보를 바탕으로 Custom Post Type 으로 생성
*   게시판 슬러그를 바탕으로 Custom Post Type 명을 정함.
*   글 작성 에디터의 경우 플러그인 업데이트와 상관없이 한번 설치하면 영구적으로 세팅이 되도록 구현.
*   게시판 생성 관련 클래스로 정의
*/

  function create_kingkong_board(){

    $result         = array();
    $BasicSettings  = array();
    parse_str($_POST['data'], $options);

    do_action('create_kingkong_board_before', $options);

    $board_name       = $options['kkb_board_name'];

    if(isset($options['kkb_board_slug'])){
      $board_slug     = $options['kkb_board_slug'];
    } else {
      $board_slug     = null;
    }

    if(isset($options['kkb_board_shortcode'])){
      $board_shortcode = $options['kkb_board_shortcode'];
    } else {
      $board_shortcode = null;
    }
    
    $board_rows                 = $options['kkb_board_rows'];
    $board_editor               = $options['kkb_board_editor'];
    $page_type                  = $options['kkb_type'];
    $board_search               = $options['kkb_board_search_filter'];
    if(isset($options['kkb_board_thumbnail_display'])){
      $board_thumbnail_display    = $options['kkb_board_thumbnail_display'];
    } else {
      $board_thumbnail_display    = null;
    }
    $board_thumbnail_input      = $options['kkb_board_thumbnail_input_content'];

    $settings    = array(
      'board_name'                => $board_name,
      'board_slug'                => $board_slug,
      'board_shortcode'           => $board_shortcode,
      'board_rows'                => $board_rows,
      'board_editor'              => $board_editor,
      'board_thumbnail_display'   => $board_thumbnail_display,
      'board_thumbnail_input'     => $board_thumbnail_input,
      'board_search'              => $board_search
    );

    $KingkongBoard = new KingkongBoard();

    switch($page_type){
      case "create" :
        $Status             = $KingkongBoard->CreateBoard($settings);
        ($Status['status'] == 'success') ? $board_id = $Status['board_id'] : $board_id = null;
      break;

      case "modify" :
        $board_id         = $options['board_id'];
        $Status           = $KingkongBoard->ModifyBoard($board_id, $settings); 
      break;
    }

    if(isset($options['kkb_reply_use'])){
      update_post_meta($board_id, 'kkb_reply_use', $options['kkb_reply_use']);
    }

    if(isset($options['kkb_attach_number'])){
      update_post_meta($board_id, 'kkb_attach_number', $options['kkb_attach_number']);
    }

    if(isset($options['kkb_basic_form']) && $options['kkb_basic_form'] != ''){
      update_post_meta($board_id, 'kkb_basic_form', $options['kkb_basic_form']);
    } else {
      delete_post_meta($board_id, 'kkb_basic_form');
    }

    if(isset($options['kkb_exclude_keyword']) && $options['kkb_exclude_keyword'] != ''){
      update_post_meta($board_id, 'kkb_exclude_keyword', $options['kkb_exclude_keyword']);
    } else {
      delete_post_meta($board_id, 'kkb_exclude_keyword');
    }

    if(isset($options['kkb_must_section'])){
      if($options['kkb_must_section'] == "T"){
        update_post_meta($board_id, 'kkb_must_section', $options['kkb_must_section']);
      } else {
        delete_post_meta($board_id, 'kkb_must_section');
      }
    } else {
      delete_post_meta($board_id, 'kkb_must_section');
    }

    if(isset($options['kkb_must_secret'])){
      if($options['kkb_must_secret'] == "T"){
        update_post_meta($board_id, 'kkb_must_secret', $options['kkb_must_secret']);
      } else {
        delete_post_meta($board_id, 'kkb_must_secret');
      }
    } else {
      delete_post_meta($board_id, 'kkb_must_secret');
    }

    if(isset($options['kkb_read_under_loop'])){
      if($options['kkb_read_under_loop'] == "T"){
        update_post_meta($board_id, 'kkb_read_under_loop', $options['kkb_read_under_loop']);
      } else {
        delete_post_meta($board_id, 'kkb_read_under_loop');
      }
    } else {
      delete_post_meta($board_id, 'kkb_read_under_loop');
    }

    if(isset($options['kkb_must_thumbnail'])){
      if($options['kkb_must_thumbnail'] == "T"){
        update_post_meta($board_id, 'kkb_must_thumbnail', $options['kkb_must_thumbnail']);
      } else {
        delete_post_meta($board_id, 'kkb_must_thumbnail');
      }
    } else {
      delete_post_meta($board_id, 'kkb_must_thumbnail');
    }

    if(isset($options['kkb_auto_link'])){
      if($options['kkb_auto_link'] == "T"){
        update_post_meta($board_id, 'kkb_auto_link', 'T');
      } else {
        delete_post_meta($board_id, 'kkb_auto_link');
      }
    } else {
      delete_post_meta($board_id, 'kkb_auto_link');
    }

    $result['status']   = $Status['status'];
    $result['message']  = $Status['message'];
    $result['board_id'] = $board_id;
    do_action('create_kingkong_board_after', $board_id, $options);

    header( "Content-Type: application/json" );
    echo json_encode($result);  
    //echo $result;
    exit();

  }

  add_filter('admin_kingkong_board_entry_columns', 'admin_kingkong_board_entry_columns', 10, 2);

  function admin_kingkong_board_entry_columns($entries, $board_id){
    $entries = array(
      array(
        'label' => __('썸네일', 'kingkongboard'),
        'value' => 'thumbnail'
      ),
      array(
        'label' => __('제목', 'kingkongboard'),
        'value' => 'title'
      ),
      array(
        'label' => __('작성일시', 'kingkongboard'),
        'value' => 'date'
      ),
      array(
        'label' => __('작성자', 'kingkongboard'),
        'value' => 'writer'
      ),
      array(
        'label' => __('설정', 'kingkongboard'),
        'value' => 'options'
      )
    );

    return $entries;
  }

  add_filter('kingkong_board_entry_columns', 'kingkong_board_entry_columns', 10, 2);

  function kingkong_board_entry_columns($entries, $board_id){

    $entries = array(
      array(
        'label' => __('번호', 'kingkongboard'),
        'value' => 'number'
      ),
      array(
        'label' => __('분류', 'kingkongboard'),
        'value' => 'section'
      ),
      array(
        'label' => __('제목', 'kingkongboard'),
        'value' => 'title'
      ),
      array(
        'label' => __('작성자', 'kingkongboard'),
        'value' => 'writer'
      ),
      array(
        'label' => __('작성일', 'kingkongboard'),
        'value' => 'date'
      ),
      array(
        'label' => __('조회', 'kingkongboard'),
        'value' => 'hit'
      )
    );

    if(isset($_GET['kkb_section'])) unset($entries[1]);

    return $entries;
  }

  add_filter('kkb_loop_column_date', 'kkb_loop_column_date', 10, 4);

  function kkb_loop_column_date($content, $entry, $entry_id, $cnt){
    $content = "<td class='kingkongboard-list-".$entry['value']."'>".apply_filters('kkb_list_date', get_the_date('Y.m.d', $entry_id), $entry_id)."</td>";
    return $content;
  }

  add_filter('kkb_loop_column_writer', 'kkb_loop_column_writer', 10, 4);

  function kkb_loop_column_writer($content, $entry, $entry_id, $cnt){
    $writer = '<span>'.get_kingkong_board_meta_value($entry_id, 'writer').'</span>';
    if(!$writer){
      $writer = __('비회원', 'kingkongboard');
    }
    $content = "<td class='kingkongboard-list-".$entry['value']."'>".apply_filters('kkb_list_writer', $writer, $entry_id)."</td>";
    return $content;
  }

  add_filter('kkb_loop_column_hit', 'kkb_loop_column_hit', 10, 4);

  function kkb_loop_column_hit($content, $entry, $entry_id, $cnt){
    $hit = get_post_meta($entry_id, 'kingkongboard_hits', true);
    if(!$hit){ $hit = 0; }
    $content .= '<td class="kingkongboard-list-'.$entry['value'].'">'.$hit.'</td>';
    return $content;
  }

  add_filter('kkb_loop_column_number', 'kkb_loop_column_number', 10, 4);

  function kkb_loop_column_number($content, $entry, $entry_id, $cnt){
    (isset($_GET['view'])) ? $view = sanitize_text_field($_GET['view']) : $view = null;
    $entry_type       = get_kingkong_board_meta_value($entry_id, 'type');
    if($entry_type == 1){
      $content = "<td class='kingkongboard-list-".$entry['value']."'>".apply_filters('kkb_list_icon_notice', '<span class="kkb-list-icon kkblc-notice" alt="'.__('공지사항', 'kingkongboard').'" title="'.__('공지사항', 'kingkongboard').'"></span>', $entry_id)."</td>";
    } else if($view == 'read' && $entry_id == $_GET['id']){
      $content = "<td class='kingkongboard-list-".$entry['value']."'><span>".apply_filters('kkb_list_icon_current', '<span class="kkb-list-icon kkblc-current-post" alt="'.__('현재글', 'kingkongboard').'" title="'.__('현재글', 'kingkongboard').'"></span>', $entry_id)."</span></td>";
    } else {
      $content = "<td class='kingkongboard-list-".$entry['value']."'><span>".apply_filters('kkb_list_number', $cnt, $entry_id)."</span></td>";
    }
    return $content;
  }

  add_filter('kkb_loop_column_section', 'kkb_loop_column_section', 10, 4);

  function kkb_loop_column_section($content, $entry, $entry_id, $cnt){
    $board_sections   = get_post_meta(get_board_id_by_entry_id($entry_id), 'board_sections', true);
    $section          = get_kingkong_board_meta_value($entry_id, 'section');
    $link_url         = add_query_arg(array('kkb_section' => $section), get_the_permalink());
    if($board_sections && !isset($_GET['kkb_section'])){
      if($section != "0"){
        $content = "<td class='kingkongboard-list-".$entry['value']."'><a href='".$link_url."'>".apply_filters('kkb_list_section', kingkongboard_text_cut($section, 6, "..."), $section, $entry_id)."</a></td>";
      } else {
        $content = "<td class='kingkongboard-list-".$entry['value']."'></td>";
      }
    } else {
      $content = null;
    }
    return $content;
  }

  add_filter('kkb_loop_column_title', 'kkb_loop_column_title', 10, 4);

  function kkb_loop_column_title($content, $entry, $entry_id, $cnt){

      $entry_type       = '';
      $entry_type       = get_kingkong_board_meta_value($entry_id, 'type');
      $entry_attachment = get_post_meta($entry_id, 'kingkongboard_attached', true);
      $entry_secret     = get_post_meta($entry_id, 'kingkongboard_secret', true);

      (isset($_GET['view'])) ? $view = sanitize_text_field($_GET['view']) : $view = null;
      (($view == 'read') && $entry_id == $_GET['id']) ? $view_style = 'font-weight:bold' : $view_style = null;
      $board_sections   = get_post_meta(get_board_id_by_entry_id($entry_id), 'board_sections', true);
    if(isset($_GET['pageid'])){
      $pageid = sanitize_text_field($_GET['pageid']);
    } else {
      $pageid = null;
    }

    if(!$pageid){
      $pageid = 1;
    }
    $thumbnail_id    = get_post_thumbnail_id($entry_id);
    $thumbnail_image = wp_get_attachment_image_src($thumbnail_id, array(22,22), true);
    //$thumbnail_image = get_the_post_thumbnail( $entry_id, array(22,22) );
    $thumbnail_html  = '<image src="'.$thumbnail_image[0].'" style="width:22px; height:22px">';
    if($thumbnail_id){
      $board_id           = get_board_id_by_entry_id($entry_id);
      $board_thumbnail_dp = get_post_meta($board_id, 'kingkongboard_thumbnail_dp', true);

      if($board_thumbnail_dp == "display"){ 
        $thumbnail = '<div class="entry_thumbnail_image" style="display:inline-block; vertical-align:middle; margin-right:5px">'.apply_filters('kkb_list_thumbnail', $thumbnail_html, $entry_id).'</div>';
      } else {
        $thumbnail = "";
      }
    } else {
      $thumbnail = "";
    }

    $args = array('post_id' => $entry_id, 'status'=>'approve', 'count' => true);
    $comments = new kkbComment();
    $comments_count = $comments->kkb_get_comment_count($entry_id);

    if($comments_count > 0){
      $comments_count = apply_filters('kkb_list_comment_count', " <span class='entry_comment_count'>".$comments_count."</span>", $comments_count, $entry_id);
    } else {
      $comments_count = null;
    }

    if($entry_attachment){
      $entry_attach_text = apply_filters('kkb_list_icon_attach_outer', '<span class="kkb-list-icon kkblc-attach"></span>', $entry_id);
    } else {
      $entry_attach_text = "";
    }

    if($entry_secret){
      $entry_secret_icon = apply_filters('kkb_list_icon_secret_outer', '<span class="kkb-list-icon kkblc-secret"></span>', $entry_id);
    } else {
      $entry_secret_icon = '';
    }

    $controller  = new kkbController();

    $parent      = $controller->getMeta($entry_id, 'parent');
    $entry_depth = $controller->getMeta($entry_id, 'depth');
    $guid        = $controller->getMeta($entry_id, 'guid');
    if($entry_depth > 1){
      $padding        = 5 * $entry_depth;
      $reply_padding  = apply_filters('kkb_list_reply_padding', ' padding-left:'.$padding.'px;', $entry_id, $padding);
      $reply_icon     = apply_filters('kkb_list_icon_reply', '<img src="'.KINGKONGBOARD_PLUGINS_URL.'/assets/images/icon-reply.gif" style="width:25px; height:auto; position:relative; top:0px; margin-right:5px; display:inline-block; line-height:0; vertical-align:middle">', $entry_id );
      $parent_id      = '&prnt='.$parent;
    } else {
      $reply_padding  = '';
      $reply_icon     = '';
      $parent_id      = '';
    }

      $writer = $controller->getMeta($entry_id, 'writer');
      $writer = kingkongboard_text_cut($writer, 6, "...");
      $hit = get_post_meta($entry_id, 'kingkongboard_hits', true);

      $mobile_writer_info  = "<div class='mobile-writer-info'>";
      $mobile_writer_info .= "<ul>";
      $mobile_writer_info .= "<li>".__('작성자', 'kingkongboard')." : ".$writer."</li>";
      $mobile_writer_info .= "<li>".__('작성일', 'kingkongboard')." : ".get_the_date('Y.m.d', $entry_id)."</li>";
      $mobile_writer_info .= "<li>".__('조회수', 'kingkongboard')." : ".$hit."</li>";
      $mobile_writer_info .= "</ul>";
      $mobile_writer_info .= "</div>";

      $mobile_writer_info = apply_filters('kkb_list_mobile_writer_info', $mobile_writer_info, $entry_id);

      $extra_priority = apply_filters('kkb_loop_extra_priority', $entry_secret_icon.$entry_attach_text.$comments_count, $entry_secret_icon, $entry_attach_text, $comments_count, $entry_id);

      $read_path = add_query_arg(array('pageid' => $pageid, 'view' => 'read', 'id' => $entry_id.$parent_id), get_the_permalink($guid));
      
      $title = get_the_title($entry_id);
      $title = str_replace('비공개: ', '', $title);
      $title = str_replace('Private: ', '', $title);
      $link  = "<a href='".$read_path."'>".apply_filters('kkb_loop_title_value', $reply_icon.$thumbnail.$title.$extra_priority, $entry_id)."</a>".$mobile_writer_info;
      $content = "<td style='position:relative;".$reply_padding." ".$view_style."' class='kingkongboard-list-".$entry['value']."'>".$link."</td>";

      return $content;
  }

  add_filter('admin_kingkong_board_manage_entry_column', 'admin_kingkong_board_manage_entry_column', 10, 2);

  function admin_kingkong_board_manage_entry_column($entries, $entry_id){

    $return_content = '';
    $controller     = new kkbController();
    foreach($entries as $entry){
      switch($entry['value']){

        case 'thumbnail' :
          if(has_post_thumbnail($entry_id)){
            $thumbnail_id = get_post_thumbnail_id($entry_id);
            $url          = wp_get_attachment_image_src( $thumbnail_id, 'thumbnail' );
            $image        = '<img src="'.$url[0].'" style="width:20px; height:auto">';
          } else {
            $image        = __('없음', 'kingkongboard');
          }
          $return_content .= '<td style="text-align:center">'.$image.'</td>';
        break;

        case "title" :
        $entry_type = '';
        $entry_type = get_kingkong_board_meta_value($entry_id, 'type');
        $entry_attachment = get_post_meta($entry_id, 'kingkongboard_attached', true);
        $entry_secret     = get_post_meta($entry_id, 'kingkongboard_secret', true);

        if($entry_attachment){
          $entry_attach_text = "<i class='kkb-icon kkb-icon-attachment'></i>";
        } else {
          $entry_attach_text = "";
        }

        if($entry_secret){
          $entry_secret_icon = '<img src="'.KINGKONGBOARD_PLUGINS_URL.'/assets/images/icon-locked.png" style="width:12px; height:auto; position:relative; top:0px; margin-left:5px">';
        } else {
          $entry_secret_icon = '';
        }


        $parent      = get_kingkong_board_meta_value($entry_id, 'parent');
        $entry_depth = get_kingkong_board_meta_value($entry_id, 'depth');
        $writer      = get_kingkong_board_meta_value($entry_id, 'writer');
        if($entry_depth > 1){
          $padding        = 10 * $entry_depth;
          $reply_padding  = 'style="padding-left:'.$padding.'px;"';
          $reply_icon     = '<img src="'.KINGKONGBOARD_PLUGINS_URL.'/assets/images/icon-reply.gif" style="width:25px; height:auto; position:relative; top:3px; margin-right:5px">';
          $parent_id      = '&prnt='.$parent;
        } else {
          $reply_padding  = '';
          $reply_icon     = '';
          $parent_id      = '';
        }

          $return_content .= "<td ".$reply_padding.">".$reply_icon."<a href='?page=KingkongBoard&view=entry-view&id=".$entry_id.$parent_id."'>".get_the_title($entry_id)."</a>".$entry_attach_text.$entry_secret_icon."</td>";
        break;

        case "date" :
          $return_content .= "<td>".get_the_date('Y-m-d H:i:s', $entry_id)."</td>";
        break;

        case "writer" :
          $user_id  = $controller->getMeta($entry_id, 'login_id');
          $post     = get_post($entry_id);
          $writer   = kingkongboard_text_cut($writer, 8, "...");
          ($user_id > 1) ? $writer_path = '<a href="'.get_edit_user_link($user_id).'">'.$writer.'</a>' : $writer_path = $writer;
          $return_content .= "<td>".$writer_path."</td>";
        break;

        case "options" :
          $guid       = $controller->getMeta($entry_id, 'guid');
          $preview_path = add_query_arg(array('view' => 'read', 'id' => $entry_id), get_the_permalink($guid));
          $return_content .= "<td style='text-align:left'><a href='admin.php?page=KingkongBoard&view=entry-modify&id=".$entry_id."' class='button-kkb kkbgreen button-modify-each-entry' data='".$entry_id."'><i class='kkb-icon kkb-icon-modify'></i></a> <a class='button-kkb kkbred button-remove-each-entry' data='".$entry_id."'><i class='kkb-icon kkb-icon-trash'></i></a> <a href='".$preview_path."' target='_blank' class='button-kkb kkbyellow' data='".$entry_id."'><i class='kkb-icon kkb-icon-preview'></i></a></td>";
        break;
      }
    }

    return $return_content;
  }

  function kingkongboard_text_cut($str, $cut, $fix="..") { 
    if (!$str || strlen($str)<=$cut*2) return $str;
    if (strlen($str)<=$cut) return $str;
    return mb_substr($str,0,$cut, 'UTF-8').$fix;
  } 

  function get_board_id_by_entry_id($entry_id){
    global $wpdb;
    $entry_slug = $wpdb->get_row("SELECT post_type from $wpdb->posts WHERE ID = $entry_id");
    $entry_slug = $entry_slug->post_type;
    $entry_slug = str_replace("kkb_", "", $entry_slug);

    $board_result = $wpdb->get_row("SELECT post_id from $wpdb->postmeta WHERE meta_key = 'kingkongboard_slug' and meta_value = '".$entry_slug."' ");
    return $board_result->post_id;
  }

  function get_kingkong_board_meta_value($board_id, $key){
    global $wpdb;
    $table  = $wpdb->prefix.'kingkongboard_meta';
    $result = $wpdb->get_row("SELECT ".$key." FROM {$table} WHERE post_id = '".$board_id."' ");
    if($result){
      return $result->$key;
    } else {
      return false;
    }
  }

  function get_post_id_by_list_number($board_id, $list_number){
    global $wpdb;
    $table  = $wpdb->prefix.'kingkongboard_meta';
    $result = $wpdb->get_row("SELECT post_id FROM {$table} WHERE list_number = '".$list_number."' AND board_id = $board_id AND type != 99");
    if($result){
      return $result->post_id;
    } else {
      return false;
    }
  }

  function get_kingkong_board_id_by_slug($slug){
    global $wpdb;
    $result = $wpdb->get_row("SELECT post_id from $wpdb->postmeta where meta_key = 'kingkongboard_slug' AND meta_value='".$slug."' ");
    if($result){
      return $result->post_id;
    }
  }

function kkb_button_classer($board_id){
  $class = "kingkongboard-button";
  return apply_filters('kkb_button_class_after', $class, $board_id);
}

function kkb_button_text($board_id, $type){
  switch($type){
    case "write" :
      $text = __('글쓰기', 'kingkongboard');
    break;

    case "modify" :
      $text = __('글수정', 'kingkongboard');
    break;

    case "submit" :
      $text = __('확인', 'kingkongboard');
    break;

    case "back" :
      $text = __('돌아가기', 'kingkongboard');
    break;

    case "save" :
      $text = __('저장하기', 'kingkongboard');
    break;

    case "modified" :
      $text = __('수정하기', 'kingkongboard');
    break;

    case "reply" ;
      $text = __('답글쓰기', 'kingkongboard');
    break;

    case "list" :
      $text = __('목록보기', 'kingkongboard');
    break;

    case "prev" :
      $text = __('이전글', 'kingkongboard');
    break;

    case "next" :
      $text = __('다음글', 'kingkongboard');
    break;

    case "delete" :
      $text = __('글삭제', 'kingkongboard');
    break;

    case "search" :
      $text = __('검색', 'kingkongboard');
    break;

    default :
      $text = null;
    break;
  }
    return apply_filters('kkb_button_text_after', $text, $board_id, $type);
}

function get_kingkongboard_permission_modify_by_role_name($board_id, $role_name){
  $modify_status     = null;
  $permission_modify = get_post_meta($board_id, 'permission_modify', true);
  if($permission_modify){
    $permission_modify = maybe_unserialize($permission_modify);
    foreach($permission_modify as $each){
      if($each == $role_name){
        $modify_status = "checked";
      }
    }
  }
  return $modify_status;
}

function get_kingkongboard_permission_delete_by_role_name($board_id, $role_name){
  $delete_status     = null;
  $permission_delete = get_post_meta($board_id, 'permission_delete', true);
  if($permission_delete){
    $permission_delete = maybe_unserialize($permission_delete);
    foreach($permission_delete as $each){
      if($each == $role_name){
        $delete_status = "checked";
      }
    }
  }
  return $delete_status;
}


function get_kingkongboard_permission_write_by_role_name($board_id, $role_name){
  $write_status     = null;
  $permission_write = get_post_meta($board_id, 'permission_write', true);
  if($permission_write){
    $permission_write = maybe_unserialize($permission_write);
    foreach($permission_write as $each){
      if($each == $role_name){
        $write_status = "checked";
      }
    }
  }
  return $write_status;
}


function get_kingkongboard_permission_read_by_role_name($board_id, $role_name){
  $read_status     = null;
  $permission_read = get_post_meta($board_id, 'permission_read', true);
  if($permission_read){
    $permission_read = maybe_unserialize($permission_read);
    foreach($permission_read as $each){
      if($each == $role_name){
        $read_status = "checked";
      }
    }
  }
  return $read_status;
}

function kingkongboard_attached_getSize($attach_id){
  if($attach_id){
    $bytes = filesize(get_attached_file( $attach_id ));
    if($bytes > 0){
      $s     = array('b', 'Kb', 'Mb', 'Gb');
      $e     = floor(log($bytes)/log(1024));
      return sprintf('%.2f '.$s[$e], ($bytes/pow(1024, floor($e))));
    }
  }
}

function get_kingkongboard_uploaded_filename($attach_id){
  $path = get_attached_file($attach_id);
  $path = preg_replace( '/^.+[\\\\\\/]/', '', $path );
  return $path;
}

function kingkongboard_reArrayFiles($file_post){
  $file_ary = array();
  $file_count = count($file_post['name']);
  $file_keys = array_keys($file_post);

  for ($i=0; $i<$file_count; $i++) {
    foreach ($file_keys as $key) {
      $file_ary[$i][$key] = $file_post[$key][$i];
    }
  }
  return $file_ary;    
}

add_action('create_kingkong_board_after', 'save_kingkong_board_skin', 10, 2);

function save_kingkong_board_skin($board_id, $options){

  if(isset($options['kkb_board_captcha'])){
    if($options['kkb_board_captcha'] == "T"){
      update_post_meta($board_id, 'board_captcha', $options['kkb_board_captcha']);
    }
  } else {
    delete_post_meta($board_id, 'board_captcha');
  }

  if(isset($options['kkb_board_captcha_sitekey']) && isset($options['kkb_board_captcha_secretkey'])){
    $captchaKey = array(
      'site_key'    => $options['kkb_board_captcha_sitekey'],
      'secret_key'  => $options['kkb_board_captcha_secretkey']
    );
    $captchaKey = serialize($captchaKey);
    update_post_meta($board_id, 'board_captcha_key', $captchaKey);
  }

  if(isset($options['board_skin_slug'])){
    update_post_meta($board_id, 'board_skin', $options['board_skin_slug']);
  }

  if(isset($options['board_extension'])){
    update_post_meta($board_id, 'board_extension', $options['board_extension']);
  }
  if(isset($options['board_manager'])){
    $board_managers = $options['board_manager'];
    update_post_meta($board_id, 'board_managers', serialize($board_managers));
  }
  if(isset($options['board_section'])){
    $board_sections = $options['board_section'];
    update_post_meta($board_id, 'board_sections', serialize($board_sections));
  } else {
    delete_post_meta($board_id, 'board_sections');
  }
  if(isset($options['permission_read'])){
    update_post_meta($board_id, 'permission_read', serialize($options['permission_read']));
  }

  if(isset($options['permission_modify'])){
    update_post_meta($board_id, 'permission_modify', serialize($options['permission_modify']));
  }

  if(isset($options['permission_write'])){
    update_post_meta($board_id, 'permission_write', serialize($options['permission_write']));
  }

  if(isset($options['permission_delete'])){
    update_post_meta($board_id, 'permission_delete', serialize($options['permission_delete']));
  }

  if(isset($options['permission_comment_read'])){
    update_post_meta($board_id, 'permission_comment_read', serialize($options['permission_comment_read']));
  }

  if(isset($options['permission_comment_modify'])){
    update_post_meta($board_id, 'permission_comment_modify', serialize($options['permission_comment_modify']));
  }

  if(isset($options['permission_comment_write'])){
    update_post_meta($board_id, 'permission_comment_write', serialize($options['permission_comment_write']));
  }

  if(isset($options['permission_comment_delete'])){
    update_post_meta($board_id, 'permission_comment_delete', serialize($options['permission_comment_delete']));
  }


  if(isset($options['kkb_board_thumbnail_upload'])){
    if($options['kkb_board_thumbnail_upload'] == "T"){
      update_post_meta($board_id, 'thumbnail_upload', 'T');
    } else {
      delete_post_meta($board_id, 'thumbnail_upload');
    }
  } else {
    delete_post_meta($board_id, 'thumbnail_upload');
  }

  if(isset($options['kkb_board_file_upload'])){
    if($options['kkb_board_file_upload'] == "T"){
      update_post_meta($board_id, 'file_upload', 'T');
    } else {
      delete_post_meta($board_id, 'file_upload');
    }
  } else {
    delete_post_meta($board_id, 'file_upload');
  }

  if(isset($options['board_comment'])){
    update_post_meta($board_id, 'board_comment', $options['board_comment']);
  }

  if(isset($options['kkb_notice_entry'])){
    update_post_meta($board_id, 'kingkongboard_notice_entry', $options['kkb_notice_entry']);
  } else {
    delete_post_meta($board_id, 'kingkongboard_notice_entry');
  }

  if(isset($options['kkb_notice_comment'])){
    update_post_meta($board_id, 'kingkongboard_notice_comment', $options['kkb_notice_comment']);
  } else {
    delete_post_meta($board_id, 'kingkongboard_notice_comment');
  }

  if(isset($options['kkb_notice_emails'])){
    update_post_meta($board_id, 'kingkongboard_notice_emails', $options['kkb_notice_emails']);
  }

  if(isset($options['added_latest_shortcode_title'])){
    $added_title      = $options['added_latest_shortcode_title'];
    $added_skin       = $options['added_latest_skin'];
    $added_number     = $options['added_latest_list_number'];
    $added_length     = $options['added_latest_length'];
    $added_board_id   = $options['added_latest_board_id'];

    $latest_shortcodes = array();

    for ($i=0; $i < count($added_title); $i++) { 
      $latest_shortcodes[$i] = array(
        'title'     => $added_title[$i],
        'skin'      => $added_skin[$i],
        'number'    => $added_number[$i],
        'length'    => $added_length[$i],
        'board_id'  => $added_board_id[$i]
      );
    }

    $latest_shortcodes = serialize($latest_shortcodes);

    update_post_meta($board_id, 'kingkongboard_added_latest', $latest_shortcodes);

  } else {
    delete_post_meta($board_id, 'kingkongboard_added_latest');
  }


  $origin_options = get_post_meta($board_id, 'kingkongboard_comment_options', true);
  if(!$origin_options){
    $origin_options = array(
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
  } else {
    $origin_options = unserialize($origin_options);
  }

  if(isset($options['setting_comment_thumbnail'])){
    if($options['setting_comment_thumbnail'] == "T"){
      $origin_options['thumbnail'] = $options['setting_comment_thumbnail'];
    }
  } else {
    $origin_options['thumbnail'] = "F";
  }

  if(isset($options['setting_comment_background_color'])){
    $origin_options['background']['color'] = $options['setting_comment_background_color'];
  }

  if(isset($options['setting_comment_background_border_color'])){
    $origin_options['background']['border_color'] = $options['setting_comment_background_border_color'];
  }

  if(isset($options['setting_comment_background_border'])){
    $origin_options['background']['border'] = $options['setting_comment_background_border'];
  }

  if(isset($options['setting_comment_writer_color'])){
    $origin_options['writer']['color'] = $options['setting_comment_writer_color'];
  }
 
  if(isset($options['setting_comment_writer_bold'])){
    if($options['setting_comment_writer_bold'] == "bold"){
      $origin_options['writer']['font_weight'] = "bold";
    } else {
      $origin_options['writer']['font_weight'] = "";
    }
  } else {
    $origin_options['writer']['font_weight'] = "";
  }

  if(isset($options['setting_comment_writer_font_size'])){
    $origin_options['writer']['font_size'] = $options['setting_comment_writer_font_size'];
  }

  if(isset($options['setting_comment_date_format'])){
    $origin_options['date']['format'] = $options['setting_comment_date_format'];
  }

  if(isset($options['setting_comment_date_color'])){
    $origin_options['date']['color'] = $options['setting_comment_date_color'];
  }

  if(isset($options['setting_comment_date_font_size'])){
    $origin_options['date']['font_size'] = $options['setting_comment_date_font_size'];
  }

  if(isset($options['setting_comment_content_color'])){
    $origin_options['content']['color'] = $options['setting_comment_content_color'];
  }



  $origin_options = serialize($origin_options);

  update_post_meta($board_id, 'kingkongboard_comment_options', $origin_options);

}

function kingkong_formatBytes($bytes, $precision = 2) { 
    $units = array('B', 'KB', 'MB', 'GB', 'TB'); 

    $bytes = max($bytes, 0); 
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
    $pow = min($pow, count($units) - 1); 

    // Uncomment one of the following alternatives
    $bytes /= pow(1024, $pow);
    //$bytes /= (1 << (10 * $pow)); 

    return round($bytes, $precision) . ' ' . $units[$pow]; 
} 

function kingkong_file_upload_max_size() {
  static $max_size = -1;

  if ($max_size < 0) {
    // Start with post_max_size.
    $max_size = kingkong_parse_size(ini_get('post_max_size'));

    // If upload_max_size is less, then reduce. Except if upload_max_size is
    // zero, which indicates no limit.
    $upload_max = kingkong_parse_size(ini_get('upload_max_filesize'));
    if ($upload_max > 0 && $upload_max < $max_size) {
      $max_size = $upload_max;
    }
  }
  return $max_size;
}

function kingkong_parse_size($size) {
  $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
  $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
  if ($unit) {
    // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
    return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
  }
  else {
    return round($size);
  }
}

function kingkongboard_add_extension($slug, $title, $plugins_url, $abspath, $core){
  if(!$slug || !$title){
    return false;
  } else {
    $duplicate = 0;
    $extensions = get_option("kingkongboard_extensions");
    if($extensions){
      $extensions = maybe_unserialize($extensions);

        foreach($extensions as $extension){
          if($extension['slug'] == $slug){
            $duplicate = 1;
          }
        }
        if($duplicate == 0){
          $extensions[] = array(
            'slug'        => $slug,
            'title'       => $title,
            'plugins_url' => $plugins_url,
            'abspath'     => $abspath,
            'core'        => $core
          );

          $extensions = serialize($extensions);
        }

    } else {
      $extensions = array();
      $extensions[0] = array(
        'slug'        => $slug,
        'title'       => $title,
        'plugins_url' => $plugins_url,
        'abspath'     => $abspath,
        'core'        => $core
      );
      $extensions = serialize($extensions);
    }
    update_option("kingkongboard_extensions", $extensions);    
  }
}

function kingkongboard_add_skin($type, $slug, $title, $plugins_url, $abspath, $core){
  if(!$type || !$slug || !$title){
    return false;
  } else {
    $duplicate = 0;
    $skins = get_option("kingkongboard_skins");
    if($skins){
      $skins = maybe_unserialize($skins);
      if($skins != false){
        $cnt = 0;
        foreach($skins as $skin){
          if($skin['slug'] == $slug && $skin['type'] == $type){
            unset($skins[$cnt]);
            $duplicate = 1;
          }
          $cnt++;
        }
        if($duplicate == 0){
          $skins[] = array(
            'type'        => $type,
            'slug'        => $slug,
            'title'       => $title,
            'plugins_url' => $plugins_url,
            'abspath'     => $abspath,
            'core'        => $core
          );

          $skins = serialize($skins);
        } else {
          foreach($skins as $skin){
            $newSkins[] = array(
              'type'        => $skin['type'],
              'slug'        => $skin['slug'],
              'title'       => $skin['title'],
              'plugins_url' => $skin['plugins_url'],
              'abspath'     => $skin['abspath'],
              'core'        => $skin['core']
            );
          }
          $newSkins[] = array(
            'type'        => $type,
            'slug'        => $slug,
            'title'       => $title,
            'plugins_url' => $plugins_url,
            'abspath'     => $abspath,
            'core'        => $core
          );

          $skins = serialize($newSkins);          
        }
      }
    } else {
      $skins = array();
      $skins[0] = array(
        'type'        => $type,
        'slug'        => $slug,
        'title'       => $title,
        'plugins_url' => $plugins_url,
        'abspath'     => $abspath,
        'core'        => $core
      );
      $skins = serialize($skins);
    }
    update_option("kingkongboard_skins", $skins);
  }
}

function kingkongboard_button_permission_check($board_id, $entry_id){

  global $current_user;

  $board_managers = get_post_meta($board_id, 'board_managers', true);
  $added_user     = get_kingkong_board_meta_value($entry_id, 'login_id');

  if($board_managers){
    $board_managers = unserialize($board_managers);
  } else {
    $board_managers = array();
  }

  if(is_user_logged_in()){
    $user_login = $current_user->user_login;
  } else {
    $user_login = null;
  }

  if( (in_array($user_login, $board_managers)) or current_user_can('manage_options') or ($added_user == $current_user->ID)){
    return true;
  } else {
    return false;
  }
}

function get_kingkongboard_skin_path($type, $slug){
  $skins = get_option("kingkongboard_skins", true);
  $skin_path = null;
    if($skins){
      $skins = maybe_unserialize($skins);
      if( !empty($skins) && (is_array($skins)) ){
        foreach($skins as $skin){
          if( ($skin['slug'] == $slug) && ($skin['type'] == $type) ){
            $skin_path = array(
              'plugins_url' => $skin['plugins_url'],
              'abspath'     => $skin['abspath'],
              'core'        => $skin['core']
            );
          }
        }
      }
      return $skin_path;
    } else {
      return false;
    } 
}

// check the current post for the existence of a short code
function check_board_shortcode_using( $slug ) {

  $found = array();

  $args = array(
    'posts_per_page' => -1,
    'post_type'      => array('post', 'page'),
    'post_status'    => 'publish'
  );

  $posts = get_posts($args);

  if($posts){
    foreach($posts as $post){
      if( stripos($post->post_content, '['.$slug ) !== false ){
        $found[] = $post->ID;
      }
    }
  } else {
    $found = false;
  }
    return $found;
}


function kingkongboard_captcha_initialize($board_id, $response){

  $board_captcha      = get_post_meta($board_id, 'board_captcha', true);
  $board_captcha_key  = get_post_meta($board_id, 'board_captcha_key', true);
  $captcha_secret_key = null;

  if($board_captcha_key){
    $keys = unserialize($board_captcha_key);
    $captcha_secret_key = $keys['secret_key'];
  }

  if($board_captcha == "T" && $captcha_secret_key != null){
    $url          = "https://www.google.com/recaptcha/api/siteverify";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch, CURLOPT_SSLVERSION,3);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "secret=".$captcha_secret_key."&response=".$response ); // Post 값 Get 방식처럼적는다.
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response     = curl_exec ($ch);
    curl_close($ch);
    $response     = json_decode($response, true);
    if($response['success'] == true){
      return true;
    } else {
      return false;
    }
  } else {
    return true;
  }
}

function kingkongboard_set_prefix(){
    if(get_option('permalink_structure') || get_option('permalink_structure') != '' || is_front_page()){
      $prm_pfx = "?";
    } else {
      $prm_pfx = "&";
    }
  return $prm_pfx;
}

function kkb_update_hit_count($board_id, $entry_id){
  $hit = get_post_meta($entry_id, 'kingkongboard_hits', true);
  if(!$hit){
    $hit = 0;
  }
  if(isset($_SESSION['cnt_list'])){
    $cnt_list = $_SESSION['cnt_list'];
    $cnt_list_dummy = explode(";", $cnt_list);
    $board_cnt_ok = 0;

    for($i = 0; $i < sizeof($cnt_list_dummy); $i++){
      if($cnt_list_dummy[$i] == $board_id."_".$entry_id){
        $board_cnt_ok = 1;
        break;
      }
    }
    if($board_cnt_ok == 0){
      update_post_meta($entry_id, 'kingkongboard_hits', ($hit+1) );
      $_SESSION['cnt_list'] .= ";".$board_id."_".$entry_id;
    }
  } else {
    update_post_meta($entry_id, 'kingkongboard_hits', $hit+1 );
    $_SESSION['cnt_list'] = ";".$board_id."_".$entry_id;
  }
  $hit = get_post_meta($entry_id, 'kingkongboard_hits', true);

  return apply_filters('kingkongboard_hit_count', $hit, $board_id, $entry_id);

}

add_action( 'wp_ajax_kkb_backup_reset', 'kkb_backup_reset' );

function kkb_backup_reset(){
  include KINGKONGBOARD_ABSPATH.'/class/class.KKB_Backup.php';
  $result = array();

  $Recovery = new KKB_Backup();
  $status   = $Recovery->delete_all_kkb_posts();

  if($status > 0){
    $result['status'] = 'failed';
    $result['log']    = $status;
  } else {
    $result['status'] = 'success';
  }

  header( "Content-Type: application/json" );
  echo json_encode($result);
  exit(); 
}

add_action( 'wp_ajax_kkb_backup_insert_board_meta', 'kkb_backup_insert_board_meta' );

function kkb_backup_insert_board_meta(){
  $result = array();
  $data = array(
    'board_id'                      => sanitize_text_field($_POST['board_id']),
    'kingkongboard_title'           => sanitize_text_field($_POST['title']),
    'kingkongboard_slug'            => str_replace("kkb_", "", sanitize_text_field($_POST['slug'])),
    'kingkongboard_shortcode'       => sanitize_text_field($_POST['shortcode']),
    'kingkongboard_rows'            => sanitize_text_field($_POST['rows']),
    'kingkongboard_editor'          => sanitize_text_field($_POST['editor']),
    'kingkongboard_search'          => sanitize_text_field($_POST['search_filter']),
    'kingkongboard_thumbnail_dp'    => sanitize_text_field($_POST['thumbnail_dp']),
    'kingkongboard_thumbnail_input' => sanitize_text_field($_POST['thumbnail_input']),
    'board_skin'                    => sanitize_text_field($_POST['board_skin']),
    'permission_read'               => sanitize_text_field($_POST['permission_read']),
    'permission_delete'             => sanitize_text_field($_POST['permission_delete']),
    'permission_write'              => sanitize_text_field($_POST['permission_write']),
    'board_comment'                 => sanitize_text_field($_POST['board_comment']),
    'kingkongboard_notice_emails'   => sanitize_text_field($_POST['notice_emails']),
    'kingkongboard_comment_options' => sanitize_text_field($_POST['comment_options']),
    'thumbnail_upload'              => sanitize_text_field($_POST['thumbnail_upload']),
    'file_upload'                   => sanitize_text_field($_POST['file_upload']),
    'board_sections'                => sanitize_text_field($_POST['board_sections']),
    'kkb_board_captcha_sitekey'     => sanitize_text_field($_POST['captcha_sitekey']),
    'board_captcha_key'             => sanitize_text_field($_POST['captcha_key']),
    'board_captcha'                 => sanitize_text_field($_POST['captcha']),
    'board_managers'                => sanitize_text_field($_POST['managers']),
    'kkb_basic_form'                => sanitize_text_field($_POST['basic_form']),
    'kkb_exclude_keyword'           => sanitize_text_field($_POST['exclude_keyword']),
    'kkb_reply_use'                 => sanitize_text_field($_POST['reply_use'])
  );
  include KINGKONGBOARD_ABSPATH.'/class/class.KKB_Backup.php';
  $Recovery = new KKB_Backup();
  $status   = $Recovery->importBoardMeta($data);
  if($status){
    $result['status'] = 'success';
  } else {
    $result['status'] = 'failed';
  }

  header( "Content-Type: application/json" );
  echo json_encode($result);
  //echo $status;
  exit();
}

add_action( 'wp_ajax_kkb_backup_insert_entry_meta', 'kkb_backup_insert_entry_meta' );

function kkb_backup_insert_entry_meta(){
  $result = array();
  $post_id = sanitize_text_field($_POST['post_id']);
  $data = array(
    'ID'          => sanitize_text_field($_POST['ID']),
    'board_id'    => sanitize_text_field($_POST['board_id']),
    'post_id'     => $post_id,
    'section'     => sanitize_text_field($_POST['section']),
    'related_id'  => sanitize_text_field($_POST['related_id']),
    'list_number' => sanitize_text_field($_POST['list_number']),
    'depth'       => sanitize_text_field($_POST['depth']),
    'parent'      => sanitize_text_field($_POST['parent']),
    'type'        => sanitize_text_field($_POST['type']),
    'date'        => sanitize_text_field($_POST['date']),
    'guid'        => sanitize_text_field($_POST['guid']),
    'login_id'    => sanitize_text_field($_POST['login_id']),
    'writer'      => sanitize_text_field($_POST['writer'])
  );

  $secret   = null;
  $thumb    = null;
  $attached = null;

  if(isset($_POST['secret'])){
    $secret = sanitize_text_field($_POST['secret']);
  }
  if(isset($_POST['attached'])){
    $attached = sanitize_text_field($_POST['attached']);
  }

  if(isset($_POST['thumbnail'])){
    $thumb = sanitize_text_field($_POST['thumbnail']);
  }

  $meta = array(
    'entry_id'               => $post_id,
    'kingkongboard_hits'     => sanitize_text_field($_POST['hits']),
    'kingkongboard_attached' => $attached,
    'kingkongboard_secret'   => $secret,
    '_thumbnail_id'          => $thumb
  );

  include KINGKONGBOARD_ABSPATH.'/class/class.KKB_Backup.php';
  $result   = array();
  $Recovery = new KKB_Backup();
  $status = $Recovery->importKKBMeta($data);
  
  if($status){
    $result['status'] = 'success';
    $Recovery->importEntryMeta($meta);
  } else {
    $result['status'] = 'failed';
  }

  header( "Content-Type: application/json" );
  echo json_encode($result);
  //echo print_r($meta);
  exit();

}

add_action( 'wp_ajax_remove_kkb_comment_meta', 'remove_kkb_comment_meta' );

function remove_kkb_comment_meta(){
  $result = array();
  include KINGKONGBOARD_ABSPATH.'/class/class.KKB_Backup.php';
  $Recovery = new KKB_Backup();
  $status = $Recovery->delete_all_kkb_comment_meta();
  if($status){
    $result['status'] = 'success';
  } else {
    $result['status'] = 'failed';
  }
  header( "Content-Type: application/json" );
  echo json_encode($result);
  exit();  
}

add_action( 'wp_ajax_kkb_remove_all_kkb_meta', 'kkb_remove_all_kkb_meta' );

function kkb_remove_all_kkb_meta(){
  $result = array();
  include KINGKONGBOARD_ABSPATH.'/class/class.KKB_Backup.php';
  $Recovery = new KKB_Backup();
  $status = $Recovery->delete_all_kkb_meta();
  if($status){
    $result['status'] = 'success';
  } else {
    $result['status'] = 'failed';
  }
  header( "Content-Type: application/json" );
  echo json_encode($result);
  exit();
}

add_action( 'wp_ajax_kkb_remove_temp_file', 'kkb_remove_temp_file' );

function kkb_remove_temp_file(){
  $result     = array();
  $upload_dir = wp_upload_dir();
  $folder     = $upload_dir['basedir'].'/kingkongboard/';
  $status     = array_map('unlink', glob($folder.'*.xml'));
  $error      = null;
  foreach($status as $element){
    if($element != 1){
      $error = 1;
    }
  }
  if($error == 1){
    $result['status'] = "failed";
  } else {
    $result['status'] = "success";
  }

  header( "Content-Type: application/json" );
  echo json_encode($result);
  //echo print_r($status);
  exit();    
}

add_action( 'wp_ajax_kkb_backup_insert_comment_meta', 'kkb_backup_insert_comment_meta' );

function kkb_backup_insert_comment_meta(){
  $result = array();
  $data = array(
    'ID'          => sanitize_text_field($_POST['ID']),
    'lnumber'     => sanitize_text_field($_POST['lnumber']),
    'eid'         => sanitize_text_field($_POST['eid']),
    'cid'         => sanitize_text_field($_POST['cid']),
    'origin'      => sanitize_text_field($_POST['origin']),
    'parent'      => sanitize_text_field($_POST['parent']),
    'depth'       => sanitize_text_field($_POST['depth'])
  );
  include KINGKONGBOARD_ABSPATH.'/class/class.KKB_Backup.php';
  $result   = array();
  $Recovery = new KKB_Backup();
  $status = $Recovery->importCommentMeta($data);
  if($status){
    $result['status'] = 'success';
  } else {
    $result['status'] = 'failed';
  }
  header( "Content-Type: application/json" );
  echo json_encode($result);
  //echo $result;
  exit();  
}

add_action( 'wp_ajax_kkb_backup_insert_comments', 'kkb_backup_insert_comments' );

function kkb_backup_insert_comments(){
  $result = array();
  $data = array(
    'comment_ID'            => sanitize_text_field($_POST['comment_ID']),
    'comment_post_ID'       => sanitize_text_field($_POST['comment_post_ID']),
    'comment_author'        => sanitize_text_field($_POST['comment_author']),
    'comment_author_email'  => sanitize_text_field($_POST['comment_author_email']),
    'comment_author_url'    => sanitize_text_field($_POST['comment_author_url']),
    'comment_author_IP'     => sanitize_text_field($_POST['comment_author_IP']),
    'comment_date'          => sanitize_text_field($_POST['comment_date']),
    'comment_date_gmt'      => sanitize_text_field($_POST['comment_date_gmt']),
    'comment_content'       => sanitize_text_field($_POST['comment_content']),
    'comment_karma'         => sanitize_text_field($_POST['comment_karma']),
    'comment_approved'      => sanitize_text_field($_POST['comment_approved']),
    'comment_agent'         => sanitize_text_field($_POST['comment_agent']),
    'comment_type'          => sanitize_text_field($_POST['comment_type']),
    'comment_parent'        => sanitize_text_field($_POST['comment_parent']),
    'user_id'               => sanitize_text_field($_POST['user_id'])
  );
  include KINGKONGBOARD_ABSPATH.'/class/class.KKB_Backup.php';
  $result   = array();
  $Recovery = new KKB_Backup();
  $status = $Recovery->importComments($data);
  if($status){
    $result['status'] = 'success';
  } else {
    $result['status'] = 'failed';
  }
  header( "Content-Type: application/json" );
  echo json_encode($result);
  //echo $result;
  exit();
}

add_action( 'wp_ajax_kkb_backup_insert_post', 'kkb_backup_insert_post' );

function kkb_backup_insert_post(){
  $result = array();
  $data = array(
    'ID'                    => sanitize_text_field($_POST['ID']),
    'post_author'           => sanitize_text_field($_POST['post_author']),
    'post_date'             => sanitize_text_field($_POST['post_date']),
    'post_date_gmt'         => sanitize_text_field($_POST['post_date_gmt']),
    'post_content'          => sanitize_text_field($_POST['post_content']),
    'post_title'            => sanitize_text_field($_POST['post_title']),
    'post_excerpt'          => sanitize_text_field($_POST['post_excerpt']),
    'post_status'           => sanitize_text_field($_POST['post_status']),
    'comment_status'        => sanitize_text_field($_POST['comment_status']),
    'ping_status'           => sanitize_text_field($_POST['ping_status']),
    'post_password'         => sanitize_text_field($_POST['post_password']),
    'post_name'             => sanitize_text_field($_POST['post_name']),
    'to_ping'               => sanitize_text_field($_POST['to_ping']),
    'pinged'                => sanitize_text_field($_POST['pinged']),
    'post_modified'         => sanitize_text_field($_POST['post_modified']),
    'post_modified_gmt'     => sanitize_text_field($_POST['post_modified_gmt']),
    'post_content_filtered' => sanitize_text_field($_POST['post_content_filtered']),
    'post_parent'           => sanitize_text_field($_POST['post_parent']),
    'guid'                  => sanitize_text_field($_POST['guid']),
    'menu_order'            => sanitize_text_field($_POST['menu_order']),
    'post_type'             => sanitize_text_field($_POST['post_type']),
    'post_mime_type'        => sanitize_text_field($_POST['post_mime_type']),
    'comment_count'         => sanitize_text_field($_POST['comment_count'])
  );
  include KINGKONGBOARD_ABSPATH.'/class/class.KKB_Backup.php';
  $result   = array();
  $Recovery = new KKB_Backup();
  $status = $Recovery->importPosts($data);
  if($status){
    $result['status'] = 'success';
  } else {
    $result['status'] = 'failed';
  }

  header( "Content-Type: application/json" );
  echo json_encode($result);
  //echo $result;
  exit();
}

function get_kkb_thead_th($entry){
  if(isset($entry['width'])){
    $width = "width='".$entry['width']."px"."'";
  } else {
    $width = null;
  }
  $value = "<th class='entry-th-".$entry['value']."' ".$width."><span><label>".$entry['label']."</label></span></th>";
  return apply_filters('get_kkb_thead_th_after', $value, $entry);
}

add_action( 'wp_ajax_srshop_product_detail', 'srshop_product_detail' );
function srshop_product_detail(){
  $product_id = $_POST['product_id'];
  $srShop     = new srShop();
  $response   = $srShop->getDetail($product_id);
  header( "Content-Type: application/json" );
  echo json_encode($response);
  exit();
}














?>