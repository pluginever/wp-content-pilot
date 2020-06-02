<?php
class Tests_Formatting extends WP_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function test() {
		$this->assertEquals( 369.99, wpcp_get_numbers_from_string( 'Renewed from $369.99' ) );
		$this->assertEquals( '', wpcp_get_numbers_from_string( 'Renewed from' ) );
		$this->assertEquals( [ 120 ], wpcp_get_numbers_from_string( 'Showing 120', true ) );
		$this->assertEquals( [ 372 ], wpcp_get_numbers_from_string( 'Showing x of 372', true ) );
		$this->assertEquals( [ 120, 372 ], wpcp_get_numbers_from_string( 'Showing 120 of 372', true ) );

		//wpcp_string_to_array
		$this->assertEquals( [ 'hello', 'dello', 'mello' ], wpcp_string_to_array( 'hello,dello,mello ', ',' ) );
	}

	public function tearDown() {
		parent::tearDown();
	}
}
