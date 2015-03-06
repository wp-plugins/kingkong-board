<?php
if(!isset($_SESSION)){
  session_start();
}
add_shortcode("kingkong_board","kingkong_board");
function kingkong_board($attr){
  global $post, $current_user;
  $slug               = $attr[0];
  $board_id           = get_kingkong_board_id_by_slug($slug);
  $board_skin         = get_post_meta($board_id, 'board_skin', true);

  do_action('kingkongboard_loop_before', $board_id, $board_skin);

  $permission_read    = get_post_meta($board_id, 'permission_read', true);
  $permission_read    = unserialize($permission_read);
  $board_comment      = get_post_meta($board_id, 'board_comment', true);

if ( is_user_logged_in() ) {
  $user_roles = $current_user->roles;
  $user_login = $current_user->user_login;
  $role = array_shift($user_roles);
  $permission_read_status   = get_kingkongboard_permission_read_by_role_name($board_id, $role);
  $permission_write_status  = get_kingkongboard_permission_write_by_role_name($board_id, $role);
  $permission_delete_status = get_kingkongboard_permission_delete_by_role_name($board_id, $role);
} else {
  $permission_read_status   = get_kingkongboard_permission_read_by_role_name($board_id, 'guest');
  $permission_write_status  = get_kingkongboard_permission_write_by_role_name($board_id, 'guest');
  $permission_delete_status = get_kingkongboard_permission_delete_by_role_name($board_id, 'guest');
  $user_login = null;
}
  
  if($board_id){
    $KingkongEntries    = new KingkongBoard_Entries($board_id);

    if(isset($_GET['view'])){
      $view = sanitize_text_field($_GET['view']);
    } else {
      $view = "list";
    }

    $page_id = $post->ID;

    $kkbContent  = '<div id="kingkongboard-wrapper">';
    $kkbContent .= '<input type="hidden" class="plugins_url" value="'.KINGKONGBOARD_PLUGINS_URL.'">';

    switch($view){
      case "list" :

        if (file_exists(TEMPLATEPATH . '/' . "kingkongboard/board-loop.php")) {
          require_once(TEMPLATEPATH . "/kingkongboard/board-loop.php");
        } else {
            $skin_path = get_kingkongboard_skin_path('board', $board_skin);
            if($skin_path){
              if( file_exists($skin_path['abspath']."kingkongboard/board-loop.php")){
                require_once($skin_path['abspath']."kingkongboard/board-loop.php");
              } else {
                require_once(KINGKONGBOARD_ABSPATH."/shortcodes/board-loop.php");
              }
            } else {
              require_once(KINGKONGBOARD_ABSPATH."/shortcodes/board-loop.php");
            }
        }

      break;

      case "write" :
        if($permission_write_status == "checked"){
          if (file_exists(TEMPLATEPATH . '/' . "kingkongboard/board-write.php")) {
            require_once(TEMPLATEPATH . "/kingkongboard/board-write.php");
          } else {
            $skin_path = get_kingkongboard_skin_path('board', $board_skin);
            if($skin_path){
              if( file_exists($skin_path['abspath']."kingkongboard/board-write.php")){
                require_once($skin_path['abspath']."kingkongboard/board-write.php");
              } else {
                require_once(KINGKONGBOARD_ABSPATH."/shortcodes/board-write.php");
              }
            } else {
              require_once(KINGKONGBOARD_ABSPATH."/shortcodes/board-write.php");
            }
          }
        } else {
          return __('글 쓰기 권한이 없습니다.', 'kingkongboard');
        }
      break;

      case "reply" :
        if (file_exists(TEMPLATEPATH .'/' . "kingkongboard/board-reply.php")) {
          require_once(TEMPLATEPATH . "/kingkongboard/board-reply.php");
        } else {
            if( file_exists(WP_CONTENT_DIR . '/' . "kingkongboard/skins/".$board_skin."/board-reply.php")){
              require_once(WP_CONTENT_DIR."/kingkongboard/skins/".$board_skin."/board-reply.php");
            } else {
              require_once(KINGKONGBOARD_ABSPATH."/shortcodes/board-reply.php");
            }
        }
      break;

      case "read" :
        $entry_id       = sanitize_text_field($_GET['id']);
        $entry_secret   = get_post_meta($entry_id, 'kingkongboard_secret', true);
        $entry_password = get_post_meta($entry_id, 'kingkongboard_entry_password', true);

        if($permission_read_status == "checked"){
          if($entry_secret){
            if (file_exists(TEMPLATEPATH . '/' . "kingkongboard/board-read-verifying.php")) {
              require_once(TEMPLATEPATH . "/kingkongboard/board-read-verifying.php");
            } else {
                if( file_exists(WP_CONTENT_DIR . '/' . "kingkongboard/skins/".$board_skin."/board-read-verifying.php")){
                  require_once(WP_CONTENT_DIR."/kingkongboard/skins/".$board_skin."/board-read-verifying.php");
                } else {
                  require_once(KINGKONGBOARD_ABSPATH."/shortcodes/board-read-verifying.php");
                }
            }
          } else {
            if (file_exists(TEMPLATEPATH . '/' . "kingkongboard/board-read.php")) {
              require_once(TEMPLATEPATH . "/kingkongboard/board-read.php");
            } else {
                if( file_exists(WP_CONTENT_DIR . '/' . "kingkongboard/skins/".$board_skin."/board-read.php")){
                  require_once(WP_CONTENT_DIR."/kingkongboard/skins/".$board_skin."/board-read.php");
                } else {
                  require_once(KINGKONGBOARD_ABSPATH."/shortcodes/board-read.php");
                }
            }
          }
        } else {
          return __('글 읽기 권한이 없습니다.', 'kingkongboard');
        }
      break;

      case "delete" :

        $entry_id       = sanitize_text_field($_GET['id']);
        $entry_secret   = get_post_meta($entry_id, 'kingkongboard_secret', true);
        $entry_password = get_post_meta($entry_id, 'kingkongboard_entry_password', true);
        $board_managers = get_post_meta($board_id, 'board_managers', true);
        $added_user     = get_kingkong_board_meta_value($entry_id, 'login_id');

        if($board_managers){
          $board_managers = unserialize($board_managers);
        } else {
          $board_managers = array();
        }

        if( (in_array($user_login, $board_managers)) or is_user_admin() or ( ($added_user == $current_user->ID) and ($added_user != 0) )){
          $KingkongEntries->Kingkong_Board_Delete_Entry($entry_id);
          return "<script>location.href='".get_the_permalink()."';</script>";
        } else {
          if (file_exists(TEMPLATEPATH . '/' . "kingkongboard/board-read-verifying.php")) {
            require_once(TEMPLATEPATH . "/kingkongboard/board-read-verifying.php");
          } else {
            if( file_exists(WP_CONTENT_DIR . '/' . "kingkongboard/skins/".$board_skin."/board-read-verifying.php")){
              require_once(WP_CONTENT_DIR."/kingkongboard/skins/".$board_skin."/board-read-verifying.php");
            } else {
              require_once(KINGKONGBOARD_ABSPATH."/shortcodes/board-read-verifying.php");
            }
          }
        }      

      break;

      case "modify" :
        if (file_exists(TEMPLATEPATH . '/' . "kingkongboard/board-modify.php")) {
          require_once(TEMPLATEPATH . "/kingkongboard/board-modify.php");
        } else {
            if( file_exists(WP_CONTENT_DIR . '/' . "kingkongboard/skins/".$board_skin."/board-modify.php")){
              require_once(WP_CONTENT_DIR."/kingkongboard/skins/".$board_skin."/board-modify.php");
            } else {
              require_once(KINGKONGBOARD_ABSPATH."/shortcodes/board-modify.php");
            }
        }
      break;

      case "proc" :
        require_once(KINGKONGBOARD_ABSPATH."/shortcodes/board-proc.php");
      break;

      case "modify_proc" :
        require_once(KINGKONGBOARD_ABSPATH."/shortcodes/board-modify-proc.php");
      break;

      default :
        if (file_exists(TEMPLATEPATH . '/' . "kingkongboard/board-loop.php")) {
          require_once(TEMPLATEPATH . "/kingkongboard/board-loop.php");
        } else {
            if( file_exists(WP_CONTENT_DIR . '/' . "kingkongboard/skins/".$board_skin."/board-loop.php")){
              require_once(WP_CONTENT_DIR."/kingkongboard/skins/".$board_skin."/board-loop.php");
            } else {
              require_once(KINGKONGBOARD_ABSPATH."/shortcodes/board-loop.php");
            }
        }
      break;
    }

    $kkbContent .= '</div>';

    return $kkbContent;
  } else {
    return __('킹콩보드 해당 게시판이 존재하지 않습니다.', 'kingkongboard');
  }
}
?>