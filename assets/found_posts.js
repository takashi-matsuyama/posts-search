/*
* Author: Takashi Matsuyama of Caronima Inc.
* Author URI: https://www.caronima.com/
* Description: AjaxでWordPressの検索結果の投稿数を表示
* Version: 1.0.0
*/

/* グローバルネームスペース */
/* MYAPP = CCC */
var CCC = CCC || {};

(function($){
  /* サブネームスペース */
  CCC.search_ajax = {

    initial : function() {

      var data_set = {};

      /*** Ajaxフック用 ***/
      data_set['action'] = CCC_SEARCH_AJAX_FOUND_POSTS.action;
      data_set['nonce'] = CCC_SEARCH_AJAX_FOUND_POSTS.nonce;


      /*** 検索キーワードを送信データに追加 ***/
      data_set['s'] = $("#ccc-search_ajax-search-keyword[name='s']").val();
      /*** 投稿タイプを送信データに追加 ***/
      data_set['search_post_type'] = $("#ccc-search_ajax-select-post_type[name='search_post_type']").val();
      /*** タクソノミーを送信データに追加 ***/
      /** 複数のタクソノミーで選択された複数の値を配列に追加してタクソノミーの数だけループして送信データに追加 **/
      /* ポイント：可変変数を駆使する「eval()」 */
      $('#ccc-search_ajax-form').find('.search_ajax_taxonomy').each( function() {
        var data_taxonomy = $(this).data('search_ajax_taxonomy');
        if( data_taxonomy ) {
          eval( 'var array_val_' + data_taxonomy + '= []' );
          $("[name='search_"+ data_taxonomy +"[]']:checked").each( function() {
            eval( 'array_val_'+ data_taxonomy ).push(this.value);
          });
          //console.log(eval( 'array_val_'+ data_taxonomy ));
          data_set['search_'+ data_taxonomy] = eval( 'array_val_'+ data_taxonomy );
        }
      });

      /*** 複数の戻り値を返すために配列に値を追加して変数に格納 ***/
      return data_set;

    }, // メンバのメソッドを定義

    found_posts : function(data_set) {
      $.ajax({
        type: 'POST',
        url: CCC_SEARCH_AJAX_FOUND_POSTS.api,
        data: data_set
      }).fail( function(){
        alert('error');
      }).done( function(response) {
        var found_posts = $('#ccc-search_ajax-found_posts');
        var submit = $('#ccc-search_ajax-submit');
        found_posts.html(response);
        if( found_posts.find('.number').text() > 0 ) {
          submit.addClass('found');
        } else {
          submit.removeClass('found');
        }
      });
    } // メンバのメソッドを定義

  }; // サブネームスペース


  //console.log( CCC.search_ajax.initial() );
  $('.ccc-search_ajax-trigger').on( 'change', function() {
    CCC.search_ajax.found_posts( CCC.search_ajax.initial() );
    //console.log( CCC.search_ajax.initial() );
  });

})(jQuery);