<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

if( ! class_exists( 'CCC_Custom_Search' ) ) {
  /*** サイト内検索の範囲に、カテゴリー名、タグ名、を含める ***/
  add_filter('posts_search', array('CCC_Custom_Search', 'query'), 10, 2);

  class CCC_Custom_Search {
    /*
 * サイト内検索の設定
 */
    public static function query($search, $wp_query) {
      global $wpdb;

      //サーチページ以外だったら終了
      if (!$wp_query->is_search) {
        return $search;
      }
      if (!isset($wp_query->query_vars)) {
        return $search;
      }
      // ターム名・タクソノミー 名、カスタムフィールド、抜粋も検索対象に変更（投稿者名は検索条件に含まない）
      $search_words = explode(' ', isset($wp_query->query_vars['s']) ? $wp_query->query_vars['s'] : '');
      if ( count($search_words) > 0 ) {
        $search = '';
        foreach( $search_words as $word ) {
          if( !empty($word) ) {
            $search_word = '%' . esc_sql( $word ) . '%';
            $search .= " AND (
          {$wpdb->posts}.post_title LIKE '{$search_word}'
          OR {$wpdb->posts}.post_content LIKE '{$search_word}'
          OR {$wpdb->posts}.post_excerpt LIKE '{$search_word}'
          OR {$wpdb->posts}.ID IN (
          SELECT distinct r.object_id
          FROM {$wpdb->term_relationships} AS r
          INNER JOIN {$wpdb->term_taxonomy} AS tt ON r.term_taxonomy_id = tt.term_taxonomy_id
          INNER JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id
          WHERE t.name LIKE '{$search_word}'
          OR t.slug LIKE '{$search_word}'
          OR tt.description LIKE '{$search_word}'
          )
          OR {$wpdb->posts}.ID IN (
          SELECT distinct post_id
          FROM {$wpdb->postmeta}
          WHERE meta_value LIKE '{$search_word}'
          )
        ) ";
          } //endif
        } //endforeach
        return $search;
      } //endif
    } //endfunction

  } //endclass
} //endif
