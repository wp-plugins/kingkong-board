<?php

if(isset($_POST['view_type']) && isset($_POST['entry_id']) && isset($_POST['board_id'])){

    if(sanitize_text_field($_POST['view_type']) == "read"){
        if (file_exists(TEMPLATEPATH . '/' . "kingkongboard/board-read.php")) {
            require_once(TEMPLATEPATH . "/kingkongboard/board-read.php");
        } else {
            if( file_exists(WP_CONTENT_DIR . '/' . "kingkongboard/skins/".$board_skin."/board-read.php")){
                require_once(WP_CONTENT_DIR."/kingkongboard/skins/".$board_skin."/board-read.php");
            } else {
                require_once(KINGKONGBOARD_ABSPATH."/shortcodes/board-read.php");
            }
        }
    } else {
        $kkbContent .= __("잘못된 접근 입니다.", "kingkongboard");
    }

} else {

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

    switch(sanitize_text_field($_GET['view'])){
        case "read" :
            $input_text = __('비밀글 입니다. ', 'kingkongboard');
        break;

        case "delete" :
            $input_text = __('삭제 하시려면 ', 'kingkongboard');
        break;

        default :
            $input_text = null;
        break;
    }

        $verifyContent  = '<div>';
        $verifyContent .= $input_text.__('아래에 비밀번호를 입력 해 주시기 바랍니다.', 'kingkongboard');
        $verifyContent .= '</div>';
        $verifyContent .= '<form method="post" id="kingkongboard_verify_form">';
        $verifyContent .= '<div class="kingkongboard-read-verifying-wrapper">';
        $verifyContent .= '<table class="kingkongboard-read-verifying-table"><tr><td style="width:300px"><input type="password" name="kingkongboard-verifying-pwd" style="width:100%"></td><td style="padding-left:5px"><a  class="kingkongboard-button button-verify-submit" style="height:26px;">확인</a></td></tr></table>';
        $verifyContent .= '</div>';
        $verifyContent .= '<input type="hidden" name="view_type" value="'.sanitize_text_field($_GET['view']).'">';
        $verifyContent .= '<input type="hidden" name="entry_id" value="'.$entry_id.'">';
        $verifyContent .= '<input type="hidden" name="board_id" value="'.$board_id.'">';
        $verifyContent .= '</form>';
        $verifyContent .= '<div><a href="'.get_the_permalink().$prm_pfx.'pageid='.$pageid.'" class="kingkongboard-button">'.__('목록보기', 'kingkongboard').'</a></div>';

        $kkbContent .= $verifyContent;    
}

?>