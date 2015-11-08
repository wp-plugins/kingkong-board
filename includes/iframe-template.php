<?php
  wp_head();
?>		
    <?php if (have_posts()): while (have_posts()) : the_post(); ?>

<?php
  $woo_qna = get_post_meta($post->ID, 'kkbext_qna_woo_selected', true);
  if(is_product()){
    echo do_shortcode('[kingkong_board '.$woo_qna.']');
  } else {
    $post = get_post($post->ID);
    $atts = shortcode_parse_atts( $post->post_content );
    foreach($atts as $key => $att){
      if(strpos($att, '[kingkong_board') !== false){
        $board_att = $key;
      }
    }
    $board_slug = $atts[$board_att + 1];
    $board_slug = str_replace(']', '', $board_slug);
    if(!empty($board_slug)){
      echo do_shortcode('[kingkong_board '.$board_slug.']');
    } else {
      the_content();
    }
  }
?>

		<?php endwhile; ?>
		<?php endif; ?>