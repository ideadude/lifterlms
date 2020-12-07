<?php
/**
 * Tests for LifterLMS Core Functions
 *
 * @group LLMS_Student
 * @group LLMS_Person_Handler
 *
 * @since 3.19.4
 * @since 3.29.4 Unknown.
 * @since 3.37.17 Add voucher-related tests.
 * @since 4.5.0 Added tests on account.signon event recorded on user registration.
 * @since [version] Update to work with changes from LLMS_Forms.
 *               Add tests for the LLMS_Person_Handler::get_login_forms() method.
 *               Login tests don't rely on deprecated option `lifterlms_registration_generate_username`.
 */
class LLMS_Test_Person_Handler extends LLMS_UnitTestCase {

	/**
	 * Test username generation
	 * @return   void
	 * @since    3.19.4
	 * @version  3.19.4
	 */
	public function test_generate_username() {

		// username is first part of email
		$this->assertEquals( 'mock', LLMS_Person_Handler::generate_username( 'mock@whatever.com' ) );

		// create a user with the mock username
		$this->factory->user->create( array(
			'user_login' => 'mock',
		) );

		// test that usernames are unique
		$i = 1;
		while ( $i <= 5 ) {
			$this->factory->user->create( array(
				'user_login' => sprintf( 'mock%d', $i ),
			) );
			$this->assertEquals( sprintf( 'mock%d', $i+1 ), LLMS_Person_Handler::generate_username( 'mock@whatever.com' ) );
			$i++;
		}

		// test character sanitization
		$tests = array(
			'mock_mock' => 'mock_mock',
			"mock'mock" => "mockmock",
			'mock+mock' => "mockmock",
			'mock.mock' => "mock.mock",
			'mock-mock' => "mock-mock",
			'mock mock' => "mock mock",
			'mock!mock' => "mockmock",
		);

		foreach ( $tests as $email => $expect) {
			$this->assertEquals( $expect, LLMS_Person_Handler::generate_username( $email . '@whatever.com' ) );
		}

	}


	// public function test_get_available_fields() {}

	/**
	 * Test the get_login_fields() method.
	 *
	 * It should return an array of LifterLMS Form Fields.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_login_fields() {

		$fields = LLMS_Person_Handler::get_login_fields();

		$this->assertTrue( is_array( $fields ) );
		$this->assertEquals( 5, count( $fields ) );
		foreach ( $fields as $field ) {
			$this->assertTrue( is_array( $field ) );
			$this->assertArrayHasKey( 'id', $field );
		}

	}

	/**
	 * Test the get_login_fields() method when the layout is columns
	 *
	 * It should return an array of LifterLMS Form Fields.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_login_fields_layout_columns() {

		$default = LLMS_Person_Handler::get_login_fields();
		$fields  = LLMS_Person_Handler::get_login_fields( 'columns' );

		// Default value is "columns".
		$this->assertEquals( $default, $fields );

		$this->assertEquals( 6, $fields[0]['columns'] );
		$this->assertEquals( 6, $fields[1]['columns'] );
		$this->assertEquals( 3, $fields[2]['columns'] );
		$this->assertEquals( 6, $fields[3]['columns'] );
		$this->assertEquals( 3, $fields[4]['columns'] );

	}

	/**
	 * Test the get_login_fields() method when the layout is stacked
	 *
	 * It should return an array of LifterLMS Form Fields.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_login_fields_layout_stacked() {

		$fields = LLMS_Person_Handler::get_login_fields( 'stacked' );

		$this->assertEquals( 12, $fields[0]['columns'] );
		$this->assertEquals( 12, $fields[1]['columns'] );
		$this->assertEquals( 12, $fields[2]['columns'] );
		$this->assertEquals( 6, $fields[3]['columns'] );
		$this->assertEquals( 6, $fields[4]['columns'] );

	}

	/**
	 * Test get_login_fields() when usernames are enabled.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_login_fields_usernames_enabled() {

		add_filter( 'llms_are_usernames_enabled', '__return_true' );
		$field = LLMS_Person_Handler::get_login_fields()[0];
		$this->assertEquals( 'Username or Email Address', $field['label'] );
		$this->assertEquals( 'text', $field['type'] );
		remove_filter( 'llms_are_usernames_enabled', '__return_true' );

	}

	/**
	 * Test get_login_fields() when usernames are disabled.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_login_fields_usernames_disabled() {

		add_filter( 'llms_are_usernames_enabled', '__return_false' );
		$field = LLMS_Person_Handler::get_login_fields()[0];
		$this->assertEquals( 'Email Address', $field['label'] );
		$this->assertEquals( 'email', $field['type'] );
		remove_filter( 'llms_are_usernames_enabled', '__return_false' );

	}

	/**
	 * Test get_lost_password_fields() when usernames are enabled.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_lost_password_fields_usernames_enabled() {

		add_filter( 'llms_are_usernames_enabled', '__return_true' );
		$fields = LLMS_Person_Handler::get_lost_password_fields();
		$this->assertTrue( false !== strpos( $fields[0]['value'], 'username' ) );
		$this->assertEquals( 'Username or Email Address', $fields[1]['label'] );
		$this->assertEquals( 'text', $fields[1]['type'] );
		remove_filter( 'llms_are_usernames_enabled', '__return_true' );

	}

	/**
	 * Test get_lost_password_fields() when usernames are disabled.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_lost_password_fields_usernames_disabled() {

		add_filter( 'llms_are_usernames_enabled', '__return_false' );
		$fields = LLMS_Person_Handler::get_lost_password_fields();
		$this->assertFalse( strpos( $fields[0]['value'], 'username' ) );
		$this->assertEquals( 'Email Address', $fields[1]['label'] );
		$this->assertEquals( 'email', $fields[1]['type'] );
		remove_filter( 'llms_are_usernames_enabled', '__return_false' );

	}

	/**
	 * Test get_password_reset_fields() when "custom" password reset fields exist on the checkout form.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_password_reset_fields_from_checkout() {

		update_option( 'lifterlms_registration_password_strength', 'yes' );
		LLMS_Forms::instance()->create( 'checkout', true );

		add_filter( 'llms_password_reset_fields', function( $fields, $key, $login, $location ) {
			$this->assertEquals( 'checkout', $location );
			return $fields;
		}, 10, 4 );

		$expect = array(
			'password',
			'password_confirm',
			'llms-password-strength-meter',
			'llms_lost_password_button',
			'llms_reset_key',
			'llms_reset_login',
		);
		$this->assertEquals( $expect, wp_list_pluck( LLMS_Person_Handler::get_password_reset_fields(), 'id' ) );

	}

	/**
	 * Test get_password_reset_fields() when "custom" password reset fields don't exist on checkout but do exist on reg form.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_password_reset_fields_from_registration() {

		wp_update_post( array(
			'ID'           => LLMS_Forms::instance()->create( 'checkout', true ),
			'post_content' => '',
		) );

		update_option( 'lifterlms_registration_password_strength', 'no' );
		LLMS_Forms::instance()->create( 'registration', true );

		add_filter( 'llms_password_reset_fields', function( $fields, $key, $login, $location ) {
			$this->assertEquals( 'registration', $location );
			return $fields;
		}, 10, 4 );

		$expect = array(
			'password',
			'password_confirm',
			'llms_lost_password_button',
			'llms_reset_key',
			'llms_reset_login',
		);
		$this->assertEquals( $expect, wp_list_pluck( LLMS_Person_Handler::get_password_reset_fields(), 'id' ) );

	}

	/**
	 * Test get_password_reset_fields() when "custom" password reset fields don't exist on checkout but do exist on reg form.
	 *
	 * @since [version]
	 *
	 * @return void
	 */
	public function test_get_password_reset_fields_from_fallback() {

		add_filter( 'llms_password_reset_fields', function( $fields, $key, $login, $location ) {
			$this->assertEquals( 'fallback', $location );
			return $fields;
		}, 10, 4 );

		$expect = array(
			'password',
			'password_confirm',
			'llms-password-strength-meter',
			'llms_lost_password_button',
			'llms_reset_key',
			'llms_reset_login',
		);
		$this->assertEquals( $expect, wp_list_pluck( LLMS_Person_Handler::get_password_reset_fields(), 'id' ) );

	}

	/**
	 * Test logging in with a username.
	 *
	 * @since 3.29.4
	 * @since [version] Remove deprecated option `lifterlms_registration_generate_username` and allow username login via filter.
	 *
	 * @return  void
	 */
	public function test_login_with_username() {

		// Enable Usernames.
		add_filter( 'llms_are_usernames_enabled', '__return_true' );

		// Missing login.
		$login = LLMS_Person_Handler::login( array(
			'llms_password' => 'faker',
		) );

		$this->assertIsWPError( $login );
		$this->assertWPErrorCodeEquals( 'llms_login', $login );

		// Missing Password
		$login = LLMS_Person_Handler::login( array(
			'llms_login' => 'faker',
		) );

		$this->assertIsWPError( $login );
		$this->assertWPErrorCodeEquals( 'llms_password', $login );

		// Totally Invalid creds.
		$login = LLMS_Person_Handler::login( array(
			'llms_login' => '3OGgpZZ146cH3vw775aMg1R7qQIrF4ph',
			'llms_password' => 'Ip439RKmf0am5MWRjD38ov6M45OEYs79',
		) );

		$this->assertIsWPError( $login );
		$this->assertWPErrorCodeEquals( 'login-error', $login );

		// Test against a real user with bad creds.
		$user = $this->factory->user->create_and_get( array( 'user_login' => 'test_user_login', 'user_pass' => '1234' ) );
		$uid  = $user->ID;

		$login = LLMS_Person_Handler::login( array(
			'llms_login' => 'test_user_login',
			'llms_password' => '1',
		) );

		$this->assertIsWPError( $login );
		$this->assertWPErrorCodeEquals( 'login-error', $login );

		// Success.
		$login = LLMS_Person_Handler::login( array(
			'llms_login' => 'test_user_login',
			'llms_password' => '1234',
		) );

		$this->assertEquals( $uid, $login );
		wp_logout();


		// Use a fake email address in the login field.
		$login = LLMS_Person_Handler::login( array(
			'llms_login' => 'fake@whatever.com',
			'llms_password' => '1234',
		) );
		$this->assertIsWPError( $login );
		$this->assertWPErrorCodeEquals( 'login-error', $login );

		// Use the real email address in the login field.
		$login = LLMS_Person_Handler::login( array(
			'llms_login' => $user->user_email,
			'llms_password' => '1234',
		) );
		$this->assertEquals( $uid, $login );
		wp_logout();

		remove_filter( 'llms_are_usernames_enabled', '__return_true' );

	}

	/**
	 * Test logging in with a username.
	 *
	 * @since 3.29.4
	 * @since [version] Remove deprecated option `lifterlms_registration_generate_username`.
	 *
	 * @return  void
	 */
	public function test_login_with_email() {

		// Missing login.
		$login = LLMS_Person_Handler::login( array(
			'llms_password' => 'faker',
		) );

		$this->assertIsWPError( $login );
		$this->assertWPErrorCodeEquals( 'llms_login', $login );

		// Invalid email address.
		$login = LLMS_Person_Handler::login( array(
			'llms_login' => 'faker',
		) );

		$this->assertIsWPError( $login );
		$this->assertWPErrorCodeEquals( 'llms_login', $login );

		// Missing password.
		$login = LLMS_Person_Handler::login( array(
			'llms_login' => 'faker@fake.tld',
		) );

		$this->assertIsWPError( $login );
		$this->assertWPErrorCodeEquals( 'llms_password', $login );

		// Totally Invalid creds.
		$login = LLMS_Person_Handler::login( array(
			'llms_login' => '3OGgpZZ146cH3vw775aMg1R7qQIrF4ph@fake.tld',
			'llms_password' => 'Ip439RKmf0am5MWRjD38ov6M45OEYs79',
		) );

		$this->assertIsWPError( $login );
		$this->assertWPErrorCodeEquals( 'login-error', $login );

		// Test against a real user with bad creds.
		$user = $this->factory->user->create_and_get( array( 'user_pass' => '1234' ) );

		$login = LLMS_Person_Handler::login( array(
			'llms_login' => $user->user_email,
			'llms_password' => '1',
		) );

		$this->assertIsWPError( $login );
		$this->assertWPErrorCodeEquals( 'login-error', $login );

		// Success.
		$login = LLMS_Person_Handler::login( array(
			'llms_login' => $user->user_email,
			'llms_password' => '1234',
		) );

		$this->assertEquals( $user->ID, $login );
		wp_logout();

		// Make sure that email addresses with an apostrophe in them can login without issue.
		$user = $this->factory->user->create_and_get( array( 'user_email' => "mock'mock@what.org", 'user_pass' => '1234' ) );
		$login = LLMS_Person_Handler::login( array(
			'llms_login' => wp_slash( $user->user_email ), // add slashes like the $_POST data.
			'llms_password' => '1234',
		) );

		$this->assertEquals( $user->ID, $login );
		wp_logout();

	}

	/**
	 * Test account.signon event recorded on user registration
	 *
	 * @since 4.5.0
	 */
	public function test_account_signon_event_recorded_on_registration_signon() {

		LLMS_Install::create_pages();
		LLMS_Forms::instance()->install();

		global $wpdb;

		$data = $this->get_mock_registration_data();
		$data['email_address'] = "new_{$data['email_address']}";

		$query_signon_event = "
			SELECT COUNT(*) FROM {$wpdb->prefix}lifterlms_events
			WHERE event_type='account'
			AND event_action='signon'
			AND object_type='user'
			AND actor_id='%d'
			";

		// Test no event registered, if no signon.
		$user_id = llms_register_user( $data, $screen = 'registration', false );
		$this->assertEquals( 0, $wpdb->get_var( $wpdb->prepare( $query_signon_event, $user_id ) ) );

		// Test event registered when signing on registration (defaults).
		$data['email_address'] = "new1_{$data['email_address']}";
		$user_id = llms_register_user( $data );
		$this->assertEquals( 1, $wpdb->get_var( $wpdb->prepare( $query_signon_event, $user_id ) ) );

		// Clean up tables.
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}lifterlms_events" );
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}lifterlms_events_open_sessions" );
	}

	/**
	 * Test the deprecated update() method.
	 *
	 * This test remains to ensure backwards compatibility.
	 *
	 * @since 3.26.1
	 * @since [version] Create forms before running & update error codes to match updated codes.
	 *
	 * @expectedDeprecated LLMS_Person_Handler::update()
	 *
	 * @return void
	 */
	public function test_update() {

		LLMS_Install::create_pages();
		LLMS_Forms::instance()->install();

		$data = array();

		// No user Id supplied.
		$update = LLMS_Person_Handler::update( $data, 'account' );
		$this->assertTrue( is_wp_error( $update ) );
		$this->assertEquals( 'llms-form-no-user', $update->get_error_code() );

		$uid = $this->factory->user->create( array( 'role' => 'student' ) );
		$user = new WP_User( $uid );

		// user Id Interpreted from current logged in user.
		wp_set_current_user( $uid );
		$update = LLMS_Person_Handler::update( $data, 'account' );
		$this->assertTrue( is_wp_error( $update ) );
		$this->assertFalse( in_array( 'llms-form-no-user', $update->get_error_codes(), true ) );
		wp_set_current_user( null );

		// Used ID explicitly passed.
		$data['user_id'] = $uid;
		$update = LLMS_Person_Handler::update( $data, 'account' );
		$this->assertTrue( is_wp_error( $update ) );
		$this->assertTrue( in_array( 'llms-form-no-user', $update->get_error_codes(), true ) );

	}

	private function get_mock_registration_data( $data = array() ) {

		$password = wp_generate_password();

		return wp_parse_args( $data, array(
			'user_login' => 'mocker',
			'email_address' => 'mocker@mock.com',
			'first_name' => 'Bird',
			'last_name' => 'Person',
			'llms_billing_address_1' => '1234 Street Ave.',
			'llms_billing_address_2' => '#567',
			'llms_billing_city' => 'Anywhere,',
			'llms_billing_state' => 'CA',
			'llms_billing_zip' => '12345',
			'llms_billing_country' => 'US',
			'llms_agree_to_terms' => 'yes',
			'password' => $password,
			'password_confirm' => $password,
		) );

	}

	public function test_validate_fields() {

		$this->markTestIncomplete( 'This tests needs to be updated when backwards validate_fields() is refactored for backwards compat.' );

		/**
		 * Registration
		 */

		// no data
		$this->assertTrue( is_wp_error( LLMS_Person_Handler::validate_fields( array(), 'registration' ) ) );

		$data = $this->get_mock_registration_data();
		$this->assertTrue( LLMS_Person_Handler::validate_fields( $data, 'registration' ) );

		// check emails with quotes
		$data['email_address'] = "mock\'mock@what.org";
		$this->assertTrue( LLMS_Person_Handler::validate_fields( $data, 'registration' ) );


		/**
		 * Login
		 */

		// no data
		$this->assertTrue( is_wp_error( LLMS_Person_Handler::validate_fields( array(), 'login' ) ) );

		$data = array(
			'llms_login' => 'mocker@mock.com',
			'llms_password' => '4bKyvI41Xxnf',
		);
		$this->assertTrue( LLMS_Person_Handler::validate_fields( $data, 'login' ) );

		// check emails with quotes
		$data = array(
			'llms_login' => "moc\'ker@mock.com",
			'llms_password' => '4bKyvI41Xxnf',
		);
		$this->assertTrue( LLMS_Person_Handler::validate_fields( $data, 'login' ) );

		/**
		 * Update
		 */

		// no data
		$this->assertTrue( is_wp_error( LLMS_Person_Handler::validate_fields( array(), 'account' ) ) );

		$data = $this->get_mock_registration_data();
		$data['email_address_confirm'] = $data['email_address'];
		$this->assertTrue( LLMS_Person_Handler::validate_fields( $data, 'account' ) );


		$uid = $this->factory->user->create( array(
			'user_email' =>"mock\'mock@what.org",
		) );
		wp_set_current_user( $uid );

		$data = $this->get_mock_registration_data();
		$data['email_address'] = "mock\'mock@what.org";
		$data['email_address_confirm'] = $data['email_address'];
		$this->assertTrue( LLMS_Person_Handler::validate_fields( $data, 'account' ) );

	}

	/**
	 * Test validate_fields() when a non-existent voucher is submitted.
	 *
	 * @since 3.37.17
	 *
	 * @return void
	 */
	public function test_validate_fields_with_voucher_not_found() {

		$data = $this->get_mock_registration_data( array(
			'email_address' => 'mock@test.tld',
			'llms_voucher'  => 'fake',
		) );

		$res = LLMS_Person_Handler::validate_fields( $data, 'registration' );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_voucher', $res );
		$this->assertWPErrorDataEquals( 'voucher-not-found', $res );

	}

	/**
	 * Test validate_fields() when a voucher code has been deleted.
	 *
	 * @since 3.37.17
	 *
	 * @return void
	 */
	public function test_validate_fields_with_voucher_code_deleted() {

		$voucher = $this->create_voucher( 1, 1 );
		$code    = $voucher->get_voucher_codes()[0];

		$voucher->delete_voucher_code( $code->id );

		$data = $this->get_mock_registration_data( array(
			'email_address' => sprintf( 'mock+%d@test.tld', rand() ),
			'llms_voucher'  => $code->code,
		) );

		$res = LLMS_Person_Handler::validate_fields( $data, 'registration' );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_voucher', $res );
		$this->assertWPErrorDataEquals( 'voucher-not-found', $res );

	}

	/**
	 * Test validate_fields() when a voucher code's parent post is deleted (or not published).
	 *
	 * @since 3.37.17
	 *
	 * @return void
	 */
	public function test_validate_fields_with_voucher_post_deleted() {

		$voucher = $this->create_voucher( 1, 1 );
		$code    = $voucher->get_voucher_codes()[0];

		wp_delete_post( $code->voucher_id, true );

		$data = $this->get_mock_registration_data( array(
			'email_address' => sprintf( 'mock+%d@test.tld', rand() ),
			'llms_voucher'  => $code->code,
		) );

		$res = LLMS_Person_Handler::validate_fields( $data, 'registration' );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_voucher', $res );
		$this->assertWPErrorDataEquals( 'voucher-deleted', $res );

	}

	/**
	 * Test validate_fields() when a voucher code has been redeemed the maximum number of times allowed.
	 *
	 * @since 3.37.17
	 *
	 * @return void
	 */
	public function test_validate_fields_with_voucher_redemptions_maxed() {

		$voucher = $this->create_voucher( 1, 1 );
		$code    = $voucher->get_voucher_codes()[0];

		$voucher->use_voucher( $code->code, $this->factory->user->create() );

		$data = $this->get_mock_registration_data( array(
			'email_address' => sprintf( 'mock+%d@test.tld', rand() ),
			'llms_voucher'  => $code->code,
		) );

		$res = LLMS_Person_Handler::validate_fields( $data, 'registration' );
		$this->assertIsWPError( $res );
		$this->assertWPErrorCodeEquals( 'llms_voucher', $res );
		$this->assertWPErrorDataEquals( 'voucher-max', $res );

	}

}
