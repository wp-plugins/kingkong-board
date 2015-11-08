<?php
  class kkbPagenation extends kkbList {
    function __construct($board_id){
      $this->board_id = $board_id;
      (isset($_GET['pageid'])) ? $this->page = sanitize_text_field($_GET['pageid']) : $this->page = 1;
      (isset($_POST['kkb_keyword'])) ? $this->keyword = sanitize_text_field($_POST['kkb_keyword']) : $this->keyword = null;
      (isset($_POST['kkb_section'])) ? $this->section = sanitize_text_field($_POST['kkb_section']) : $this->section = null;
      (isset($_GET['kkb_keyword']) && empty($this->keyword)) ? $this->keyword = sanitize_text_field($_GET['kkb_keyword']) : $this->keyword = $this->keyword;
      (isset($_GET['kkb_section']) && empty($this->section)) ? $this->section = sanitize_text_field($_GET['kkb_section']) : $this->section = $this->section;
      (isset($_POST['srchtype'])) ? $this->srctype = sanitize_text_field($_POST['srchtype']) : $this->srctype = null;
      (isset($_GET['srchtype']) && empty($this->srctype)) ? $this->srctype = sanitize_text_field($_GET['srchtype']) : $this->srctype = $this->srctype;

      if($this->keyword || $this->section){
        $data['kkb_search_keyword'] = $this->keyword;
        $data['kkb_search_section'] = $this->section;
        $data['kkb_search_type']    = $this->srctype;
        $this->totalCount   = parent::getSearch($board_id, $data, $this->page, 'count');
        $this->pageCount    = parent::searchCount($board_id, $data, $this->page);
      } else {
        $this->totalCount   = parent::getCount($board_id, 'basic');
        $this->pageCount    = parent::pageCount($board_id);
      }

    }

    public function getPagenation(){
      $display       = null;
      $param_keyword = null;
      $param_section = null;
      $bid           = $this->board_id;
      $page          = $this->page;
      $count         = $this->pageCount;
      $keyword       = $this->keyword;
      $section       = $this->section;
      $srctype       = $this->srctype;

      if($count > 1){
        if(!empty($keyword) || !empty($section) || !empty($srctype)){ 
          (isset($keyword)) ? $param_keyword = "&kkb_keyword=".$keyword : $param_keyword = null;
          (isset($section)) ? $param_section = "&kkb_section=".$section : $param_section = null;
          (isset($srctype)) ? $param_srctype = "&srchtype=".$srctype : $param_srctype = null;
          $param = $param_keyword.$param_section.$param_srctype;
        } else {
          $param = null;
        }

        if( $page > 3){
          $page_args = apply_filters('kkb_read_arg_after', array('pageid' => '1'), $this->board_id);
          $page_path = add_query_arg($page_args, get_the_permalink());
          $display  = '<a href="'.$page_path.'"><span>1</span></a>';
          $display .= '<span class="none-select">...</span>';
          for ($i=($page-3); $i < $count; $i++) { 
            if($page == ($i+1)){
              $display .= '<span>'.($i+1).'</span>';
            } else {
              if(($i+1) > $page + 2){
                $display  .= '<span class="none-select">...</span>';
                $page_args = apply_filters('kkb_read_arg_after', array('pageid' => ($count.$param)), $this->board_id);
                $page_path = add_query_arg( $page_args, get_the_permalink());
                $display  .= '<a href="'.$page_path.'" class="kkb-page-move"><span>'.$count.'</span></a>';
                break;
              } else {
                $page_args = apply_filters('kkb_read_arg_after', array('pageid' => (($i+1).$param)), $this->board_id);
                $page_path = add_query_arg( $page_args, get_the_permalink());
                $display .= '<a href="'.$page_path.'" class="kkb-page-move"><span>'.($i+1).'</span></a>';
              }
            }
          }
        } else {
          for ($i=0; $i < $count; $i++) { 
            if($i > 3){
                $display  .= '<span class="none-select">...</span>';
                $page_args = apply_filters('kkb_read_arg_after', array('pageid' => ($count.$param)), $this->board_id);
                $page_path = add_query_arg( $page_args, get_the_permalink());
                $display  .= '<a href="'.$page_path.'" class="kkb-page-move"><span>'.$count.'</span></a>';
                break;          
            } else {
              if($page == ($i+1)){
                $display  .= '<span>'.($i+1).'</span>';
              } else {
                $page_args = apply_filters('kkb_read_arg_after', array('pageid' => (($i+1).$param)), $this->board_id);
                $page_path = add_query_arg( $page_args, get_the_permalink());
                $display  .= '<a href="'.$page_path.'" class="kkb-page-move"><span>'.($i+1).'</span></a>';
              }
            }
          }
        }
      }
      return $display;
    }

  }
?>