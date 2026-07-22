=== University Multilang ===
Contributors: faruq
Tags: multilingual, translation, elementor, router, language-switcher
Requires at least: 6.8
Tested up to: 6.8
Stable tag: 1.0.0
Requires PHP: 8.2
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A modern, high-performance multilingual plugin for WordPress built with Domain-Driven Design (DDD) principles.

== Description ==

University Multilang is a lightweight, scalable, and developer-friendly multilingual plugin designed for WordPress. It offers seamless integration with Elementor, Gutenberg, WooCommerce, custom post types, and taxonomies.

Key Features:
* Full Elementor Visual Builder Support: Translates Elementor page layouts, sections, containers, and templates cleanly.
* Language Routing Engine: Clean directory-based URL structure (`/en/`, `/id/`) with canonical redirect guards.
* Translation Management UI: Native post table status columns, metaboxes, and AJAX linking.
* Automatic Translation Providers: Extensible provider interface supporting DeepL API and Google Translate.
* High Performance: Zero database bloat, optimized query filters, and clean PSR-12 Architecture.

== Installation ==

1. Upload the `university-multilang` directory to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to 'University Multilang' -> 'Languages' to register your site languages.
4. Set your default language under 'University Multilang' -> 'Settings'.

== Frequently Asked Questions ==

= Does it support Elementor? =
Yes! University Multilang fully supports Elementor Free and Elementor Pro Theme Builder locations.

= Is it compatible with WooCommerce? =
Yes, WooCommerce products and taxonomies are fully supported out of the box.

== Changelog ==

= 1.0.0 =
* Initial official release.
* Added Language Switcher Widget & Shortcode.
* Added Translation Management UI with AJAX linking.
* Added Language Routing Engine & Bypass Guards.
* Added Settings Page & Configuration Abstraction.
* Added Automatic Translation Provider Interface (DeepL & Google Translate).
* Added Elementor Integration & _elementor_data cloner.
