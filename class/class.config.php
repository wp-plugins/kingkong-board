<?php
  class kkbConfig {
 
    function __construct(){}
 
    public function getBoard($board_id){
      if(!$board_id or !is_numeric($board_id)) : return false; endif;
      $origin_slug = get_post_meta($board_id, 'kingkongboard_slug', true);
      $data = new stdClass();
      $data->ID                 = $board_id;
      $data->title              = get_the_title($board_id);
      $data->slug               = 'kkb_'.$origin_slug;
      $data->oslug              = $origin_slug;
      $data->rows               = get_post_meta($board_id, 'kingkongboard_rows', true);
      $data->shortcode          = get_post_meta($board_id, 'kingkongboard_shortcode', true);
      $data->editor             = get_post_meta($board_id, 'kingkongboard_editor', true);
      $data->search             = get_post_meta($board_id, 'kingkongboard_search', true);
      $data->thumbDP            = get_post_meta($board_id, 'kingkongboard_thumbnail_dp', true);
      $data->thumbInput         = get_post_meta($board_id, 'kingkongboard_thumbnail_input', true);
      $data->skin               = str_replace('kkb_skin_', '', get_post_meta($board_id, 'board_skin', true));
      $data->permissionR        = get_post_meta($board_id, 'permission_read', true);
      $data->permissionW        = get_post_meta($board_id, 'permission_write', true);
      $data->permissionD        = get_post_meta($board_id, 'permission_delete', true);
      $data->permissionM        = get_post_meta($board_id, 'permission_modify', true);
      $data->commentuse         = get_post_meta($board_id, 'board_comment', true);
      $data->emailNotice        = get_post_meta($board_id, 'kingkongboard_notice_emails', true);
      $data->commentOptions     = get_post_meta($board_id, 'kingkongboard_comment_options', true);
      $data->thumbUpload        = get_post_meta($board_id, 'thumbnail_upload', true);
      $data->fileUpload         = get_post_meta($board_id, 'file_upload', true);
      $data->sections           = maybe_unserialize(get_post_meta($board_id, 'board_sections', true));
      $data->captchaSitekey     = get_post_meta($board_id, 'kkb_board_captcha_sitekey', true);
      $data->captchaKey         = maybe_unserialize(get_post_meta($board_id, 'board_captcha_key', true));
      $data->captcha            = get_post_meta($board_id, 'board_captcha', true);
      $data->cmtcaptcha         = get_post_meta($board_id, 'comment_captcha', true);
      $data->managers           = get_post_meta($board_id, 'board_managers', true);
      $data->basicForm          = get_post_meta($board_id, 'kkb_basic_form', true);
      $data->excKeyword         = get_post_meta($board_id, 'kkb_exclude_keyword', true);
      $data->replyUse           = get_post_meta($board_id, 'kkb_reply_use', true);
      $data->auto_link          = get_post_meta($board_id, 'kkb_auto_link', true);
      $data->comment_auto_link  = get_post_meta($board_id, 'kkb_comment_auto_link', true);
      $data->board_under        = get_post_meta($board_id, 'kkb_read_under_loop', true);
      $data->must_secret        = get_post_meta($board_id, 'kkb_must_secret', true);
      $data->comment_html       = get_post_meta($board_id, 'kkb_comment_html_use', true);
      return apply_filters('get_board_data_after', $data, $board_id);
    }

  }
?>