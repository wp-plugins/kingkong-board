<?php

  class kkbLatest extends kkbController {

    function __construct($bid){
      $this->config      = new kkbConfig($bid);
      $this->config      = $this->config->getBoard($bid);
    }

    public function kkb_get_latest_list($number){
      global $wpdb;
      $config  = $this->config;
      $Limit   = $number;
      $table   = $wpdb->prefix.'kingkongboard_meta';
      $filters = "WHERE board_id = '".$config->ID."' AND type != 99 order by list_number ASC LIMIT ".$Limit;
      $results = $wpdb->get_results("SELECT * FROM {$table} {$filters}");
      return apply_filters('kingkongboard_latest_list_after', $results, $config, $number);
    }

  }

?>