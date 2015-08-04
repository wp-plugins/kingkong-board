<?php
  class kkboard_view extends kkbController {

    function __construct($board_id){
      $this->board_id = $board_id;
      isset($_GET['id']) ? $this->entry_id = sanitize_text_field($_GET['id']) : $this->entry_id = null;
    }

    public function get_title($entry_id){
      global $wpdb;
      $table    = $wpdb->prefix.'posts';
      $result   = $wpdb->get_row("SELECT post_title FROM {$table} WHERE ID = {$entry_id}");
      $title    = $result->post_title;
      $title    = str_replace('비공개: ', '', $title);
      $title    = str_replace('Private: ', '', $title);
      return $title;
    }

    public function view($view){
      global $post;
      switch($view){
        case 'list' :
          ob_start();
          require_once(KINGKONGBOARD_ABSPATH."/includes/view.list.php");
          $content = ob_get_contents();
          ob_end_clean();
        break;

        case 'read' :
          $entry_type = parent::getMeta($this->entry_id, 'type');
          if($this->entry_id && parent::actionPermission($this->board_id, $this->entry_id, 'read') == true && ($entry_type <= 1)){
            switch(parent::checkSecret($this->board_id, $this->entry_id)){
              case 0 :
              // 열람가능
                ob_start();
                require_once(KINGKONGBOARD_ABSPATH."includes/view.read.php");
                $content  = ob_get_contents();
                ob_end_clean();
              break;

              case 1 :
                ob_start();
                require_once(KINGKONGBOARD_ABSPATH."includes/view.read.check.php");
                $content  = ob_get_contents();
                ob_end_clean();
              break;

              case 2 :
              // 회원, 본인글이 아니므로 열람 불가하다.
                return apply_filters('kkb_read_secret_denied', __('비밀글 입니다. 작성자 본인과 관리자만 열람할 수 있습니다.', 'kingkongboard'), $this->board_id);
              break;
            }
          } else {
            $content = apply_filters('kkb_loop_read_denied_message', __('권한이 없거나 게시글이 존재하지 않습니다.', 'kingkongboard'), $this->board_id);
          }
        break;

        case 'write' :
          if(parent::actionPermission($this->board_id, null, 'write') == true){
            ob_start();
            require_once(KINGKONGBOARD_ABSPATH."includes/view.write.php");
            $content = ob_get_contents();
            ob_end_clean();
          } else {
            $content = apply_filters('kkb_loop_write_denied_message', __('글 쓰기 권한이 없습니다.', 'kingkongboard'), $this->board_id);
          }
        break;

        case 'modify' :
          if(parent::actionPermission($this->board_id, $this->entry_id, 'modify') == true){
            ob_start();
            require_once(KINGKONGBOARD_ABSPATH."includes/view.modify.php");
            $content = ob_get_contents();
            ob_end_clean();
          } else {
            $content = apply_filters('kkb_loop_modify_denied_message', __('글수정 권한이 없습니다.', 'kingkongboard'), $this->board_id);
          }
        break;

        case 'reply' :
          $parent_type = parent::getMeta($this->entry_id, 'type');
          if(parent::actionPermission($this->board_id, $this->entry_id, 'reply') == true && ($parent_type != 1)){
            ob_start();
            require_once(KINGKONGBOARD_ABSPATH."includes/view.reply.php");
            $content = ob_get_contents();
            ob_end_clean();
          } else {
            $content = apply_filters('kkb_loop_reply_denied_message', __('답글쓰기가 허용되지 않습니다.', 'kingkongboard'), $this->board_id);
          }        
        break;

        case 'delete' :
          if(parent::actionPermission($this->board_id, $this->entry_id, 'delete') == true){
            ob_start();
            require_once(KINGKONGBOARD_ABSPATH."includes/view.delete.php");
            $content = ob_get_contents();
            ob_end_clean();
          } else {
            $content = apply_filters('kkb_loop_delete_denied_message', __('글 삭제 권한이 없습니다.', 'kingkongboard'), $this->board_id);
          }
        break;

        case 'cmtcheck' :
          if(isset($_GET['mod']) && isset($_GET['cid'])){
            $cid = sanitize_text_field($_GET['cid']);
            $mod = sanitize_text_field($_GET['mod']);
            $exists = get_comment($cid);
            if(isset($exists->comment_ID)){
              if(parent::actionCommentPermission($this->board_id, $cid, $mod) == true){
                ob_start();
                require_once(KINGKONGBOARD_ABSPATH."includes/view.read.comment.check.php");
                $content = ob_get_contents();
                ob_end_clean();
              } else {
                $content = apply_filters('kkb_comment_permission_denied_message', __('권한이 없습니다.', 'kingkongboard'), $this->board_id);
              }
            } else {
              $content = apply_filters('kkb_comment_noexists_message', __('존재하지 않는 댓글 입니다.', 'kingkongboard'), $this->board_id);
            }
          } else {
            $content = apply_filters('kkb_comment_action_denied_message', __('잘못된 접근 입니다.', 'kingkongboard'), $this->board_id);
          }
          
        break;
      }
      return $content;
    }

  }
?>