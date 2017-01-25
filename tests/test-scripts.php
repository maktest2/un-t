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
	function setUp() {
		parent::setUp();

		add_action( 'wp_default_scripts', array( $this, 'copy_original_src' ) );
	}

	public function copy_original_src( $scripts ) {
		foreach ( Use_unpkg::get_instance()->unpkg_scripts as $handle => $data ) {
			$script = $scripts->query( $handle );
			$script->original_src = $script->src;
		}
	}

	/**
	 * Test that scripts sources are replaced.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function test_scripts_replaced() {
		$scripts = wp_scripts();
		foreach ( Use_unpkg::get_instance()->unpkg_scripts as $handle => $data ) {
			if ( in_array( $handle, array( 'twentysixteen-html5', 'html5', 'jquery-scrollto' ) ) ) {
				continue;
				//$this->markTestSkipped( 'Twenty Sixteen should be active for this test' );
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
				//$this->markTestSkipped( 'Twenty Sixteen should be active for this test' );
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

	/**
	 * Test that original files are same.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function test_scripts_originals_and_unpkg_same() {
		$scripts = wp_scripts();
		foreach ( Use_unpkg::get_instance()->unpkg_scripts as $handle => $data ) {
			if ( in_array( $handle, array( 'twentysixteen-html5', 'html5', 'jquery-scrollto' ) ) ) {
				continue;
				//$this->markTestSkipped( 'Twenty Sixteen should be active for this test' );
			}

			$script = $scripts->query( $handle );
			$original_content = file_get_contents( untrailingslashit( ABSPATH ) . $script->original_src );
			if ( 'jquery-core' == $handle ) {
				$original_content .= '
jQuery.noConflict();';
			}
			$response_body = wp_remote_retrieve_body( wp_remote_get( $script->src ) );
			$this->assertEquals(
				$original_content,
				$response_body,
				$handle . ' should be the same on unpkg'
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
