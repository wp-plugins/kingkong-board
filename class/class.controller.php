<?php
  /*
  * DB I/O
  */
  class kkbController {
    //public static $board_where  = "post_type = 'kkboard' and post_status = 'publish'";
    //public static $basic_where  = "type = 0";
    //public static $notice_where = "type = 1";
    //public static $list_where   = "type != 99";
    //public static $delete_where = "type != 99";
    function __construct(){
      global $wpdb;
      $this->post_table   = $wpdb->prefix.'posts';
      $this->kkbtable     = $wpdb->prefix.'kingkongboard_meta';
      $this->kkbcmtable   = $wpdb->prefix.'kingkongboard_comment_meta';
      $this->board_where  = "post_type = 'kkboard' and post_status = 'publish'";
    }
 
    /*
    * 생성된 전체 게시판 아이디를 불러온다.
    */
    public function getBoards(){
      global $wpdb;
      $post_table   = $wpdb->prefix.'posts';
      $results      = $wpdb->get_results("SELECT ID FROM {$post_table} WHERE {$this->board_where}");
      if(!$results) : return false; endif;
      return $results;
    }

    public function getCount($bid, $kind){
      global $wpdb;
      $kkbtable = $wpdb->prefix.'kingkongboard_meta';
      switch($kind){
        case 'basic' :
          $where = "type = 0";
        break;

        case 'notice' :
          $where = "type = 1";
        break;

        default :
          $where = "type = 0";
        break;
      }
      $count = $wpdb->get_var("SELECT COUNT(*) FROM {$kkbtable} WHERE board_id = {$bid} AND {$where}");
      return $count;
    }

    /*
    * 게시판 게시글을 불러온다.
    */
    public function getList($bid, $kind, $page){
      global $wpdb;
      $kkbtable = $wpdb->prefix.'kingkongboard_meta';
      switch($kind){
        // 일반글 목록
        case 'basic' :
          $where = "type = 0";
        break;
        // 공지사항
        case 'notice' :
          $where = "type = 1";
        break;
        // 삭제된 글
        case 'deleted' :
          $where = "type != 99";
        break;
        // 기본 일반글 목록으로
        default :
          $where = "type = 0";
        break;
      }

      $limit = $this->getLimit($bid, $page);
      $filter_where   = apply_filters('kkb_get_basic_list_where', null, $bid);
      $filters        = apply_filters('kkb_get_basic_list_filter', "WHERE board_id = '".$bid."' AND ".$where." ".$filter_where." order by list_number ASC ".$limit, $bid);

      $results = $wpdb->get_results("SELECT * FROM {$kkbtable} {$filters}");
      if(!$results) : return false; endif;
      return $results;
    }

    /*
    * 검색 정보를 불러온다.
    */
    public function getSearch($bid, $data, $page, $type){
      global $wpdb;
      $kkbtable   = $wpdb->prefix.'kingkongboard_meta';
      $post_table = $wpdb->prefix.'posts';
      $kkb        = new kkbConfig();
      $config     = $kkb->getBoard($bid);
      $where      = apply_filters('kkb_search_list_where', null, $bid);
      $search_id  = array();
      ($type != 'count') ? $limit      = $this->getLimit($bid, $page) : $limit = null;

      (isset($data['kkb_search_keyword'])) ? $keyword = kingkongboard_xssfilter(kingkongboard_htmlclear($data['kkb_search_keyword'])) : $keyword = null;
      (isset($data['kkb_search_section'])) ? $section = kingkongboard_xssfilter(kingkongboard_htmlclear($data['kkb_search_section'])) : $section = null;
      (isset($data['kkb_search_type']))    ? $srctype = kingkongboard_xssfilter(kingkongboard_htmlclear($data['kkb_search_type']))    : $srctype = null;

      ($section) ? $section_where = "AND section = '".$section."'" : $section_where = null;

      switch($srctype){
        case 'content' :
          $results = $wpdb->get_results("SELECT ID FROM {$post_table} WHERE ( post_type = '".$config->slug."' AND (post_status = 'publish' OR post_status = 'private') ) AND (post_title like '%".$keyword."%' or post_content like '%".$keyword."%')");
            if($results){ foreach($results as $result){ $search_id[] = $result->ID; }}

            (count($results) > 0) ? $search_array = join(',', $search_id) : $search_array = null;
            (count($results) > 0) ? $array__in    = "AND post_id IN(".$search_array.")" : $array__in = null;

          $filters = "WHERE board_id = '".$bid."' AND type = 0 {$section_where} {$array__in} order by list_number ASC ".$limit;
          $countFilter = "WHERE board_id = '{$bid}' AND type = 0 {$section_where} {$array__in}";
        break;

        case 'writer' :
          $writer_where = "AND writer like '%".$keyword."%'";
          $filters = "WHERE board_id = {$bid} AND type = 0 {$section_where} {$writer_where} order by list_number ASC ".$limit;
          $countFilter = "WHERE board_id = {$bid} AND type = 0 {$section_where} {$writer_where}";
        break;

        case 'id' :
          $user = get_user_by('login', $keyword);
          (isset($user->ID)) ? $id_where = "AND login_id = ".$user->ID : $id_where = null;
          $filters = "WHERE board_id = {$bid} AND type = 0 {$section_where} {$id_where} order by list_number ASC ".$limit;
          $countFilter = "WHERE board_id = {$bid} AND type = 0 {$section_where} {$id_where}";
        break;

        case 'tag' :
          $tag_ids = null;
          $query = new WP_Query( array( 'tag' => $keyword, 'post_type' => $config->slug ) );
          while($query->have_posts()) : $query->the_post();
          $tag_ids[] = get_the_ID();
          endwhile;

          if(count($tag_ids) > 0){
            $ids = join(',', $tag_ids);
            $array__in = "AND post_id IN(".$ids.")";
          } else {
            $array__in = null;
          }

          $filters = "WHERE board_id = '".$bid."' AND type = 0 {$section_where} {$array__in} order by list_number ASC ".$limit;
          $countFilter = "WHERE board_id = {$bid} AND type = 0 {$section_where} {$array__in}";

        break;

        default :
          $filters = "WHERE board_id = {$bid} AND type = 0 {$section_where} order by list_number ASC".$limit;
          $countFilter = "WHERE board_id = {$bid} AND type = 0 {$section_where}";
        break;
      } 

      if($type == 'count'){
        $values = $wpdb->get_var("SELECT COUNT(*) FROM {$kkbtable} {$countFilter}");
      } else {
        $values = $wpdb->get_results("SELECT * FROM {$kkbtable} {$filters}");
      }
    
      return $values;  
    }

    /**
    * 게시글 ID 를 기준으로 원하는 컬럼 값을 가지고 온다.
    */
    public function getMeta($entry_id, $key){
      global $wpdb;
      $table  = $wpdb->prefix.'kingkongboard_meta';
      $result = $wpdb->get_row("SELECT {$key} FROM {$table} WHERE post_id = '".$entry_id."' ");
      if($result){
        return $result->$key;
      } else {
        return false;
      }      
    }

    /**
    * 조회수를 업데이트 한다.
    */
    public function updateHit($board_id, $entry_id){

      if(!isset($_SESSION)) : session_start(); endif;

      $hit = get_post_meta($entry_id, 'kingkongboard_hits', true);
      (!$hit || empty($hit)) ? $hit = 0 : $hit = $hit;

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

    /**
    * 리스트 번호를 기준으로 이전의 entry id 를 반환한다.
    */
    public function getPrev($board_id, $list_number){
      global $wpdb;
      $table  = $wpdb->prefix.'kingkongboard_meta';
      $result = $wpdb->get_row("SELECT post_id FROM {$table} WHERE list_number < {$list_number} AND board_id = {$board_id} AND type != 99 ORDER BY list_number DESC");
      if($result){
        return $result->post_id;
      } else {
        return false;
      }
    }

    /**
    * 리스트 번호를 기준으로 다음의 entry id 를 반환한다.
    */
    public function getNext($board_id, $list_number){
      global $wpdb;
      $table  = $wpdb->prefix.'kingkongboard_meta';
      $result = $wpdb->get_row("SELECT post_id FROM {$table} WHERE list_number > {$list_number} AND board_id = {$board_id} AND type != 99 ORDER BY list_number ASC");
      if($result){
        return $result->post_id;
      } else {
        return false;
      }
    }

    /**
    * 입력받은 비밀번호와 게시글의 비밀번호가 같다면 true, 틀리면 false 를 반환
    */
    public function checkPassword($entry_id, $ipwd){
      $epwd = get_post_meta($entry_id, 'kingkongboard_entry_password', true);
      if($epwd == md5($ipwd)){
        return true;
      } else {
        return false;
      }
    }


    public function actionCommentPermission($board_id, $comment_id, $type){
      $status = false;
      switch($type){
        case 'delete' :
          if( is_user_logged_in() ){
            $user          = wp_get_current_user();
            $checkManagers = $this->checkManagers($board_id);
            if($checkManagers == true || current_user_can('manage_options')){
              $status = true;
            } else {
              if( ($this->checkCommentPermission($board_id, 'delete') == true) && get_comment($comment_id)->user_id == $user->ID ){
                $status = true;
              }              
            }
          } else {
            if( ($this->checkCommentPermission($board_id, 'delete') == true) && get_comment($comment_id)->user_id == 0 ){
              $status = true;
            }
          }
        break;

        case 'write' :
          if( is_user_logged_in() ){
            $user           = wp_get_current_user();
            $checkManagers  = $this->checkManagers($board_id);
            if($checkManagers == true || current_user_can('manage_options')){
              $status = true;
            } else {
              if($this->checkCommentPermission($board_id, 'write') == true){
                $status = true;
              }
            }          
          } else {
            if($this->checkCommentPermission($board_id, 'write') == true){
              $status = true;
            }
          }
        break;

        case 'modify' :
          if( is_user_logged_in() ){
            $user           = wp_get_current_user();
            $checkManagers  = $this->checkManagers($board_id);
            if($checkManagers == true || current_user_can('manage_options')){
              $status = true;
            } else {
              if($this->checkCommentPermission($board_id, 'modify') == true && get_comment($comment_id)->user_id == $user->ID){
                $status = true;
              }
            }
          } else {
            if($this->checkCommentPermission($board_id, 'modify') == true && get_comment($comment_id)->user_id == 0){
              $status = true;
            }            
          }
        break;

      }
      return $status;
    }

    /**
    * 권한, 작성자 아이디, 관리자 권한 을 복합적으로 판단해 TRUE & FALSE 를 반환한다.
    */
    public function actionPermission($board_id, $entry_id, $type){
      $status = false;
      switch($type){
        case 'delete' :
          if( is_user_logged_in() ){
            $user           = wp_get_current_user();
            $checkManagers  = $this->checkManagers($board_id);
            if($checkManagers == true || current_user_can('manage_options')){
              $status = true;
            } else {
              if( ($this->checkPermission($board_id, 'delete') == true) && ($this->getMeta($entry_id, 'login_id') == $user->ID) ){
                $status = true;
              }
            }
          } else {
            if( ($this->checkPermission($board_id, 'delete') == true) && ($this->getMeta($entry_id, 'login_id') == 0) ){
              $status = true;
            }
          }
        break;

        case 'write' :
          if( is_user_logged_in() ){
            $user           = wp_get_current_user();
            $checkManagers  = $this->checkManagers($board_id);
            if($checkManagers == true || current_user_can('manage_options')){
              $status = true;
            } else {
              if($this->checkPermission($board_id, 'write') == true){
                $status = true;
              }
            }          
          } else {
            if($this->checkPermission($board_id, 'write') == true){
              $status = true;
            }
          }
        break;

        case 'read' :
          if( is_user_logged_in() ){
            $user           = wp_get_current_user();
            $checkManagers  = $this->checkManagers($board_id);
            $parent         = $this->getMeta($entry_id, 'parent');
            if($parent != $entry_id){
              $parent_user = $this->getMeta($parent, 'login_id');
            } else {
              $parent_user = null;
            }
            if($checkManagers == true || current_user_can('manage_options') || $parent_user == $user->ID){
              $status = true;
            } else {
              if($this->checkPermission($board_id, 'read') == true){
                $status = true;
              }
            }
          } else {
            if($this->checkPermission($board_id, 'read') == true){
              $status = true;
            }            
          }
        break;

        case 'modify' :
          if( is_user_logged_in() ){
            $user           = wp_get_current_user();
            $checkManagers  = $this->checkManagers($board_id);
            if($checkManagers == true || current_user_can('manage_options')){
              $status = true;
            } else {
              if($this->checkPermission($board_id, 'modify') == true && ($this->getMeta($entry_id, 'login_id') == $user->ID)){
                $status = true;
              }
            }
          } else {
            if($this->checkPermission($board_id, 'modify') == true && ($this->getMeta($entry_id, 'login_id') == 0)){
              $status = true;
            }            
          }
        break;

        case 'reply' :
          if( is_user_logged_in() ){
            $user           = wp_get_current_user();
            $checkManagers  = $this->checkManagers($board_id);
            $parent_id      = $this->getMeta($entry_id, 'parent');
            $parent_user    = $this->getMeta($parent_id, 'login_id');

            if($checkManagers == true || current_user_can('manage_options') || $user->ID == $parent_user){
              $status = true;
            } else {
              if($this->checkPermission($board_id, 'write') == true){
                $status = true;
              }
            }          
          } else {
            if($this->checkPermission($board_id, 'write') == true){
              $status = true;
            }
          }
        break;
      }
      return $status;
    }

    /**
    * 게시판의 포스트타입 (슬러그) 를 반환한다.
    */
    public function getPostType($board_id){
      $slug = get_post_meta($board_id, 'kingkongboard_slug', true);
      return 'kkb_'.$slug;
    }


    /**
     * 업로드시 파일명을 변경한다.
     * @param string $file
    */
    public function filenameChanger($file){
      $random = rand(1000, 9999);
      $filetype = wp_check_filetype($file['name']);
      $file['name'] = 'kkb-'.date('YmdHis').$random.'.'.$filetype['ext'];
      return $file;
    }

    /**
     * 캐시 플러그인의 캐시를 삭제한다.
     * @return boolen
    */
      public function clearCache(){
        global $cache_path;
        // WP-SUPER-CACHE
        if ( function_exists('prune_super_cache') ){
          prune_super_cache( $cache_path . 'supercache/', true);
          prune_super_cache( $cache_path, true);
        }
      }

    /**
     * 답변글이라면 origin 은 부모 글을 지칭하고 신규 글이라면 해당 글의 entry_id 가 된다.
     * @param string $origin
     * @return $mktime
    */
    public function getEntryDepth($origin){
      global $wpdb;
      $table   = $wpdb->prefix.'kingkongboard_meta';
      $result  = $wpdb->get_row("SELECT * FROM {$table} WHERE post_id = {$origin}");
      if($result){
        $returnCount = ($result->depth)+1;
      } else {
        $returnCount = 1;
      }
      return $returnCount;    
    }


    /**
    * 첨부파일(썸네일포함) 을 업로드 한다.
    */
    public function fileUploader($entry_id, $data, $files){
      $status      = true;
      $board_id    = $this->getMeta($entry_id, 'board_id');
      $board_title = get_the_title($board_id);
      $kkberror    = new kkbError();

      if(!function_exists('media_upload_tabs')){
        require_once ( ABSPATH . 'wp-admin/includes/media.php');
      }
      require_once ( ABSPATH . 'wp-admin/includes/image.php' ); 

      if($files['thumbnail_file']){
        do_action('kingkongboard_thumbnail_save_before', $board_id );
        $thumbnail = kingkongboard_reArrayFiles($files['thumbnail_file']);

        $dateY = date('Y');
        $dateM = date('m');
        $upload_path  = ABSPATH.'wp-content/uploads/kingkongboard/'.$dateY.'/'.$dateM.'/'.$board_id.'/'.$entry_id.'/thumbnail';

        if(is_dir($upload_path) == false){
          mkdir($upload_path, 0777, true);
        }
 
        for ($i=0; $i < count($thumbnail); $i++) { 
          if($thumbnail[$i]['name']){
            $upfile       = $this->filenameChanger($thumbnail[$i]);
            $file_path    = $upload_path.'/'.$upfile['name'];
            $movefile     = move_uploaded_file( $thumbnail[$i]['tmp_name'], $file_path );
            if( $movefile ){
              $filename       = $upfile['name'];
              $parent_post_id = $entry_id;
              $filetype       = wp_check_filetype( basename($filename), null );
              $wp_upload_dir  = wp_upload_dir();
              $attachment     = array(
                'guid'            => $wp_upload_dir['baseurl'] . '/kingkongboard/'.$dateY.'/'.$dateM.'/'.$board_id.'/thumbnail/'. $filename,
                'post_mime_type'  => $filetype['type'],
                'post_title'      => preg_replace( '/\.[^.]+$/', '', $thumbnail[$i]['name'] ),
                'post_content'   => '',
                'post_status'    => 'inherit'
              );

              $thumbnail_id      = wp_insert_attachment( $attachment, $file_path, $parent_post_id );
              // Generate the metadata for the attachment, and update the database record.
              if($thumbnail_id != 0){
                $attach_data = wp_generate_attachment_metadata( $thumbnail_id, $file_path );
                wp_update_attachment_metadata( $thumbnail_id, $attach_data );
                set_post_thumbnail( $entry_id, $thumbnail_id);

                $term = term_exists($board_title, 'kkb_media_cat');
                if($term === 0 or $term === null){
                  $tag = wp_insert_term( $board_title, 'kkb_media_cat', array( 'slug' => $this->getPostType($board_id) ) );
                  $term_id = array( $tag['term_id'] );
                  wp_set_post_terms($thumbnail_id, $term_id, 'kkb_media_cat');
                } else {
                  $term_id = array( $term['term_id'] );
                  wp_set_post_terms($thumbnail_id, $term_id, 'kkb_media_cat');
                }

              } else {
                $status = $kkberror->Error(012);             
              }
            }
          }
        }
        if( !isset($data['entry_each_thumbnail']) && !isset($thumbnail_id) ){
          delete_post_thumbnail($entry_id);
        }
      }
      if($files['entry_file']){
        $Files = kingkongboard_reArrayFiles($files['entry_file']);
        $attached = array();

        $dateY = date('Y');
        $dateM = date('m');
        $upload_path  = ABSPATH.'wp-content/uploads/kingkongboard/'.$dateY.'/'.$dateM.'/'.$board_id.'/'.$entry_id.'/attached';

        if(is_dir($upload_path) == false){
          mkdir($upload_path, 0777, true);
        }

        for ($i=0; $i < count($Files); $i++) { 
          if($Files[$i]['name']){
            $upfile       = $this->filenameChanger($Files[$i]);
            $file_path    = $upload_path.'/'.$upfile['name'];
            $movefile     = move_uploaded_file( $Files[$i]['tmp_name'], $file_path );
            if( $movefile ){
              $filename       = $upfile['name'];
              $parent_post_id = $entry_id;
              $filetype       = wp_check_filetype( basename($filename), null );
              $wp_upload_dir  = wp_upload_dir();
              $attachment     = array(
                'guid'            => $wp_upload_dir['baseurl'] . '/kingkongboard/'.$dateY.'/'.$dateM.'/'.$board_id.'/attached/'.$filename,
                'post_mime_type'  => $filetype['type'],
                'post_title'      => preg_replace( '/\.[^.]+$/', '', $Files[$i]['name'] ),
                'post_content'   => '',
                'post_status'    => 'inherit'
              );

              $attach_id      = wp_insert_attachment( $attachment, $file_path, $parent_post_id );
              // Generate the metadata for the attachment, and update the database record.
              $attach_data    = wp_generate_attachment_metadata( $attach_id, $file_path );
              wp_update_attachment_metadata( $attach_id, $attach_data );

              if(!is_wp_error($attach_id) && $attach_id != ""){
                update_post_meta($attach_id, 'file_type', $filetype['ext']);
                $term = term_exists($board_title, 'kkb_media_cat');
                if($term === 0 or $term === null){
                  $tag = wp_insert_term( $board_title, 'kkb_media_cat', array( 'slug' => $this->getPostType($board_id) ) );
                  $term_id = array( $tag['term_id'] );
                  wp_set_post_terms($attach_id, $term_id, 'kkb_media_cat');
                } else {
                  $term_id = array( $term['term_id'] );
                  wp_set_post_terms($attach_id, $term_id, 'kkb_media_cat');
                }

                $attached[]     = $attach_id;
              } else {
                $status = $kkberror->Error(013); 
              }
            }
          }
        }

        if( !isset($data['entry_each_attached_id']) && !isset($attach_id) ){
          delete_post_meta($entry_id, 'kingkongboard_attached');
        } else {
          if(isset($data['entry_each_attached_id'])){
            $prevAttached = $data['entry_each_attached_id'];
            foreach($prevAttached as $eattach){
              $attached[] = $eattach;
            }
            update_post_meta($entry_id, 'kingkongboard_attached', serialize($attached) );
          } else {
            if($attach_id != ""){
              update_post_meta($entry_id, 'kingkongboard_attached', serialize($attached) );
            } else {
              delete_post_meta($entry_id, 'kingkongboard_attached');
            }
          }
        }
      } else {
        delete_post_meta($entry_id, 'kingkongboard_attached');
      }
      return $status;
    }

    /**
     * data 값을 받아 글을 수정하는 함수
     * @param string $data
     * @return boolen
    */

    public function writeModify($data, $type){
      $entry_id       = $data['entry_id'];
      $board_id       = $this->getMeta($entry_id, 'board_id');
      $entry_title    = $data['entry_title'];
      $entry_content  = $data['entry_content'];
      $entry_title    = kingkongboard_xssfilter(kingkongboard_htmlclear($entry_title));
      $entry_content  = kingkongboard_xssfilter(trim($entry_content));

      (isset($data['entry_section'])) ? $entry_section = $data['entry_section'] : $entry_section = null;

      switch($type){
        case 'admin' :
          $date = $data['entry_ymd']." ".sprintf("%02d", $data['entry_h']).":".sprintf("%02d", $data['entry_i']).":".sprintf("%02d", $data['entry_s']);
          $entry = array(
            'ID'            => $entry_id,
            'post_title'    => $entry_title,
            'post_content'  => $entry_content,
            'post_date'     => $date,
            'post_status'   => 'publish'
          );           
        break;

        case 'basic' :
        default      :
          $entry = array(
            'ID'            => $entry_id,
            'post_title'    => $entry_title,
            'post_content'  => $entry_content,
            'post_status'   => 'publish'
          );
        break;
      }

      $callback = wp_update_post( $entry );

      if(!is_wp_error($callback)){
        global $wpdb;
        $type = 0;
        (isset($data['entry_notice'])) ? $type = 1 : $type = 0;

        $date       = get_the_date("Y-m-d H:i:s", $entry_id);
        $mktime     = strtotime($date);

        $wpdb->update(
          $this->kkbtable,
          array( 'type'     => $type, 'date' => $mktime, 'section'  => $entry_section ),
          array( 'board_id' => $board_id, 'post_id'  => $entry_id ),
          array( '%d', '%d', '%s' ),
          array( '%d', '%d' )
        );

        if(isset($data['entry_secret'])){
          $this->updateSecret($entry_id);
        } else {
          delete_post_meta($entry_id, 'kingkongboard_secret');
        }

        if($type == 'admin' && isset($data['entry_thumbnail'])){
          set_post_thumbnail( $entry_id, $data['entry_thumbnail']);
        }


        if(isset($data['entry_tags'])){
          wp_set_post_terms( $entry_id, $data['entry_tags'], 'kkb_tag', false );
        }

        if($type == 'admin' && isset($data['entry_attachment'])){
          $entry_attachments = serialize($data['entry_attachment']);
          update_post_meta($entry_id, 'kingkongboard_attached', $entry_attachments);          
        }

        if($type == 'admin' && isset($data['entry_password'])){
          if($data['entry_password'] != null && $data['entry_password'] != ''){
            $entry_secret = $data['entry_password'];
            $entry_secret = md5($entry_secret);
            update_post_meta($entry_id, 'kingkongboard_entry_password', $entry_secret);
          }
        }

        $result = $entry_id;
      } else {
        $result = $kkberror->Error(014); 
      }
      if( ! function_exists('wp_super_cache_clear_cache') ){
        $this->clearCache();
      }
        return $result;
      }

    /**
    * 비밀글로 업데이트 한다.
    */
    public function updateSecret($entry_id){
      if(is_numeric($entry_id)){
        update_post_meta($entry_id, 'kingkongboard_secret', 'on');
        $current_post = get_post( $entry_id, 'ARRAY_A' );
        $current_post['post_status'] = 'private';
        wp_update_post($current_post);        
      }
    }



    /**
    * 게시글을 등록 한다.
    */
    public function writeEntry($board_id, $data, $type){
      $status   = false;
      $ptype    = $this->getPostType($board_id);
      $title    = $data['entry_title'];
      $content  = $data['entry_content'];
      $title    = kingkongboard_xssfilter(kingkongboard_htmlclear($title));
      $content  = kingkongboard_xssfilter(trim($content));
      switch($type){
        case 'admin' :
          $date = $data['entry_ymd']." ".sprintf("%02d", $data['entry_h']).":".sprintf("%02d", $data['entry_i']).":".sprintf("%02d", $data['entry_s']);
          $entry = array(
            'post_title'    => $title,
            'post_content'  => $content,
            'post_date'     => $date,
            'post_status'   => 'publish',
            'post_type'     => $ptype
          );           
        break;

        case 'basic' :
        default      :
          $entry = array(
            'post_title'    => $title,
            'post_content'  => $content,
            'post_status'   => 'publish',
            'post_type'     => $ptype
          );
        break;
      }
      if($this->actionPermission($board_id, null, 'write') == true){
        $status = wp_insert_post($entry);
        if(!is_wp_error($status)){
          // 포스트 메타 정보와 킹콩보드 메타 테이블에 정보 삽입
          $status = $this->writeMeta($board_id, $status, $data);
          if( ! function_exists('wp_super_cache_clear_cache') ){
            $this->clearCache();
          }

          if($type == 'admin' && isset($data['entry_thumbnail'])){
            set_post_thumbnail( $status, $data['entry_thumbnail']);
          }

          if(isset($data['entry_tags'])){
            wp_set_post_terms( $status, $data['entry_tags'], 'kkb_tag', true );
          }

          // 게시글 저장 후 동작하는 액션 훅
          do_action('kingkongboard_entry_save_after', $board_id, $status);
        } else {
          $status = false;
        }
      }
      return $status;
    }

    /**
    * 게시글을 삭제 한다.
    */
    public function deleteEntry($board_id, $entry_id){
      $status = false;
      $user   = wp_get_current_user();
      if($this->checkManagers($board_id) == true || current_user_can('manage_options') == true){
        $status = wp_delete_post($entry_id);
        if($status != false){
          $status = $this->deleteMeta($board_id, $entry_id);
        } 
      } else if( is_user_logged_in() && $this->getMeta($entry_id, 'login_id') == $user->ID ){
        $status = wp_delete_post($entry_id);
        if($status != false){
          $status = $this->deleteMeta($board_id, $entry_id);
        }
      } else if( !is_user_logged_in() && $this->getMeta($entry_id, 'login_id') == 0 ){
        $status = wp_delete_post($entry_id);
        if($status != false){
          $status = $this->deleteMeta($board_id, $entry_id);
        }
      } else {
        $status = false;
      }
      return $status;
    }

    /**
    * 킹콩보드 메타 테이블에 삭제 처리를 업데이트 한다. (99)
    */
    public function deleteMeta($board_id, $entry_id){
      global $wpdb;
      $table = $wpdb->prefix.'kingkongboard_meta';
      $status = $wpdb->update(
        $table,
        array( 'type' => 99 ),
        array( 'board_id' => $board_id, 'post_id'  => $entry_id ),
        array( '%d' ),
        array( '%d','%d' )
      );
      if(!is_wp_error($status)){
        return true;
      } else {
        return false;
      }
    }


  /**
   * data, entry_id 값을 받아 포스트 메타 정보를 업데이트 한다.
   * @param string $data, $entry_id
  */
    public function writeMeta($board_id, $entry_id, $data){
      $kkberror = new kkbError();
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
          //update_post_meta($entry_id, 'kingkongboard_secret', 'on');
          $this->updateSecret($entry_id);
        }

        // 비밀글 답변글일때 기존 글 작성자가 비회원 이라면 기존글 비밀번호를 답변글에 적용한다.
        if(isset($data['write_type'])){
          if($data['write_type'] == 'reply'){
            $parent_id    = $this->getMeta($data['parent'], 'parent');
            $parent_user  = $this->getMeta($data['parent'], 'login_id');

            $parent_secret = get_post_meta($parent_id, 'kingkongboard_secret', true);

            if($parent_user == 0 && !empty($parent_secret)){
              $entry_secret = get_post_meta($parent_id, 'kingkongboard_entry_password', true);
              update_post_meta($entry_id, 'kingkongboard_entry_password', $entry_secret);
            }
          }
        }
        $status = $this->writeTable($board_id, $entry_id, $data);
        if($status == false){
          return $kkberror->Error(010);
        } else {
          return $status;
        }
      } else {
        return $kkberror->Error(009);
      }
    }

   /**
    * kingkongboard_meta 테이블을 작성한다.
    * @param array $data, int $entry_id
    * @return boolen
   */ 
    public function writeTable($board_id, $entry_id, $data){
      if($board_id && $data && $entry_id){
        global $wpdb, $current_user;
        if(is_user_logged_in()){
          $user_id = $current_user->ID;
          $writer  = $current_user->display_name;
          (isset($data['entry_writer'])) ? $writer = $data['entry_writer'] : $writer = $writer;
          (isset($data['entry_write_guest'])) ? $user_id = 0 : $user_id = $current_user->ID;
        } else {
          $user_id = 0;
          $writer  = $data['entry_writer'];
        }
        $date       = get_the_date("Y-m-d H:i:s", $entry_id);
        $mktime     = strtotime($date);
        $writer     = kingkongboard_xssfilter(kingkongboard_htmlclear($writer));
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

        (isset($data['post_id'])) ? $guid = $data['post_id'] : $guid = $data['entry_guid'];

        (isset($data['entry_section'])) ? $entry_section = $data['entry_section'] : $entry_section = null;
        (!isset($data['parent'])) ? $data['parent'] = $entry_id : $data['parent'] = $data['parent']; 
        (!isset($data['origin'])) ? $data['origin'] = $entry_id : $data['origin'] = $data['origin'];

        $depth      = $this->getEntryDepth($data['origin']);
        $table      = $wpdb->prefix.'kingkongboard_meta';
        $status     = $wpdb->insert(
          $table,
          array(
            'post_id'     => $entry_id,
            'section'     => $entry_section,
            'board_id'    => $board_id,
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
          $status = $this->listChanger($board_id, $entry_id, $wpdb->insert_id, $data['origin']);
          return $status;
        } else {
          return false;
        }
      } else {
        return false;
      }
    }


    /**
     * 킹콩보드 게시글 리스팅 넘버를 업데이트 한다.
     * @param int $id, $listNumber
    */
      public function updateListnumber($id, $listNumber){
        global $wpdb; 
        $table = $wpdb->prefix.'kingkongboard_meta';
        $wpdb->update(
          $table,
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
      public function getListnumber($post_id){
        global $wpdb;
        $table   = $wpdb->prefix.'kingkongboard_meta';
        $results = $wpdb->get_row("SELECT * FROM {$table} WHERE post_id = {$post_id}");
        if($results){
          $listNum = $results->list_number;
        }
        wp_reset_query();
        return $listNum;
      }

    /**
     * 올바른 글의 순서를 위해 등록글의 번호와 부모글의 리스팅 번호를 비교해 업데이트 해준다.
     * @param string $post_id, $parent
    */
      public function listChanger($board_id, $entry_id, $post_id, $parent){
        global $wpdb;
        $table        = $wpdb->prefix.'kingkongboard_meta';
        $results      = $wpdb->get_results("SELECT * FROM {$table} WHERE board_id = {$board_id} AND ID != {$post_id} ORDER BY date ASC");
        $lastRst      = $wpdb->get_row("SELECT * FROM {$table} WHERE board_id = {$board_id} AND ID = {$post_id}");

        if($lastRst){
          if($lastRst->depth > 1){
            $pNumber    = $this->getListnumber($lastRst->parent);
            $Upresults  = $wpdb->get_results("SELECT * FROM {$table} WHERE board_id = {$board_id} AND list_number > {$pNumber}");
            if($Upresults){
              foreach($Upresults as $Upresult){
                $this->updateListnumber($Upresult->ID, ($Upresult->list_number+1) );
              }
            }
            $this->updateListnumber($lastRst->ID, ($pNumber+1));
            return $entry_id;
          } else {
            if($results){
              foreach($results as $result){
                $this->updateListnumber($result->ID, ($result->list_number+1) );
              }
            }
            return $entry_id;
          }
        } else {
          return $kkberror->Error(011);
        }
      }

    /*
    * 게시판 옵션에 등록된 관리자를 반환한다.
    */
    public function getManagers($board_id){
      $managers = get_post_meta($board_id, 'board_managers', true);
      (!empty($managers)) ? $board_managers = maybe_unserialize($managers) : $board_managers = null;
      return $board_managers;
    }

    /*
    * 접속자가 관리자인지 판별한다.
    */
    public function checkManagers($board_id){
      $managers = $this->getManagers($board_id);
      if($managers && is_user_logged_in()){
        $user       = wp_get_current_user();
        $user_login = $user->user_login; 
        if(in_array($user_login, $managers)){
          return true;
        } else {
          return false;
        }
      } else {
        return false;
      }
    }

    /**
    * 현재 접속자의 유저롤을 반환한다.
    */
    public function getRole(){
      if ( is_user_logged_in() ) {
        $current_user   = wp_get_current_user();
        $user_roles     = $current_user->roles;
        $user_login     = $current_user->user_login;
        $role = array_shift($user_roles);
      } else {
        $role = 'guest';
      }
      return $role;
    }

    /*
    * 비밀글일 경우 열람 여부를 부여한다.
    */
    public function checkSecret($board_id, $entry_id){
      $user = wp_get_current_user();
      (get_post_meta($entry_id, 'kingkongboard_secret')) ? $secret = true : $secret = false;
      $parent         = $this->getMeta($entry_id, 'parent');
      if($parent != $entry_id){
        $parent_user = $this->getMeta($parent, 'login_id');
      } else {
        $parent_user = 99;
      }

      if($secret == true){
        $writer_id = $this->getMeta($entry_id, 'login_id');
        if(!is_user_logged_in()){
          // 비밀번호를 체크한다.
          if($writer_id == 0 && $parent_user == 99){
            return 1;
          } else if($writer_id != 0 && $parent_user == 0){
            return 1;
          } else {
            return 2;
          }
        } else if( ( ($this->checkManagers($board_id) == false) && !current_user_can('manage_options') ) && ($user->ID != $writer_id) && ($parent_user != $user->ID) ) {
          // 본인 글이 아니므로 열람 불가하다.
          return 2;
        } else {
          // 열람 가능 하다.
          return 0;
        }
      } else {
        // 열람 가능 하다.
        return 0;
      }
    }

    public function checkPermissionByRole($board_id, $kind, $type, $role){
      $status = false;
      if($board_id != null){
        switch($kind){
          case 'entry' :
          default      :
            $read_key   = 'permission_read';
            $write_key  = 'permission_write';
            $modify_key = 'permission_modify';
            $delete_key = 'permission_delete';
          break;

          case 'comment' :
            $read_key   = 'permission_comment_read';
            $write_key  = 'permission_comment_write';
            $modify_key = 'permission_comment_modify';
            $delete_key = 'permission_comment_modify';
          break;
        }

        switch($type){
          case 'read' :
            $permission_read = get_post_meta($board_id, $read_key, true);
            if($permission_read && !empty($permission_read)){
              $permission_read = maybe_unserialize($permission_read);
              foreach($permission_read as $each){
                if($each == $role){
                  $status = true;
                }
              }
            } else {
              $status = true;
            }
          break;

          case 'write' :
            $permission_write = get_post_meta($board_id, $write_key, true);
            if($permission_write && !empty($permission_write)){
              $permission_write = maybe_unserialize($permission_write);
              foreach($permission_write as $each){
                if($each == $role){
                  $status = true;
                }
              }
            } else {
              $status = true;
            }
          break;

          case 'modify' :
            $permission_modify = get_post_meta($board_id, $modify_key, true);
            if($permission_modify && !empty($permission_modify)){
              $permission_modify = maybe_unserialize($permission_modify);
              foreach($permission_modify as $each){
                if($each == $role){
                  $status = true;
                }
              }
            } else {
              $status = true;
            }
          break;

          case 'delete' :
            $permission_delete = get_post_meta($board_id, $delete_key, true);
            if($permission_delete && !empty($permission_delete)){
              $permission_delete = maybe_unserialize($permission_delete);
              foreach($permission_delete as $each){
                if($each == $role){
                  $status = true;
                }
              }
            } else {
              $status = true;
            }
          break;
        }
      } else {
        $status = true;
      }
      return $status;
    }

    /**
     * 해당 게시판의 댓글의 권한을 판별해 true or false 를 반환한다.
     */ 
    public function checkCommentPermission($board_id, $type){
      $status = false;

      switch($type){
        case 'read' :
          $permission_read = get_post_meta($board_id, 'permission_comment_read', true);
          if($permission_read){
            $permission_read = maybe_unserialize($permission_read);
            foreach($permission_read as $each){
              if($each == $this->getRole()){
                $status = true;
              }
            }
          }
        break;

        case 'write' :
          $permission_write = get_post_meta($board_id, 'permission_comment_write', true);
          if($permission_write){
            $permission_write = maybe_unserialize($permission_write);
            foreach($permission_write as $each){
              if($each == $this->getRole()){
                $status = true;
              }
            }
          }
        break;

        case 'modify' :
          $permission_modify = get_post_meta($board_id, 'permission_comment_modify', true);
          if($permission_modify){
            $permission_modify = maybe_unserialize($permission_modify);
            foreach($permission_modify as $each){
              if($each == $this->getRole()){
                $status = true;
              }
            }
          }
        break;

        case 'delete' :
          $permission_delete = get_post_meta($board_id, 'permission_comment_delete', true);
          if($permission_delete){
            $permission_delete = maybe_unserialize($permission_delete);
            foreach($permission_delete as $each){
              if($each == $this->getRole()){
                $status = true;
              }
            }
          }
        break;
      }
      return $status;
    }

    /*
    * 해당 게시글의 권한을 판별해 true false 를 반환한다.
    */
    public function checkPermission($board_id, $type){
      $status = false;

      switch($type){
        case 'read' :
          $permission_read = get_post_meta($board_id, 'permission_read', true);
          if($permission_read){
            $permission_read = maybe_unserialize($permission_read);
            foreach($permission_read as $each){
              if($each == $this->getRole()){
                $status = true;
              }
            }
          }
        break;

        case 'write' :
          $permission_write = get_post_meta($board_id, 'permission_write', true);
          if($permission_write){
            $permission_write = maybe_unserialize($permission_write);
            foreach($permission_write as $each){
              if($each == $this->getRole()){
                $status = true;
              }
            }
          }
        break;

        case 'modify' :
          $permission_modify = get_post_meta($board_id, 'permission_modify', true);
          if($permission_modify){
            $permission_modify = maybe_unserialize($permission_modify);
            foreach($permission_modify as $each){
              if($each == $this->getRole()){
                $status = true;
              }
            }
          }
        break;

        case 'delete' :
          $permission_delete = get_post_meta($board_id, 'permission_delete', true);
          if($permission_delete){
            $permission_delete = maybe_unserialize($permission_delete);
            foreach($permission_delete as $each){
              if($each == $this->getRole()){
                $status = true;
              }
            }
          }
        break;
      }
      return $status;
    }

    public function getLimit($bid, $page){
      $kkb    = new kkbConfig();
      $config = $kkb->getBoard($bid);
      (empty($page)) ? $page = 1 : $page = $page;
      ($page == 1) ? $limit = "LIMIT 0, ".$config->rows : $limit = "LIMIT ".(($page * $config->rows) - $config->rows ).", ".$config->rows;
      return $limit;
    }

    public function getTags($bid){
      global $wpdb;
      $result_tags      = null;
      $controller       = new kkbController();
      $tax_table        = $wpdb->prefix.'term_taxonomy';
      $relation_table   = $wpdb->prefix.'term_relationships';
      $kkb_tags = $wpdb->get_results("SELECT term_taxonomy_id FROM {$tax_table} WHERE taxonomy = 'kkb_tag' ORDER BY count ASC");
      if($kkb_tags){
        foreach($kkb_tags as $tag){
          $term_ids[] = $tag->term_taxonomy_id;
        }
        if($term_ids){
          $term_ids = join(',', $term_ids);
          $relations = $wpdb->get_results("SELECT object_id, term_taxonomy_id FROM {$relation_table} WHERE term_taxonomy_id IN ({$term_ids})");
          if($relations){
            foreach($relations as $relation){
              $entry_id = $relation->object_id;
              $get_board_id = $controller->getMeta($entry_id, 'board_id');
              if($bid == $get_board_id){
                $result_tags[] = $relation->term_taxonomy_id;
              }
            }
          }
        } 
      }
      if($result_tags){
        $result_tags = join(',', $result_tags);
        $results = $wpdb->get_results("SELECT term_taxonomy_id FROM {$tax_table} WHERE term_taxonomy_id IN ({$result_tags}) ORDER BY count DESC LIMIT 8");
        foreach($results as $result){
          $terms[] = $result->term_taxonomy_id;
        }
        $result_tags = $terms;
      }
      return $result_tags;
    }

  }

?>