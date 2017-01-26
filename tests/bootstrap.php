<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Clean_Expired_Transients
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

/**
 * Manually load the plugin being tested.
 */
function _manually_load_plugin() {
	require dirname( dirname( __FILE__ ) ) . '/use-unpkg.php';
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

function _copy_original_dependency_src( $scripts ) {
	foreach ( Use_unpkg::get_instance()->unpkg_scripts as $handle => $data ) {
		if ( in_array( $handle, array( 'twentysixteen-html5', 'html5', 'jquery-scrollto' ) ) ) {
			continue;
		}
		$script = $scripts->query( $handle );
		$script->original_src = $script->src;
	}
}
tests_add_filter( 'wp_default_scripts', '_copy_original_dependency_src', 11 );

// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
