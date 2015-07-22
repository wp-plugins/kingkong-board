<?php

  class kkbError {

    function __construct(){}

    public function Error($code){
      $array = array(
        'code'    => $code,
        'message' => $this->getMessage($code)
      );
      return $array;
    }
 
    public function getMessage($code){
      switch($code){
        case 001 :
          $message = __('제목을 기입하시기 바랍니다.', 'kingkongboard');
        break;

        case 002 :
          $message = __('작성자명을 기입하시기 바랍니다.', 'kingkongboard');
        break;

        case 003 :
          $message = __('비밀번호를 기입하시기 바랍니다.', 'kingkongboard');
        break;

        case 004 :
          $message = __('이메일을 기입하시기 바랍니다.', 'kingkongboard');
        break;

        case 005 :
          $message = __('이메일 형식이 올바르지 않습니다.', 'kingkongboard');
        break;

        case 006 :
          $message = __('내용을 기입하시기 바랍니다.', 'kingkongboard');
        break;

        case 007 :
          $message = __('작성자 본인만 수정할 수 있습니다.', 'kingkongboard');
        break;

        case 008 :
          $message = __('비밀번호가 일치하지 않습니다.', 'kingkongboard');
        break;

        case 009 :
          $message = __('글 작성에 문제가 발생하였습니다.', 'kingkongboard');
        break;

        case 010 :
          $message = __('킹콩보드 메타 테이블 작성에 문제가 발생하였습니다.', 'kingkongboard');
        break;

        case 011 :
          $message = __('킹콩보드 리스트 체인저에 문제가 발생하였습니다.', 'kingkongboard');
        break;

        case 012 :
          $message = __('킹콩보드 썸네일 업로드에 문제가 발생하였습니다.', 'kingkongboard');
        break;

        case 013 :
          $message = __('킹콩보드 첨부파일 업로드에 문제가 발생하였습니다.', 'kingkongboard');
        break;

        case 014 :
          $message = __('킹콩보드 글 수정에 문제가 발생하였습니다.', 'kingkongboard');
        break;

        case 015 :
          $message = __('킹콩보드 게시글 삭제에 필요한 인자값이 없습니다.', 'kingkongboard');
        break;

        case 016 :
          $message = __('킹콩보드 게시글 삭제에 문제가 발생하였습니다.', 'kingkongboard');
        break;
      }
      return apply_filters('kkb_error_after', $message, $code);
    }

  }

?>