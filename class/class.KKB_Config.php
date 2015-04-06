<?php
/**
 * 킹콩보드 워드프레스 게시판 관련 기본 세팅 값 클래스
 * @link www.superrocket.io
 * @copyright Copyright 2015 SuperRocket. All rights reserved.
 * @license http://www.gnu.org/licenses/gpl.html
*/

class KKB_Config {

/**
 * 게시판 아이디를 넘겨 받아 해당 게시판의 기본 정보를 설정한다.
 * @param int $bid
*/
  function __construct($bid){
    global $wpdb;
    $this->meta_table         = $wpdb->prefix."kingkongboard_meta";
    $this->bid                = $bid;
    $this->pfx                = 'kkb_';
    $this->Oslug              = get_post_meta($this->bid, 'kingkongboard_slug', true);
    $this->board_slug         = $this->pfx.$this->Oslug;
    $this->board_rows         = get_post_meta($this->bid, 'kingkongboard_rows', true); 
  }
  
}

?>