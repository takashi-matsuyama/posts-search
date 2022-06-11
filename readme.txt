=== Posts Search ===
Contributors: takashimatsuyama
Donate link:
Tags: posts search, search, taxonomy, term
Requires at least: 4.8
Tested up to: 6.0
Requires PHP: 5.4.0
Stable tag: 1.2.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Search posts by taxonomy terms and post type with Ajax.

== Description ==

Search posts by taxonomy terms and post type with Ajax and list them.

This plugin is simple. You can search posts by taxonomy terms and post type with Ajax with just a shortcode.

== Usage ==

* **Shortcode:** `[ccc_posts_search_searchform placeholder="" style=""]`

For example, in `header.php` add `<?php if( shortcode_exists( 'ccc_posts_search_searchform' ) ) { echo do_shortcode('[ccc_posts_search_searchform placeholder=""]'); } ? >`.

* **Shortcode:** `[ccc_posts_search_results posts_per_page="" search_keyword="" style=""]`

For example, in `search.php` add `<?php if( shortcode_exists( 'ccc_posts_search_results' ) ) { echo do_shortcode('[ccc_posts_search_results posts_per_page=" 15" search_keyword="false"]'); } ? >`.

Detailed usage is under preparation.

== Installation ==

1. Upload `posts-search` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Use shortcodes to display the posts search.

== Discover More ==

This plugin is [developed on GitHub](https://github.com/takashi-matsuyama/posts-search)

== Changelog ==

= 1.2.2 =
Tested on WordPress 6.0.

See the [release notes on GitHub](https://github.com/takashi-matsuyama/posts-search/releases).
