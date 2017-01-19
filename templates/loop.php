<!doctype html>
<html amp <?php echo AMP_HTML_Utils::build_attributes_string( $this->get( 'html_tag_attributes' ) ); ?>>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
	<?php do_action( 'amp_post_template_head', $this ); ?>
	<style amp-custom>
		<?php $this->load_parts( array( 'style' ) ); ?>
		<?php do_action( 'amp_post_template_css', $this ); ?>
	</style>
</head>

<body class="<?php echo esc_attr( $this->get( 'body_class' ) ); ?>">

<?php $this->load_parts( array( 'header-bar' ) ); ?>
<article class="amp-wp-article">
	<div class="loop">
		<div class="amp-blog-loop">
		  <?php while(have_posts()): the_post(); ?>
		    <article class="amp-blog">
		      <?php echo wp_ampify_sanitize_content(get_the_post_thumbnail()); ?>
		      <a href="<?php the_permalink() ?>"><h1><?php the_title() ?></h1></a>
		      <p><?php echo wp_ampify_sanitize_content(get_the_excerpt()) ?></p>
		    </article>
		  <?php endwhile; ?>
		</div>
		<div class="dzinr-pagination">
		  <?php
		  global $wp_query;
		  $big = 999999999; // need an unlikely integer
		  echo paginate_links( array(
		    'base' => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
		    'format' => '?paged=%#%',
		    'current' => max( 1, get_query_var('paged') ),
		    'total' => $wp_query->max_num_pages
		  ));
		  ?>
		  </div>
	</div>
</article>
<?php $this->load_parts( array( 'footer' ) ); ?>

<?php do_action( 'amp_post_template_footer', $this ); ?>

</body>
</html>
