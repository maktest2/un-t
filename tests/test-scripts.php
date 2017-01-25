<?php
/**
 * Class Tests_Use_unpkg
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
		foreach ( Use_unpkg::get_instance()->unpkg_scripts as $handle => $data ) {
			if ( 'twentysixteen-html5' == $handle && 'twentysixteen' != get_template() ) {
				$this->markTestSkipped( 'Twenty Sixteen should be active for this test' );
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
			if ( 'twentysixteen-html5' == $handle && 'twentysixteen' != get_template() ) {
				$this->markTestSkipped( 'Twenty Sixteen should be active for this test' );
			}

			$script = $scripts->query( $handle );
			$response_code = wp_remote_retrieve_response_code( wp_remote_get( $script->src ) );
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
