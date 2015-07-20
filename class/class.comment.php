<?php

  class kkbComment {

    function __construct(){
      global $wpdb;
      $this->table = $wpdb->prefix."kingkongboard_comment_meta";
    }

    public function kkb_get_comment_list($entry_id){
      global $wpdb;
      $table = $this->table;
      $results = $wpdb->get_results('SELECT * FROM '.$table.' WHERE eid = '.$entry_id.' order by lnumber DESC');
      if($results){
        return apply_filters('kkb_get_comment_list_before', $results, $entry_id);
      } else {
        return false;
      }
    }

    public function kkb_get_comment_list_by_parent($parent_id){
      global $wpdb;
      $table = $this->table;
      $results = $wpdb->get_results('SELECT * FROM '.$table.' WHERE parent = '.$parent_id.' order by lnumber DESC');
      if($results){
        return apply_filters('kkb_get_comment_list_before', $results, $parent_id);
      } else {
        return false;
      }
    } 

    public function kkb_get_comment_count_1depth($entry_id){
      global $wpdb;
      $table = $wpdb->prefix.'comments';
      $count = $wpdb->get_var('SELECT COUNT(*) FROM '.$table.' WHERE comment_post_ID = '.$entry_id.' AND comment_approved = 1 AND comment_parent = 0');
      return $count;      
    }

    public function kkb_get_comment_count($entry_id){
      global $wpdb;
      $table = $wpdb->prefix.'comments';
      $count = $wpdb->get_var('SELECT COUNT(*) FROM '.$table.' WHERE comment_post_ID = '.$entry_id.' AND comment_approved = 1');
      return $count;
    }

    public function kkb_comment_delete($cid, $inputpwd){
      $comment  = get_comment($cid);
      $entry_id = $comment->comment_post_ID;
      $controller = new kkbController();
      $post_id  = $controller->getMeta($entry_id, 'guid');
      $board_id = $controller->getMeta($entry_id, 'board_id');
      if(is_user_logged_in()){
        $user = wp_get_current_user();
        if($controller->actionCommentPermission($board_id, $cid, 'delete') == true){
          $status = wp_delete_comment($cid);
          if(!is_wp_error($status)){
            $result['status'] = 'success';
          } else {
            $result['status'] = 'failed';
            $result['message'] = __('삭제에 문제가 발생하였습니다.', 'kingkongboard');
          }          
        } else {
          $result['status'] = 'failed';
          $result['message'] = __('본인 댓글이 아닙니다.', 'kingkongboard');
        }
      } else {
        if(!is_user_logged_in() && $inputpwd != null){
          $pwd      = get_comment_meta($cid, 'kkb_comment_password', true);

          if( ($controller->actionCommentPermission($board_id, $cid, 'delete') == true) && ($pwd == md5($inputpwd)) ){
            $status = wp_delete_comment($cid);
            if(!is_wp_error($status)){
              $result['status'] = 'success';
              $result['url']    = add_query_arg( array( 'view' => 'read', 'id' => $entry_id), get_the_permalink($post_id));
            } else {
              $result['status'] = 'failed';
              $result['message'] = __('삭제에 문제가 발생하였습니다.', 'kingkongboard');
            }         
          } else {
            $result['status'] = 'failed';
            $result['message'] = __('비밀번호가 일치하지 않습니다.', 'kingkongboard');            
          }
        } else {
          $result['status'] = 'check';
          $result['url']    = add_query_arg( array( 'view' => 'cmtcheck', 'cid' => $cid, 'id' => $comment->comment_post_ID, 'mod' => 'delete'), get_the_permalink($post_id));
        }
      }
      return $result;
    }

    public function kkb_comment_modify($data){
      global $wpdb;
      $table      = $wpdb->prefix.'comments';
      $controller = new kkbController();
      $content    = kingkongboard_xssfilter($data['kkb_comment_modify_textarea']);
      $entry_id   = kingkongboard_xssfilter(kingkongboard_htmlclear($data['entry_id']));
      $board_id   = $controller->getMeta($entry_id, 'board_id');
      $cid        = $data['cid'];
      if($controller->actionCommentPermission($board_id, $cid, 'modify') == true){
        $wpdb->update(
          $table,
          array( 'comment_content' => $content ),
          array( 'comment_ID' => $cid ),
          array( '%s' ),
          array( '%d' )
        );
      }
    }

    public function kkb_comment_save($data){

      $content        = kingkongboard_xssfilter($data['kkb_comment_content']);
      $entry_id       = kingkongboard_xssfilter(kingkongboard_htmlclear($data['entry_id']));
      (isset($data['comment_parent'])) ? $comment_parent = kingkongboard_xssfilter(kingkongboard_htmlclear($data['comment_parent'])) : $comment_parent = null;

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
        $writer   = kingkongboard_xssfilter(kingkongboard_htmlclear($data['writer']));
        $email    = kingkongboard_xssfilter(kingkongboard_htmlclear($data['email']));
        $user_id  = 0;
      }

      if( !empty($data['comment_origin']) ){
        $origin = sanitize_text_field($data['comment_origin']);
      } else {
        $origin = 0;
      }

      if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
          $ip = $_SERVER['HTTP_CLIENT_IP'];
      } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
          $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
      } else {
          $ip = $_SERVER['REMOTE_ADDR'];
      }

      $time = current_time('mysql');

      $comment = array(
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

      $comment_id = wp_insert_comment($comment);
      
      if(!is_wp_error($comment_id)){

        $parent_depth  = $this->kkb_get_comment_meta($comment_parent, 'depth');
        $parent_origin = $this->kkb_get_comment_meta($comment_parent, 'origin');

        if(!$parent_depth){
          $parent_depth = 0;
        }

        if($parent_origin){
          if( $parent_origin == $comment_parent ){
            $origin = $comment_parent;
          } else {
            $origin = $parent_origin;
          }
        } else {
          $origin = $comment_id;
        }

        $input_meta = array(
          'lnumber' => 1,
          'eid'     => $entry_id,
          'cid'     => $comment_id,
          'origin'  => $origin,
          'parent'  => $comment_parent,
          'depth'   => ($parent_depth + 1)
        );

        $this->kkb_update_comment_meta($input_meta);
        if(!is_user_logged_in()){
          update_comment_meta($comment_id, 'kkb_comment_password', md5($data['password']));
        }
        do_action('kingkongboard_save_comment_after', $entry_id, $comment_id, $content);
        return $comment_id;
      }

    }

    public function kkb_get_comment_meta($comment_id, $key){
      if($comment_id && $key){
        global $wpdb;
        $table = $this->table;
        $result = $wpdb->get_row('SELECT '.$key.' FROM '.$table.' WHERE cid = '.$comment_id );
        if($result){
          return $result->$key;
        } else {
          return false;
        }
      } else {
        return false;
      }
    }

    public function kkb_update_comment_meta($data){
      global $wpdb;
      if($this->kkb_get_comment_meta($data['cid'], 'ID') == true){
        $wpdb->update(
          $this->table,
          array(
            'lnumber' => 1,
            'eid'     => $data['eid'],
            'cid'     => $data['cid'],
            'origin'  => $data['origin'],
            'parent'  => $data['parent'],
            'depth'   => $data['depth']
          ),
          array( 'ID' => $this->kkb_get_comment_meta($data['cid'], 'ID') ),
          array( '%d', '%d', '%d', '%d', '%d', '%d' ),
          array( '%d' )
        );
      } else {
        $wpdb->insert(
          $this->table,
          array(
            'lnumber' => 1,
            'eid'     => $data['eid'],
            'cid'     => $data['cid'],
            'origin'  => $data['origin'],
            'parent'  => $data['parent'],
            'depth'   => $data['depth']
          ),
          array( '%d', '%d', '%d', '%d', '%d', '%d' )
        );
        $this->kkb_comment_lnumber_changer($data['eid'], $data['parent'], $wpdb->insert_id);
      }
    }

    public function kkb_comment_lnumber_changer($entry_id, $parent, $ID){
      global $wpdb;
      $filters      = "WHERE eid = ".$entry_id." AND ID != ".$ID;
      $results      = $wpdb->get_results("SELECT * FROM ".$this->table." ".$filters);
      $lastRow      = "WHERE eid = ".$entry_id." AND ID = ".$ID;
      $lastRst      = $wpdb->get_row("SELECT * FROM ".$this->table." ".$lastRow);

      if($lastRst){
        if($lastRst->depth > 1){
          $pNumber = $this->kkb_get_comment_meta($lastRst->parent, 'lnumber');
          if($pNumber){
            $Upfilters = "WHERE eid = ".$entry_id." AND lnumber >= ".$pNumber;
            $Upresults = $wpdb->get_results("SELECT * FROM ".$this->table." ".$Upfilters);
            if($Upresults){
              foreach($Upresults as $Upresult){
                $this->kkb_comment_lnumber_update($Upresult->ID, ($Upresult->lnumber+1) );
              }
            }
            $ed_filters = "WHERE eid = ".$entry_id." AND parent = ".$lastRst->parent." AND depth = ".$lastRst->depth;
            $ed_results = $wpdb->get_results("SELECT * FROM ".$this->table." ".$ed_filters);
            if(count($ed_results) > 1){
              foreach($ed_results as $ed_result){
                if($ed_result->ID == $lastRst->ID){
                  $this->kkb_comment_lnumber_update($lastRst->ID, ($pNumber - (count($ed_results)-1)));
                } else {
                  $this->kkb_comment_lnumber_update($ed_result->ID, ($ed_result->lnumber + 1));
                }
              }
            } else {
              $this->kkb_comment_lnumber_update($lastRst->ID, ($pNumber));
            }
          }
        } else {
          if($results){
            foreach($results as $result){
              $this->kkb_comment_lnumber_update($result->ID, ($result->lnumber+1) );
            }
          }
        }
      }
    }

    public function kkb_comment_lnumber_update($ID, $number){
      global $wpdb;
      $wpdb->update(
        $this->table,
        array(
          'lnumber' => $number
        ),
        array( 'ID' => $ID ),
        array( '%d' ),
        array( '%d' )
      );        
    }

    public function kkb_comment_depth_padding($depth){

      $depth_padding = apply_filters("kingkongboard_comment_depth_padding", 15);

      switch($depth){
        case 1 :
          $padding = 0;
        break;
        case 2 :
          $padding = 1*$depth_padding;
        break;

        case 3 :
          $padding = 2*$depth_padding;
        break;

        case 4 :
          $padding = 3*$depth_padding;
        break;

        case 5 :
          $padding = 4*$depth_padding;
        break;

        default :
          $padding = 5*$depth_padding;
        break;
      }
      return $padding;
    }

    public function kkb_comment_display($entry_id, $comments){

      $board_id        = get_board_id_by_entry_id($entry_id);
      $comment_options = get_post_meta($board_id, 'kingkongboard_comment_options', true);
      $comment_options = maybe_unserialize($comment_options);
      $comment_result  = null;
      $depth_padding   = null;

      ob_start();
      require_once KINGKONGBOARD_ABSPATH.'/includes/view.read.comment.loop.php';
      $comment_result = ob_get_contents();
      ob_end_clean();
/*
            apply_filters('kingkongboard_comment_list_before', $board_id, $comment);
            if($comment->comment_approved == 1){
              $comment_result .= '<div id="comment-'.$comment->comment_ID.'" class="each-comment comment-'.$comment->comment_ID.'" style="'.$depth_padding.'">';
              $comment_result .= $depth_div;
              $comment_result .= '<div>';
              $comment_result .= '<div class="each-comment-author">';
              $comment_result .= '<table class="comment-author-table">';
              $comment_result .= '<tr>';
              if($comment_options['thumbnail'] == "T"){
                $comment_result .= '<td>';
                $comment_result .= get_avatar($comment->comment_author_email, '20');
                $comment_result .= '</td>';
              }
              $comment_result .= '<td style="font-weight:'.$comment_options['writer']['font_weight'].'; font-size:'.$comment_options['writer']['font_size'].'; color:'.$comment_options['writer']['color'].'">';
              $comment_result .= $comment->comment_author;
              $comment_result .= '</td>';
              $comment_result .= '<td class="comment-author-date" style="color:'.$comment_options['date']['color'].'">';
              $comment_result .= get_comment_date( $comment_options['date']['format'], $comment->comment_ID);
              $comment_result .= '</td>';
              $comment_result .= '<td style="position:relative">';
              $comment_result .= '<a class="comment-reply-button" data-origin="'.$cmmt->origin.'" data="'.$comment->comment_ID.'">'.__('답글', 'kingkongboard').'</a> ';

              if(is_user_logged_in()){
                global $current_user;
                $user_id = $current_user->ID;
                if($comment->user_id == $user_id || current_user_can('manage_options')){
                  $comment_result .= '<a class="comment-delete-button" data-entry="'.$entry_id.'" data="'.$comment->comment_ID.'" message="'.__('삭제하시겠습니까?', 'kingkongboard').'">'.__('삭제', 'kingkongboard').'</a>';
                }
              }
 
              $comment_result .= '</td>';
              $comment_result .= '</tr>';
              $comment_result .= '</table>';
              $comment_result .= '</div>';

            $comment_content_before = apply_filters('kingkongboard_comment_content_before', $entry_id, $comment->comment_ID);
            if($comment_content_before != $entry_id){
              $comment_result .= $comment_content_before;
            }
            $comment_result .= '<div class="each-comment-content" style="color:'.$comment_options['content']['color'].'">';
            $comment_result .= nl2br($comment->comment_content)."<br>";
            $comment_result .= '</div>';
            $comment_result .= '</div>';

            $comment_after = apply_filters('kingkongboard_comment_after', $board_id, $entry_id, $comment->comment_ID );
            if($comment_after != $board_id){
              $comment_result .= $comment_after;
            }

            $comment_result .= '</div>';
          
            }
              */
        return apply_filters('kkb_comment_display_after', $comment_result, $entry_id, $comments);   
    }

  }

?>