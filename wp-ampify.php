<?php
/**
 * Plugin Name: WP AMPify
 * Description: Enables AMP for posts/pages/ archives etc
 * Plugin URI: https://github.com/automattic/amp-wp
 * Author: Suraj Air
 * Author URI: http://happydoodles.in
 * Version: 0.1.0
 * Text Domain: amp
 * Domain Path: /languages/
 * License: GPLv2 or later
 */


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define('WP_AMPIFY_VERSION', "0.1.0");
define('WP_AMPIFY_PLUGIN_ROOT_PATH', plugin_dir_path( __FILE__ ));


//inlcude the amp plugin
require_once 'amp/amp.php';

//change the default template file for the amp page
function wp_ampify_set_custom_template( $file, $type, $post ) {
  if ( 'single' === $type ) {
		$file = WP_AMPIFY_PLUGIN_ROOT_PATH . '/templates/' . $type . '.php';
	}
  if($type === 'single' && wp_ampify_is_archive_page() ){
    $file = WP_AMPIFY_PLUGIN_ROOT_PATH . '/templates/loop.php';
  }

  if($type === 'single'){
    if ( wp_ampify_is_amp_front_page() && wp_ampify_is_amp_home()) {
      $file = WP_AMPIFY_PLUGIN_ROOT_PATH . '/templates/loop.php';
    } elseif ( wp_ampify_is_amp_home() ) {
      $file = WP_AMPIFY_PLUGIN_ROOT_PATH . '/templates/loop.php';
    }
  }

  return $file;
}
add_filter( 'amp_post_template_file', 'wp_ampify_set_custom_template', 10, 3 );


function wp_ampify_title() {
  if (is_home()) {
    if (get_option('page_for_posts', true)) {
      return get_the_title(get_option('page_for_posts', true));
    } else {
      return __('Latest Posts', 'dzinr');
    }
  } elseif (is_archive()) {
    return get_the_archive_title();
  } elseif (is_search()) {
    return sprintf(__('Search Results for %s', 'dzinr'), get_search_query());
  } elseif (is_404()) {
    return __('Not Found', 'dzinr');
  } else {
    return get_the_title();
  }
}

function wp_ampify_sanitize_content($content){
  list( $sanitized_content, $scripts, $styles ) = AMP_Content_Sanitizer::sanitize($content, apply_filters( 'amp_content_sanitizers', array(
    'AMP_Style_Sanitizer' => array(),
    'AMP_Blacklist_Sanitizer' => array(),
    'AMP_Img_Sanitizer' => array(),
    'AMP_Video_Sanitizer' => array(),
    'AMP_Audio_Sanitizer' => array(),
    'AMP_Iframe_Sanitizer' => array(
      'add_placeholder' => true,
    )
  ), null));
  return $sanitized_content;
}

function wp_ampify_amp_frontend_add_canonical() {
  $amp_url = '';
  if ( is_front_page() && is_home()) {
    $amp_url = site_url('?amp=1');
  } elseif ( is_front_page()) {
    $front_page_id = get_option('page_on_front');
    $amp_url = get_permalink($front_page_id) .'?amp=1';
  } elseif ( is_home() ) {
    $posts_page_id = get_option('page_for_posts');
    $amp_url = get_permalink($posts_page_id) .'?amp=1';
  }
  if ( wp_ampify_is_archive_page()) {
    $amp_url = home_url( add_query_arg( NULL, NULL ) ) . '?amp=1';
  }

  if('' === $amp_url) {
    return;
  }
  add_filter('amp_frontend_show_canonical', '__return_false');
  printf( '<link rel="amphtml" href="%s" />', esc_url( $amp_url ) );
}
add_action( 'wp_head', 'wp_ampify_amp_frontend_add_canonical', 0 );


function wp_ampify_amp_post_template_canonical($data){
  if ( wp_ampify_is_amp_front_page() && wp_ampify_is_amp_home()) {
    $data['canonical_url'] = site_url();
  } elseif ( wp_ampify_is_amp_front_page()) {
    $front_page_id = get_option('page_on_front');
    $data['canonical_url'] = get_permalink($front_page_id);
  } elseif ( wp_ampify_is_amp_home() ) {
    $posts_page_id = get_option('page_for_posts');
    $data['canonical_url'] = get_permalink($posts_page_id);
  } elseif ( wp_ampify_is_archive_page()) {
    $data['canonical_url'] = str_replace('?amp=1', '', home_url( add_query_arg( NULL, NULL )));
  }
  return $data;
}
add_action( 'amp_post_template_data', 'wp_ampify_amp_post_template_canonical', 10, 1 );


function wp_ampify_amp_front_page_render(){
  $front_page_id = get_option('page_on_front');
  amp_load_classes();
  do_action( 'pre_amp_render_post', $front_page_id );

  amp_add_post_template_actions();
  $template = new \AMP_Post_Template( $front_page_id );
  $template->load();
  exit;
}

function wp_ampify_is_amp_front_page(){
  global $wp;
  $current_url = home_url(add_query_arg(array(),$wp->request));
  return rtrim($current_url, '/') === rtrim(site_url(), '/') && isset($_GET['amp']) && 'page' === get_option('show_on_front');
}

function wp_ampify_is_amp_home(){
  global $wp;
  $current_url = home_url(add_query_arg(array(),$wp->request));
  if('posts' === get_option('show_on_front')){
    return $current_url === site_url() && isset($_GET['amp']) && 'posts' === get_option('show_on_front');
  }
  else if('page' === get_option('show_on_front')){
    $posts_page = get_option('page_for_posts');
    $posts_page_url = get_permalink($posts_page);
    return rtrim($current_url) === rtrim($posts_page_url, '/') && isset($_GET['amp']);
  }
  return false;
}

/** Interept the  template selection and  change template if amp endpoint */
function wp_ampify_amp_endpoint_template( $template ) {
  // ignore if amp plugin is not activated
  if(!function_exists('is_amp_endpoint')){
    return $template;
  }

  if(is_amp_endpoint()){
    if ( wp_ampify_is_amp_front_page() && wp_ampify_is_amp_home()) {
      amp_render();
    } elseif ( wp_ampify_is_amp_front_page()) {
      wp_ampify_amp_front_page_render();
    } elseif ( wp_ampify_is_amp_home() ) {
      amp_render();
    } elseif ( wp_ampify_is_archive_page()) {
      amp_render();
    }
    die;
  }
  return $template;
}
add_filter( 'template_include', 'wp_ampify_amp_endpoint_template', 5, 1 );


function wp_ampify_is_archive_page(){
  return ( is_archive() || is_paged() || is_author() || is_category() || is_tag() ) && 'post' == get_post_type();
}
