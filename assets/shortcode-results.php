<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
} //endif

if( ! class_exists( 'CCC_Search_Ajax_ShortCode_Results' ) ) {

  add_shortcode('ccc_posts_search_results', array('CCC_Search_Ajax_ShortCode_Results', 'html') );

  class CCC_Search_Ajax_ShortCode_Results {

    public static function html($atts) {
      ob_start(); // returnでHTMLを返す：出力のバッファリングを有効にする

      $atts = shortcode_atts(array(
        "posts_per_page" => '',
        "search_keyword" => '',
        "style" => '',
      ), $atts);
      if( $atts['posts_per_page'] and ctype_digit($atts['posts_per_page']) ) {
        $posts_per_page = $atts['posts_per_page'];
      } else {
        $posts_per_page = 10;
      }
      if( $atts['search_keyword'] === "required" ) {
        $search_keyword_required = true;
      } else {
        $search_keyword_required = false;
      }
      if( $atts['style'] or $atts['style'] === 0 or $atts['style'] === '0' ) {
        $style = $atts['style'];
      } else {
        $style = 1;
      }
      $ajax_wp_query = CCC_Search_Ajax_WP_Query::get_data();
      $the_query = $ajax_wp_query['the_query'];
      $posttype_name = $ajax_wp_query['posttype_name'];
?>
<div id="ccc-search_ajax-results-content" data-ccc_posts_search-results-style="<?php echo $style; ?>">
  <?php if( empty( get_search_query() ) and $search_keyword_required ) { ?>
  <div class="no-post">
    <p><?php _e('No search keyword has been entered.', CCCSEARCHAJAX_TEXT_DOMAIN); ?></p>
  </div><!-- /.no-post -->
  <?php } else {
        self::header_search_results( $the_query, $posttype_name ); /* 検索結果のヘッダーを出力する関数を呼び出し */
  ?>
  <div id="ccc-search_ajax-results" data-ccc_search_ajax-posts_per_page="<?php echo $posts_per_page; ?>"></div><!-- /#ccc-search_ajax-results -->
  <div id="ccc-search_ajax-header-clone"></div><!-- /#ccc-search_ajax-header-clone -->
  <div class="results-more"><a href="#" id="ccc-search_ajax-results-more-trigger"><i class="icon-ccc_search_ajax-refresh"></i><?php _e('Read further', CCCSEARCHAJAX_TEXT_DOMAIN); ?></a></div><!-- /#ccc-search_ajax-results-more-trigger -->
  <div id="ccc-search_ajax-loader"><div class="loader"><?php _e('Loading', CCCSEARCHAJAX_TEXT_DOMAIN); ?>...</div></div><!-- /#ccc-search_ajax-loader -->
  <?php } //endif ?>
</div><!-- /#ccc-search_ajax-results-content -->
<?php
      return ob_get_clean();  // returnでHTMLを返す：関数からHTMLを返し、それをいろいろ編集したり、処理を加えてから出力する場面で有効：バッファリングの内容を出力した後にバッファリングを削除
    } //endfunction

    /*** 検索リクエストしたタクソノミーの項目内容を表示する関数（START）  ***/
    public static function search_query_taxonomy_text( $query_name, $query_label ) {
      if( isset($_GET['search_'. $query_name]) ) {
        echo '<span class="text-query '. $query_name .'">';
        echo '<span class="text-query-label">'. $query_label .'</span>';
        echo '<span class="text-query-value">';
        if( is_array( $_GET['search_'. $query_name] ) ) {
          $get_search_query_name = array_map( 'absint', $_GET['search_'. $query_name] );
          foreach( $get_search_query_name as $val ) {
            $term_name = get_term_by('id', $val, $query_name);
            if( $term_name ) {
              if( $val === end( $get_search_query_name ) ) {
                echo $term_name->name;
              } else {
                echo $term_name->name.', ';
              } //endif
            } //endif
          } //endforeach
        } //endif
        echo '</span>'; //<!-- /.text-query-value -->
        echo '</span>'; //<!-- /.text-query -->
      } //endif
    } //endfunction
    /*** 検索リクエストしたタクソノミーの項目内容を表示する関数（END）  ***/

    /*** 検索結果のヘッダーを出力する関数（START） ***/
    public static function header_search_results( $the_query, $posttype_name ) {
      extract($GLOBALS); // 注意：これを使えば関数内で全てのグローバル変数を読みだすことはできますが、書き換えることはできません。関数の引数でいうところの値渡しの状態です。
?>
<div class="ccc-search_ajax-header clearfix">
  <h2 class="query-search_results">
    <span class="text-query keyword"><span class="text-query-label"><?php _e('Search keyword', CCCSEARCHAJAX_TEXT_DOMAIN); ?></span><span class="text-query-value"><?php echo get_search_query(); ?></span></span>
    <span class="text-query post_type"><span class="text-query-label"><?php _e('Search area', CCCSEARCHAJAX_TEXT_DOMAIN); ?></span><span class="text-query-value"><?php echo $posttype_name; ?></span></span>
    <?php
      /* 検索リクエストしたタクソノミーの項目内容を表示する関数を呼び出し */
      $taxonomies = get_object_taxonomies( $the_query->query['post_type'], 'objects' ); //第一引数は「配列/文字列/オブジェクト」
      if( isset($taxonomies) and is_array($taxonomies) ) {
        foreach( $taxonomies as $taxonomy ) {
          self::search_query_taxonomy_text( $taxonomy->name, $taxonomy->label );
        } //endforeach
      } //endif
    ?>
  </h2><!-- /.query-search_results -->
  <?php if( $the_query->post_count > 0 ) { ?>
  <p><span class="ccc-search_ajax-post-count"><?php echo $the_query->post_count; ?></span>/<span class="ccc-search_ajax-found_posts-count"><?php echo $the_query->found_posts; ?></span><?php printf( _n( 'item display', 'items display', $the_query->post_count, CCCSEARCHAJAX_TEXT_DOMAIN ), $the_query->post_count ); ?></p>
  <?php } ?>
</div><!-- /.ccc-search_ajax-header -->
<?php
    } //endfunction
    /*** 検索結果のヘッダーを出力する関数（END） ***/

  } //endclass
} //endif
