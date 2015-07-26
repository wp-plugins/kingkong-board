<?php
/**
 * 킹콩보드 워드프레스 게시판 쓰기 및 수정 삭제 관련 컨트롤러
 * @link www.superrocket.io
 * @copyright Copyright 2015 SuperRocket. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
*/


class KKB_Controller {

/**
 * 게시판 아이디를 넘겨 받아 config 클래스를 호출하여 해당 게시판의 기본 정보를 설정한다.
 * @param int $bid
*/
  function __construct($bid){
    $this->config = new KKB_Config($bid);
  }

/**
 * data 값을 받아 글을 등록하는 함수
 * @param string $data
 * @return boolen
*/
  public function kkb_entry_write($data, $mode){
    $config         = $this->config;
    $entry_title    = $data['entry_title'];
    $entry_content  = $data['entry_content'];
    $entry_title    = kingkongboard_xssfilter(kingkongboard_htmlclear($entry_title));
    $entry_content  = kingkongboard_xssfilter(kingkongboard_htmlclear($entry_content));

    // 관리자 모드 글쓰기 등록이라면
    if( $mode == "admin" ){
      $post_date = $data['entry_ymd']." ".sprintf("%02d", $data['entry_h']).":".sprintf("%02d", $data['entry_i']).":".sprintf("%02d", $data['entry_s']);
      $entry = array(
        'post_title'    => $entry_title,
        'post_content'  => $entry_content,
        'post_date'     => $post_date,
        'post_status'   => 'publish',
        'post_type'     => $config->board_slug
      ); 
    } else {
      $entry = array(
        'post_title'    => $entry_title,
        'post_content'  => $entry_content,
        'post_status'   => 'publish',
        'post_type'     => $config->board_slug
      );
    }
    $entry_id = wp_insert_post($entry);

    if(!is_wp_error($entry_id)){
      // 포스트 메타 정보와 킹콩보드 메타 테이블에 정보 삽입
      $this->mode = $mode;
      $this->kkb_entry_write_meta($data, $entry_id);
      return $entry_id;
    }
  }

/**
 * data 값을 받아 글을 수정하는 함수
 * @param string $data
 * @return boolen
*/
  public function kkb_entry_modify($data, $mode){
    $config         = $this->config;
    $entry_id       = $data['entry_id'];
    $entry_title    = $data['entry_title'];
    $entry_content  = $data['entry_content'];
    $entry_title    = kingkongboard_xssfilter(kingkongboard_htmlclear($entry_title));
    $entry_content  = kingkongboard_xssfilter(kingkongboard_htmlclear($entry_content));
    $entry = array(
      'ID'            => $entry_id,
      'post_title'    => $entry_title,
      'post_content'  => $entry_content
    );

    $callback = wp_update_post( $entry );

    if(!is_wp_error($callback)){
      global $wpdb;
      if(isset($data['entry_notice'])){ 
        switch($data['entry_notice']){
          case "notice" :
            $wpdb->update(
              $config->meta_table,
              array( 'type'     => 1, 'section'  => $data['entry_section'] ),
              array( 'board_id' => $config->bid, 'post_id'  => $entry_id ),
              array( '%d', '%s' ),
              array( '%d', '%d' )
            );
          break;

          default :
            $wpdb->update(
              $config->meta_table,
              array( 'type'     => 0, 'section'  => $data['entry_section'] ),
              array( 'board_id' => $config->bid, 'post_id'  => $entry_id ),
              array( '%d', '%s' ),
              array( '%d', '%d' )
            );
          break;
        }
      } else {
        $wpdb->update(
          $config->meta_table,
          array( 'type'     => 0, 'section'  => $data['entry_section'] ),
          array( 'board_id' => $config->bid, 'post_id'  => $entry_id ),
          array( '%d', '%s' ),
          array( '%d', '%d' )
        );          
      }
      if(isset($data['entry_secret'])){
        update_post_meta($entry_id, 'kingkongboard_secret', 'on');
      } else {
        update_post_meta($entry_id, 'kingkongboard_secret', null);
      }

      $result = $entry_id;
    } else {
      $result = false;
    }  
    return $result;
  }

/**
 * entry_id (post_id) 를 받아 해당 글을 삭제한다.
 * @param string $data
 * @return boolen
*/
  public function kkb_entry_remove($entry_id){
    wp_delete_post($entry_id);
    $this->kkb_entry_remove_changer($entry_id);
  }

/**
 * data, entry_id 값을 받아 포스트 메타 정보를 업데이트 한다.
 * @param string $data, $entry_id
*/
  public function kkb_entry_write_meta($data, $entry_id){
    if($data && $entry_id){
      if(isset($data['entry_attachment'])){
        $entry_attachments = serialize($data['entry_attachment']);
        update_post_meta($entry_id, 'kingkongboard_attached', $entry_attachments);
      }
      if(isset($data['entry_password'])){
        $entry_secret = $data['entry_password'];
        $entry_secret = md5($entry_secret);
        update_post_meta($entry_id, 'kingkongboard_entry_password', $entry_secret);
      }
      if(isset($data['entry_secret'])){
        update_post_meta($entry_id, 'kingkongboard_secret', 'on');
      }
      $this->kkb_entry_write_kkbtable($data, $entry_id);
    } else {
      return kingkongboard_error_message("kkb_entry_write_meta");
    }
  }

/**
 * data, entry_id 값을 받아 킹콩보드 메타 테이블에 레코드를 삽입한다.
 * @param string $entry_id
 * @return boolen
*/
  public function kkb_entry_write_kkbtable($data, $entry_id){
    if($data && $entry_id){
      global $wpdb, $current_user;
      if(is_user_logged_in()){
        $user_id = $current_user->ID;
        $writer  = $current_user->display_name;
        if(isset($data['entry_writer'])){
          $writer  = $data['entry_writer'];
        }
      } else {
        $user_id = 0;
        $writer  = $data['entry_writer'];
      }
      $config = $this->config;
      $mktime = $this->kkb_entry_timetoMK($entry_id);
      $writer = kingkongboard_xssfilter(kingkongboard_htmlclear($writer));
      
      if( $this->mode == "admin" ){
        $guid = $data['entry_guid'];
      } else {
        $guid = $data['page_id'];
      }
      if(!isset($data['parent'])){
        $data['parent'] = $entry_id;
      }
      if(!isset($data['origin'])){
        $data['origin'] = $entry_id;
      }
      if(isset($data['entry_section'])){
        $entry_section = $data['entry_section'];
      } else {
        $entry_section = null;
      }

      $depth      = $this->get_kkb_entry_depth($data['origin']);
      $listNumber = 1;

      if(isset($data['entry_notice'])){
        switch($data['entry_notice']){
          case "notice" :
            $entry_type = 1;
          break;

          default :
            $entry_type = 0;
          break;
        }
      } else {
        $entry_type = 0;
      }

      $status = $wpdb->insert(
        $config->meta_table,
        array(
          'post_id'     => $entry_id,
          'section'     => $entry_section,
          'board_id'    => $config->bid,
          'related_id'  => $data['parent'],
          'list_number' => $listNumber,
          'depth'       => $depth,
          'parent'      => $data['origin'],
          'type'        => $entry_type,
          'date'        => $mktime,
          'guid'        => $guid,
          'login_id'    => $user_id,
          'writer'      => $writer
        ), 
        array( '%d','%s','%d','%d','%d','%d','%d','%d','%d','%d','%d','%s')
      );
      if(!is_wp_error($status)){
        $this->kkb_entry_list_changer($wpdb->insert_id, $data['origin']);
      }
    } else {
      return kingkongboard_error_message("kkb_entry_write_kkbtable");
    }
  }

/**
 * 올바른 글의 순서를 위해 등록글의 번호와 부모글의 리스팅 번호를 비교해 업데이트 해준다.
 * @param string $post_id, $parent
*/
  public function kkb_entry_list_changer($post_id, $parent){
    global $wpdb;
    $config       = $this->config;
    $filters      = "WHERE board_id = $config->bid AND ID != $post_id order by date ASC";
    $results      = $this->get_kkb_meta_multiple($filters);
    $parentDepth  = $this->get_kkb_entry_depth($parent);
    $lastRow      = "WHERE board_id = $config->bid AND ID = $post_id";
    $lastRst      = $this->get_kkb_meta_row($lastRow);

    if($lastRst){
      if($lastRst->depth > 1){
        $pNumber = $this->get_kkb_meta_list_number($lastRst->parent);
        $Upfilters = "WHERE board_id = $config->bid AND list_number > $pNumber";
        $Upresults = $this->get_kkb_meta_multiple($Upfilters);
        if($Upresults){
          foreach($Upresults as $Upresult){
            $this->update_kkb_meta_list_number($Upresult->ID, ($Upresult->list_number+1) );
          }
        }
        $this->update_kkb_meta_list_number($lastRst->ID, ($pNumber+1));
      } else {
        if($results){
          foreach($results as $result){
            $this->update_kkb_meta_list_number($result->ID, ($result->list_number+1) );
          }
        }
      }
    }

  }

/**
 * 킹콩보드 메타테이블의 해당글을 지움 처리 한다. (type 99)
 * @param $entry_id
*/
  public function kkb_entry_remove_changer($entry_id){
    global $wpdb;
    $config = $this->config;
    $wpdb->update(
      $config->meta_table,
      array( 'type' => 99 ),
      array( 'board_id' => $config->bid, 'post_id'  => $entry_id ),
      array( '%d' ),
      array( '%d','%d' )
    );
  }

/**
 * 킹콩보드 게시글 리스팅 넘버를 업데이트 한다.
 * @param int $id, $listNumber
*/
  public function update_kkb_meta_list_number($id, $listNumber){
    global $wpdb; 
    $config = $this->config;
    $wpdb->update(
      $config->meta_table,
      array( 'list_number' => $listNumber ),
      array( 'ID' => $id ),
      array( '%d' ),
      array( '%d' )
    );
  }

/**
 * 킹콩보드 리스팅 넘버를 불러온다.
 * @param int $post_id
 * @return $listNum
*/
  public function get_kkb_meta_list_number($post_id){
    global $wpdb;
    $filters = "WHERE post_id = '".$post_id."' ";
    $results = $this->get_kkb_meta_row($filters);
    if($results){
      $listNum = $results->list_number;
    }
    wp_reset_query();
    return $listNum;
  }

/**
 * 킹콩보드 메타테이블에서 filter 조건에 맞는 레코드를 불러온다. (복수)
 * @param string $filter
 * @return $mktime
*/
  public function get_kkb_meta_multiple($filter){
    global $wpdb;
    $config   = $this->config;
    $results  = null;
    $db_table = $config->meta_table;
    $results  = $wpdb->get_results("SELECT * FROM $db_table ".$filter);
    wp_reset_query();
    return $results;   
  }

/**
 * 킹콩보드 메타테이블에서 filter 조건에 맞는 레코드를 불러온다. (단수)
 * @param string $filter
 * @return $mktime
*/
  public function get_kkb_meta_row($filter){
    global $wpdb;
    $config   = $this->config;
    $results  = null;
    $db_table = $config->meta_table;
    $results  = $wpdb->get_row("SELECT * FROM $db_table ".$filter);
    wp_reset_query();
    return $results;      
  }

/**
 * 답변글이라면 origin 은 부모 글을 지칭하고 신규 글이라면 해당 글의 entry_id 가 된다.
 * @param string $origin
 * @return $mktime
*/
  public function get_kkb_entry_depth($origin){
    $filters = "WHERE post_id = '".$origin."'";
    $results = $this->get_kkb_meta_row($filters);
    if($results){
      $returnCount = ($results->depth)+1;
    } else {
      $returnCount = 1;
    }
    return $returnCount;    
  }

/**
 * entry_id 값을 받아 등록된 포스트의 작성일자를 MKTIME 으로 변환하여 반환한다.
 * @param string $entry_id
 * @return $mktime
*/
  public function kkb_entry_timetoMK($entry_id){
    if($entry_id){
      $WriteTimeH = get_the_date("H", $entry_id);
      $WriteTimei = get_the_date("i", $entry_id);
      $WriteTimes = get_the_date("s", $entry_id);
      $WriteTimen = get_the_date("n", $entry_id);
      $WriteTimej = get_the_date("j", $entry_id);
      $WriteTimeY = get_the_date("Y", $entry_id);
      $TimetoMk   = mktime($WriteTimeH, $WriteTimei, $WriteTimes, $WriteTimen, $WriteTimej, $WriteTimeY);
      return $TimetoMk;
    } else {
      return false;
    }
  }
}

?>