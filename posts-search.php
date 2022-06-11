<?php

/**
 * Plugin Name: Posts Search
 * Plugin URI: https://wordpress.org/plugins/posts-search/
 * Description: Search posts by taxonomy terms and post type with Ajax.
 * Version: 1.2.2
 * Requires at least: 4.8
 * Requires PHP: 5.4.0
 * Author: Takashi Matsuyama
 * Author URI: https://profiles.wordpress.org/takashimatsuyama/
 * Text Domain: posts-search
 */

if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly.
}

$this_plugin_info = get_file_data(__FILE__, array(
  'name' => 'Plugin Name',
  'version' => 'Version',
  'text_domain' => 'Text Domain',
  'minimum_php' => 'Requires PHP',
));

define('CCCSEARCHAJAX_PLUGIN_PATH', rtrim(plugin_dir_path(__FILE__), '/'));
define('CCCSEARCHAJAX_PLUGIN_URL', rtrim(plugin_dir_url(__FILE__), '/'));
define('CCCSEARCHAJAX_PLUGIN_SLUG', trim(dirname(plugin_basename(__FILE__)), '/'));
define('CCCSEARCHAJAX_PLUGIN_NAME', $this_plugin_info['name']);
define('CCCSEARCHAJAX_PLUGIN_VERSION', $this_plugin_info['version']);
define('CCCSEARCHAJAX_TEXT_DOMAIN', $this_plugin_info['text_domain']);

load_plugin_textdomain(CCCSEARCHAJAX_TEXT_DOMAIN, false, basename(dirname(__FILE__)) . '/languages');

/*** Require PHP Version Check ***/
if (version_compare(phpversion(), $this_plugin_info['minimum_php'], '<')) {
  $plugin_notice = sprintf(__('Oops, this plugin will soon require PHP %s or higher.', CCCSEARCHAJAX_TEXT_DOMAIN), $this_plugin_info['minimum_php']);
  register_activation_hook(__FILE__, create_function('', "deactivate_plugins('" . plugin_basename(__FILE__) . "'); wp_die('{$plugin_notice}');"));
}

if (!class_exists('CCC_Search_Ajax')) {
  require(CCCSEARCHAJAX_PLUGIN_PATH . '/function.php');
  /****** CCC_Search_Ajax Initialize ******/
  function ccc_search_ajax_initialize()
  {
    global $ccc_search_ajax;
    /* Instantiate only once. */
    if (!isset($ccc_search_ajax)) {
      $ccc_search_ajax = new CCC_Search_Ajax();
    }
    return $ccc_search_ajax;
  }
  /*** Instantiate ****/
  ccc_search_ajax_initialize();

  /*** How to use this Shortcode ***/
  /*
  * [ccc_posts_search_searchform placeholder="string" style="string"]
  * [ccc_posts_search_results posts_per_page="int" search_keyword="required" style="string"]
  */
  require(CCCSEARCHAJAX_PLUGIN_PATH . '/assets/shortcode-searchform.php');
  require(CCCSEARCHAJAX_PLUGIN_PATH . '/assets/shortcode-results.php');
} else {
  $plugin_notice = __('Oops, PHP Class Name Conflict.', CCCSEARCHAJAX_TEXT_DOMAIN);
  wp_die($plugin_notice);
}
