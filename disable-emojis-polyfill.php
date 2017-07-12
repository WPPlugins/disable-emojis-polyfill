<?php
/**
 * Disable Emojis Polyfill
 *
 * @package DisableEmojisPolyfill
 * @author Sérgio 'wherd' Leal <hello@wherd.name>
 * @license GPL-2.0
 * @since 1.0.0
 *
 * @wordpress-plugin
 * Plugin Name: Disable Emojis Polyfill
 * Plugin URI:  https://wordpress.org/plugins/disable-emojis-polyfill/
 * Description: Disable WordPress Emojis backwards compatibility.
 * Version:     1.0.1
 * Author:      Sérgio 'wherd' Leal
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: disable-emojis-polyfill
 * Domain Path: /languages
 * Tags: disable, emojis, speed, performance
 *
 * Disable Emojis Polyfill is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Disable Emojis Polyfill is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Remove Version Arg. If not, see license.txt
 */

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if ( ! function_exists( 'wd_disable_emojis' ) ) :
	/**
	 * Disable WordPress emojis support.
	 *
	 * @since 1.0.0
	 * @since 1.0.1 - load textdoamin
	 *
	 * @see https://core.trac.wordpress.org/browser/tags/4.7/src/wp-includes/default-filters.php
	 */
	function wd_disable_emojis() {
		// Load plugin textdomain.
		load_plugin_textdomain( 'disable-emojis-polyfill', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

		// Disable from printing the inline Emoji detection style and script.
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'embed_head', 'print_emoji_detection_script' );

		// Disable from printing the admin inline Emoji detection style and script.
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );

		// Prevent feed, rss and emails from converting emoji to a static img element.
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

		// Prevent from printing emoji resource hints to browsers for pre-fetching, pre-rendering and pre-connecting to web sites.
		add_filter( 'wp_resource_hints', 'wd_disable_emojis_resource_hints', 10, 2 );

		// Disables WordPress from loading the tinyMce emoji plugin.
		add_filter( 'tiny_mce_plugins', 'wd_disable_emojis_tinymce' );
	}

	add_action( 'init', 'wd_disable_emojis' );
endif;

if ( ! function_exists( 'wd_disable_emojis_tinymce' ) ) :
	/**
	 * The filter specifies which of the default plugins included in WordPress should be added to the TinyMCE instance.
	 *
	 * @since 1.0.0
	 * @since 1.0.1 - fix variable name typo
	 *
	 * @see https://developer.wordpress.org/reference/hooks/tiny_mce_plugins/
	 *
	 * @param  array $plugins An array of default TinyMCE plugins.
	 * @return array TinyMce without the emoji plugin.
	 */
	function wd_disable_emojis_tinymce( $plugins ) {
		return is_array( $plugins ) ? array_diff( $plugins, array( 'wpemoji' ) ) : $plugins;
	}
endif;

if ( ! function_exists( 'wd_disable_emojis_resource_hints' ) ) :
	/**
	 * Filters domains and URLs for resource hints of relation type.
	 *
	 * @since 1.0.0
	 *
	 * @see https://developer.wordpress.org/reference/hooks/wp_resource_hints/
	 *
	 * @param  array  $urls URLs to print for resource hints.
	 * @param  string $relation_type The relation type the URLs are printed for, e.g. 'preconnect' or 'prerender'.
	 * @return array List of URLs without the emoji one.
	 */
	function wd_disable_emojis_resource_hints( $urls, $relation_type ) {
		if ( 'dns-prefetch' === $relation_type ) {
			/**
			 * Get emoji link.
			 *
			 * @see `https://core.trac.wordpress.org/browser/tags/4.7/src/wp-includes/formatting.php#L5026`
			 */
			$url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2.2.1/svg/' );

			return array_diff( $urls, array( $url ) );
		}

		return $urls;
	}
endif;
