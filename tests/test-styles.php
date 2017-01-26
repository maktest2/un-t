<?php
/**
 * Class Tests_Use_unpkg_Styles

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
class Tests_Use_unpkg_Styles extends WP_UnitTestCase {
	/**
	 * Test that styles sources are replaced.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function test_styles_replaced() {
		$styles = wp_styles();
		$s = Use_unpkg::get_instance()->unpkg_styles;
	
		foreach ( $s as $handle => $data ) {
			$style = $styles->query( $handle );
			$this->assertContains(
				'https://unpkg.com',
				$style->src, $handle . ' should be loading from unpkg'
			);
		}
	}

	/**
	 * Test that remote files exist.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function test_styles_exists_on_unpkg() {
		$styles = wp_styles();
		foreach ( Use_unpkg::get_instance()->unpkg_styles as $handle => $data ) {
			$style = $styles->query( $handle );
			$response = wp_remote_get( $style->src );
			$response_code = wp_remote_retrieve_response_code( $response );
			$this->assertEquals(
				200,
				$response_code,
				$handle . ' should exists on unpkg'
			);
		}
	}
}
