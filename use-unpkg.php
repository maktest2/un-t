<?php

/**
 * The Use unpkg Plugin
 *
 * Load common JavaScript and CSS libraries from unpkg instead of local copies.
 *
 * @package    Use_unpkg
 * @subpackage Main
 */

/**
 * Plugin Name: Use unpkg
 * Plugin URI:  http://blog.milandinic.com/wordpress/plugins/use-unpkg/
 * Description: Load common JavaScript and CSS libraries from unpkg instead of local copies.
 * Author:      Milan Dinić
 * Author URI:  http://blog.milandinic.com/
 * Version:     0.1-alpha-2
 * Text Domain: use-unpkg
 * Domain Path: /languages/
 * License:     GPL
 */

// Exit if accessed directly
defined( 'ABSPATH' ) or exit;

/*
 * Initialize a plugin.
 *
 * Load class when all plugins are loaded
 * so that other plugins can overwrite it.
 */
add_action( 'plugins_loaded', array( 'Use_unpkg', 'get_instance' ), 10 );

if ( ! class_exists( 'Use_unpkg' ) ) :
/**
 * Use unpkg main class.
 *
 * Use common JavaScript libraries from unpkg instead of local copies.
 */
class Use_unpkg {
	/**
	 * Scripts and their data available for replacement.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var array
	 */
	public $unpkg_scripts = array();

	/**
	 * Styles and their data available for replacement.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @var array
	 */
	public $unpkg_styles = array();

	/**
	 * Set class properties and add main methods to appropriate hooks.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct() {
		// Disables concatenation
		add_action( 'init',                          array( $this, 'disable_concatenation'    )         );

		// Put jQuery in noConflict mode
		add_action( 'wp_loaded',                     array( $this, 'jquery_noconflict'        )         );

		// Replace WordPress paths with unpkg URLs
		add_action( 'wp_default_scripts',            array( $this, 'replace'                  ), 888    );
		add_action( 'wp_default_styles',             array( $this, 'replace'                  ), 888    );

		// Apply filters that are hooked later just before they are printed to page
		add_action( 'wp_enqueue_scripts',            array( $this, 'apply_filters'            ), 888    );
		add_action( 'wp_print_footer_scripts',       array( $this, 'apply_filters'            ),  9     );

		// Remove version query string
		add_filter( 'script_loader_src',             array( $this, 'remove_version'           )         );
		add_filter( 'style_loader_src',              array( $this, 'remove_version'           )         );

		// Define scripts and styles available for replacement with one on unpkg
		$this->define_scripts();
		$this->define_styles();
	}

	/**
	 * Initialize Use_unpkg object.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Use_unpkg $instance Instance of Use_unpkg class.
	 */
	public static function get_instance() {
		static $instance = false;

		if ( false === $instance ) {
			$instance = new self;
		}

		return $instance;
	}

	/**
	 * Define scripts and their data available for replacement.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function define_scripts() {
		/*
		 * What is needed is handle with which they are
		 * registered in WordPress, package name,
		 * file name, and is script minified and with
		 * what suffix on unpkg.
		 *
		 * Sometimes all four of values are same as in
		 * WordPress, but some use different names.
		 */
		$scripts = array(
			'jquery-core'              => array(
				'package'  => 'jquery',
				'file'     => 'dist/jquery',
				'minified' => '.min',
			),
			'jquery-migrate'           => array(
				'package'  => 'jquery-migrate',
				'file'     => 'dist/jquery-migrate',
				'minified' => '.min',
			),
			'underscore'               => array(
				'package'  => 'underscore',
				'file'     => 'underscore',
				'minified' => '-min',
			),
			'backbone'                 => array(
				'package'  => 'backbone',
				'file'     => 'backbone',
				'minified' => '-min',
			),
			'mediaelement'              => array(
				'package'  => 'mediaelement',
				'file'     => 'build/mediaelement-and-player',
				'minified' => '.min',
			),
			'twentysixteen-html5'      => array(
				'package'  => 'html5shiv',
				'file'     => 'dist/html5shiv',
				'minified' => '.min',
			),
			'html5'                    => array(
				'package'  => 'html5shiv',
				'file'     => 'dist/html5shiv',
				'minified' => '.min',
			),
			'jquery-scrollto'          => array(
				'package'  => 'jquery.scrollto',
				'file'     => 'jquery.scrollTo',
				'minified' => '.min',
			),
		);

		/**
		 * Filter scripts and their data available for replacement.
		 *
		 * @since 1.0.0
		 *
		 * @param array $scripts Scripts and their data available for replacement.
		 */
		$this->unpkg_scripts = (array) apply_filters( 'use_unpkg_scripts', $scripts );
	}

	/**
	 * Define styles and their data available for replacement.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function define_styles() {
		/*
		 * What is needed is handle with which they are
		 * registered in WordPress, package name,
		 * file name, and is script minified and with
		 * what suffix on unpkg.
		 *
		 * Sometimes all four of values are same as in
		 * WordPress, but some use different names.
		 */
		$styles = array(
			'mediaelement'              => array(
				'package'  => 'mediaelement',
				'file'     => 'build/mediaelementplayer',
				'minified' => '.min',
			),
		);

		/**
		 * Filter styles and their data available for replacement.
		 *
		 * @since 1.0.0
		 *
		 * @param array $styles Styles and their data available for replacement.
		 */
		$this->unpkg_styles = (array) apply_filters( 'use_unpkg_styles', $styles );
	}

	/**
	 * Disables script and styles concatenation.
	 *
	 * It can only be done by using global
	 * variable that holds value, not via filter.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function disable_concatenation() {
		$GLOBALS['concatenate_scripts'] = false;
	}

	/**
	 * Print JavaScript to put jQuery in noConflict mode.
	 *
	 * Copy of jQuery in WordPress uses this at the end of
	 * the file. However, original copy (and one used in
	 * CDN) doesn't have this. By printing we replicate
	 * behaviour as when WordPress copy is used.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function jquery_noconflict() {
		wp_add_inline_script( 'jquery-core', 'try{jQuery.noConflict();}catch(e){};' );
	}

	/**
	 * Remove version query string from unpkg source path.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $src Item loader source path.
	 * @return string $src Updated source path.
	 */
	public function remove_version( $src ) {
		// Only from URLs on unpkg
		if ( preg_match( '/unpkg\.com\//', $src ) ) {
			$src = remove_query_arg( 'ver', $src );
		}

		return $src;
	}

	/**
	 * Replace WordPress paths with unpkg URLs.
	 *
	 * Loop through items registered on unpkg and if item
	 * is registered in WordPress, get item's URL on unpkg.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_Dependencies $wordpress_items Object of appropiate child class.
	 */
	public function replace( &$wordpress_items ) {
		// Default extension is .js and items for JavaScript
		$extension   = '.js';
		$unpkg_items = &$this->unpkg_scripts;

		// If it is for styles, change extension and items
		if ( $wordpress_items instanceof WP_Styles ) {
			$extension   = '.css';
			$unpkg_items = &$this->unpkg_styles;
		}

		// Loop through items registered on unpkg
		foreach ( $unpkg_items as $name => &$data ) {
			// Check if this item is already processed
			if ( array_key_exists( 'done', $data ) ) {
				continue;
			}

			// Get dependency for handle
			$item = $wordpress_items->query( $name );

			// Check if item is registered in WordPress
			if ( ! $item ) {
				continue;
			}

			// Mark that this item is processed
			$data['done'] = true;

			// Set package and file name
			$package = $data['package'];
			$file    = $data['file'];

			// Use the same version as one that is registered for handle
			$version = $item->ver;

			/*
			 * Items that have hyphen in its version aren't allowed.
			 * This usually means that this is non-standard version.
			 */
			if ( false !== strpos( $version, '-' ) ) {
				continue;
			}

			// Check if item has minified version and set suffix
			if ( ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) && $data['minified'] ) {
				$suffix = $data['minified'];
			} else {
				$suffix = '';
			}

			// Always use HTTPS for URL on unpkg
			$url = "https://unpkg.com/$package@$version/$file$suffix$extension";

			// Set item's path to one on unpkg
			$item->src = $url;
		}
	}

	/**
	 * Apply filters that are hooked.
	 *
	 * Replacements methods are fired again because
	 * plugins and themes register and enqueue items
	 * after default items are replaced.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function apply_filters() {
		$this->replace( wp_scripts() );
		$this->replace( wp_styles()  );
	}
}
endif;
