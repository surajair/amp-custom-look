<?php
/**
 * Plugin Name: WP AMPify
 * Description: Enables AMP for posts/pages/archives etc
 * Plugin URI: https://github.com/surajair/wp-ampify
 * Author: Suraj Air
 * Author URI: http://happydoodles.in
 * Version: 1.0.0
 * Text Domain: amp
 * Domain Path: /languages/
 * License: GPLv2 or later
 */


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

define('WP_AMPIFY_VERSION', "1.0.0");
define('WP_AMPIFY_PLUGIN_ROOT_PATH', plugin_dir_path( __FILE__ ));
define('WP_AMPIFY_PLUGIN_ACCESS_TOKEN', 'd29d84d6c162f806c144a29d9d04ad3321bdaa65');



//inlcude the amp plugin
require_once 'amp/amp.php';


function wp_ampify_activate() {
  if ( ! did_action( 'amp_init' ) ) {
    amp_init();
  }
  flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'wp_ampify_activate' );

function wp_ampify_deactivate() {
  // We need to manually remove the amp endpoint
  global $wp_rewrite;
  foreach ( $wp_rewrite->endpoints as $index => $endpoint ) {
    if ( AMP_QUERY_VAR === $endpoint[1] ) {
      unset( $wp_rewrite->endpoints[ $index ] );
      break;
    }
  }

  flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'wp_ampify_deactivate' );

function wp_ampify_check_for_update(){
  require_once WP_AMPIFY_PLUGIN_ROOT_PATH . '/wp-updater.php';
  if ( is_admin() ) {
      new WPFDGitHubPluginUpdater( __FILE__, 'surajair', "wp-ampify", WP_AMPIFY_PLUGIN_ACCESS_TOKEN );
  }
}
add_action('admin_init', 'wp_ampify_check_for_update');

//change the default template file for the amp page
function wp_ampify_set_custom_template( $file, $type, $post ) {
  $custom_file = get_template_directory() . '/templates/amp/' . $type . '.php';
  if($type === 'single' && wp_ampify_is_archive_page() ){
    $custom_file = get_template_directory() . '/templates/amp/loop.php';
    if(!file_exists($custom_file)){ 
      $custom_file = WP_AMPIFY_PLUGIN_ROOT_PATH . '/templates/loop.php';
    }
  }

  if($type === 'single'){
    if ((wp_ampify_is_amp_front_page() && wp_ampify_is_amp_home()) || wp_ampify_is_amp_home()) {
      $custom_file = get_template_directory() . '/templates/amp/loop.php';

      if(!file_exists($custom_file)){ 
        $custom_file = WP_AMPIFY_PLUGIN_ROOT_PATH . '/templates/loop.php';
      }
    }
    
  }
  //fallback to default template file if not found
  if(!file_exists($custom_file)){ 
    $custom_file = WP_AMPIFY_PLUGIN_ROOT_PATH . '/templates/' . $type . '.php';
  }
  return $custom_file;
}
add_filter( 'amp_post_template_file', 'wp_ampify_set_custom_template', 10, 3 );


function wp_ampify_custom_css_styles(){
  $custom_css = get_option('_wp_ampify_custom_css','');
  echo $custom_css;
}
add_action( 'amp_post_template_css', 'wp_ampify_custom_css_styles' );

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
  list( $sanitized_content, $scripts, $styles ) = AMP_Content_Sanitizer::sanitize($content, apply_filters('amp_content_sanitizers', array(
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
  return is_archive();
}


function wp_ampify_options(){
  add_options_page( 'WP AMPify', 'WP AMPify', 'manage_options', 'wp-ampify', 'wp_ampify_css_page', '', 10 );  
}
add_action( 'admin_menu', 'wp_ampify_options' );


function wp_ampify_css_page(){
  include_once('admin/custom-css.php');  
}

function wp_ampify_save_custom_css(){
  // var_dump($_POST['wp_ampify_custom_css']);die;
  if(!isset($_POST['wp_ampify_custom_css'])){
    return;
  }
  if (isset($_POST['wp_ampify_css']) && !wp_verify_nonce($_POST['wp_ampify_css'], 'wp_ampify_css' ) ){
    wp_die('Invalid');
  }

  $css = $_POST['wp_ampify_custom_css'];
  update_option('_wp_ampify_custom_css', stripcslashes($css));
  $_SESSION['wp_ampify_settings_saved'] = 'Settings Saved';
}
add_action('wp_loaded', 'wp_ampify_save_custom_css');


function wp_ampify_save_fonts(){
  // var_dump($_POST['wp_ampify_custom_css']);die;
  if(!isset($_POST['wp_ampify_fonts_nonce'])){
    return;
  }
  if (isset($_POST['wp_ampify_fonts_nonce']) && !wp_verify_nonce($_POST['wp_ampify_fonts_nonce'], 'wp_ampify_fonts_nonce' ) ){
    wp_die('Invalid');
  }

  $fonts = trim($_POST['wp_ampify_fonts']);
  if('' === $fonts){
    update_option('_wp_ampify_fonts', array());
  }
  else{
    $fonts = explode(',', $fonts);
    update_option('_wp_ampify_fonts', $fonts);
  }
  $_SESSION['wp_ampify_settings_saved'] = 'Settings Saved';
}
add_action('wp_loaded', 'wp_ampify_save_fonts');



function wp_ampify_remove_default_font($data, $post){
  unset($data['font_urls']['merriweather']);
    
  $fonts = get_option('_wp_ampify_fonts', array());
  foreach ($fonts as  $i => $font) {
    $data['font_urls']['font' . $i] = $font;
  }
  return $data;
}
add_filter( 'amp_post_template_data', 'wp_ampify_remove_default_font', 10, 2 );

function wp_ampify_post_template_head($amp_template){
  $font_awesome = get_option('_wp_ampify_font_awesome', 'no');
  if('yes' === $font_awesome){ ?>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <?php }
}
add_action( 'amp_post_template_head', 'wp_ampify_post_template_head', 10, 1);