<?php

  class kkboard extends kkbConfig {

    function __construct(){

    }

    public function getData($board_id){
      $data = parent::getBoard($board_id);
      return $data;
    }

    public function boardList(){
      require_once KINGKONGBOARD_ABSPATH.'/class/class.controller.php';
      $controller = new kkbController();
      return $controller->getBoards();
    }

  }

?>