<?php

  class KingkongBoard_Initialize {
    function __construct($file){
      add_action( 'admin_menu', array($this, 'KingkongBoard_AddMenu') );
      add_action( 'admin_enqueue_scripts', array($this, 'KingkongBoard_AdminStyle') );
      add_action( 'wp_enqueue_scripts', array($this, 'KingkongBoard_Style'), 9999);
      add_filter( 'mce_buttons', array($this, 'KingkongBoard_register_buttons') );
      add_filter( 'mce_external_plugins', array($this, 'KingkongBoard_register_tinymce_javascript') );
      add_action( 'get_header', array($this, 'kingkongboard_enable_threaded_comments') );
      register_activation_hook( $file , array(&$this,'KingkongBoard_Active_Create_DB'));
      register_activation_hook( $file , array(&$this, 'KingkongBoard_Remote_Post'));
      add_action('pre_get_posts', array($this, 'KingkongBoard_Search_Filter'));
      add_action( 'in_admin_footer', array($this, 'KingkongBoard_Facebook_Script'));
      $this->KingkongBoard_Panel_Setup();
      add_action( 'init', array($this, 'KingkongBoard_Register_Post_Types'));
      add_action( 'admin_init', array($this,'kingkongboard_add_theme_caps'));
      add_filter( 'map_meta_cap', array($this, 'kingkongboard_map_meta_cap'), 10, 4 );
      add_filter( 'upload_mimes', array($this, 'kingkongboard_upload_mimes') );
      add_action( 'init', array($this, 'kingkongboard_localization') );
      add_action( 'admin_init', array($this, 'check_current_kkb_version') );
    }


    public function check_current_kkb_version(){
      global $wpdb;
      $installed_ver = get_option( 'kingkongboard_version' );
      $plugin = get_plugin_data( KINGKONGBOARD_ABSPATH.'KingkongBoard.php', $markup = true, $translate = true );
      if( $installed_ver != $plugin['Version'] ){
        $db_table = $wpdb->prefix . "kingkongboard_comment_meta";
        if($wpdb->get_var("show tables like '$db_table'") !== $db_table){
            //create sql
            $sql =  "CREATE TABLE ". $db_table . " (
                          ID mediumint(12) NOT NULL AUTO_INCREMENT,
                          lnumber mediumint(12) NOT NULL,
                          eid mediumint(12) NOT NULL,
                          cid mediumint(12) NOT NULL,
                          origin mediumint(12) NOT NULL,
                          parent mediumint(12) NOT NULL,
                          depth mediumint(12) NOT NULL,
                          UNIQUE KEY ID (ID));";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
        $this->prev_version_comment_migration();
        update_option( 'kingkongboard_version', $plugin['Version'] );        
      }
    }

    public function prev_version_comment_migration(){
      global $wpdb;
      $args = array(
        'status' => 'approve',
        'number' => ''
      );
      $comments = get_comments($args);
      $comment_meta_table = $wpdb->prefix."kingkongboard_comment_meta";
      foreach($comments as $comment){
        $result = $wpdb->get_row("SELECT * FROM ".$comment_meta_table." WHERE eid = ".$comment->comment_post_ID." AND cid = ".$comment->comment_ID);
        if(!$result){
          $kkb_comment = new KKB_Comments();

          if($comment->comment_parent){
            $origin = $comment->comment_parent;
            $parent = $comment->comment_parent;
            $depth  = 1;
          } else {
            $origin = $comment->comment_ID;
            $parent = 0;
            $depth  = 1;
          }
          $input_meta = array(
            'lnumber' => 1,
            'eid'     => $comment->comment_post_ID,
            'cid'     => $comment->comment_ID,
            'origin'  => $origin,
            'parent'  => $parent,
            'depth'   => $depth
          );
          if(!$comment->comment_parent){
            $kkb_comment->kkb_update_comment_meta($input_meta);
          }
        }
      }
    }

    public function kingkongboard_localization(){
      $path   = KINGKONGBOARD_ABSPATH.'/lang/';
      $loaded = load_plugin_textdomain( 'kingkongboard', false, $path );
    }

    public function kingkongboard_upload_mimes( $existing_mimes=array() ){
      $existing_mimes['hwp'] = 'application/hangul';
      $existing_mimes['rar'] = 'application/rar';
      return $existing_mimes;
    }

    public function kingkongboard_map_meta_cap( $caps, $cap, $user_id, $args ) {

    if( 'edit_post' == $cap){
      $post = get_post( $args[0] );
      $post_type = get_post_type_object($post->post_type);
      $caps = array();
      $caps[] .= ( $user_id == $post->post_author ) ? $post_type->cap->edit_posts : $post_type->cap->edit_others_posts;
    }
    return $caps;
    }    

    public function KingkongBoard_Register_Post_Types(){

        global $wpdb;
        $table   = $wpdb->prefix."posts";
        $results = $wpdb->get_results("SELECT * FROM ".$table." WHERE post_type = 'kkboard' AND post_status = 'publish' ");
        if($results){
          foreach($results as $result){

            $board_id         = $result->ID;
            $board_slug       = get_post_meta($board_id, 'kingkongboard_slug', true);

            if($board_slug){
              register_post_type( 'kkb_'.$board_slug,
                array(
                  'labels' => array(
                    'name' => 'kkb_'.$board_slug
                  ),
                  'public' => false,
                  'capability_type' => 'kkb_'.$board_slug,
                  'capabilities' => array(
                    'edit_post'           => 'edit_kkb_'.$board_slug,
                    'edit_posts'          => 'edit_kkb_'.$board_slug.'s',
                    'edit_others_posts'   => 'edit_others_kkb_'.$board_slug.'s',
                    'publish_posts'       => 'publish_kkb_'.$board_slug.'s',
                    'read_post'           => 'read_kkb_'.$board_slug,
                    'read_private_posts'  => 'read_private_kkb_'.$board_slug.'s',
                    'delete_post'         => 'delete_kkb_'.$board_slug,
                    'edit_comment'        => 'edit_comment_kkb_'.$board_slug,
                  ),
                  'map_meta_cap'    => true
                  )
              );
            }
          }
        }
    }

    public function kingkongboard_add_theme_caps() {
      $admins = get_role( 'administrator' );
      global $wpdb;
      $table   = $wpdb->prefix."posts";
      $results = $wpdb->get_results("SELECT * FROM ".$table." WHERE post_type = 'kkboard' AND post_status = 'publish' ");

      if($results){

        foreach($results as $result){
          $board_id         = $result->ID;
          $board_slug       = get_post_meta($board_id, 'kingkongboard_slug', true);

          if($board_slug && is_object($admins) && method_exists($admins,'add_cap')){
            $admins->add_cap( 'edit_kkb_'.$board_slug );
            $admins->add_cap( 'edit_kkb_'.$board_slug.'s');
            $admins->add_cap( 'publish_kkb_'.$board_slug.'s' );
            $admins->add_cap( 'edit_others_kkb_'.$board_slug.'s');
            $admins->add_cap( 'read_kkb_'.$board_slug);
            $admins->add_cap( 'read_private_kkb_'.$board_slug.'s');
            $admins->add_cap( 'delete_kkb_'.$board_slug);
            $admins->add_cap( 'edit_comment_kkb_'.$board_slug);
          }
          
        }
      }
    }

    public function KingkongBoard_register_buttons($buttons){
      if(is_admin()){
        array_push($buttons, '|', 'KingkongBoard');
      }
      return $buttons;
    }

    public function KingkongBoard_register_tinymce_javascript($plugin_array){
      $plugin_array['KingkongBoard'] = plugins_url('../assets/js/tinymce-plugin.js', __file__);
      return $plugin_array;
    }

    public function KingkongBoard_AddMenu(){
      add_menu_page( 'KingkongBoard', __('Kingkong Board','kingkongboard'), 'administrator', 'KingkongBoard', 'KingkongBoard', KINGKONGBOARD_PLUGINS_URL.'/assets/images/kingkong.svg' ); 
      add_submenu_page('KingkongBoard', 'KingkongBoard', __('대시보드', 'kingkongboard'), 'administrator', 'KingkongBoard');
    }

    public function KingkongBoard_AdminStyle(){
        wp_enqueue_media();
        wp_enqueue_script('jquery');
        wp_enqueue_script("jquery-effects-core");
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_style( 'jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
        wp_enqueue_style('kingkongboard-admin-style', KINGKONGBOARD_PLUGINS_URL."/assets/css/kingkongboard-admin.css" );
        wp_enqueue_script('kingkongboard-admin-js', KINGKONGBOARD_PLUGINS_URL."/assets/js/kingkongboard-admin.js", array('jquery'));      
    }

    public function KingkongBoard_Style(){
      wp_enqueue_script('jquery');
      wp_enqueue_style('kingkongboard-style', KINGKONGBOARD_PLUGINS_URL."/assets/css/kingkongboard.css");
      wp_enqueue_script('kingkongboard-js', KINGKONGBOARD_PLUGINS_URL."/assets/js/kingkongboard.js", array("jquery"));
      wp_localize_script('kingkongboard-js', 'ajax_kingkongboard', array( 'ajax_url' => admin_url('admin-ajax.php') ));
      do_action('kingkongboard_style_after');
    }

    public function KingkongBoard_Panel_Setup(){
      add_action('kingkong_board_columns', array($this, 'Column_KingkongBoard_Init') );
      add_action('create_kingkong_board_after', array($this, 'Save_Kingkong_Board_Meta'), 10, 2);
    }

    static function KingkongBoard_Remote_Post(){
      $admin_email = get_bloginfo('admin_email');

      $url = "http://superrocket.io/plugins/";

      wp_remote_post( 
        $url, 
        array(
        'method' => 'POST',
        'body' => array( 
          'plugin_name' => 'kingkongboard', 
            'website' => home_url(), 
            'email' => $admin_email
          )
        )
      );
    }

    public function Column_KingkongBoard_Init($content){
      add_kingkong_board_column( 'permission', __('권한설정', 'kingkongboard'), 'KingkongBoard_Setting_Panel_Permission' );
      add_kingkong_board_column( 'section', __('분류설정', 'kingkongboard'), 'KingkongBoard_Setting_Panel_Section' );
      add_kingkong_board_column( 'board_type', __('게시판 스킨설정', 'kingkongboard'), 'KingkongBoard_Setting_Panel_Board_Style' );
      add_kingkong_board_column( 'notice', __('알림설정', 'kingkongboard'), 'KingkongBoard_Setting_Panel_Notice' );
      add_kingkong_board_column( 'comment', __('댓글설정', 'kingkongboard'), 'KingkongBoard_Setting_Panel_Comment' );
      add_kingkong_board_column( 'latest_board', __('최신글 리스트 숏코드 매니저', 'kingkongboard'), 'KingkongBoard_Setting_Panel_Latest_Board' );

    }

    public function Save_Kingkong_Board_Meta($board_id, $options){
      
      if(isset($options['kkb_board_style'])){
        $board_style = $options['kkb_board_style'];
        update_post_meta($board_id, 'kingkongboard_board_style', $board_style);
      }
    }

    static function KingkongBoard_Active_Create_DB(){

        global $wpdb;

        $db_table = $wpdb->prefix . "kingkongboard_meta";
 
        if($wpdb->get_var("show tables like '$db_table'") !== $db_table){
            //create sql
            $sql =  "CREATE TABLE ". $db_table . " (
                          ID mediumint(12) NOT NULL AUTO_INCREMENT,
                          board_id mediumint(12) NOT NULL,
                          post_id mediumint(12) NOT NULL,
                          section varchar(100) NOT NULL,
                          related_id mediumint(12) NOT NULL,
                          list_number mediumint(9) NOT NULL,
                          depth mediumint(9) NOT NULL, 
                          parent mediumint(12) NOT NULL,
                          type mediumint(9) NOT NULL,
                          date int(30) NOT NULL,
                          guid mediumint(12) NOT NULL,
                          login_id mediumint(12) NOT NULL,
                          writer varchar(30) NOT NULL,
                          UNIQUE KEY ID (ID));";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    public function Get_All_KingkongBoards(){
      
      global $wp_query, $wpdb;

      $Array_Boards = null;

      $Results = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE post_type = 'kkboard' ");
      foreach($Results as $Result){

        $search           = get_post_meta($Result->ID, 'kingkongboard_search', true);

        if ( is_user_logged_in() ) {
          global $current_user;
          $user_roles = $current_user->roles;
          $role = array_shift($user_roles);
          $permission_read_status   = get_kingkongboard_permission_read_by_role_name($Result->ID, $role);
        } else {
          $permission_read_status   = get_kingkongboard_permission_read_by_role_name($Result->ID, 'guest');

        }

        if( ($search == 'T') && $permission_read_status == "checked" ){
          $Board = new KKB_Config($Result->ID);
          $Array_Boards .= $Board->board_slug.",";
        }
      }
      $args = array(
        'public'    => true
      );

      $output   = 'names';
      $operator = 'and';

      $post_types = get_post_types($args, $output, $operator);

      foreach($post_types as $post_type){
        $Array_Boards .= $post_type.",";
      }
      $Array_Boards = explode(",", $Array_Boards);
      return $Array_Boards;
    }

    public function KingkongBoard_Search_Filter($query){

        if($query->is_search){
          $query->set('post_type', $this->Get_All_KingkongBoards());
          $meta = array(
            array(
              'key'     => 'kingkongboard_secret',
              'value'   => 'on',
              'compare' => 'NOT EXISTS'
            )
          );
          $query->set('meta_query', $meta);
        }
        if(!is_admin() && is_single() && $query->is_main_query() ){
          $query->set('post_type', $this->Get_All_KingkongBoards());
        }
        return $query;
    }

    public function kingkongboard_enable_threaded_comments(){
      if(!is_admin()){
        if(is_singular() AND comments_open() AND (get_option('thread_comments') == 1)){
          wp_enqueue_script('comment-reply');
        }
      }
    }

    public function KingkongBoard_Facebook_Script(){
      wp_enqueue_script('kingkongboard-admin-facebook-js', KINGKONGBOARD_PLUGINS_URL."/assets/js/kingkongboard-facebook-script.js", array('jquery'));
    }

  }
?>