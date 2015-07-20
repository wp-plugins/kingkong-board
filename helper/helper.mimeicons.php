<?php
class kkbMimeIcons {
  function __construct(){
    $this->base = KINGKONGBOARD_PLUGINS_URL . "/assets/images/mime-icons/";
  }
  public function getImage($filename){
    $filetypes  = wp_check_filetype($filename);
    $filetype   = $filetypes['type'];
    return '<img src="'.$this->getPath($filetype).'" class="attach-mime-icon">';
  }

  public function getPath($filetype){
    switch ($filetype) {
      case 'image/jpeg':
      case 'image/png':
      case 'image/gif':
        return $this->base . "image.png"; break;
      case 'application/excel':
      case 'application/vnd.ms-excel':
      case 'application/x-excel':
      case 'application/x-msexcel':
      case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' :
        return $this->base . "xls.png"; break;
      case 'application/pdf':
        return $base . "pdf.png"; break;
      case 'application/x-compressed':
      case 'application/x-zip-compressed':
      case 'application/zip':
      case 'application/multipart/x-zip':
        return $this->base . "zip.png"; break;
      case 'video/mpeg':
      case 'video/mp4': 
      case 'video/quicktime':
        return $this->base . "movie.png"; break;
      case 'text/csv':
      case 'text/plain': 
      case 'text/xml':
        return $this->base . "file.png"; break;
      default:
        return $this->base . "file.png";
    }

  }
}
?>