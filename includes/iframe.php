<?php
  $iframe_link = add_query_arg(array('kkb_mod' => 'iframe'), get_the_permalink());
?>
<iframe id="kingkongboard-iframe" onload="javascript:resizeKKBIframe(this);" frameborder="0" border="0" cellspacing="0" src="<?php echo $iframe_link;?>" style="border-style:none; width:100%; height:auto; outline:0"></iframe>  