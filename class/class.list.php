<?php
  
  class kkbList extends kkbController {

    function __construct(){
      parent::__construct();
    }

    /**
    * 공지사항을 불러온다.
    */
    public function getNotice($board_id){
      $notice = parent::getList($board_id, 'notice', null);
      if(empty($notice)) : $notice = array(); endif;
      return $notice;
    }

    /**
    * 공지사항을 제외한 일반 게시글 리스트를 불러온다.
    */
    public function getBasic($board_id, $page){
      $basic = parent::getList($board_id, 'basic', $page);
      if(empty($basic)) : $basic = array(); endif;
      return $basic;
    }

    /**
    * 전체 게시글 숫자를 불러온다.
    */
    public function getCount($board_id, $type){
      $count = parent::getCount($board_id, $type);
      return $count;
    }



    /**
    * 검색 결과를 불러온다.
    */
    public function searchResult($board_id, $data, $page){
      return parent::getSearch($board_id, $data, $page, null);
    }

    /**
    * 타입에 맞는 검색 결과를 불러온다.
    */
    public function getSearch($board_id, $data, $page, $type){
      return parent::getSearch($board_id, $data, $page, $type);
    }    

    /**
    * 해당 게시글을 삭제한다.
    */
    public function delete($board_id, $entry_id){
      $status = parent::deleteEntry($board_id, $entry_id);
      if($status == true){
        $status = $this->removeChanger($board_id, $entry_id);
      }
      return $status;
    }

    /**
    * 게시글을 등록 한다.
    */
    public function write($board_id, $data, $type){
      $status = false;
      $entry_id = parent::writeEntry($board_id, $data, $type);
      if($entry_id != false || !is_wp_error($entry_id)){
        $status = $this->updateChanger($board_id, $entry_id);
      }
      return $status;
    }

  /**
   * 킹콩보드 메타테이블의 해당글을 지움 처리 한다. (type 99)
   * @param $entry_id
  */
    public function removeChanger($board_id, $entry_id){
      global $wpdb;
      $table  = $wpdb->prefix.'kingkongboard_meta';
      $status = $wpdb->update(
        $table,
        array( 'type' => 99 ),
        array( 'board_id' => $board_id, 'post_id'  => $entry_id ),
        array( '%d' ),
        array( '%d','%d' )
      );
      if(is_wp_error($status) || $status == false){
        return false;
      } else {
        return $status;
      }
    }

    /**
    * 게시판의 페이지 개수를 불러온다.
    */
    public function pageCount($board_id){
      $kkb         = new kkbConfig();
      $config      = $kkb->getBoard($board_id);
      $basicCount  = parent::getCount($board_id, 'basic');
      $noticeCount = parent::getCount($board_id, 'notice');
      /* 기존 
      $total       = $basicCount + $noticeCount;
      */
      /* 변경 */
      $total       = $basicCount;
      if($total > 0){
        $pages   = $total / $config->rows;
        $pages   = ceil($pages);
      } else {
        $pages   = 0;
      }
      return $pages;
    }

    /**
    * 게시판 검색결과의 페이지 카운트를 불러온다.
    */
    public function searchCount($board_id, $data, $page){
      $kkb        = new kkbConfig();
      $config     = $kkb->getBoard($board_id);
      $count = parent::getSearch($board_id, $data, $page, 'count');
      if($count > 0){
        $pages = $count / $config->rows;
        $pages = ceil($pages);
      } else {
        $pages = 0;
      }
      return $pages;      
    }
  }

?>