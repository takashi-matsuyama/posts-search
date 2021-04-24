<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

if( ! class_exists( 'CCC_Search_Ajax_Results' ) ) {
  class CCC_Search_Ajax_Results {

    public static function action() {
      $ajax_wp_query = CCC_Search_Ajax_WP_Query::get_data();
      $the_query = $ajax_wp_query['the_query'];
      $looplength = $ajax_wp_query['looplength'];
      //print_r($the_query);
      if( $the_query->have_posts() ) {
        $count = $looplength;
        while( $the_query->have_posts() ) {
          $the_query->the_post();
          $count++;
?>
<div class="ccc-search_ajax-results-one clearfix">
  <div class="images-search_results">
    <a href="<?php the_permalink(); ?>">
      <?php
          if( has_post_thumbnail() ) {
            echo '<div class="img-post-thumbnail has_post_thumbnail"><img src="'.get_the_post_thumbnail_url($the_query->post->ID, 'medium').'" alt="'.$the_query->post->post_title.'" loading="lazy" /></div>';
          } else {
            echo '<div class="img-post-thumbnail has_post_thumbnail-no"><img src="'.CCC_Post_Thumbnail::get_first_image_url($the_query->post).'" alt="'.$the_query->post->post_title.'" loading="lazy" /></div>';
          }
      ?>
    </a>
  </div><!-- /.images-search_results -->
  <div class="text-search_results">
    <h3 class="title-search_results-post"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3><!-- /.title-search_results-post -->
    <p class="text-search_results-post">
      <?php self::content_serch_highlight_filters(); ?>
      <a class="read-more link-color" href="<?php the_permalink(); ?>"><?php _e('read more', CCCSEARCHAJAX_TEXT_DOMAIN); ?></a>
    </p><!-- /.text-search_results-post -->
  </div><!-- /.text-search_results -->
</div><!-- /.ccc-search_ajax-results-one -->
<?php } //endwhile ?>
<?php wp_reset_postdata(); /* オリジナルの投稿データを復元 */ ?>
<?php } else { ?>
<div class="no-post">
  <p><?php _e('There are no articles that hit the search criteria.', CCCSEARCHAJAX_TEXT_DOMAIN); ?></p>
</div><!-- /.no-post -->
<?php
      } //endif
    } //endfunction

    /*** 検索キーワードハイライトのフィルターを使用して投稿本文を書き出す関数（START）***/
    public static function content_serch_highlight_filters() {
      /*
   * 投稿本文の値（get_the_content）には検索キーワードハイライトのフィルターを追加できないため、
   * 投稿本文の値（get_the_content）を投稿抜粋のフィルター（get_the_excerpt）を利用して書き出す。
   *
   * apply_filters('get_the_excerpt', $content)
   * => 投稿抜粋の値を変数の値に置き換えてから抜粋の値のフィルター（検索文字ハイライト）を適用させる。
   * => 関数フィルターの中身がこの文字列に置き換えられます。検索置き換えの関数も適用されます。
  */
      $content = get_the_excerpt();
      $content = wp_strip_all_tags( $content ); //投稿本文のHTMLタグをすべて取り除く
      $content = strip_shortcodes( $content ); //投稿本文のショートコードを取り除く
      if( mb_strlen($content, 'UTF-8') > 80 ) {
        /* 文字数を制限 */
        $ellipsis = mb_substr($content, 0, 80, 'UTF-8');
        /* 投稿抜粋の値を文字数を制限した変数の値に置き換えてから抜粋の値のフィルター（検索文字ハイライト）を適用させて「三点リーダー」を付け加える。*/
        echo apply_filters('get_the_excerpt', $ellipsis).'…';
      } else {
        /* 投稿抜粋の値を変数の値に置き換えてから抜粋の値のフィルター（検索文字ハイライト）を適用させる。*/
        echo apply_filters('get_the_excerpt', $content);
      }
    } //endfunction
    /*** 検索キーワードハイライトのフィルターを使用して投稿本文を書き出す関数（END）***/


  } //endclass
} //endif
