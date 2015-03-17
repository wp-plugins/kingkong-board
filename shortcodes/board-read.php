<?php
  if(isset($_GET['id'])){
    $entry_id    = sanitize_text_field($_GET['id']);
    $writer      = get_kingkong_board_meta_value($entry_id, 'writer');
    $date        = get_kingkong_board_meta_value($entry_id, 'date');
    $content     = nl2br(get_post_field('post_content', $entry_id));
    $attached    = get_post_meta($entry_id, 'kingkongboard_attached', true);
    $hit         = get_post_meta($entry_id, 'kingkongboard_hits', true);

    if(get_option('permalink_structure')){
      $prm_pfx = "?";
    } else {
      $prm_pfx = "&";
    }

    if(isset($_GET['pageid'])){
      $pageid = sanitize_text_field($_GET['pageid']);
    } else {
      $pageid = 1;
    }

    // 게시판 이름   : board_id
    // 게시판글 : entry_id
    if(isset($_SESSION['cnt_list'])){
        $cnt_list = $_SESSION['cnt_list'];
        $cnt_list_dummy = explode(";", $cnt_list);
        $board_cnt_ok = 0;
        for($i = 0; $i < sizeof($cnt_list_dummy); $i++){
            if($cnt_list_dummy[$i] == $board_id."_".$entry_id){
                $board_cnt_ok = 1;
                break;
            }
        }
        if($board_cnt_ok == 0){
            if($hit){
                update_post_meta($entry_id, 'kingkongboard_hits', ($hit+1) );
            } else {
                update_post_meta($entry_id, 'kingkongboard_hits', 1 );
            }
            $_SESSION['cnt_list'] .= ";".$board_id."_".$entry_id;
        }
    } else {
        update_post_meta($entry_id, 'kingkongboard_hits', 1 );
        $_SESSION['cnt_list'] = ";".$board_id."_".$entry_id;
    }

    $hit         = get_post_meta($entry_id, 'kingkongboard_hits', true);

    $currentListNumber      = get_kingkong_board_meta_value($entry_id, 'list_number');
    $prevListNumber         = get_post_id_by_list_number($board_id, ($currentListNumber+1));
    $nextListNumber         = get_post_id_by_list_number($board_id, ($currentListNumber-1));
    $board_thumbnail_input  = get_post_meta($board_id, 'kingkongboard_thumbnail_input', true);

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
    $kkbContent .= '<a href="'.get_the_permalink().$prm_pfx.'pageid='.$pageid.'" class="kingkongboard-button">'.__('목록보기', 'kingkongboard').'</a>';

    if($prevListNumber){
      $kkbContent .= '<a href="'.get_the_permalink().$prm_pfx.'pageid='.$pageid.'&view=read&id='.$prevListNumber.'" class="kingkongboard-button">'.__('이전글', 'kingkongboard').'</a>';
    }
    if($nextListNumber){
      $kkbContent .= '<a href="'.get_the_permalink().$prm_pfx.'pageid='.$pageid.'&view=read&id='.$nextListNumber.'" class="kingkongboard-button">'.__('다음글', 'kingkongboard').'</a>';
    }

    if($permission_write_status == "checked"){
        $kkbContent .= '<a href="'.get_the_permalink().$prm_pfx.'pageid='.$pageid.'&view=reply&id='.$entry_id.$parent_prm.'" class="kingkongboard-button">'.__('답글쓰기', 'kingkongboard').'</a>';
    }
    $kkbContent .= '</div>';
    $kkbContent .= '<div class="kingkongboard-controller-right">';


    if(kingkongboard_button_permission_check($board_id, $entry_id)){
        $kkbContent .= '<a href="'.get_the_permalink().$prm_pfx.'pageid='.$pageid.'&view=modify&id='.$entry_id.'" class="kingkongboard-button">'.__('글수정', 'kingkongboard').'</a>';
    }


    if($permission_delete_status == "checked"){
        $kkbContent .= '<a class="kingkongboard-button button-entry-delete" data="'.get_the_permalink().$prm_pfx.'pageid='.$pageid.'&view=delete&id='.$entry_id.'">'.__('글삭제', 'kingkongboard').'</a>';
    }
    $kkbContent .= '</div>';
    $kkbContent .= '</div>';
    $kkbContent .= '<div class="kingkongboard-copyrights"><a href="http://superrocket.io" target="_blank">Powered by Kingkong Board</a></div>';
  }
?>