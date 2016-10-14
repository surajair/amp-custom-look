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