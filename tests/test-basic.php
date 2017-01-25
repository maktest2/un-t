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
class Tests_Use_unpkg extends WP_UnitTestCase {
	/**
	 * Test default behaviour.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function test_remove_ver_query() {
		$dummy_src    = 'https://unpkg.com/dummy.js';
		$dummy_script = add_query_arg( 'ver', '1.2.3', $dummy_src );
		$this->assertNotEquals( $dummy_src, $dummy_script );
		$this->assertEquals(
			$dummy_src, Use_unpkg::get_instance()->remove_version( $dummy_script ),
			'ver should be removed from url if from unpkg'
		);
	}

	/**
	 * Test default behaviour.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function test_dont_remove_ver_query() {
		$dummy_src    = 'dummy.js';
		$dummy_script = add_query_arg( 'ver', '1.2.3', $dummy_src );
		$this->assertNotEquals( $dummy_src, $dummy_script );
		$this->assertEquals(
			$dummy_script, Use_unpkg::get_instance()->remove_version( $dummy_script ),
			'ver should not be removed unless url is from unpkg'
		);
	}

	/**
	 * Test default behaviour.
	 *
	 * @since 1.0
	 * @access public
	 */
	public function test_concatenate_scripts_disabled() {
		global $concatenate_scripts;
		$this->assertFalse( $concatenate_scripts );
	}
}
