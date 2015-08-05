<ul>
	<li class="write-info-title-writer"><?php _e('작성자', 'kingkongboard');?></li>
	<li class="write-info-title-date"><?php _e('작성일', 'kingkongboard');?></li>
	<li class="write-info-date"><?php echo date("Y-m-d H:i:s", $date);?></li>
	<li class="write-info-title-hit"><?php _e('조회수', 'kingkongboard');?></li>
	<li class="write-info-hit"><?php echo $hit;?></li>
</ul>