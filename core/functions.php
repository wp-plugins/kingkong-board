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

  add_action( 'wp_ajax_nopriv_kingkongboard_password_verifying', 'kingkongboard_password_verifying');
  add_action( 'wp_ajax_kingkongboard_password_verifying', 'kingkongboard_password_verifying');

  function kingkongboard_password_verifying(){
    $password       = sanitize_text_field($_POST['password']);
    $input_pwd      = md5($password);
    $view           = sanitize_text_field($_POST['view']);
    $entry_id       = sanitize_text_field($_POST['entry_id']);
    $board_id       = sanitize_text_field($_POST['board_id']);
    $guid           = get_kingkong_board_meta_value($entry_id, 'guid');
    $entry_password = get_post_meta($entry_id, 'kingkongboard_entry_password', true);
    $result         = array();

    if($input_pwd == $entry_password){
      $result['status'] = "success";
      if($view == "delete"){
        $KingkongEntries = new KingkongBoard_Entries($board_id);
        $KingkongEntries->Kingkong_Board_Delete_Entry($entry_id);
        $result['redirect'] = get_the_permalink($guid);
      }
    } else {
      $result['status'] = "failed";
    }

    header( "Content-Type: application/json" );
    echo json_encode($result);     

    exit();
  }

  add_action( 'kingkongboard_save_comment_after', 'kingkongboard_comment_notice_sender', 10, 2);

  function kingkongboard_comment_notice_sender($entry_id, $content){

    global $current_user;
    get_currentuserinfo();
    $board_id                 = get_kingkong_board_meta_value($entry_id, 'board_id');
    $user_name                = $current_user->display_name;
    $user_email               = $current_user->user_email;
    $setting_comment_notice   = get_post_meta($board_id, 'kingkongboard_notice_comment', true);
    $setting_notice_emails    = get_post_meta($board_id, 'kingkongboard_notice_emails', true);
    $setting_notice_email     = explode(",", $setting_notice_emails);
    $get_content              = strip_tags($content);

    if(is_user_logged_in()){
      $headers            = 'From: '.$user_name.' <'.$user_email.'>' . "\r\n";
    } else {
      $headers            = 'From: '.__('킹콩보드 콩돌이', 'kingkongboard').' <'.get_bloginfo('admin_email').'>' . "\r\n";
    }

    if($setting_comment_notice == "checked"){
      if($setting_notice_emails){
        foreach($setting_notice_email as $email){
          wp_mail($email, '['.get_bloginfo('name').'-'.__('킹콩보드', 'kingkongboard').'] '.__('새 댓글이 등록 되었습니다.', 'kingkongboard'), $get_content, $headers);
        }
      }
    }

  }

  add_action( 'kingkongboard_entry_save_after', 'kingkongboard_notice_sender', 10, 2);

  function kingkongboard_notice_sender($board_id, $entry_id){

    global $current_user;
    get_currentuserinfo();
    $user_name                = $current_user->display_name;
    $user_email               = $current_user->user_email;
    $setting_entry_notice     = get_post_meta($board_id, 'kingkongboard_notice_entry', true);
    $setting_notice_emails    = get_post_meta($board_id, 'kingkongboard_notice_emails', true);
    $setting_notice_email     = explode(",", $setting_notice_emails);
    $get_content              = get_post_field('post_content', $entry_id);
    $get_content              = strip_tags($get_content);

    if(is_user_logged_in()){
      $headers            = 'From: '.$user_name.' <'.$user_email.'>' . "\r\n";
    } else {
      $headers            = 'From: '.__('킹콩보드 콩돌이', 'kingkongboard').' <'.get_bloginfo('admin_email').'>' . "\r\n";
    }

    if($setting_entry_notice == "checked"){
      if($setting_notice_emails){
        foreach($setting_notice_email as $email){
          wp_mail($email, '['.get_bloginfo('name').'-'.__('킹콩보드', 'kingkongboard').'] '.__('새 글', 'kingkongboard').' : '.get_the_title($entry_id), $get_content, $headers);
        }
      }
    }
  }

  // email validation
  function kingkongboard_email_valid($email) { 
    return preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $email);
  } 

  add_action( 'wp_ajax_nopriv_kingkongboard_entry_comment_validation', 'kingkongboard_entry_comment_validation');
  add_action( 'wp_ajax_kingkongboard_entry_comment_validation', 'kingkongboard_entry_comment_validation');

  function kingkongboard_entry_comment_validation(){
    $result                 = array();
    $comment_writer         = sanitize_text_field($_POST['writer']);
    $comment_email          = sanitize_text_field($_POST['email']);
    $comment_content        = sanitize_text_field($_POST['content']);
    $result['status']       = "failed";

    if(!is_user_logged_in()){
      if(!$comment_writer){
        $result['message']  = kingkongboard_error_message("writer_empty");
      } else if(!$comment_email){
        $result['message']  = kingkongboard_error_message("email_empty");
      } else if( kingkongboard_email_valid($comment_email) == false ){
        $result['message']  = kingkongboard_error_message("email_not_valid");
      } else if(!$comment_content){
        $result['message']  = kingkongboard_error_message("content_empty");
      } else {
        $result['status']   = "success";
      }
    } else {
      if(!$comment_content){
        $result['message']  = kingkongboard_error_message("content_empty");
      } else {
        $result['status']   = "success";
      }
    }
    header( "Content-Type: application/json" );
    echo json_encode($result); 
    exit();
  }

  add_action( 'wp_ajax_nopriv_kingkongboard_entry_validation', 'kingkongboard_entry_validation');
  add_action( 'wp_ajax_kingkongboard_entry_validation', 'kingkongboard_entry_validation');

  function kingkongboard_entry_validation(){

    $result               = array();
    $entry_id             = sanitize_text_field($_POST['entry_id']);
    $entry_pwd            = sanitize_text_field($_POST['pwd']);
    $entry_title          = sanitize_text_field($_POST['title']);
    $entry_writer         = sanitize_text_field($_POST['writer']);
    $entry_content        = sanitize_text_field($_POST['content']);
    $board_id             = get_kingkong_board_meta_value($entry_id, 'board_id');
    $added_user           = get_kingkong_board_meta_value($entry_id, 'login_id');
    $entry_secret         = get_post_meta($entry_id, 'kingkongboard_entry_password', true);
    $entry_password       = get_post_meta($entry_id, 'kingkongboard_entry_password', true);
    $board_managers       = get_post_meta($board_id, 'board_managers', true);

    $result['status']     = "failed";

    if(!$entry_title){
      $result['message']  = kingkongboard_error_message("title_empty");
    } else if (!is_user_logged_in() && !$entry_writer){
      $result['message']  = kingkongboard_error_message("writer_empty");
    } else if(!is_user_logged_in() && !$entry_pwd){
      $result['message']  = kingkongboard_error_message("password_empty");
    } else if(!$entry_content){
      $result['message']  = kingkongboard_error_message("content_empty");
    } else {
      $result['status']   = "success";
    }

    header( "Content-Type: application/json" );
    echo json_encode($result); 

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
      if(($added_user != $current_user->ID) && ($added_user != 0)){
        $result['message'] = kingkongboard_error_message("modify_just_writer");
      } else {
        $result['message'] = kingkongboard_error_message("password_not_equal");
      }
    }

    header( "Content-Type: application/json" );
    echo json_encode($result); 

    exit();

  }

  add_action( 'wp_ajax_nopriv_kingkongboard_entry_save', 'kingkongboard_entry_save');
  add_action( 'wp_ajax_kingkongboard_entry_save', 'kingkongboard_entry_save');

  function kingkongboard_entry_save(){
    $board_id = sanitize_text_field($_POST['board_id']);
    $post_id  = sanitize_text_field($_POST['post_id']);
    $result   = array();
    parse_str($_POST['data'], $options);

    $Board    = new KingkongBoard_Entries($board_id);
    $callBack = $Board->Kingkong_Board_Insert_Entry($options);

    if($callBack){
      $result['status']     = 'success';
      $result['redirect']   = get_the_permalink($post_id);
    } else {
      $result['status']     = 'failed';
      $result['redirect']   = null;
    }

    header( "Content-Type: application/json" );
    echo json_encode($result); 

    exit();
  }

  add_action( 'wp_ajax_nopriv_kingkongboard_comment_list', 'kingkongboard_comment_list');
  add_action( 'wp_ajax_kingkongboard_comment_list', 'kingkongboard_comment_list');

  function kingkongboard_comment_list(){

    $entry_id = sanitize_text_field($_POST['entry_id']);
    $board_id = get_board_id_by_entry_id($entry_id);
    $comment_options = get_post_meta($board_id, 'kingkongboard_comment_options', true);
    $board_comment   = get_post_meta($board_id, 'board_comment', true);

    if($board_comment == "T"){
      if($comment_options){
        $comment_options = unserialize($comment_options);
      }

      $args = array(
        'post_id' => $entry_id,
        'parent'  => 0,
        'order'   => 'ASC'
      );

      $comments = get_comments($args);

      $cmContent  = '';
      $cmpContent = null;
      $cmsContent = null;

      foreach($comments as $comment){

        apply_filters('kingkongboard_comment_list_before', $board_id, $comment);

        if($comment->comment_parent == 0){
          if($comment->comment_approved == 1){
            $cmpContent .= '<div class="each-comment">';
            $cmpContent .= '<div class="each-comment-author">';
            $cmpContent .= '<table class="comment-author-table">';
            $cmpContent .= '<tr>';
            if($comment_options['thumbnail'] == "T"){
              $cmpContent .= '<td width="30px">';
              $cmpContent .= get_avatar($comment->comment_author_email, '20');
              $cmpContent .= '</td>';
            }
            $cmpContent .= '<td style="font-weight:'.$comment_options['writer']['font_weight'].'; font-size:'.$comment_options['writer']['font_size'].'; color:'.$comment_options['writer']['color'].'">';
            $cmpContent .= $comment->comment_author;
            $cmpContent .= '</td>';
            $cmpContent .= '<td class="comment-author-date" style="color:'.$comment_options['date']['color'].'">';
            $cmpContent .= get_comment_date( $comment_options['date']['format'], $comment->comment_ID);
            $cmpContent .= '</td>';
            $cmpContent .= '<td style="position:relative">';
            $cmpContent .= '<img src="'.KINGKONGBOARD_PLUGINS_URL.'/assets/images/icon-comment-reply.png" class="icon-comment-reply">';
            $cmpContent .= '<a class="comment-reply-button" data="'.$comment->comment_ID.'">'.__('답글', 'kingkongboard').'</a>';
            $cmpContent .= '</td>';
            $cmpContent .= '</tr>';
            $cmpContent .= '</table>';
            $cmpContent .= '</div>';
            $cmpContent .= '<div class="each-comment-content" style="color:'.$comment_options['content']['color'].'">';
            $cmpContent .= nl2br($comment->comment_content);
            $cmpContent .= '</div>';
            $cmpContent .= '</div>';
          }

          $cmContent .= apply_filters('kingkongboard_comment_list_parent' , $cmpContent, $comment, $comment_options);

        }

        $subargs = array(
          'post_id' => $entry_id,
          'parent'  => $comment->comment_ID,
          'order'   => 'ASC'
        );

        $subComments = get_comments($subargs);

        if($subComments){
          foreach($subComments as $subcomment){

            apply_filters('kingkongboard_sub_comment_list_before', $board_id, $subcomment);

            if($subcomment->comment_approved == 1){
              $cmsContent .= '<div class="each-comment" style="margin-left:30px; position:relative">';
              $cmsContent .= '<img src="'.KINGKONGBOARD_PLUGINS_URL.'/assets/images/icon-comment-reply.png" class="kkb_comment_reply_icon" style="top:6px">';
              $cmsContent .= '<div class="each-comment-author">';
              $cmsContent .= '<table class="comment-author-table">';
              $cmsContent .= '<tr>';
              if($comment_options['thumbnail'] == "T"){
                $cmsContent .= '<td width="30px">';
                $cmsContent .= get_avatar($subcomment->comment_author_email, '20');
                $cmsContent .= '</td>';
              }
              $cmsContent .= '<td style="font-weight:'.$comment_options['writer']['font_weight'].'; font-size:'.$comment_options['writer']['font_size'].'; color:'.$comment_options['writer']['color'].'">';
              $cmsContent .= $subcomment->comment_author;
              $cmsContent .= '</td>';
              $cmsContent .= '<td class="comment-author-date" style="color:'.$comment_options['date']['color'].'">';
              $cmsContent .= get_comment_date( $comment_options['date']['format'], $subcomment->comment_ID);
              $cmsContent .= '</td>';
              $cmsContent .= '<td style="position:relative">';
              $cmsContent .= '<img src="'.KINGKONGBOARD_PLUGINS_URL.'/assets/images/icon-comment-reply.png" class="icon-comment-reply">';
              $cmsContent .= '<a class="comment-reply-button" data="'.$comment->comment_ID.'">'.__('답글', 'kingkongboard').'</a>';
              $cmsContent .= '</td>';
              $cmsContent .= '</tr>';
              $cmsContent .= '</table>';
              $cmsContent .= '</div>';
              $cmsContent .= '<div class="each-comment-content" style="color:'.$comment_options['content']['color'].'">';
              $cmsContent .= nl2br($subcomment->comment_content);
              $cmsContent .= '</div>';
              $cmsContent .= '</div>';
            }

            $cmContent .= apply_filters('kingkongboard_comment_list_sub' , $cmsContent, $subcomment, $comment_options);
          }      
        }
      }
    } else {
      $cmContent = false;
    }

    echo $cmContent;
    exit();

  }


  add_action( 'wp_ajax_nopriv_kingkongboard_comment_save', 'kingkongboard_comment_save');
  add_action( 'wp_ajax_kingkongboard_comment_save', 'kingkongboard_comment_save');

  function kingkongboard_comment_save(){
    include_once(ABSPATH . 'wp-includes/pluggable.php');

    $content  = sanitize_text_field($_POST['content']);
    $entry_id = sanitize_text_field($_POST['entry_id']);
    $comment_parent = sanitize_text_field($_POST['comment_parent']);
    if($comment_parent){
      $parent   = $comment_parent;
    } else {
      $parent   = 0;
    }

    if(is_user_logged_in()){
      global $current_user;
      get_currentuserinfo();
      $writer   = $current_user->display_name;
      $email    = $current_user->user_email;
      $user_id  = $current_user->ID;
    } else {
      $writer   = sanitize_text_field($_POST['writer']);
      $email    = sanitize_text_field($_POST['email']);
      $user_id  = 0;
    }

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }


    $time = current_time('mysql');

    $data = array(
        'comment_post_ID' => $entry_id,
        'comment_author' => $writer,
        'comment_author_email' => $email,
        'comment_author_url' => '',
        'comment_content' => $content,
        'comment_type' => '',
        'comment_parent' => $parent,
        'user_id' => $user_id,
        'comment_author_IP' => $ip,
        'comment_agent' => $_SERVER['HTTP_USER_AGENT'],
        'comment_date' => $time,
        'comment_approved' => 1,
    );

    wp_insert_comment($data);
    
    do_action('kingkongboard_save_comment_after', $entry_id, $content);

    exit();
  }


  add_action( 'wp_ajax_nopriv_kingkongboard_entry_modify', 'kingkongboard_entry_modify');
  add_action( 'wp_ajax_kingkongboard_entry_modify', 'kingkongboard_entry_modify');

  function kingkongboard_entry_modify(){
    $board_id = sanitize_text_field($_POST['board_id']);
    $post_id  = sanitize_text_field($_POST['post_id']);
    $result   = array();
    parse_str($_POST['data'], $options);

    $Board    = new KingkongBoard_Entries($board_id);
    $callBack = $Board->Kingkong_Board_Modify_Entry($options);

    if($callBack){
      $result['status']     = 'success';
      $result['redirect']   = get_the_permalink($post_id);
    } else {
      $result['status']     = 'failed';
      $result['redirect']   = null;
    }

    header( "Content-Type: application/json" );
    echo json_encode($result); 

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

  add_action( 'wp_ajax_remove_kingkong_board_each_entry', 'remove_kingkong_board_each_entry' );

  function remove_kingkong_board_each_entry(){
    $result   = array();
    $entry_id = sanitize_text_field($_POST['entry_id']);
    $board_id = sanitize_text_field($_POST['board_id']);
    $status   = wp_delete_post($entry_id);
    $board    = new KingkongBoard_Entries($board_id);
    $board->entry_remove_changer($entry_id);
 
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

    $Board    = new KingkongBoard_Entries($board_id);

    $args = array(
      'post_type'       => $Board->board_slug,
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

    $BasicSettings    = array(
      'board_name'                => $board_name,
      'board_slug'                => $board_slug,
      'board_shortcode'           => $board_shortcode,
      'board_rows'                => $board_rows,
      'board_editor'              => $board_editor,
      'board_thumbnail_display'   => $board_thumbnail_display,
      'board_thumbnail_input'     => $board_thumbnail_input,
      'board_search'              => $board_search
    );

    $KingkongBoard = new KingkongBoard($BasicSettings);

    switch($page_type){
      case "create" :
        $Status             = $KingkongBoard->CreateBoard();
        $result['board_id'] = $Status['board_id'];
      break;

      case "modify" :
        $board_id         = $options['board_id'];
        $Status           = $KingkongBoard->ModifyBoard($board_id); 
      break;
    }
    if(isset($KingkongBoard->board_id)){
      $board_id           = $KingkongBoard->board_id;
    } else {
      $board_id = null;
    }

    $result['status']   = $Status['status'];
    $result['message']  = $Status['message'];

    do_action('create_kingkong_board_after', $board_id, $options);

    header( "Content-Type: application/json" );
    echo json_encode($result);  
    //echo print_r($result);

    exit();

  }

  add_filter('admin_kingkong_board_entry_columns', 'admin_kingkong_board_entry_columns', 10, 2);

  function admin_kingkong_board_entry_columns($entries, $board_id){
    $entries = array(
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

    return $entries;
  }
 
  add_filter('kingkong_board_manage_entry_column', 'kingkong_board_manage_entry_column', 10, 3);

  function kingkong_board_manage_entry_column($entries, $entry_id, $cnt){

    $return_content = '';
    foreach($entries as $entry){

      $entry_type       = '';
      $entry_type       = get_kingkong_board_meta_value($entry_id, 'type');
      $entry_attachment = get_post_meta($entry_id, 'kingkongboard_attached', true);
      $entry_secret     = get_post_meta($entry_id, 'kingkongboard_secret', true);

      $board_sections   = get_post_meta(get_board_id_by_entry_id($entry_id), 'board_sections', true);


      switch($entry['value']){

        case "number" :
          if($entry_type == 1){
            $return_content .= "<td class='kingkongboard-list-".$entry['value']."'>공지</td>";
          } else {
            $return_content .= "<td class='kingkongboard-list-".$entry['value']."'>".$cnt."</td>";
          }
        break;

        case "section" :
        if($board_sections){
          $section      = get_kingkong_board_meta_value($entry_id, 'section');
          if($section != "0"){
            $return_content .= "<td class='kingkongboard-list-".$entry['value']."'>".kingkongboard_text_cut($section, 5, "...")."</td>";
          } else {
            $return_content .= "<td class='kingkongboard-list-".$entry['value']."'></td>";
          }
        }
          
        break;

        case "title" :
        if(isset($_GET['pageid'])){
          $pageid = sanitize_text_field($_GET['pageid']);
        } else {
          $pageid = null;
        }
        if(!$pageid){
          $pageid = 1;
        }

        $thumbnail_image = get_the_post_thumbnail( $entry_id, array(22,22) );

        if($thumbnail_image){
          $board_id           = get_board_id_by_entry_id($entry_id);
          $board_thumbnail_dp = get_post_meta($board_id, 'kingkongboard_thumbnail_dp', true);
          if($board_thumbnail_dp == "display"){ 
            $thumbnail = '<div class="entry_thumbnail_image">'.$thumbnail_image.'</div>';
          } else {
            $thumbnail = "";
          }
        } else {
          $thumbnail = "";
        }



        $args = array('post_id' => $entry_id, 'status'=>'approve', 'count' => true);
        $comments = get_comments($args);

        if($comments > 0){
          $comments_count = " <span class='entry_comment_count'>[<strong>".$comments."</strong>]</span>";
        } else {
          $comments_count = null;
        }

        if($entry_attachment){
          $entry_attach_text = '<div style="display:inline; padding:0; margin:0">'.apply_filters('kingkongboard_entry_attachment_icon', "<img src='".KINGKONGBOARD_PLUGINS_URL."/assets/images/icon-attachment.png' style='width:15px; height:auto; position:relative; top:0px'>" ).'</div>';
        } else {
          $entry_attach_text = "";
        }

        if($entry_secret){
          $entry_secret_icon = '<div style="display:inline"><img src="'.KINGKONGBOARD_PLUGINS_URL.'/assets/images/icon-locked.png" style="width:auto; height:15px; position:relative; top:-2px; margin-left:5px"></div>';
        } else {
          $entry_secret_icon = '';
        }

        $parent      = get_kingkong_board_meta_value($entry_id, 'parent');
        $entry_depth = get_kingkong_board_meta_value($entry_id, 'depth');
        if($entry_depth > 1){
          $padding        = 5 * $entry_depth;
          $reply_padding  = ' padding-left:'.$padding.'px;';
          $reply_icon     = '<img src="'.KINGKONGBOARD_PLUGINS_URL.'/assets/images/icon-reply.gif" style="width:25px; height:auto; position:relative; top:0px; margin-right:5px">';
          $parent_id      = '&prnt='.$parent;
        } else {
          $reply_padding  = '';
          $reply_icon     = '';
          $parent_id      = '';
        }
        if(get_option('permalink_structure')){
          $prm_pfx = "?";
        } else {
          $prm_pfx = "&";
        }

          $writer = get_kingkong_board_meta_value($entry_id, 'writer');
          $writer = kingkongboard_text_cut($writer, 6, "...");
          $hit = get_post_meta($entry_id, 'kingkongboard_hits', true);

          $mobile_writer_info  = "<div class='mobile-writer-info'>";
          $mobile_writer_info .= "<ul>";
          $mobile_writer_info .= "<li>".__('작성자', 'kingkongboard')." : ".$writer."</li>";
          $mobile_writer_info .= "<li>".__('작성일', 'kingkongboard')." : ".get_the_date('Y.m.d', $entry_id)."</li>";
          $mobile_writer_info .= "<li>".__('조회수', 'kingkongboard')." : ".$hit."</li>";
          $mobile_writer_info .= "</ul>";
          $mobile_writer_info .= "</div>";

          $return_content .= "<td style='position:relative;".$reply_padding."' class='kingkongboard-list-".$entry['value']."'>".$reply_icon.$thumbnail."<a href='".get_the_permalink().$prm_pfx."pageid=".$pageid."&view=read&id=".$entry_id.$parent_id."'>".get_the_title($entry_id)."</a> ".$comments_count.$entry_secret_icon." ".$entry_attach_text.$mobile_writer_info."</td>";
        break;

        case "date" :
          $return_content .= "<td class='kingkongboard-list-".$entry['value']."'>".get_the_date('Y.m.d', $entry_id)."</td>";
        break;

        case "writer" :
          $writer = get_kingkong_board_meta_value($entry_id, 'writer');
          $writer = kingkongboard_text_cut($writer, 6, "...");
          if(!$writer){
            $writer = __('비회원', 'kingkongboard');
          }
          $return_content .= "<td class='kingkongboard-list-".$entry['value']."'>".$writer."</td>";
        break;

        case "hit" :
          $hit = get_post_meta($entry_id, 'kingkongboard_hits', true);

          if(!$hit){
            $hit = 0;
          }

          $return_content .= '<td class="kingkongboard-list-'.$entry['value'].'">'.$hit.'</td>';

        break;

      }
    }

    return $return_content;

  }

  add_filter('admin_kingkong_board_manage_entry_column', 'admin_kingkong_board_manage_entry_column', 10, 2);

  function admin_kingkong_board_manage_entry_column($entries, $entry_id){

    $return_content = '';

    foreach($entries as $entry){
      switch($entry['value']){

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
          $post = get_post($entry_id);
          $writer = kingkongboard_text_cut($writer, 8, "...");
          $return_content .= "<td>".$writer."</td>";
        break;

        case "options" :
          $return_content .= "<td style='text-align:left'><a class='button-kkb kkbred button-remove-each-entry' data='".$entry_id."'><i class='kkb-icon kkb-icon-trash'></i></a></td>";
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
    $board = new KingkongBoard_Entries($board_id);
    $result = $wpdb->get_row("SELECT ".$key." FROM ".$board->meta_table." WHERE post_id = '".$board_id."' ");
    if($result){
      return $result->$key;
    }
  }

  function get_post_id_by_list_number($board_id, $list_number){
    global $wpdb;
    $board = new KingkongBoard_Entries($board_id);
    $result = $wpdb->get_row("SELECT post_id FROM ".$board->meta_table." WHERE list_number = '".$list_number."' ");
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

function kingkongboard_get_icon_for_attachment($post_id) {
  $base = KINGKONGBOARD_PLUGINS_URL . "/assets/images/mime-icons/";
  $type = get_post_mime_type($post_id);
  switch ($type) {
    case 'image/jpeg':
    case 'image/png':
    case 'image/gif':
      return $base . "image.png"; break;
    case 'application/excel':
    case 'application/vnd.ms-excel':
    case 'application/x-excel':
    case 'application/x-msexcel':
    case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' :
      return $base . "xls.png"; break;
    case 'application/pdf':
      return $base . "pdf.png"; break;
    case 'application/x-compressed':
    case 'application/x-zip-compressed':
    case 'application/zip':
    case 'application/multipart/x-zip':
      return $base . "zip.png"; break;
    case 'video/mpeg':
    case 'video/mp4': 
    case 'video/quicktime':
      return $base . "movie.png"; break;
    case 'text/csv':
    case 'text/plain': 
    case 'text/xml':
      return $base . "file.png"; break;
    default:
      return $base . "file.png";

    // hwp 파일 안올라
  }
}

function get_kingkongboard_permission_delete_by_role_name($board_id, $role_name){
  $delete_status     = null;
  $permission_delete = get_post_meta($board_id, 'permission_delete', true);
  if($permission_delete){
    $permission_delete = unserialize($permission_delete);
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
    $permission_write = unserialize($permission_write);
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
    $permission_read = unserialize($permission_read);
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

  if(isset($options['permission_write'])){
    update_post_meta($board_id, 'permission_write', serialize($options['permission_write']));
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

  if(isset($options['permission_delete'])){
    update_post_meta($board_id, 'permission_delete', serialize($options['permission_delete']));
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

function kingkongboard_add_skin($type, $slug, $title, $plugins_url, $abspath, $core){
  if(!$type || !$slug || !$title){
    return false;
  } else {
    $duplicate = 0;
    $skins = get_option("kingkongboard_skins");
    if($skins != null){
      $skins = maybe_unserialize($skins);
      if($skins != false){
        foreach($skins as $skin){
          if($skin['slug'] == $skin['slug'] && $skin['type'] == $skin['type']){
            $duplicate = 1;
          }
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

?>
