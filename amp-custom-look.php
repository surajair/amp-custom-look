<?php
/**
 * Plugin Name: Amp Custom Look
 * Description: Adds a custom look to you amp template.
 * Plugin URI: https://github.com/automattic/amp-wp
 * Author: Suraj Air
 * Author URI: http://happydoodles.in
 * Version: 0.1.0
 * Text Domain: amp
 * Domain Path: /languages/
 * License: GPLv2 or later
 */


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


//change the default template file for the amp page
function acl_set_custom_template( $file, $type, $post ) {
    $file = dirname( __FILE__ ) . '/templates/'.$type.'.php';
    return $file;
}
add_filter( 'amp_post_template_file', 'acl_set_custom_template', 10, 3 );



function acl_get_featured_image_src($post_id, $size = 'large'){
	$featured_image_url = 'http://placehold.it/150x150';
    if(has_post_thumbnail($post)){
      	$feat_image = wp_get_attachment_image_src(get_post_thumbnail_id($post), $size);
     	$featured_image_url = $feat_image[0];
    }
    return $featured_image_url;
}

function acl_get_post_data($post_id){
	$post = get_post($post_id);
	$post_data = array();
	$post_data['title'] = $post_id->post_title;
	$post_data['featured_image_src'] = acl_get_featured_image_src($post_id);
	return $post_data;
}

function acl_get_related_posts(){
	$post = get_post(); // returns global post instance
	$related_posts = array();
	$related_args = array(
	  'post_type' => 'post',
	  'posts_per_page' => 5,
	);
	$related = new WP_Query( $related_args );
	while($related->have_posts()): $related->the_post();
		$related_posts[] = acl_get_post_data(get_the_ID())
	endwhile;
	wp_reset_postdata();
}

// add related posts
function acl_add_related_posts( $data ) {
    $data[ 'realted_posts' ] = acl_get_related_posts();
    return $data;
}
add_filter( 'amp_post_template_data', 'acl_add_related_posts' );