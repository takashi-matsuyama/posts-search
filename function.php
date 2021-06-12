<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}
require( CCCSEARCHAJAX_PLUGIN_PATH .'/assets/wp_query.php' );
require( CCCSEARCHAJAX_PLUGIN_PATH .'/assets/results.php' );
require( CCCSEARCHAJAX_PLUGIN_PATH .'/addons/ccc-post_thumbnail/ccc-post_thumbnail.php' );
require( CCCSEARCHAJAX_PLUGIN_PATH .'/addons/ccc-custom_search.php' );
require( CCCSEARCHAJAX_PLUGIN_PATH .'/addons/ccc-search_highlight.php' );



class CCC_Search_Ajax {

  /*** Initial execution ***/
  public function __construct() {
    add_action( 'wp_enqueue_scripts', array( $this, 'jquery_check' ) );
    add_action( 'wp_enqueue_scripts', array( $this, 'found_posts_styles' ) );
    add_action( 'wp_enqueue_scripts', array( $this, 'found_posts_scripts' ) );
    add_action( 'wp_ajax_ccc_search_ajax-found_posts-action', array( $this, 'found_posts_action' ) );
    add_action( 'wp_ajax_nopriv_ccc_search_ajax-found_posts-action', array( $this, 'found_posts_action' ) );
    add_action( 'wp_enqueue_scripts', array( $this, 'results_styles' ) );
    add_action( 'wp_enqueue_scripts', array( $this, 'results_scripts' ) );
    add_action( 'wp_ajax_ccc_search_ajax-results-action', array( $this, 'results_action' ) );
    add_action( 'wp_ajax_nopriv_ccc_search_ajax-results-action', array( $this, 'results_action' ) );
  }

  public function jquery_check() {
    wp_enqueue_script('jquery');
  } //endfunction

  public function found_posts_styles() {
    wp_enqueue_style( 'ccc_search_ajax-found_posts', CCCSEARCHAJAX_PLUGIN_URL.'/assets/found_posts.css', array(), CCCSEARCHAJAX_PLUGIN_VERSION, 'all');
  }

  public function found_posts_scripts() {
    $handle = 'ccc_search_ajax-found_posts';
    $file = 'found_posts.js';
    wp_register_script( $handle, CCCSEARCHAJAX_PLUGIN_URL.'/assets/'.$file, array( 'jquery' ), CCCSEARCHAJAX_PLUGIN_VERSION, true );
    wp_enqueue_script( $handle );

    $action = 'ccc_search_ajax-found_posts-action';
    wp_localize_script( $handle, 'CCC_SEARCH_AJAX_FOUND_POSTS',
                       array(
                         'api'    => admin_url( 'admin-ajax.php' ),
                         'action' => $action,
                         'nonce'  => wp_create_nonce( $action )
                       )
                      );
  }

  public function found_posts_action() {
    if( check_ajax_referer( $_POST['action'], 'nonce', false ) ) {
      $ajax_wp_query = CCC_Search_Ajax_WP_Query::get_data();
      $the_query = $ajax_wp_query['the_query'];
      //$data = '<p class="count-found_posts"><span class="number">'.$the_query->found_posts.'</span><span class="unit">'.__('件').'</span></p>';
      $number = sprintf( _n( '<span class="number">%s</span><span class="unit">item</span>', '<span class="number">%s</span><span class="unit">items</span>', $the_query->found_posts, CCCSEARCHAJAX_TEXT_DOMAIN ), $the_query->found_posts );
      $data = '<p class="count-found_posts">'.$number.'</p>';
      //print_r( $ajax_wp_query['taxqueries'] );
    } else {
      //status_header( '403' );
      $data = 'Forbidden';
    }
    echo $data;
    die();
  }



  public function results_styles() {
    if( is_search() ) {
      wp_enqueue_style( 'ccc_search_ajax-results', CCCSEARCHAJAX_PLUGIN_URL.'/assets/results.css', array(), CCCSEARCHAJAX_PLUGIN_VERSION, 'all');
    }
  }

  public function results_scripts() {
    if( is_search() ) {
      $handle = 'ccc_search_ajax-results';
      $file = 'results.js';
      wp_register_script( $handle, CCCSEARCHAJAX_PLUGIN_URL.'/assets/'.$file, array( 'jquery' ), CCCSEARCHAJAX_PLUGIN_VERSION, true );
      wp_enqueue_script( $handle );

      $action = 'ccc_search_ajax-results-action';
      wp_localize_script( $handle, 'CCC_SEARCH_AJAX_RESULTS',
                         array(
                           'api'    => admin_url( 'admin-ajax.php' ),
                           'action' => $action,
                           'nonce'  => wp_create_nonce( $action )
                         )
                        );
    }
  }

  public function results_action() {
    if( check_ajax_referer( $_POST['action'], 'nonce', false ) ) {
      $data = CCC_Search_Ajax_Results::action();
    } else {
      //status_header( '403' );
      $data = 'Forbidden';
    }
    echo $data;
    die();
  }



}











