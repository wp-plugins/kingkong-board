<?php
  if(isset($_GET['id'])){
    $entry_id    = sanitize_text_field($_GET['id']);
    $writer      = get_kingkong_board_meta_value($entry_id, 'writer');
    $date        = get_kingkong_board_meta_value($entry_id, 'date');
    $content     = nl2br(get_post_field('post_content', $entry_id));
    $attached    = get_post_meta($entry_id, 'kingkongboard_attached', true);
    $hit         = kkb_update_hit_count($board_id, $entry_id);

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
    $kkbContent .= '<td class="write-content">'.$thumbnail_image.$content.'</td>';
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

    if($board_comment == 'T'){
        $kkbContent .= do_shortcode('[kingkong_board_comment id='.$entry_id.']');
    }

    $kkbContent .= '<div class="kingkongboard-controller">';
    $kkbContent .= '<div class="kingkongboard-controller-left">';
    $list_path = add_query_arg( 'pageid', $pageid, get_the_permalink());
    $kkbContent .= '<a href="'.$list_path.'" class="kingkongboard-button">'.__('목록보기', 'kingkongboard').'</a>';

    if($prevListNumber){
        $prev_path = add_query_arg( array('pageid' => $pageid, 'view' => 'read', 'id' => $prevListNumber), get_the_permalink());
        $kkbContent .= '<a href="'.$prev_path.'" class="kingkongboard-button">'.__('이전글', 'kingkongboard').'</a>';
    }
    if($nextListNumber){
        $next_path = add_query_arg( array('pageid' => $pageid, 'view' => 'read', 'id' => $nextListNumber), get_the_permalink());
        $kkbContent .= '<a href="'.$next_path.'" class="kingkongboard-button">'.__('다음글', 'kingkongboard').'</a>';
    }

    if($permission_write_status == "checked" && $board_reply_use == "T"){
        $reply_path = add_query_arg( array('pageid' => $pageid, 'view' => 'reply', 'id' => $entry_id.$parent_prm), get_the_permalink());
        $kkbContent .= '<a href="'.$reply_path.'" class="kingkongboard-button">'.__('답글쓰기', 'kingkongboard').'</a>';
    }
    $kkbContent .= '</div>';
    $kkbContent .= '<div class="kingkongboard-controller-right">';


    if(kingkongboard_button_permission_check($board_id, $entry_id)){
        $modify_path = add_query_arg( array('pageid' => $pageid, 'view' => 'modify', 'id' => $entry_id), get_the_permalink());
        $kkbContent .= '<a href="'.$modify_path.'" class="kingkongboard-button">'.__('글수정', 'kingkongboard').'</a>';
    }


    if($permission_delete_status == "checked"){
        $delete_path = add_query_arg( array('pageid' => $pageid, 'view' => 'delete', 'id' => $entry_id), get_the_permalink());
        $kkbContent .= '<a class="kingkongboard-button button-entry-delete" data="'.$delete_path.'">'.__('글삭제', 'kingkongboard').'</a>';
    }
    $kkbContent .= '</div>';
    $kkbContent .= '</div>';
    $kkbContent .= '<div class="kingkongboard-copyrights"><a href="http://superrocket.io" target="_blank">Powered by Kingkong Board</a></div>';
  }
?>