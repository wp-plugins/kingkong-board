<?php

  class KingkongBoard_Entries {

    function __construct($board_id){
      global $wpdb;
      $this->meta_table       = $wpdb->prefix."kingkongboard_meta";
      $this->board_id         = $board_id;
      $this->OriginBoard_Slug = get_post_meta($this->board_id, 'kingkongboard_slug', true);
      $this->board_prefix     = 'kkb_';
      $this->board_slug       = $this->board_prefix.$this->OriginBoard_Slug;
      $this->board_rows       = get_post_meta($this->board_id, 'kingkongboard_rows', true);
      $this->entry_count      = count(get_posts( 'post_type='.$this->board_slug.'&posts_per_page=-1&post_status=publish' ));
      $this->comment_options  = get_post_meta($this->board_id, 'kingkongboard_comment_options', true);
      add_action('template_redirect', array($this, 'kingkongboard_entry_redirect'));
    }

    public function kingkongboard_entry_redirect(){

      if(get_option('permalink_structure')){
        $prm_pfx = "?";
      } else {
        $prm_pfx = "&";
      }

      if (is_single()) {
          global $wpdb, $post;
          //check by postID
          if ($post->post_type == $this->board_slug) {
              $result = $wpdb->get_row("SELECT guid FROM ".$this->meta_table." WHERE post_id = '".$post->ID."' ");
              $guid = $result->guid;
              wp_redirect( (get_the_permalink($guid).$prm_pfx."view=read&id=".$post->ID) );
              exit();
          }
      }
    }

    public function Kingkong_Board_Modify_Entry($POST){

      $entry_id = $POST['entry_id'];

      $entry = array(
        'ID'            => $entry_id,
        'post_title'    => $POST['entry_title'],
        'post_content'  => $POST['entry_content']
      );

      $callback = wp_update_post( $entry );

      if(!is_wp_error($callback)){
        global $wpdb;
        if(isset($POST['entry_notice'])){
          
          switch($POST['entry_notice']){

            case "notice" :
              $wpdb->update(
                $this->meta_table,
                array(
                  'type'     => 1,
                  'section'  => $POST['entry_section']
                ),
                array( 
                  'board_id' => $this->board_id,
                  'post_id'  => $entry_id
                ),
                array( '%d', '%s' ),
                array( '%d', '%d' )
              );
            break;

            default :
              $wpdb->update(
                $this->meta_table,
                array(
                  'type'     => 0,
                  'section'  => $POST['entry_section']
                ),
                array( 
                  'board_id' => $this->board_id,
                  'post_id'  => $entry_id
                ),
                array( '%d', '%s' ),
                array( '%d', '%d' )
              );
            break;
          }
        } else {
          $wpdb->update(
            $this->meta_table,
            array(
              'type'     => 0,
              'section'  => $POST['entry_section']
            ),
            array( 
              'board_id' => $this->board_id,
              'post_id'  => $entry_id
            ),
            array( '%d', '%s' ),
            array( '%d', '%d' )
          );          
        }

        if(isset($POST['entry_password'])){
          $entry_secret = $POST['entry_password'];
          $entry_secret = md5($entry_secret);
          update_post_meta($entry_id, 'kingkongboard_entry_password', $entry_secret);
        }

        if(isset($POST['entry_secret'])){
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

    public function Kingkong_Board_Delete_Entry($entry_id){
      wp_delete_post($entry_id);
      $this->entry_remove_changer($entry_id);
    }

    public function Kingkong_Board_Insert_Entry($POST){
      if( !post_type_exists( $this->board_slug ) ){
        register_post_type( $this->board_slug,
            array(
                'labels' => array(
                    'name' => $this->OriginBoard_Slug
                ),
            'public' => true,
            'capability_type' => $this->board_slug,
            'map_meta_cap'    => true
            )
        );
      }

      $entry = array(
        'post_title'    => $POST['entry_title'],
        'post_content'  => $POST['entry_content'],
        'post_status'   => 'publish',
        'post_type'     => $this->board_slug
      );

      $entry_id = wp_insert_post($entry);

      if(!is_wp_error($entry_id)){

        $WriteTimeH = get_the_date("H", $entry_id);
        $WriteTimei = get_the_date("i", $entry_id);
        $WriteTimes = get_the_date("s", $entry_id);
        $WriteTimen = get_the_date("n", $entry_id);
        $WriteTimej = get_the_date("j", $entry_id);
        $WriteTimeY = get_the_date("Y", $entry_id);

        $TimetoMk   = mktime($WriteTimeH, $WriteTimei, $WriteTimes, $WriteTimen, $WriteTimej, $WriteTimeY);

        if(isset($POST['entry_attachment'])){
          $entry_attachments = serialize($POST['entry_attachment']);
          update_post_meta($entry_id, 'kingkongboard_attached', $entry_attachments);
        }

        if(isset($POST['entry_password'])){
          $entry_secret = $POST['entry_password'];
          $entry_secret = md5($entry_secret);
          update_post_meta($entry_id, 'kingkongboard_entry_password', $entry_secret);
        }

        if(isset($POST['entry_secret'])){
          update_post_meta($entry_id, 'kingkongboard_secret', 'on');
        }

        $this->Kingkong_Board_Insert_Meta($entry_id, $TimetoMk, $POST);
        $result = $entry_id;
      } else {
        $result = false;
      }

      do_action('kingkong_board_insert_entry_after', $entry_id);

      return $result;

    }

    public function Kingkong_Board_Insert_Meta($entry_id, $mktime, $options){
      global $wpdb;

      if(is_user_logged_in()){
        global $current_user;
        get_currentuserinfo();
        $user_id = $current_user->ID;
        $writer  = $current_user->display_name;
      } else {
        $user_id = 0;
        $writer  = $options['entry_writer'];
      }
      $db_table = $wpdb->prefix."kingkongboard_meta";

      if(!isset($options['parent'])){
        $options['parent'] = $entry_id;
      }

      if(!isset($options['origin'])){
        $options['origin'] = $entry_id;
      }

      if(isset($options['entry_section'])){
        $entry_section = $options['entry_section'];
      } else {
        $entry_section = null;
      }

      $depth          = $this->Get_Kingkong_Board_Depth($options['origin']);
      $listNumber     = 1;

      if(isset($options['entry_notice'])){
        switch($options['entry_notice']){
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
        $db_table,
          array(
            'post_id'     => $entry_id,
            'section'     => $entry_section,
            'board_id'    => $this->board_id,
            'related_id'  => $options['parent'],
            'list_number' => $listNumber,
            'depth'       => $depth,
            'parent'      => $options['origin'],
            'type'        => $entry_type,
            'date'        => $mktime,
            'guid'        => $options['page_id'],
            'login_id'    => $user_id,
            'writer'      => $writer
          ),
          array(
            '%d',
            '%s',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%d',
            '%s'
          )
      );

      if(!is_wp_error($status)){
        $this->Kingkong_Board_List_Changer($wpdb->insert_id, $options['origin']);
      }

    }

    public function Get_Kingkong_Board_Depth($parent){
      $filters = "WHERE post_id = '".$parent."'";
      $results = $this->Get_Kingkong_Board_Meta_Row($filters);
      if($results){
        $returnCount = ($results->depth)+1;
      } else {
        $returnCount = 1;
      }

      return $returnCount;

    }
    public function Get_Kingkong_Board_List_Number($post_id){
      global $wpdb;
      $filters = "WHERE post_id = '".$post_id."' ";
      $results = $this->Get_Kingkong_Board_Meta_Row($filters);
     
      if($results){
        $listNum = $results->list_number;
      }
      wp_reset_query();
      return $listNum;

    }
    public function Get_Kingkong_Board_Related($origin){
      global $wpdb;
      $filters = "WHERE board_id = '".$this->board_id."' AND related_id = '".$origin."' ";
      $results = $this->Get_Kingkong_Board_Meta_Multiple($filters);

      if($results){
        $value = count($results);
      } else {
        $value = 1;
      }

      return $value;
    }
 
    public function Kingkong_Board_List_Changer($post_id, $parent){
      // 1. db 를 date 순서대로 역순으로 넘버링 한다.
      global $wpdb;
      $status     = "";
      $filters    = "WHERE board_id = '".$this->board_id."' AND ID != $post_id order by date ASC";
      $results    = $this->Get_Kingkong_Board_Meta_Multiple($filters);
      $parentDepth = $this->Get_Kingkong_Board_Depth($parent);

      $lastRow    = "WHERE board_id = '".$this->board_id."' AND ID = $post_id";
      $lastRst    = $this->Get_Kingkong_Board_Meta_Row($lastRow);

      if($lastRst){

        if($lastRst->depth > 1){
          $pNumber = $this->Get_Kingkong_Board_List_Number($lastRst->parent);
          $Upfilters = "WHERE board_id = '".$this->board_id."' AND list_number > $pNumber";
          $Upresults = $this->Get_Kingkong_Board_Meta_Multiple($Upfilters);

          if($Upresults){
            foreach($Upresults as $Upresult){
              $this->Update_Kingkong_Board_List_Number($Upresult->ID, ($Upresult->list_number+1) );
            }
          }

          $this->Update_Kingkong_Board_List_Number($lastRst->ID, ($pNumber+1));

        } else {

          if($results){
            foreach($results as $result){
              $this->Update_Kingkong_Board_List_Number($result->ID, ($result->list_number+1) );
            }
          }

        }

      }

    }

    public function Get_Kingkong_Board_Meta_Multiple($filters){
      $results = "";
      global $wpdb;
      $db_table = $wpdb->prefix."kingkongboard_meta";
      $results = $wpdb->get_results("SELECT * FROM $db_table ".$filters);
      wp_reset_query();
      return $results;      
    }

    public function Get_Kingkong_Board_Meta_Row($filters){
      $results = "";
      global $wpdb;
      $db_table = $wpdb->prefix."kingkongboard_meta";
      $results = $wpdb->get_row("SELECT * FROM $db_table ".$filters);
      wp_reset_query();

      return $results;      
    }

    public function Update_Kingkong_Board_Related($id, $count){
      global $wpdb;

      $wpdb->update(
        $this->meta_table,
        array(
          'count' => $count
        ),
        array( 'ID' => $id ),
        array( '%d' ),
        array( '%d' )
      );
    }

    public function Update_Kingkong_Board_List_Number($id, $listNumber){

      global $wpdb; 
      $db_table = $wpdb->prefix."kingkongboard_meta";

      $wpdb->update(
        $db_table,
        array(
          'list_number' => $listNumber
        ),
        array( 'ID' => $id ),
        array( '%d' ),
        array( '%d' )
      );
    }

    public function Get_Kingkong_Board_Total_Count(){
      $filters = "WHERE board_id = '".$this->board_id."' and type != 99";
      $results = count($this->Get_Kingkong_Board_Meta_Multiple($filters));

      return $results;
    }

    public function Get_Kingkong_Board_Search_Total_Count($post){
      global $wpdb;
      if($post['kkb_search_keyword'] != null){
        $results    = $wpdb->get_results("SELECT ID FROM ".$wpdb->posts." WHERE post_type = '".$this->board_slug."' AND (post_title like '%".$post['kkb_search_keyword']."%' or post_content like '%".$post['kkb_search_keyword']."%')");
        $search_id  = array();
        foreach($results as $result){
          $search_id[] = $result->ID;
        }

        $search_array = join(',',$search_id);

        if(isset($post['kkb_search_section'])){
          if($post['kkb_search_section'] != "all"){
            $filters = "WHERE board_id = '".$this->board_id."' AND type = 0 AND section = '".$post['kkb_search_section']."' AND post_id IN ($search_array) order by list_number ASC ";
          } else {
            $filters = "WHERE board_id = '".$this->board_id."' AND type = 0 AND post_id IN ($search_array) order by list_number ASC ";          
          }
        } else {
          $filters = "WHERE board_id = '".$this->board_id."' AND type = 0 AND post_id IN ($search_array) order by list_number ASC ";  
        }
      } else {

        if(isset($post['kkb_search_section'])){
          if($post['kkb_search_section'] != "all"){
            $filters = "WHERE board_id = '".$this->board_id."' AND type = 0 AND section = '".$post['kkb_search_section']."' order by list_number ASC ";
          } else {
            $filters = "WHERE board_id = '".$this->board_id."' AND type = 0 order by list_number ASC ";        
          }
        } else {
          $filters = "WHERE board_id = '".$this->board_id."' AND type = 0 order by list_number ASC ";        
        }
      }   

      $values = $this->Get_Kingkong_Board_Meta_Multiple($filters);

      return count($values);      
    }



    public function Get_Kingkong_Board_Notice_Count(){
      $filters = "WHERE board_id = '".$this->board_id."' AND type = 1";
      $results = count($this->Get_Kingkong_Board_Meta_Multiple($filters));

      return $results;
    }

    public function Get_Kingkong_Basic_Entries($page){

      if($page == 1){
        $Limit = "LIMIT 0, ".$this->board_rows;
      } else {
        $Limit = "LIMIT ".(($page * $this->board_rows) - $this->board_rows ).", ".$this->board_rows;
      }

      $filters = "WHERE board_id = '".$this->board_id."' AND type = 0 order by list_number ASC ".$Limit;
      $results = $this->Get_Kingkong_Board_Meta_Multiple($filters);

      return $results;

    }

    public function Get_Kingkong_Search_Entries($post, $page){
      global $wpdb;

      if($page == 1){
        $Limit = "LIMIT 0, ".$this->board_rows;
      } else {
        $Limit = "LIMIT ".(($page * $this->board_rows) - $this->board_rows ).", ".$this->board_rows;
      }

      if($post['kkb_search_keyword'] != null){
        $results    = $wpdb->get_results("SELECT ID FROM ".$wpdb->posts." WHERE post_type = '".$this->board_slug."' AND (post_title like '%".$post['kkb_search_keyword']."%' or post_content like '%".$post['kkb_search_keyword']."%')");
        $search_id  = array();
        foreach($results as $result){
          $search_id[] = $result->ID;
        }

        $search_array = join(',',$search_id);
        if(isset($post['kkb_search_section'])){
          if($post['kkb_search_section'] != "all"){
            $filters = "WHERE board_id = '".$this->board_id."' AND type = 0 AND section = '".$post['kkb_search_section']."' AND post_id IN ($search_array) order by list_number ASC ".$Limit;
          } else {
            $filters = "WHERE board_id = '".$this->board_id."' AND type = 0 AND post_id IN ($search_array) order by list_number ASC ".$Limit;          
          }
        } else {
          $filters = "WHERE board_id = '".$this->board_id."' AND type = 0 AND post_id IN ($search_array) order by list_number ASC ".$Limit;          
        }
      } else {
        if(isset($post['kkb_search_section'])){
          if($post['kkb_search_section'] != "all"){
            $filters = "WHERE board_id = '".$this->board_id."' AND type = 0 AND section = '".$post['kkb_search_section']."' order by list_number ASC ".$Limit;
          } else {
            $filters = "WHERE board_id = '".$this->board_id."' AND type = 0 order by list_number ASC ".$Limit;        
          }
        } else {
          $filters = "WHERE board_id = '".$this->board_id."' AND type = 0 order by list_number ASC ".$Limit;   
        }
      }
      $values = $this->Get_Kingkong_Board_Meta_Multiple($filters);
      return $values;  
    }



    public function Get_Kingkong_Notice_Entries(){
      $filters = "WHERE board_id = '".$this->board_id."' AND type = 1";
      $results = $this->Get_Kingkong_Board_Meta_Multiple($filters);

      return $results;
    }

    public function Pagination(){
      $filters = "WHERE board_id = '".$this->board_id."' and (type != 1  and type != 99)";
      $total   = count($this->Get_Kingkong_Board_Meta_Multiple($filters));
      $pages   = $total / $this->board_rows;
      $pages   = ceil($pages);

      return $pages;
    }

    public function Search_Pagination($post){
      $total   = $this->Get_Kingkong_Board_Search_Total_Count($post);
      $pages   = $total / $this->board_rows;
      $pages   = ceil($pages);

      return $pages;
    }

    public function entry_remove_changer($entry_id){
      global $wpdb;

      $wpdb->update(
        $this->meta_table,
        array(
          'type' => 99
        ),
        array( 
          'board_id' => $this->board_id,
          'post_id'  => $entry_id 
        ),
        array( '%d' ),
        array( '%d','%d' )
      );
    }

    public function Get_Latest_List($number){
      $Limit   = $number;
      $filters = "WHERE board_id = '".$this->board_id."' AND type != 99 order by list_number ASC LIMIT ".$Limit;
      $results = $this->Get_Kingkong_Board_Meta_Multiple($filters);
      return $results;
    }


  }

?>