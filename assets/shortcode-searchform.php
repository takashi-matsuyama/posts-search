<?php
if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
} //endif

if( ! class_exists( 'CCC_Search_Ajax_ShortCode_SearchForm' ) ) {

  add_shortcode('ccc_posts_search_searchform', array('CCC_Search_Ajax_ShortCode_SearchForm', 'html') );

  class CCC_Search_Ajax_ShortCode_SearchForm {

    public static function html($atts) {
      ob_start(); // returnでHTMLを返す：出力のバッファリングを有効にする

      $atts = shortcode_atts(array(
        "placeholder" => '',
        "style" => '',
        "title_select" => '',
        "input_type" => '',
        "tax_query_relation" => '',
        "tax_query_operator" => '',
        "highlight" => '',
        "locale" => '',
      ),$atts);
      if( $atts['placeholder'] ) {
        $placeholder = $atts['placeholder'];
      } else {
        $placeholder = __('Enter a keyword', CCCSEARCHAJAX_TEXT_DOMAIN);
      }
      if( $atts['style'] or $atts['style'] === 0 or $atts['style'] === '0' ) {
        $style = $atts['style'];
      } else {
        $style = 1;
      }
      if( $atts['title_select'] ) {
        $title_select = $atts['title_select'];
      } else {
        $title_select = __('Search Scope', CCCSEARCHAJAX_TEXT_DOMAIN);
      }
      if( $atts['input_type'] == 'radio' ) {
        $input_type = 'radio';
      } else {
        $input_type = 'checkbox';
      }
      if( $atts['tax_query_relation'] == 'OR' ) {
        $tax_query_relation = 'OR';
      } else {
        $tax_query_relation = 'AND';
      }
      if( $atts['tax_query_operator'] == 'IN' ) {
        $tax_query_operator = 'IN';
      } else if( $atts['tax_query_operator'] == 'NOT IN' ) {
        $tax_query_operator = 'NOT IN';
      } else {
        $tax_query_operator = 'AND';
      }

      /***** For Search Highlight : START *****/
      if( $atts['highlight'] == 'true' ) {
        $highlight = 'data-ccc_posts_search-highlight="true"';
      } else {
        $highlight = null;
      }
      /***** For Search Highlight : END *****/

      /***** For WordPress Plugin "bogo" : START *****/
      /* Detect plugin. For use on Front End and Back End. */
      if( $atts['locale'] === 'bogo' and in_array( 'bogo/bogo.php', (array) get_option( 'active_plugins', array() ) ) ) {
        $locale = 'data-ccc_posts_search-bogo="'.get_locale().'"';
      } else {
        $locale = null;
      };
      /***** For WordPress Plugin "bogo" : END *****/

      /* セレクトボックスで選択した値の保持 */
      if( isset( $_GET['search_post_type'] ) ) { $select = sanitize_text_field( $_GET['search_post_type'] ); } else { $select = null; }
?>


<form role="search" method="get" class="search-form" id="ccc-search_ajax-form" action="<?php echo esc_url( home_url('/') ); ?>" data-ccc_posts_search-searchform-style="<?php echo $style; ?>" data-ccc_posts_search-tax_query_relation="<?php echo $tax_query_relation; ?>" data-ccc_posts_search-tax_query_operator="<?php echo $tax_query_operator; ?>" <?php echo $highlight; ?> <?php echo $locale; ?> >
  <input type="search" name="s" id="ccc-search_ajax-search-keyword" placeholder="<?php echo $placeholder; ?>" value="<?php if(is_search()){ echo get_search_query(); } ?>" class="ccc-search_ajax-trigger">
  <div class="search-refine">
    <p class="title-refine"><?php echo $title_select; ?></p>
    <select name="search_post_type" id="ccc-search_ajax-select-post_type" class="ccc-search_ajax-trigger">
      <option value="all" <?php if($select === 'all' or $select == null ) { echo 'selected'; } ?> ><?php _e('All', CCCSEARCHAJAX_TEXT_DOMAIN); ?></option>
      <?php
      $args = array(
        'public' => true
      );
      $post_types = get_post_types( $args, 'names' );
      unset( $post_types['attachment'] ); // 特定のカスタム投稿タイプを除外（PHP：連想配列から要素を除外）
      //print_r($post_types);
      foreach( $post_types as $post_type ) {
        if( $select === $post_type ) { $selected = 'selected'; } else { $selected = null; }
        $post_type_obj = get_post_type_object( $post_type );
        $posttype_name = $post_type_obj->labels->name;
        echo '<option value="'.$post_type.'" '.$selected.'>'.$posttype_name.'</option>';
      } //endforeach
      ?>
    </select><!-- /#select-post_type -->
  </div><!-- /.search-refine -->
  <div class="search-refine">
    <?php self::all_taxonomies_terms_all( $post_types, $input_type ); ?>
  </div><!-- /.search-refine -->
  <button type="submit" class="button" id="ccc-search_ajax-submit"><i class="icon-ccc_search_ajax-search"></i><span class="text"><?php _e('Search', CCCSEARCHAJAX_TEXT_DOMAIN); ?></span></button><!-- /#ccc-search_ajax-submit -->
  <div id="ccc-search_ajax-found_posts"></div><!-- /#ccc-search_ajax-found_posts -->
  <input type="hidden" name="tax_query_relation" value="<?php echo $tax_query_relation; ?>">
  <input type="hidden" name="tax_query_operator" value="<?php echo $tax_query_operator; ?>">
</form>
<?php
      return ob_get_clean();  // returnでHTMLを返す：関数からHTMLを返し、それをいろいろ編集したり、処理を加えてから出力する場面で有効：バッファリングの内容を出力した後にバッファリングを削除
    } //endfunction

    /*** すべてのカスタム分類のタームを取得する関数（START） ***/
    public static function all_taxonomies_terms_all( $post_type, $input_type=false ) {
      $taxonomies = get_object_taxonomies( $post_type, 'objects' );
      unset( $taxonomies['post_format'] ); // 特定のタクソノミーを除外（PHP：連想配列から要素を除外）
      if( $taxonomies and is_array( $taxonomies ) ) {
        foreach( $taxonomies as $taxonomy ) {
          $args = array(
            'parent' => 0,
          );
          $parent_terms = get_terms( $taxonomy->name, $args );
          if( $parent_terms ) {
            echo '<div class="search_ajax_taxonomy" data-search_ajax_taxonomy="'. $taxonomy->name .'">';
            echo '<div class="select-taxonomy-title ccc-search_ajax-accordion-trigger"><p class="taxonomy-title-text">'.$taxonomy->label.'</p><div class="accordion-icon"><span class="accordion-icon-bar"></span><span class="accordion-icon-bar"></span></div>'; //<!-- /.accordion-icon -->
            echo '</div>'; //<!-- /.select-taxonomy-title -->
            echo '<div class="ccc-search_ajax-accordion-contents">';
            echo '<ul class="select-terms select-terms-parent">';
            foreach( $parent_terms as $parent_term ) {
              /* チェックボックスで選択した値の保持 */
              if( isset( $_GET['search_'. $taxonomy->name] ) ) {
                $checkboxes = array_map( 'absint', $_GET['search_'. $taxonomy->name] );
                foreach($checkboxes as $val) {
                  if($val == $parent_term->term_id) {
                    $checked[$parent_term->term_id] = 'checked="checked"';
                  } //endif
                } //endforeach
              } //endif
              $checked_val = isset($checked[$parent_term->term_id]) ? $checked[$parent_term->term_id] : null;
              echo '<li class="item-term-parent">';
              echo '<label class="label-term">';
              echo '<input type="'.$input_type.'" name="search_'. $taxonomy->name .'[]" value="'. $parent_term->term_id .'" '. $checked_val .' class="ccc-search_ajax-trigger" id="'. $taxonomy->name .'-'. $parent_term->term_id .'">';
              echo '<span class="text">'. $parent_term->name .'</span>';
              echo '</label><!-- /.label-term -->';

              $args = array(
                'parent' => $parent_term->term_id,
              );
              $child_terms = get_terms( $taxonomy->name, $args );
              if ( $child_terms ) {
                echo '<ul class="select-terms select-terms-children">';
                foreach ( $child_terms as $child_term ) {
                  /* チェックボックスで選択した値の保持 */
                  if( isset( $_GET['search_'. $taxonomy->name] ) ) {
                    $checkboxes = array_map( 'absint', $_GET['search_'. $taxonomy->name] );
                    foreach ($checkboxes as $val) {
                      if($val == $child_term->term_id) {
                        $checked[$child_term->term_id] = 'checked="checked"';
                      } //endif
                    } //endforeach
                  } //endif
                  $checked_val = isset($checked[$child_term->term_id]) ? $checked[$child_term->term_id] : null;
                  echo '<li class="item-term-children">';
                  echo '<label class="label-term">';
                  echo '<input type="'.$input_type.'" name="search_'. $taxonomy->name .'[]" value="'. $child_term->term_id .'" '. $checked_val .' class="ccc-search_ajax-trigger" id="'. $taxonomy->name .'-'. $child_term->term_id .'">';
                  echo '<span class="text">'. $child_term->name .'</span>';
                  echo '</label>'; //<!-- /.label-term -->
                  echo '</li>'; //<!-- /.item-term-children -->
                } //endforeach
                echo '</ul>'; //<!-- /.select-terms-children -->
              } //endif
              echo '</li>'; //<!-- /.item-term-parent -->
            } //endforeach
            echo '</ul>'; //<!-- /.select-terms-parent -->
            echo '</div>'; //<!-- /.ccc-search_ajax-accordion-contents -->
            echo '</div>'; //<!-- /.search_ajax_taxonomy -->
          } //endif
        } //endforeach
      } //endif
    } //endfunction
    /*** すべてのカスタム分類のタームを取得する関数（END） ***/


  } //endclass
} //endif