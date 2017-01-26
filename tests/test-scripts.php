<?php
/**
 * Class Tests_Use_unpkg

 * @ see https://stackoverflow.com/questions/7493102/phpunit-cli-output-during-test-debugging-possible#12606210
 *
 * @package Use_unpkg
 * @subpackage Test
 */

/**
 * Test case for transients.
 *
 * @since 1.0
 */
class Tests_Use_unpkg_Scripts extends WP_UnitTestCase {
	/**
	 * Test that scripts sources are replaced.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function test_scripts_replaced() {
		$scripts = wp_scripts();
		$s = Use_unpkg::get_instance()->unpkg_scripts;
	
		foreach ( $s as $handle => $data ) {
			if ( in_array( $handle, array( 'twentysixteen-html5', 'html5', 'jquery-scrollto' ) ) ) {
				continue;
			}
			
			$script = $scripts->query( $handle );
			$this->assertContains(
				'https://unpkg.com',
				$script->src, $handle . ' should be loading from unpkg'
			);
		}
	}

	/**
	 * Test that remote files exist.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function test_scripts_exists_on_unpkg() {
		$scripts = wp_scripts();
		foreach ( Use_unpkg::get_instance()->unpkg_scripts as $handle => $data ) {
			if ( in_array( $handle, array( 'twentysixteen-html5', 'html5', 'jquery-scrollto' ) ) ) {
				continue;
			}
			$script = $scripts->query( $handle );
			$response = wp_remote_get( $script->src );
			$response_code = wp_remote_retrieve_response_code( $response );
			$this->assertEquals(
				200,
				$response_code,
				$handle . ' should exists on unpkg'
			);
		}
	}

	public function test_noconflict_injected() {
		$script = wp_scripts()->query( 'jquery-core' );
		$data = $script->extra['after'][1];
		$this->assertEquals(
			'try{jQuery.noConflict();}catch(e){};',
			$data,
			'jQuery should be in noconlict mode'
		);
	}
}
