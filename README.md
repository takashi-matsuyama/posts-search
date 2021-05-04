# Posts Search

This is the development repository for Posts Search, a WordPress plugin that searches posts by taxonomy terms and post type with Ajax. You can also download the plugin package installation from the [WordPress.org Plugin Directory](https://wordpress.org/plugins/posts-search/).

Contributors: takashimatsuyama
Donate link:
Tags: posts search, search, taxonomy, term
Requires at least: 4.8
Tested up to: 5.7
Requires PHP: 5.4.0
Stable tag: 1.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Search posts by taxonomy terms and post type with Ajax.

## Description

Search posts by taxonomy terms and post type with Ajax and list them.

This plugin is simple. You can search posts by taxonomy terms and post type with Ajax with just a shortcode.

##  Usage

* **Shortcode:** `[ccc_posts_search_searchform placeholder="" style=""]`

For example, in `header.php` add `<?php if( shortcode_exists( 'ccc_posts_search_searchform' ) ) { echo do_shortcode('[ccc_posts_search_searchform placeholder=""]'); } ? >`.

* **Shortcode:** `[ccc_posts_search_results posts_per_page="" search_keyword="" style=""]`

For example, in `search.php` add `<?php if( shortcode_exists( 'ccc_posts_search_results' ) ) { echo do_shortcode('[ccc_posts_search_results posts_per_page=" 15" search_keyword="false"]'); } ? >`.

Detailed usage is under preparation.

## Installation

1. Upload `posts-search` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Use shortcodes to display the posts search.

## Changelog

### 1.1.1
[Bug fix] About is_plugin_active not working when locale="bogo".

### 1.1.0
Add shortcode attribute (`title_select="" highlight="" locale=""`) markup of thumbnails and modify CSS.

### 1.0.0
Initial release.