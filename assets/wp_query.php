<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
} //endif

if( ! class_exists( 'CCC_Search_Ajax_WP_Query' ) ) {
  class CCC_Search_Ajax_WP_Query {

    public static function get_data() {

      $data = []; //レスポンスデータ

      if( isset($_GET['s']) ) {
        $query_keyword = sanitize_text_field( $_GET['s'] );
      } else if( isset($_POST['s']) ) {
        $query_keyword = sanitize_text_field( $_POST['s'] );
      } else {
        $query_keyword = null;
      }
      if( isset($_GET['search_post_type']) ) {
        $query_posttype = sanitize_text_field( $_GET['search_post_type'] );
      } else if( isset($_POST['search_post_type']) ) {
        $query_posttype = sanitize_text_field( $_POST['search_post_type'] );
      } else {
        $query_posttype = null;
      }

      /*** post_type用 ***/
      if( $query_posttype === 'all' or ! $query_posttype ) {
      /*
      * 使用禁止：「any」だと「get_object_taxonomies()」を利用できないので使用禁止
      * $post_types = 'any'; // リビジョンと 'exclude_from_search' が true にセットされたものを除き、すべてのタイプを含める
      */
        $args = array(
          'public' => true
        );
        $post_types = get_post_types( $args, 'names' );
        unset( $post_types['attachment'] ); // 特定のカスタム投稿タイプを除外（PHP：連想配列から要素を除外）
        $posttype_name = __('All', CCCSEARCHAJAX_TEXT_DOMAIN);
      } else {
        $post_types = $query_posttype;
        $post_type_obj = get_post_type_object( $post_types );
        $posttype_name = $post_type_obj->labels->name;
      } //endif


      /*** tax_query用 ***/
      if( isset($_GET['tax_query_relation']) ) {
        $tax_query_relation = sanitize_text_field( $_GET['tax_query_relation'] );
      } else if(isset($_POST['tax_query_relation'])) {
        $tax_query_relation = sanitize_text_field( $_POST['tax_query_relation'] );
      } else {
        $tax_query_relation = 'AND';
      } //endif
      if( isset($_GET['tax_query_operator']) ) {
        $tax_query_operator = sanitize_text_field( $_GET['tax_query_operator'] );
      } else if(isset($_POST['tax_query_operator'])) {
        $tax_query_operator = sanitize_text_field( $_POST['tax_query_operator'] );
      } else {
        $tax_query_operator = 'AND';
      } //endif
      if( $tax_query_operator == 'AND' ) {
        $include_children = false;
      } else {
        $include_children = true;
      }

      $taxqueries = array( 'relation' => $tax_query_relation );
      /*
      * NG：$taxonomies = get_object_taxonomies( $post_types, 'objects' );
      * 注意：get_object_taxonomies( $post_types_any, 'objects' );の「$post_type_any」の部分は「$post_types」では選択項目と検索結果に矛盾が発生する場合がある。
      * 理由：投稿タイプを絞り込んで検索した場合、その投稿タイプには登録されていないタクソノミーもユーザーが選択して検索する可能性がある。この場合、その投稿タイプに登録されたタクソノミーのみ配列に入れるユーザーが選択したタクソノミーを無視した結果になるため、配列にはすべてのタクソノミーを入れて検索する必要がある。
      */
      $args = array(
        'public' => true
      );
      $post_types_any = get_post_types( $args, 'names' );
      $taxonomies = get_object_taxonomies( $post_types_any, 'objects' ); //選択項目と検索結果の矛盾を避けるため配列にはすべてのタクソノミーを入れて検索する必要がある。
      foreach( $taxonomies as $taxonomy ) {
        if( isset( $_GET['search_'. $taxonomy->name] ) and is_array( $_GET['search_'. $taxonomy->name] ) ) {
          ${'taxvalue_'. $taxonomy->name} = array_map( 'absint', $_GET['search_'. $taxonomy->name] ) ;
        } else if( isset( $_POST['search_'. $taxonomy->name] ) and is_array( $_POST['search_'. $taxonomy->name] ) ) {
          ${'taxvalue_'. $taxonomy->name} = array_map( 'absint', $_POST['search_'. $taxonomy->name] );
        } else {
          ${'taxvalue_'. $taxonomy->name} = null;
        } //endif
        if( is_array( ${'taxvalue_'. $taxonomy->name} ) ) {
          ${'taxquery_'. $taxonomy->name} = array(
            'taxonomy' => $taxonomy->name,
            'terms' => ${'taxvalue_'. $taxonomy->name},
            'field' => 'term_id',
            'include_children' => $include_children,
            'operator' => $tax_query_operator, // IN（いずれかに合致）/ AND（全てに合致）/ NOT IN（いずれにも合致しない）
          );
          array_push( $taxqueries, ${'taxquery_'. $taxonomy->name} );
        } //endif
      } //endforeach


      /*** meta_query用 ***/
      /* 現在未使用：必要に応じて指定してください */
      $metaqueries = null;


      /*** 表示数の定義（指定が無ければ管理画面の表示設定（表示する最大投稿数）の値を取得） ***/
      if( isset( $_POST['ccc-posts_per_page'] ) ) {
        $posts_per_page = absint( $_POST['ccc-posts_per_page'] ); //負ではない整数に変換
      } else {
        $posts_per_page = get_option('posts_per_page');
      }

      /*** すでに表示されている記事リストの個数 ***/
      if( isset($_POST['looplength']) ) {
        $looplength = absint( $_POST['looplength'] );
      } else {
        $looplength = null;
      }

      $args= array(
        'post_type' => $post_types,
        'post_status' => 'publish', //公開済みのページのみ取得
        'posts_per_page' => $posts_per_page, //表示数を指定（初期値：指定しない場合は管理画面の表示設定の値）
        'offset' => $looplength, //指定した分だけ検索位置をずらす（ajaxから現在表示中の投稿数を取得）
        's' => $query_keyword,
        'tax_query' => $taxqueries,
        'meta_query' => $metaqueries,
        'orderby' => array( 'type' => 'ASC', 'menu_order' => 'ASC' ),
      );


      /***** For WordPress Plugin "bogo" : START *****/
      if( isset($_POST['bogo']) ) {
        $locale = sanitize_text_field( $_POST['bogo'] );
        $args['lang'] = $locale;
      }
      /***** For WordPress Plugin "bogo" : END *****/


      $the_query = new WP_Query($args);


      /*** レスポンスデータ  ***/
      $data['post_types'] = $post_types;
      $data['posttype_name'] = $posttype_name;
      $data['looplength'] = $looplength;
      $data['taxqueries'] = $taxqueries;
      $data['metaqueries'] = $metaqueries;
      $data['the_query'] = $the_query;
      return $data;

    } //endfunction


  } //endclass
} //endif
