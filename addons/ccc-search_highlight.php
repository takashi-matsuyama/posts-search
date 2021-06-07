<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

if( ! class_exists( 'CCC_Search_Highlight' ) ) {
  /*** ユーザーが検索したキーワードをハイライトして表示させる。 ***/
  add_filter('the_title', array('CCC_Search_Highlight', 'results'), 999);
  add_filter('the_content', array('CCC_Search_Highlight', 'results'), 999);
  add_filter('get_the_excerpt', array('CCC_Search_Highlight', 'results'), 999);

  class CCC_Search_Highlight {
    //投稿本文の値（get_the_content）には追加（add_filter）できません。
    public static function results($text){
      if( isset($_POST['search_highlight_ajax']) and ( isset($_POST['s']) or get_search_query() ) ) {
        if( isset($_POST['s']) ) {
          $search_query = $_POST['s'];
        } else {
          $search_query = get_search_query();
        }
        $keys = implode('|', explode(' ', $search_query)); // 検索ワードを分割して配列にして返す
        $keys = preg_quote($keys, '/'); //正規表現文字をクオート：正規表現構文の特殊文字の前にバックスラッシュを挿入（. \ + * ? [ ^ ] $ ( ) { } = ! < > | : -）：/（非特殊文字）をエスケープに追加（オプション）
        $text = preg_replace('/('. $keys .')/iu', '<mark class="search-highlight">\0</mark>', $text); // 該当ワードを<mark>で囲む
      }
      return $text; // 変更した値を呼び出し元に戻す
    } //endfunction
  } //endclass
} //endif
