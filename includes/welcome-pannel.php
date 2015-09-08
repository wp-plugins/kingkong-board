<?php
$installed_ver = get_option( 'kingkongboard_version' );
?>
<div class="welcome-panel-content kkb-welcome-panel">
  <h3><?php _e('킹콩보드 알림판', 'kingkongboard');?> <span style="color:gray; font-size:14px"><?php _e('버전', 'kingkongboard');?> <?php echo $installed_ver;?></span></h3>
  <div class="kkb-welcome-panel-content">
    <ul>
      <li class="kkmarket-li">
        <label><?php _e('익스텐션/애드온/스킨', 'kingkongboard');?></label>
        <div class="kkmarket-wrapper">
<?php
  $shop     = new srShop();
  $products = $shop->getResponse();
  $product_count = count($products);
  $wrapper_width = (300 * $product_count) + ($product_count * 20);
?>
          <div class="kkb-product-wrapper" style="width:<?php echo $wrapper_width;?>px; position:relative; left:0">
<?php
  $cnt = 0;

  foreach($products as $product){
    $position = $cnt * 300 + ($cnt * 20);
?>
          <div class="kkb-product" style="position:absolute; top:0; left:<?php echo $position;?>px">
            <ul>
              <li class="kkb-product-thumb"><a href="<?php echo $product['link'];?>" target="_blank"><img src="<?php echo $product['thumbnail_url'];?>" style="width:100px; height:auto"></a></li>
              <li class="kkb-product-info">
                <label><a href="<?php echo $product['link'];?>" target="_blank"><?php echo $product['title'];?></a></label>
                <span><?php echo $product['description'];?></span>
              </li>
            </ul>
          </div>
<?php
  $cnt++;
  }
?>
          </div>
        </div>
        <div class="kkb-product-controller">
          <span class="kkb-arrow-left"> <span class="btn-product-left dashicons dashicons-arrow-left-alt2" data-all="<?php echo count($products);?>" current-num="1"></span></span>
          <span class="kkb-arrow-right"><span class="btn-product-right dashicons dashicons-arrow-right-alt2" data-all="<?php echo count($products);?>" current-num="1"></span></span>
        </div>
      </li>
      <li>
        <label><?php _e('공지사항', 'kingkongboard');?></label>
        <div class="kkb-welcome-notice">
          <ul>
<?php
  $notices = $shop->getNotice();
  foreach($notices as $notice){
?>
            <li>
              <span class="notice-title"><a href="<?php echo $notice['link'];?>" target="_blank"><?php echo $notice['title'];?></a></span>
              <span class="notice-date"><?php echo $notice['date'];?></span>
            </li>
<?php
  }
?>

          </ul>
        </div>
      </li>
      <li>
        <label><?php _e('기술지원/관련사이트', 'kingkongboard');?></label>
        <div>
          <span><?php _e('페이스북 좋아요를 클릭 해 주세요~!', 'kingkongboard');?></span>
          <div class="fb-like" data-href="https://facebook.com/superrocketer" data-layout="button_count" data-action="like" data-show-faces="true" data-share="true" style="position:relative; top:-5px; margin-right:10px"></div>
          <span style="display:block"><?php _e('슈퍼로켓 공식사이트', 'kingkongboard');?> : <a href="http://superrocket.io" target="_blank">http://superrocket.io</a></span>
          <span style="display:block"><?php _e('킹콩보드 서포트포럼', 'kingkongboard');?> : <a href="http://help.superrocket.io" target="_blank">http://help.superrocket.io</a></span>
          <span style="display:block"><?php _e('킹콩보드 데모사이트', 'kingkongboard');?> : <a href="http://demo.superrocket.io" target="_blank">http://demo.superrocket.io</a></span>
        </div>
      </li>
    </ul>
  </div>
</div>