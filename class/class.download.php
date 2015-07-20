<?php
  class kkbDownloader extends kkbController {
    function __construct($entry_id, $attach_id){
      $this->attach_id = $attach_id;
      $this->entry_id  = $entry_id;
      $this->board_id  = parent::getMeta($this->entry_id, 'board_id');
      $this->title     = get_the_title($this->attach_id);
      $this->filePath  = wp_get_attachment_url($this->attach_id);
      $this->filetype  = get_post_meta($this->attach_id, 'file_type', true);
      if(!$this->filetype || empty($this->filetype)){
        $filetype = wp_check_filetype(basename($this->filePath));
        $this->filetype = $filetype['ext'];
      }
      $this->filename  = trim($this->title.'.'.$this->filetype);
      $this->fullpath  = get_attached_file($this->attach_id);
    }

    public function download(){
      if(parent::actionPermission($this->board_id, $this->entry_id, 'read') == true){
        $filename = $this->filename;
        $filesize = filesize($this->fullpath);
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $filename); 
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $filesize);
        ob_clean();
        flush();
        readfile($this->fullpath);
        do_action('kkb_download_after', $this->entry_id, $this->attach_id);
        exit;        
      } else {
        $message = apply_filters('kkb_download_failed_alert', "<script>alert('".__('파일 다운로드 권한이 없습니다.', 'kingkongboard')."');</script>", $this->entry_id, $this->attach_id);
        echo $message;
      }
    }
  }
?>