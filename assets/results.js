/*
* Author: Takashi Matsuyama
* Author URI: https://profiles.wordpress.org/takashimatsuyama/
* Description: AjaxでWordPressの検索結果を表示しページャー無しでクリックでさらに読み込み（found_posts.jsが必要）
* Version: 1.1.0 or later
*/

/*
* found_posts.js：別途、読み込みが必要（サブネームスペースで共通の変数と共通の関数を定義しているため）
*/

/* グローバルネームスペース */
/* MYAPP = CCC */
var CCC = CCC || {};

(function($){

  var results_body = $('#ccc-search_ajax-results');
  var posts_per_page_value = results_body.data('ccc_search_ajax-posts_per_page');
  var found_posts_count = $('.ccc-search_ajax-found_posts-count');
  var loader = $('#ccc-search_ajax-loader');
  var post_count = $('.ccc-search_ajax-post-count');
  var more_trigger = 'ccc-search_ajax-results-more-trigger'; // 注意：クリックイベントで使用する時には動的要素に変わっているためオブジェクト変数に格納する事は出来ない
  var loop = 'ccc-search_ajax-results-one'; // 注意：動的要素のためオブジェクト変数に格納する事は出来ない


  /*** header_resultsを複製する関数 ***/
  var header_results = $('.ccc-search_ajax-header');
  var clone_header_results = $('#ccc-search_ajax-header-clone');
  function clone_header() {
    clone_header_results.children().remove(); // リセット：cloneが重複するため毎回削除
    if( $('.'+ loop).length > 9 ) {
      header_results.clone(true).appendTo(clone_header_results); // clone：引数にtrueをセットすることでイベントもコピー可能
    }
  }


  /*** 実行：検索結果を表示する関数 ***/
  function search_ajax_results( data_set, looplength_val ) {

    /*** Ajaxフック用 ***/
    data_set['action'] = CCC_SEARCH_AJAX_RESULTS.action;
    data_set['nonce'] = CCC_SEARCH_AJAX_RESULTS.nonce;

    /* 現在表示中の投稿数を送信データに追加 */
    data_set['looplength'] = looplength_val;
    /* 一回のリクエストで取得する投稿数を送信データに追加 */
    data_set['ccc-posts_per_page'] = posts_per_page_value;


    /* 読み込み中のローディングを表示 */
    loader.show();
    $.ajax({
      type: 'POST',
      url: CCC_SEARCH_AJAX_RESULTS.api,
      data: data_set
    }).fail( function(){
      loader.fadeOut(); // 検索条件をリクエストして結果を表示し完了後にローディングを削除
      alert('error');
    }).done( function(response){
      loader.fadeOut(); // 検索条件をリクエストして結果を表示し完了後にローディングを削除
      results_body.append(response);
      var post_count_val = $('.'+ loop).length;
      post_count.text(post_count_val);
      var found_posts_count_val = found_posts_count.text();
      if( Number( post_count_val ) < Number( found_posts_count_val ) ) {
        $('#'+ more_trigger).fadeIn();
      } else {
        $('#'+ more_trigger).hide();
      }
      clone_header() // header_resultsを複製する関数を呼び出し
    });
  }


  /* Ajaxで検索結果の投稿数を表示を実行（found_posts.jsを使用） */
  CCC.search_ajax.found_posts( CCC.search_ajax.initial() );

  /* 初回ロード：検索結果を表示する関数を呼び出し */
  var looplength_val = $('.'+ loop).length; // 現在表示中の投稿数を取得（注意：動的要素のためオブジェクト変数に格納する事は出来ない）
  //console.log(looplength_val);
  search_ajax_results( CCC.search_ajax.initial(), looplength_val );


  /* クリック（さらに読み込むトリガー）：検索結果を表示する関数を呼び出し */
  $(document).on('click', '#'+ more_trigger, function(e) {
    e.preventDefault();
    var looplength_val = $('.'+ loop).length; // 再取得：現在表示中の投稿数を再取得（注意：動的要素のためオブジェクト変数に格納する事は出来ない）
    //console.log(looplength_val);
    /* 検索結果を表示する関数を呼び出し */
    search_ajax_results( CCC.search_ajax.initial(), looplength_val );
  });

})(jQuery);
