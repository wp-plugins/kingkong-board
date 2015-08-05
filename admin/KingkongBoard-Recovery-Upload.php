<?php
  
  $recovery_file = $_FILES['kkb_recovery_file'];
  //$Recovery = new KKB_Backup();
  //$Recovery->importXml($recovery_file['tmp_name']);
  $file = $recovery_file['tmp_name'];
  $upload_dir = wp_upload_dir();

  $folder = $upload_dir['basedir'].'/kingkongboard';
  
  if(!file_exists($folder.'/'.$recovery_file['name'])){
    $mkdir = @mkdir($folder);
  }
  copy($file, $folder.'/'.$recovery_file['name']);

  $file_path = $upload_dir['baseurl'].'/kingkongboard/'.$recovery_file['name'];
?>


<script>
  jQuery(document).ready(function(){
    jQuery.ajax({
      type : 'GET',
      url   : '<?php echo $file_path;?>',
      dataType : 'xml',
      async : false,
      success : kkb_response_parse
    });
  });

  function kkb_response_parse(xml){
    // 전체 DB 초기화
    //console.log(xml);

    jQuery(".kkb_backup_results").html("<li class='backup_reset'>기존 데이터 털어내는중...<span class='result_status'></span></li>");
    var data = {
      'action'  : 'kkb_backup_reset'
    }
    jQuery.post(ajaxurl, data, function(response) {
      if(response.status == 'success'){
        jQuery('.backup_reset').find('.result_status').html('success');
        kkb_step2_insert_board(xml);
      } else {
        jQuery('.backup_reset').find('.result_status').html('failed');
        console.log(response.log);
      }
    });

    // 게시판 부터 삽입
    // 게시판 메타정보 삽입
    // 게시글 삽입
    // 코멘트 삽입
    // 첨부/썸네일 삽입
    // 종료
  }

  function kkb_step2_insert_board(xml){
    var boards      = jQuery(xml).find('board').find('data');
    var board_count = jQuery(xml).find('board').find('data').length;
    jQuery(".kkb_backup_results").append("<li class='backup_create_board'>게시판 한땀한땀 만들고 있는중...<span class='result_status'><span class='current_count'>0</span>/"+board_count+"</span></li>");
    boards.each(function(){
      var ID                    = jQuery(this).find('ID').text();
      var post_title            = jQuery(this).find('post_title').text();
      var post_author           = jQuery(this).find('post_author').text();
      var post_date             = jQuery(this).find('post_date').text();
      var post_date_gmt         = jQuery(this).find('post_date_gmt').text();
      var post_content          = jQuery(this).find('post_content').text();
      var post_excerpt          = jQuery(this).find('post_excerpt').text();
      var post_status           = jQuery(this).find('post_status').text();
      var comment_status        = jQuery(this).find('comment_status').text();
      var ping_status           = jQuery(this).find('ping_status').text();
      var post_password         = jQuery(this).find('post_password').text();
      var post_name             = jQuery(this).find('post_name').text();
      var to_ping               = jQuery(this).find('to_ping').text();
      var pinged     