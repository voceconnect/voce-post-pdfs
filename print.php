<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<style type="text/css">
		body { font-size: 14px; color: #252525; font-family: Helvetica Neue, Helvetica, sans-serif; }
		.header { text-align:center; }
		h1 { font: 35px Arial; color: #056287; }
	</style>
</head>
<body>
<script type="text/php">
	if ( isset($pdf) ) {
		$pdf->page_script('
			if ( $PAGE_NUM > 1 ) {
				remove_filter("the_title", "wptexturize");
				$font = Font_Metrics::get_font("helvetica", "bold");
      			$pdf->text(35, 20, "Page $PAGE_NUM of $PAGE_COUNT - " . the_title_attribute( array( "echo" => false ) ), $font, 6, array(0,0,0));
      			add_filter("the_title", "wptexturize");
    		}'
    	);
	}
</script>
<div class="header">
	<?php do_action('voce_post_pdfs_logo'); ?>
</div>
<div class="well">
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	<div class="post">
		<h1><?php the_title(); ?></h1>
		<div class="content"><?php the_content(); ?></div>
	</div>
<?php endwhile; endif; ?>
</div>
</body>
</html>
