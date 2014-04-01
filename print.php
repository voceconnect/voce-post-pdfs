<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
</head>
<body>
<div class="header">
	<?php do_action('voce_post_pdfs_logo'); ?>
</div>
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
	<div class="post">
		<h1><?php the_title(); ?></h1>
		<div class="content"><?php the_content(); ?></div>
	</div>
<?php endwhile; endif; ?>
</body>
</html>
