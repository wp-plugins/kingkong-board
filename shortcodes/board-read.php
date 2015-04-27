<?php
  if(isset($_GET['id'])){
    $entry_id    = sanitize_text_field($_GET['id']);
    $writer      = get_kingkong_board_meta_value($entry_id, 'writer');
    $date        = get_kingkong_board_meta_value($entry_id, 'date');
    $content     = nl2br(get_post_field('post_content', $entry_id));
    $attached    = get_post_meta($entry_id, 'kingkongboard_attached', true);
    $hit         = kkb_update_hit_count($board_id, $entry_id);

    $prev_path   = null;
    $next_path   = null;
    $modify_path = null;
    $reply_path  = null;
    $delete_path = null;

    if(isset($_GET['pageid'])){
      $pageid = sanitize_text_field($_GET['pageid']);
    } else {
      $pageid = 1;
    }

    $currentListNumber      = get_kingkong_board_meta_value($entry_id, 'list_number');
    $prevListNumber         = get_post_id_by_list_number($board_id, ($currentListNumber+1));
    $nextListNumber         = get_post_id_by_list_number($board_id, ($currentListNumber-1));
    $board_thumbnail_input  = get_post_meta($board_id, 'kingkongboard_thumbnail_input', true);
    $board_reply_use        = get_post_meta($board_id, 'kkb_reply_use', true);

    if($board_thumbnail_input == "T"){
        $thumbnail              = wp_get_attachment_image_src( get_post_thumbnail_id($entry_id), "full" );
        if($thumbnail){
            $thumbnail_image    = '<img src="'.$thumbnail[0].'" style="max-width:70%; height:auto"><br><br>';
        } else {
            $thumbnail_image    = null;
        }
    } else {
        $thumbnail          = null;
        $thumbnail_image    = null;
    }

    $kkbContent .= '<table id="kingkongboard-read-table">';
    $kkbContent .= '<tr>';
    $kkbContent .= '<th>';
    $kkbContent .= get_the_title($entry_id);
    $kkbContent .= '</th>';
    $kkbContent .= '</tr>';
    $kkbContent .= '<tr class="write-info">';
    $kkbContent .= '<td class="write-info-td">';
    $kkbContent .= '<ul>';
    $kkbContent .= '<li class="write-info-title-writer">'.__('작성자', 'kingkongboard').'</li>';
    $kkbContent .= '<li class="write-info-writer">'.$writer.'</li>';
    $kkbContent .= '<li class="write-info-title-date">'.__('작성일', 'kingkongboard').'</li>';
    $kkbContent .= '<li class="write-info-date">'.date("Y-m-d H:i:s", $date).'</li>';
    $kkbContent .= '<li class="write-info-title-hit">'.__('조회수', 'kingkongboard').'</li>';
    $kkbContent .= '<li class="write-info-hit">'.$hit.'</li>';
    $kkbContent .= '</ul>';
    $kkbContent .= '</td>';
    $kkbContent .= '</tr>';
    $kkbContent .= '<tr>';
    $kkbContent .= '<td class="write-content">';
    $kkb_read_content_before = apply_filters('kkb_read_content_before', $board_id, $entry_id);
    if($kkb_read_content_before != $board_id){
        $kkbContent .= $kkb_read_content_before;
    }    
    $kkbContent .= $thumbnail_image.$content;
    $kkbContent .= '</td>';
    $kkbContent .= '</tr>';

    if($attached){
        
        $attached = unserialize($attached);

        $kkbContent .= '<tr>';
        $kkbContent .= '<td>';
        $kkbContent .= '<div class="entry_attachment_title">'.__('첨부파일', 'kingkongboard').' ('.count($attached).')</div>';
        
        foreach($attached as $attach){
            $filename = get_kingkongboard_uploaded_filename($attach);
            $typeIcon = "<img src='".kingkongboard_get_icon_for_attachment($attach)."' style='width:15px; height:auto; margin-right:5px'>";
            $kkbContent .= '<div class="entry_each_attach">'.$typeIcon.'<a href="'.wp_get_attachment_url($attach).'" download>'.$filename.' <span style="color:gray">'.kingkongboard_attached_getSize($attach).'</span></a></div>';
        }
        $kkbContent .= '</td>';
        $kkbContent .= '</tr>';
    }

    if(isset($_GET['prnt'])){
        $parent = sanitize_text_field($_GET['prnt']);
            
        if($parent != ''){
        $parent_id  = $parent;
        $parent_prm = '&prnt='.$parent_id;
        }
    } else {
    $parent_id    = $entry_id;
    $parent_prm   = '';
    }

    $kkbContent .= '</table>';
    $kkb_read_comment_before = apply_filters('kkb_read_comment_before', $board_id, $entry_id);
    if($kkb_read_comment_before != $board_id){
        $kkbContent .= $kkb_read_comment_before;
    }
    if($board_comment == 'T'){
        $kkbContent .= do_shortcode('[kingkong_board_comment id='.$entry_id.']');
    }

    $kkb_read_comment_after = apply_filters('kkb_read_comment_after', $board_id, $entry_id);
    if($kkb_read_comment_after != $board_id){
        $kkbContent .= $kkb_read_comment_after;
    }

    $kkbContent .= '<div class="kingkongboard-controller">';
    $kkbContent .= '<div class="kingkongboard-controller-left">';
    $list_path = add_query_arg( 'pageid', $pageid, get_the_permalink());

    if($prevListNumber){
        $prev_path = add_query_arg( array('pageid' => $pageid, 'view' => 'read', 'id' => $prevListNumber), get_the_permalink());
    }
    if($nextListNumber){
        $next_path = add_query_arg( array('pageid' => $pageid, 'view' => 'read', 'id' => $nextListNumber), get_the_permalink());
    }

    if($permission_write_status == "checked" && $board_reply_use == "T"){
        $reply_path = add_query_arg( array('pageid' => $pageid, 'view' => 'reply', 'id' => $entry_id.$parent_prm), get_the_permalink());
    }

    $Lcontrollers   = array(
                        'list' => array(
                            'type'  => 'list',
                            'link'  => $list_path,
                            'class' => kkb_button_classer($board_id)
                        ), 
                        'prev' => array(
                            'type'  => 'prev',
                            'link'  => $prev_path,
                            'class' => kkb_button_classer($board_id)
                        ), 
                        'next' => array(
                            'type'  => 'next',
                            'link'  => $next_path,
                            'class' => kkb_button_classer($board_id)
                        ), 
                        'reply' => array(
                            'type'  => 'reply',
                            'link'  => $reply_path,
                            'class' => kkb_button_classer($board_id)
                        )
                    );

    $Lcontrollers   = apply_filters('kkb_read_controller_left', $Lcontrollers, $board_id);

    foreach($Lcontrollers as $lcontroller){
        if($lcontroller['link'] != null){
            $kkbContent .= '<a href="'.$lcontroller['link'].'" class="'.$lcontroller['class'].'">'.kkb_button_text($board_id, $lcontroller['type']).'</a>';
        }
    }
    $kkbContent .= '</div>';
    $kkbContent .= '<div class="kingkongboard-controller-right">';


    if(kingkongboard_button_permission_check($board_id, $entry_id)){
        $modify_path = add_query_arg( array('pageid' => $pageid, 'view' => 'modify', 'id' => $entry_id), get_the_permalink());
    }


    if($permission_delete_status == "checked"){
        $delete_path = add_query_arg( array('pageid' => $pageid, 'view' => 'delete', 'id' => $entry_id), get_the_permalink());
    }

    $Rcontrollers   = array(
                        'modify' => array(
                            'type'  => 'modify',
                            'link'  => $modify_path,
                            'class' => kkb_button_classer($board_id)
                        ), 
                        'delete' => array(
                             'type'  => 'delete',
                            'link'  => $delete_path,
                            'class' => kkb_button_classer($board_id)
                        )
                    );

    $Rcontrollers   = apply_filters('kkb_read_controller_right', $Rcontrollers, $board_id);

    foreach($Rcontrollers as $rcontroller){
        $kkbContent .= '<a href="'.$rcontroller['link'].'" class="'.$rcontroller['class'].'">'.kkb_button_text($board_id, $rcontroller['type']).'</a>';
    }    
    $kkbContent .= '</div>';
    $kkbContent .= '</div>';
    $kkbContent .= '<div class="kingkongboard-copyrights"><a href="http://superrocket.io" target="_blank">Powered by Kingkong Board</a></div>';
  }
?>