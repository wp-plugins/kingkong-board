<?php
/*
Plugin Name: Kingkong Board
Plugin URI: http://www.superrocket.io
Description: 슈퍼로켓에서 제공하는 한국형 워드프레스 게시판 플러그인 입니다. 
Version: 1.3.1
Author: Bryan Lee
Author URI: http://www.superrocket.io
License: GPL2

Copyright 2014 Super Rocket (email : ithemeso@gmail.com )
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA
*/
/**
 *
 * @package KingkongBoard
 * @category Core
 * @author Bryan Lee
 */
define('KINGKONGBOARD_ABSPATH', plugin_dir_path(__FILE__));             // 플러그인 절대경로
define('KINGKONGBOARD_PLUGINS_URL', plugins_url('', __FILE__));         // 플러그인 상대경로

require_once 'shortcodes/board.php';                                // 숏코드 : 디폴트게시판
require_once 'shortcodes/comment.php';                              // 숏코드 : 댓글
require_once 'shortcodes/latest.php';                               // 숏코드 : 최신글
require_once 'core/functions.php';                                  // Core Funtions
require_once 'core/error.php';                                      // Core Error Message
require_once 'class/class.KingkongBoard.php';                       // 클래스.게시판 생성관련 전반
require_once 'class/class.KingkongBoard_Entries.php';               // 클래스.게시판 목록 리스팅
require_once 'class/class.initialize.php';                          // 클래스.활성화 세팅
require_once 'admin/dashboard.php';                                 // 관리자 대시보드2
require_once 'admin/setting-panel-permission.php';                  // 관리자패널 권한설정
require_once 'admin/setting-panel-section.php';                     // 관리자패널 분류설정
require_once 'admin/setting-panel-board-style.php';                 // 관리자패널 게시판 스타일
require_once 'admin/setting-panel-notice.php';                      // 관리자패널 알림설정
require_once 'admin/setting-panel-comment.php';                     // 관리자패널 댓글설정
require_once 'admin/setting-panel-latest-board.php';                // 관리자패널 최근글 뷰 생성 숏코드 매니저

$Initializing = new KingkongBoard_Initialize(__FILE__);