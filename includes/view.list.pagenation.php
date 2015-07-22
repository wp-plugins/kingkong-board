<?php
  require_once KINGKONGBOARD_ABSPATH.'/class/class.list.php';
  require_once KINGKONGBOARD_ABSPATH.'/class/class.pagenation.php';
  $pnation        = new kkbPagenation($board_id);
  $pageNavClass   = apply_filters('kkb_loop_pagination_class', 'kingkong-board-entry-nav', $board_id);
?>
<nav class="<?php echo $pageNavClass;?>">
  <?php echo $pnation->getPagenation(); ?>
</nav>