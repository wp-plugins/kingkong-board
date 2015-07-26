<?php
  (isset($_GET['view'])) ? $view = sanitize_text_field($_GET['view']) : $view = 'list';
?>
<div id="kingkongboard-wrapper">
  <input type="hidden" class="plugins_url" value="<?php echo KINGKONGBOARD_PLUGINS_URL;?>">
<?php
  echo $boardView->view($view);
?>
</div>