<?php

/**
 * Defines all api calls.
 *
 * @link       https://darpankulkarni.in
 * @since      1.0.0
 *
 * @package    Awb_Cf7
 * @subpackage Awb_Cf7/api
 * @author     Darpan Kulkarni <plugins@darpankulkarni.in>
 */
class Awb_Cf7_Api {
	/**
	 * Get accounts from AWeber API
	 *
	 * @since 1.0.0
	 */
	public function awbc_get_accounts() {
		// Get post data
		$formId = isset( $_POST['awbc_form_id'] ) ? sanitize_text_field( $_POST['awbc_form_id'] ) : null;

		// Get options
		$options = get_option( 'awbc_' . $formId );

		// Send request
		$response = Requests::get(
			'https://api.aweber.com/1.0/accounts',
			array( 'Authorization' => 'Bearer ' . $options['access_token'] )
		);

		// Send error response
		if ( ! $response->success ) {
			wp_send_json_error( __( 'Invalid access token. Unable to fetch accounts.' ), $response->status_code );
		}

		// Update options to add account id
		update_option( 'awbc_' . $formId, array_merge(
			$options,
			array( 'account_id' => json_decode( $response->body )->entries[0]->id )
		), false );

		// Send success response
		wp_send_json_success( __( 'Account details received.' ), 200 );
	}

	/**
	 * Get lists from AWeber API
	 *
	 * @since 1.0.0
	 */
	public function awbc_get_lists() {
		// Get post data
		$formId = isset( $_POST['awbc_form_id'] ) ? sanitize_text_field( $_POST['awbc_form_id'] ) : null;

		// Refresh token
		Awb_Cf7_Auth::awbc_refresh_token( $formId );

		// Get options
		$options = get_option( 'awbc_' . $formId );

		// Send request
		$response = Requests::get(
			'https://api.aweber.com/1.0/accounts/' . $options['account_id'] . '/lists',
			array( 'Authorization' => 'Bearer ' . $options['access_token'] )
		);

		// Send error response
		if ( ! $response->success ) {
			wp_send_json_error( __( 'Unable to fetch lists.' ), $response->status_code );
		}

		// Prepare lists data
		$lists = array_column( json_decode( $response->body )->entries, 'name', 'id' );

		// Update options to add lists
		update_option( 'awbc_' . $formId, array_merge( $options, array( 'lists' => $lists ) ), false );

		// Send success response
		wp_send_json_success( $lists, $response->status_code );
	}

	/**
	 * Save subscriber data to aweber
	 *
	 * @param WPCF7_ContactForm $cf Contact form 7 instance
	 *
	 * @return WPCF7_ContactForm
	 *
	 * @since 1.0.0
	 */
	public function awbc_save_subscriber_remote( WPCF7_ContactForm $cf ) {
		// Refresh token
		Awb_Cf7_Auth::awbc_refresh_token( $cf->id() );

		// Get options
		$options = get_option( 'awbc_' . $cf->id() );

		// Return if required options are not set
		if ( ! $options || ! isset( $options['subscriber'] ) || ! isset( $options['list_id'] ) ) {
			return $cf;
		}

		$accountId = $options['account_id']; // Get account id from options
		$listId    = $options['list_id']; // Get list id from options
		$formData  = WPCF7_Submission::get_instance()->get_posted_data(); // Get form data from contact form 7
		$postData  = array(); // Initialize empty post data

		// Set post data
		foreach ( $options['subscriber'] as $key => $value ) {
			// Parse value if not empty
			if ( $value != '' ) {
				$parsedTag        = str_replace( array( '[', ']' ), '', $value );
				$postData[ $key ] = $formData[ $parsedTag ];
			} else {
				$postData[ $key ] = $value; // Set value directly if empty
			}
		}

		// Send request
		$response = Requests::post(
			'https://api.aweber.com/1.0/accounts/' . $accountId . '/lists/' . $listId . '/subscribers',
			array( 'Authorization' => 'Bearer ' . $options['access_token'] ),
			$postData
		);

		// Log error response
		if ( ! $response->success ) {
			error_log( 'Unable to save user details to aweber: ' . json_encode( $response->body ) );
		}

		return $cf;
	}

	/**
	 * Connect selected list to form
	 *
	 * @since 1.0.0
	 */
	public function awbc_connect_list() {
		// Get post data
		$formId = isset( $_POST['awbc_form_id'] ) ? sanitize_text_field( $_POST['awbc_form_id'] ) : null;
		$listId = isset( $_POST['awbc_list_id'] ) ? sanitize_text_field( $_POST['awbc_list_id'] ) : null;

		// Get options
		$options = get_option( 'awbc_' . $formId );

		// Update options to add list id
		update_option( 'awbc_' . $formId, array_merge( $options, array( 'list_id' => $listId ) ), false );

		// Send success response
		wp_send_json_success( __( 'Successfully connected to list. List id: ' . $listId ), 200 );
	}

	/**
	 * Save subscriber data
	 *
	 * @param WPCF7_ContactForm $cf Contact form 7 instance
	 *
	 * @since 1.0.0
	 */
	public function awbc_save_subscriber( WPCF7_ContactForm $cf ) {
		// Get options
		$options = get_option( 'awbc_' . $cf->id() );

		// Return if options are not set
		if ( ! $options ) {
			return;
		}

		// Get post data
		$email = isset( $_POST['awbc_sub_email'] ) ? sanitize_text_field( $_POST['awbc_sub_email'] ) : null;
		$name  = isset( $_POST['awbc_sub_name'] ) ? sanitize_text_field( $_POST['awbc_sub_name'] ) : null;

		$subscriber = array(
			'email' => $email,
			'name'  => $name
		);

		// Update options to add subscriber data
		update_option( 'awbc_' . $cf->id(), array_merge( $options, array( 'subscriber' => $subscriber ) ), false );
	}
}
