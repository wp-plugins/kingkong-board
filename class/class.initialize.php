<?php

  class KingkongBoard_Initialize {
    function __construct($file){
      add_action( 'wp_head', array($this, 'meta_init'),1 );
      add_filter( 'wp_title', array($this, 'change_title'), 9999);
      add_action( 'init', array($this, 'kingkongboard_localization') );
      add_action( 'admin_menu', array($this, 'KingkongBoard_AddMenu') );
      add_action( 'admin_enqueue_scripts', array($this, 'KingkongBoard_AdminStyle') );
      add_action( 'wp_enqueue_scripts', array($this, 'KingkongBoard_Style'), 9999);
      add_filter( 'mce_buttons', array($this, 'KingkongBoard_register_buttons') );
      add_filter( 'mce_external_plugins', array($this, 'KingkongBoard_register_tinymce_javascript') );
      add_action( 'get_header', array($this, 'kingkongboard_enable_threaded_comments') ); 
      register_activation_hook( $file , array(&$this,'KingkongBoard_Active_Create_DB'));
      register_activation_hook( $file , array(&$this, 'KingkongBoard_Remote_Post'));
      //add_action('pre_get_posts', array($this, 'KingkongBoard_Search_Filter'), 9999);
      add_action( 'in_admin_footer', array($this, 'KingkongBoard_Facebook_Script'));
      $this->KingkongBoard_Panel_Setup();
      add_action( 'init', array($this, 'KingkongBoard_Register_Post_Types'));
      add_action( 'admin_init', array($this,'kingkongboard_add_theme_caps'));
      add_filter( 'map_meta_cap', array($this, 'kingkongboard_map_meta_cap'), 10, 4 );
      add_filter( 'upload_mimes', array($this, 'kingkongboard_upload_mimes') );
      add_action( 'admin_init', array($this, 'check_current_kkb_version') );
      add_action( 'template_redirect', array($this, 'kkb_entry_redirect'));
      add_filter( 'load_textdomain_mofile', array($this, 'replace_kkb_default_language_files'), 10, 2);
      add_action( 'kingkong_board_before', array($this, 'kkb_before_css'), 10, 1);
      add_action( 'init', array($this, 'media_category_create') );
      add_action( 'init', array($this, 'regist_kkb_tag'), 0);
      add_action( 'kingkongboard_entry_save_after', array($this, 'entry_notification'), 10, 2);
      add_action( 'kingkongboard_save_comment_after', array($this, 'comment_notification'), 10, 3);
    }

    public function entry_notification($board_id, $entry_id){
      $notice_entry   = get_post_meta($board_id, 'kingkongboard_notice_entry', true);
      $emails         = get_post_meta($board_id, 'kingkongboard_notice_emails', true);
      $board_title    = get_the_title($board_id);
      $entry_title    = get_the_title($entry_id);
      $entry_title    = str_replace('Private:', '', $entry_title);
      $entry_title    = str_replace('비공개: ', '', $entry_title);
      $headers[]      = 'From: '.__('관리자', 'kingkongboard').' <'.get_bloginfo('admin_email').'>';
      $body           = __('제목', 'kingkongboard').': '.$entry_title."\r\n\r\n".strip_tags(nl2br(get_post_field('post_content', $entry_id)));
      $body          .= "\r\n\r\n바로가기 : ".get_the_permalink($entry_id);
      if($emails){
        $emails = explode(",", $emails);
        if($notice_entry == 'checked'){
          wp_mail($emails, '['.get_bloginfo('name').'] '.sprintf( __('%s 신규글 알림 : %s', 'kingkongboard'), $board_title, $entry_title ), $body, $headers);
        }
      }
    }

    public function comment_notification($entry_id, $comment_id, $content){
      $controller     = new kkbController();
      $board_id       = $controller->getMeta($entry_id, 'board_id');
      $comment        = get_comment($comment_id);
      $notice_comment = get_post_meta($board_id, 'kingkongboard_notice_comment', true);
      $emails         = get_post_meta($board_id, 'kingkongboard_notice_emails', true);
      $board_title    = get_the_title($board_id);
      $entry_title    = get_the_title($entry_id);
      $entry_title    = str_replace('Private:', '', $entry_title);
      $entry_title    = str_replace('비공개: ', '', $entry_title);
      $headers[]      = 'From: '.__('관리자', 'kingkongboard').' <'.get_bloginfo('admin_email').'>';
      $body           = __('작성자', 'kingkongboard').': '.$controller->getMeta($entry_id, 'writer')."\r\n\r\n";
      $body          .= __('댓글내용', 'kingkongboard').': '.$content;
      $body          .= "\r\n\r\n바로가기 : ".get_the_permalink($entry_id);
      if($emails){
        $emails = explode(",", $emails);
        if($notice_comment == 'checked'){
          wp_mail($emails, '['.get_bloginfo('name').'] '.sprintf( __('%s 글 신규댓글 알림 : %s', 'kingkongboard'), $entry_title, $comment->comment_content ), $body, $headers);
        }
      }      
    }
 
    public function regist_kkb_tag(){
      $labels = array(
        'name'                       => __( '킹콩보드 태그', 'kingkongboard' ),
        'singular_name'              => __( '킹콩보드 태그', 'kingkongboard' ),
        'search_items'               => __( 'Search KingkongBoard Tags' ),
        'popular_items'              => __( 'Popular KingkongBoard Tags' ),
        'all_items'                  => __( 'All Tags' ),
        'parent_item'                => null,
        'parent_item_colon'          => null,
        'edit_item'                  => __( 'Edit Tag' ),
        'update_item'                => __( 'Update Tag' ),
        'add_new_item'               => __( 'Add New Tag' ),
        'new_item_name'              => __( 'New Tag Name' ),
        'separate_items_with_commas' => __( 'Separate tags with commas' ),
        'add_or_remove_items'        => __( 'Add or remove tags' ),
        'choose_from_most_used'      => __( 'Choose from the most used tags' ),
        'not_found'                  => __( 'No tags found.' ),
        'menu_name'                  => __( 'Tags' ),
      );

      $args = array(
        'hierarchical'          => false,
        'labels'                => $labels,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'update_count_callback' => '_update_post_term_count',
        'query_var'             => true,
        'rewrite'               => array( 'slug' => 'kkb_tag' ),
      );

      register_taxonomy( 'kkb_tag', array($this->getAllBoards()), $args );      
    }

    public function kkb_before_css($attr){
      wp_enqueue_style('kingkongboard-normalize', KINGKONGBOARD_PLUGINS_URL."/assets/css/normalize.css");
    }

    public function media_category_create(){
      $labels = array(
        'name'              => __('첨부 분류', 'kingkongboard'),
        'singular_name'     => __('첨부 분류', 'kingkongboard'),
        'search_items'      => __('분류 검색', 'kingkongboard'),
        'all_items'         => __('전체 분류', 'kingkongboard'),
        'parent_item'       => __('부모 분류', 'kingkongboard'),
        'edit_item'         => __('분류 수정', 'kingkongboard'),
        'update_item'       => __('업데이트', 'kingkongboard'),
        'add_new_item'      => __('신규 분류 등록', 'kingkongboard'),
        'new_item_name'     => __('신규 분류', 'kingkongboard'),
        'menu_name'         => __('첨부 분류', 'kingkongboard'),
      );

      $args = array(
          'labels' => $labels,
          'hierarchical' => true,
          'query_var' => 'true',
          'rewrite' => 'true',
          'show_admin_column' => 'true',
      );

      register_taxonomy( 'kkb_media_cat', 'attachment', $args );
    }    

    public function meta_init(){
      $url = (isset($_SERVER['HTTPS']) ? "https" : "http")."://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
      if(isset($_GET['id'])){
        $id = sanitize_text_field($_GET['id']);
        $post_type = get_post_type($id);
        if(strpos($post_type, 'kkb_') !== false){
          $entry_id = $id;
          $content  = strip_tags(nl2br(get_post_field('post_content', $entry_id)));
          $thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id($entry_id), "full" );
          if($thumbnail){
              $thumbnail_image    = $thumbnail[0];
          } else {
              $thumbnail_image    = null;
          }
        $writer     = get_kingkong_board_meta_value($entry_id, 'writer');
        $author     = get_kingkong_board_meta_value($entry_id, 'login_id');
        $user_avatar = get_avatar($author, 200);
        preg_match("/src='(.*?)'/i", $user_avatar, $matches);
        $user_avatar = $matches[1];

        if($thumbnail_image == null){
          $thumbnail_image = $user_avatar;
        }
        if(empty($content)){
          $content = '&nbsp;';
        }
?>

          <meta name="description" content="<?php echo $content;?>" />
          <meta name="title" content="<?php echo get_the_title($entry_id);?>" />
<?php
        }
      }     
    }

    public function change_title($title){
      if(isset($_GET['id'])){
        $id = sanitize_text_field($_GET['id']);
        $post_type = get_post_type($id);
        if(strpos($post_type, 'kkb_') !== false){
          $entry_id = $id;
          $board_title = get_the_title($entry_id);
          $board_title = str_replace('비공개: ', '', $board_title);
          $board_title = str_replace('Private: ', '', $board_title);
          $title = $board_title.' > '.$title;
        }
      }
      return $title;
    }

    public function kkb_get_all_board_slug(){
      global $wpdb;
      $Array_Boards = array();
      $Results      = $wpdb->get_results("SELECT ID FROM $wpdb->posts WHERE post_type = 'kkboard' ");
      if($Results){
        foreach($Results as $Result){
          $Board = new kkbConfig();
          $Board = $Board->getBoard($Result->ID);
          $Array_Boards[] = $Board->slug;            
        }
        return $Array_Boards;
      } else {
        return false;
      }
    }

    public function kkb_entry_redirect(){
      if (is_single()) {
        global $wpdb, $post;
        $all_kingkongboards = $this->kkb_get_all_board_slug();
        if($all_kingkongboards){
          if (in_array($post->post_type, $all_kingkongboards)) {
            $table = $wpdb->prefix."kingkongboard_meta";
            $result = $wpdb->get_row("SELECT guid FROM ".$table." WHERE post_id = '".$post->ID."' ");
            $guid = $result->guid;
            $guid_path = get_the_permalink($guid);
            $redirect_path = add_query_arg( array('view' => 'read', 'id' => $post->ID), get_the_permalink($guid));
            wp_redirect( $redirect_path );
            exit();
          }
        }
      }
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
          $kkb_comment = new kkbComment();

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

    public function replace_kkb_default_language_files($mofile, $domain){
        if ('kingkongboard' === $domain)
            return KINGKONGBOARD_ABSPATH.'/lang/'.$domain.'-'.get_locale().'.mo';
        return $mofile;
    }
    public function kingkongboard_localization(){
      $path   = KINGKONGBOARD_ABSPATH.'lang';
      load_plugin_textdomain( 'kingkongboard', false, $path );
      //load_plugin_textdomain( 'kingkongboard' );
    }

    public function kingkongboard_upload_mimes( $existing_mimes=array() ){
      $existing_mimes['hwp'] = 'application/hangul';
      $existing_mimes['xml'] = 'application/xml';
      $existing_mimes['doc'] = 'application/msword';
      $existing_mimes['ai']  = 'application/postscript';
      return apply_filters('kkb_upload_mimes_after', $existing_mimes);
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

        if( !post_type_exists( "kkboard" ) ){
          register_post_type( "kkboard",
              array(
                  'labels' => array(
                      'name' => __('킹콩보드', 'kingkongboard')
                  ),
              'public' => false,
              'show_ui' => false,
              'show_in_menu' => false
              )
          );
        }
        
        $table   = $wpdb->prefix."posts";
        $results = $wpdb->get_results("SELECT * FROM ".$table." WHERE post_type = 'kkboard' AND post_status = 'publish' ");
        if($results){
          foreach($results as $result){

            $board_id         = $result->ID;
            $board_slug       = get_post_meta($board_id, 'kingkongboard_slug', true);
            $board_secret     = get_post_meta($board_id, 'kingkongboard_search', true);
            ($board_secret == 'F') ? $public_value = false : $public_value = true;
            if($board_slug){
              register_post_type( 'kkb_'.$board_slug,
                array(
                  'labels' => array(
                    'name' => 'kkb_'.$board_slug
                  ),
                  'public' => $public_value,
                  'show_ui' => false,
                  'show_in_menu' => false,
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
      $srShop = new srShop;
      if($srShop->getResponse()){
        add_submenu_page('KingkongBoard', '', __('킹콩마트', 'kingkongboard'), 'administrator', 'srshop', 'srshop');
      }
      //add_submenu_page('KingkongBoard', '', __('튜토리얼', 'kingkongboard'), 'administrator', 'srshop', 'srshop');
    }

    public function KingkongBoard_AdminStyle(){
        wp_enqueue_media();
        wp_enqueue_script('jquery');
        wp_enqueue_script("jquery-effects-core");
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script('jquery-ui-datepicker');
        //wp_enqueue_style( 'jquery-ui-css', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
        wp_enqueue_style('kingkongboard-admin-style', KINGKONGBOARD_PLUGINS_URL."/assets/css/kingkongboard-admin.css" );
        wp_enqueue_script('kingkongboard-admin-js', KINGKONGBOARD_PLUGINS_URL."/assets/js/kingkongboard-admin.js", array('jquery'));      
    }

    public function KingkongBoard_Style(){
      wp_enqueue_script('jquery');
      wp_enqueue_script("jquery-effects-core");
      wp_enqueue_style('kingkongboard-style', KINGKONGBOARD_PLUGINS_URL."/assets/css/kingkongboard.css");
      wp_enqueue_script('jquery-selectionbox', KINGKONGBOARD_PLUGINS_URL."/assets/js/jquery.selectionBox.min.js", array('jquery'));
      wp_enqueue_script('kingkongboard-js', KINGKONGBOARD_PLUGINS_URL."/assets/js/kingkongboard.js", array("jquery"));
      wp_localize_script('kingkongboard-js', 'ajax_kingkongboard', array( 'ajax_url' => admin_url('admin-ajax.php') ));
      do_action('kingkongboard_style_after');
    }

    public function KingkongBoard_Panel_Setup(){
      add_action('kingkong_board_columns', array($this, 'Column_KingkongBoard_Init') );
      add_action('create_kingkong_board_after', array($this, 'Save_Kingkong_Board_Meta'), 10, 2);
    }

    static function KingkongBoard_Remote_Post(){
      $admin_email   = get_bloginfo('admin_email');
      $url           = "http://superrocket.io/plugins/";
      $installed_ver = get_option( 'kingkongboard_version' );
      $plugin = get_plugin_data( KINGKONGBOARD_ABSPATH.'KingkongBoard.php', $markup = true, $translate = true );
      wp_remote_post( 
        $url, 
        array(
        'method' => 'POST',
        'body' => array( 
          'plugin_name' => 'kingkongboard', 
          'website'     => home_url(), 
          'email'       => $admin_email,
          'version'     => $plugin['Version']
          )
        )
      );
    }

    public function Column_KingkongBoard_Init($content){
      add_kingkong_board_column( 'permission', __('권한설정', 'kingkongboard'), 'KingkongBoard_Setting_Panel_Permission' );
      add_kingkong_board_column( 'section', __('분류설정', 'kingkongboard'), 'KingkongBoard_Setting_Panel_Section' );
      add_kingkong_board_column( 'board_type', __('게시판 스킨설정', 'kingkongboard'), 'KingkongBoard_Setting_Panel_Board_Style' );
      add_kingkong_board_column( 'extension', __('익스텐션 설정', 'kingkongboard'), 'KingkongBoard_Setting_Panel_Extension');
      add_kingkong_board_column( 'notice', __('알림설정', 'kingkongboard'), 'KingkongBoard_Setting_Panel_Notice' );
      add_kingkong_board_column( 'comment', __('댓글설정', 'kingkongboard'), 'KingkongBoard_Setting_Panel_Comment' );
      add_kingkong_board_column( 'latest_board', __('숏코드 매니저', 'kingkongboard'), 'KingkongBoard_Setting_Panel_Latest_Board' );

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
                          UNIQUE KEY ID (ID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;";

            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);
        }
    }

    public function getAllBoards(){
      global $wpdb;
      $results = $wpdb->get_results("SELECT ID FROM {$wpdb->posts} WHERE post_type = 'kkboard' ");
      if($results){
        foreach($results as $result){
          $config = new kkbConfig();
          $config = $config->getBoard($result->ID);
          $post_types[] = $config->slug;
        }
        $post_type = join(',', $post_types);
      } else {
        $post_type = null;
      }
      return $post_type;
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
          $Board = new kkbConfig();
          $Board = $Board->getBoard($Result->ID);
          $Array_Boards .= $Board->slug.",";
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
      if(!is_admin()){
        if($query->is_search){
          $query->set('post_type', $this->Get_All_KingkongBoards());
        }
        if(!is_admin() && is_single() && $query->is_main_query() ){
          $query->set('post_type', $this->Get_All_KingkongBoards());
        }
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

  $Initializing = new KingkongBoard_Initialize(KINGKONGBOARD_ROOT);
?>