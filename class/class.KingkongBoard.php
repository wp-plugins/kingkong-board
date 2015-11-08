<?php

  class KingkongBoard extends kkbConfig {

    function __construct(){}

    public function CreateBoard($settings){

      if(!$settings['board_name'] or !$settings['board_slug']){
        $result['status']   = 'error';
        $result['message']  = __('게시판 명과 슬러그는 반드시 기입하셔야 합니다.', 'kingkongboard');
      } else {
        $settings['board_slug'] = strtolower($settings['board_slug']); 
        /* 커스텀 DB 를 집어넣는다. */
        $status = $this->insertBoard($settings);
        if($status != false && is_numeric($status)){
          $result['status']     = 'success';
          $result['message']    = __('정상적으로 등록 되었습니다.', 'kingkongboard');
          $result['board_id']   = $status;
        } else {
          $result['status']     = 'error';
          $result['message']    = __('실패에!!!', 'kingkongboard');
        }
      }
      return $result;
    }

    public function ModifyBoard($board_id, $settings){
      $result           = array();

      /* 커스텀 DB 를 집어넣는다. */
      $Board_ID = $this->updateBoard($board_id, $settings['board_name']);

      if(is_wp_error($Board_ID)){
        $result['status']     = 'error';
        $result['message']    = __('게시판 수정에 오류가 발생하였습니다.', 'kingkongboard');
      } else {
        update_post_meta( $Board_ID, 'kingkongboard_title', $settings['board_name'] );
        update_post_meta( $Board_ID, 'kingkongboard_rows', $settings['board_rows'] );
        update_post_meta( $Board_ID, 'kingkongboard_editor', $settings['board_editor'] );
        update_post_meta( $Board_ID, 'kingkongboard_search', $settings['board_search'] );
        update_post_meta( $Board_ID, 'kingkongboard_thumbnail_dp', $settings['board_thumbnail_display'] );
        update_post_meta( $Board_ID, 'kingkongboard_thumbnail_input', $settings['board_thumbnail_input'] );
        $result['status']     = 'success';
        $result['message']    = __('정상적으로 수정 되었습니다.', 'kingkongboard');
      }
      $this->board_id = $Board_ID;
      return $result;
    }

    public function updateBoard($board_id, $board_name){
        $Board = array();
        $Board = array(
          'ID'            => $board_id,
          'post_title'    => $board_name
        );

        $Board_Status = wp_update_post( $Board );

        return $Board_Status;
    }

    public function insertBoard($settings){
      $board_name                 = $settings['board_name'];
      $board_slug                 = $settings['board_slug'];
      $board_shortcode            = $settings['board_shortcode'];
      $board_rows                 = $settings['board_rows'];
      $board_editor               = $settings['board_editor'];
      $board_search               = $settings['board_search'];
      $board_thumbnail_display    = $settings['board_thumbnail_display'];
      $board_thumbnail_input      = $settings['board_thumbnail_input'];

      $Board = array(
        'post_title'    => $board_name,
        'post_content'  => '',
        'post_status'   => 'publish',
        'post_type'     => 'kkboard',
        'post_author'   => 1
      );

      $bid = wp_insert_post( $Board );

      if(is_wp_error($bid)){
        $result['status']     = 'error';
        $result['message']    = __('게시판 신규 생성에 오류가 발생하였습니다.', 'kingkongboard');
        $callback             = false;
      } else {
        update_post_meta( $bid, 'kingkongboard_title', $board_name );
        update_post_meta( $bid, 'kingkongboard_slug', $board_slug );
        update_post_meta( $bid, 'kingkongboard_shortcode', $board_shortcode );
        update_post_meta( $bid, 'kingkongboard_rows', $board_rows );
        update_post_meta( $bid, 'kingkongboard_editor', $board_editor );
        update_post_meta( $bid, 'kingkongboard_search', $board_search );
        update_post_meta( $bid, 'kingkongboard_thumbnail_dp', $board_thumbnail_display );
        update_post_meta( $bid, 'kingkongboard_thumbnail_input', $board_thumbnail_input );
        $callback = $bid;
      }
      do_action('kingkongboard_create_board_after', $callback); 
      return $callback;
    }

    public function KingkongBoard_Slug($board_id){
      return $board_id;
    }

  }

?>