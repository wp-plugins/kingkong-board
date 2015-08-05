<div class="kingkongboard-controller" style="display:block; width:100%; height:24px; margin:10px 0">
  <div class="kingkongboard-controller-left">
<?php

  $list_path = add_query_arg( 'pageid', $pageid, get_the_permalink());
  
  ($prevListNumber) ? $prev_path = add_query_arg( array('pageid' => $pageid, 'view' => 'read', 'id' => $prevListNumber), get_the_permalink()) : $prev_path = null;
  ($nextListNumber) ? $next_path = add_query_arg( array('pageid' => $pageid, 'view' => 'read', 'id' => $nextListNumber), get_the_permalink()) : $next_path = null;
  (parent::actionPermission($this->board_id, $entry_id, 'write') == true && $board_reply_use == "T") ? $reply_path = add_query_arg( array('pageid' => $pageid, 'view' => 'reply', 'id' => $entry_id.$parent_prm), get_the_permalink()) : $reply_path = null;

  if($list_path){
    $button['list'] = array(
      'type'  => 'list',
      'link'  => $list_path,
      'class' => kkb_button_classer($this->board_id)
    );
  }

  if($prev_path){
    $button['prev'] = array(
      'type'  => 'prev',
      'link'  => $prev_path,
      'class' => kkb_button_classer($this->board_id)
    );
  }

  if($next_path){
    $button['next'] = array(
      'type'  => 'next',
      'link'  => $next_path,
      'class' => kkb_button_classer($this->board_id)
    );
  }

  if($reply_path){
    $button['reply'] = array(
      'type'  => 'reply',
      'link'  => $reply_path,
      'class' => kkb_button_classer($this->board_id)
    );
  }

  $Lcontrollers = apply_filters('kkb_read_controller_left', $button, $this->board_id);

  foreach($Lcontrollers as $lcontroller){
    if($lcontroller['link'] != null){
?>
    <a href="<?php echo $lcontroller['link'];?>" class="<?php echo $lcontroller['class'];?>"><?php echo kkb_button_text($this->board_id, $lcontroller['type']);?></a>
<?php
    }
  }

?>
  </div>
  <div class="kingkongboard-controller-right">
<?php
  $modify_path  = add_query_arg( array('pageid' => $pageid, 'view' => 'modify', 'id' => $entry_id), get_the_permalink());
  $delete_path  = add_query_arg( array('pageid' => $pageid, 'view' => 'delete', 'id' => $entry_id), get_the_permalink());
  $Rbuttons     = null;
  if(parent::actionPermission($this->board_id, $entry_id, 'modify') == true){
    $Rbuttons['modify'] = array(
      'type'  => 'modify',
      'link'  => $modify_path,
      'class' => kkb_button_classer($this->board_id)
    );
  }

  if(parent::actionPermission($this->board_id, $entry_id, 'delete') == true){
    (is_user_logged_in()) ? $delete_class = ' kkb-entry-delete' : $delete_class = null;
    $Rbuttons['delete'] = array(
      'type'  => 'delete',
      'link'  => $delete_path,
      'class' => kkb_button_classer($this->board_id).$delete_class
    );
  }

  $Rcontrollers = apply_filters('kkb_read_controller_right', $Rbuttons, $this->board_id);
  
  if(is_array($Rcontrollers)){
    foreach($Rcontrollers as $key=>$rcontroller){
      if($key == 'delete' && is_user_logged_in() ){
?>
        <a class="<?php echo $rcontroller['class'];?>" data-id="<?php echo $entry_id;?>" data-board="<?php echo $this->board_id;?>"><?php echo kkb_button_text($this->board_id, $rcontroller['type']);?></a>
<?php
      } else {
?>
        <a href="<?php echo $rcontroller['link'];?>" class="<?php echo $rcontroller['class'];?>"><?php echo kkb_button_text($this->board_id, $rcontroller['type']);?></a>
<?php
      }
    }
  }

?>

  </div>
</div>