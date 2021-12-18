/*
 * Author: Takashi Matsuyama
 * Author URI: https://profiles.wordpress.org/takashimatsuyama/
 * Description: AjaxでWordPressの検索結果の投稿数を表示
 * Version: 1.2.1 or later
 */

/* グローバルネームスペース */
/* MYAPP = CCC */
var CCC = CCC || {};

(function ($) {
  /* サブネームスペース */
  CCC.search_ajax = {
    initial: function () {
      var data_set = {};

      /*** Ajaxフック用 ***/
      data_set["action"] = CCC_SEARCH_AJAX_FOUND_POSTS.action;
      data_set["nonce"] = CCC_SEARCH_AJAX_FOUND_POSTS.nonce;

      /*** 検索キーワードを送信データに追加 ***/
      data_set["s"] = $("#ccc-search_ajax-search-keyword[name='s']").val();
      /*** 投稿タイプを送信データに追加 ***/
      data_set["search_post_type"] = $(
        "#ccc-search_ajax-select-post_type[name='search_post_type']"
      ).val();
      /*** タクソノミーを送信データに追加 ***/
      /** 複数のタクソノミーで選択された複数の値を配列に追加してタクソノミーの数だけループして送信データに追加 **/
      /* ポイント：可変変数を駆使する「eval()」 */
      $("#ccc-search_ajax-form")
        .find(".search_ajax_taxonomy")
        .each(function () {
          var data_taxonomy = $(this).data("search_ajax_taxonomy");
          if (data_taxonomy) {
            data_set["search_" + data_taxonomy] = [];
            $("[name='search_" + data_taxonomy + "[]']:checked").each(
              function () {
                data_set["search_" + data_taxonomy].push(this.value);
              }
            );
            console.log(data_set["search_" + data_taxonomy]);
          }
        });

      /***** For WP_Query tax_query (relation and operator) : START *****/
      /* tax_query の relation と operator を設定するオプションを送信データに追加 */
      var tax_query_relation = $("#ccc-search_ajax-form").data(
        "ccc_posts_search-tax_query_relation"
      );
      if (tax_query_relation) {
        data_set["tax_query_relation"] = tax_query_relation; // 'AND' 'OR', default: 'AND'
      }
      var tax_query_operator = $("#ccc-search_ajax-form").data(
        "ccc_posts_search-tax_query_operator"
      );
      if (tax_query_operator) {
        data_set["tax_query_operator"] = tax_query_operator; // 'AND' 'IN' 'NOT IN', default: 'AND'
      }
      /***** For WP_Query tax_query (relation and operator) : END *****/

      /***** For Search Highlight : START *****/
      /* 検索キーワードをハイライトにするオプションの有無を送信データに追加 */
      var search_highlight = $("#ccc-search_ajax-form").data(
        "ccc_posts_search-highlight"
      );
      if (search_highlight) {
        data_set["search_highlight_ajax"] = search_highlight; // 検索キーワードをハイライトにするアクションフックで使用（不要な場合はショートコードの属性をfalse）
      }
      /***** For Search Highlight : END *****/

      /***** For WordPress Plugin "bogo" : START *****/
      /* WordPressの現在のロケール情報を送信データに追加 */
      var bogo_locale = $("#ccc-search_ajax-form").data(
        "ccc_posts_search-bogo"
      );
      if (bogo_locale) {
        data_set["bogo"] = bogo_locale;
      }
      /***** For WordPress Plugin "bogo" : END *****/

      /*** 複数の戻り値を返すために配列に値を追加して変数に格納 ***/
      return data_set;
    }, // メンバのメソッドを定義

    found_posts: function (data_set) {
      $.ajax({
        type: "POST",
        url: CCC_SEARCH_AJAX_FOUND_POSTS.api,
        data: data_set,
      })
        .fail(function () {
          alert("error");
        })
        .done(function (response) {
          var found_posts = $("#ccc-search_ajax-found_posts");
          var submit = $("#ccc-search_ajax-submit");
          found_posts.html(response);
          if (found_posts.find(".number").text() > 0) {
            submit.addClass("found");
          } else {
            submit.removeClass("found");
          }
          //console.log(data_set);
        });
    }, // メンバのメソッドを定義
  }; // サブネームスペース

  //console.log( CCC.search_ajax.initial() );
  $('.ccc-search_ajax-trigger:not([type="radio"])').on("change", function () {
    //console.log('you changed the input.');
    CCC.search_ajax.found_posts(CCC.search_ajax.initial());
    //console.log( CCC.search_ajax.initial() );
  });

  /*** For [type="radio"] : ラジオボタン：複数グループのラジオボタンを2回クリックで選択解除 ***/
  var radio_inputs = $('.ccc-search_ajax-trigger[type="radio"]');
  if (radio_inputs.length > 0) {
    //console.log('There is radio type input.');
    var checked_array = [];
    /* アクセス時に既にチェックがあれば事前に巡回してIDを保存 */
    var radio_inputs_checked = radio_inputs.filter(":checked");
    if (radio_inputs_checked.length > 0) {
      radio_inputs_checked.each(function () {
        var radio_checked_name = $(this).attr("name");
        checked_array[radio_checked_name] = $(this).attr("id");
      });
    } //endif
    radio_inputs.on("click", function () {
      var radio_id = $(this).attr("id");
      //console.log(radio_id);
      var radio_name = $(this).attr("name");
      //console.log(radio_name);
      /* クリックしたinputのIDと保存されてるIDが同じだったら */
      if (radio_id == checked_array[radio_name]) {
        /* チェックを外す */
        $('input[name="' + radio_name + '"]').prop("checked", false);
        /* リセット：保存されているIDを削除 */
        checked_array[radio_name] = false;
      } else {
        /* チェックしたinputのIDを保存 */
        checked_array[radio_name] = radio_id;
        //console.log(checked_array[radio_name]);
      }
      //console.log('you clicked the radio.');
      CCC.search_ajax.found_posts(CCC.search_ajax.initial());
      //console.log( CCC.search_ajax.initial() );
    });
  } else {
    //console.log('There is no radio type input.');
  } //endif
})(jQuery);
