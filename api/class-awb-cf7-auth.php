<?php

/**
 * Defines all auth api calls.
 *
 * @link       https://darpankulkarni.in
 * @since      1.0.0
 *
 * @package    Awb_Cf7
 * @subpackage Awb_Cf7/api
 * @author     Darpan Kulkarni <plugins@darpankulkarni.in>
 */
class Awb_Cf7_Auth {

	/**
	 * @since 1.0.0
	 * @access private
	 * @var int $clientId AWeber app client id
	 */
	private $clientId = '5WPjA28THHVXbS8htwxtrP1QNqOFAO8d';

	/**
	 * @since 1.0.0
	 * @access private
	 * @var int $codeVerifier Code verifier
	 */
	public $codeVerifier;

	/**
	 * Awb_Cf7_Api constructor.
	 *
	 * Set access token, code verifier, options and request session
	 *
	 * @throws Exception
	 * @since 1.0.0
	 */
	public function __construct() {
		try {
			$verifierBytes      = random_bytes( 64 );
			$this->codeVerifier = rtrim( strtr( base64_encode( $verifierBytes ), '+/', '-_' ), '=' );
		} catch ( Exception $e ) {
			error_log( 'AWBC_VERIFIER_EXCEPTION: ' . json_encode( $e ) );
		}
	}

	/**
	 * Get authorization code url
	 *
	 * @since 1.0.0
	 */
	public function awbc_get_auth_code_url() {
		// Define scopes
		$scopes = array(
			'account.read',
			'list.read',
			'subscriber.read',
			'subscriber.read-extended',
			'subscriber.write'
		);

		// Create code challenge
		$challengeBytes = hash( 'sha256', $this->codeVerifier, true );
		$codeChallenge  = rtrim( strtr( base64_encode( $challengeBytes ), '+/', '-_' ), '=' );

		// Set required params
		$params = array(
			'responseonse_type'     => 'code',
			'client_id'             => $this->clientId,
			'redirect_uri'          => 'urn:ietf:wg:oauth:2.0:oob',
			'scope'                 => implode( ' ', $scopes ),
			'state'                 => uniqid(),
			'code_challenge'        => $codeChallenge,
			'code_challenge_method' => 'S256'
		);

		// Return url
		return 'https://auth.aweber.com/oauth2/authorize?' . http_build_query( $params );
	}

	/**
	 * Get access token from AWeber API
	 *
	 * @since 1.0.0
	 */
	public function awbc_get_access_token() {
		// Get post data
		$formId       = isset( $_POST['awbc_form_id'] ) ? sanitize_text_field( $_POST['awbc_form_id'] ) : null;
		$codeVerifier = isset( $_POST['awbc_code_verifier'] ) ? sanitize_text_field( $_POST['awbc_code_verifier'] ) : null;
		$authCode     = isset( $_POST['awbc_auth_code'] ) ? sanitize_text_field( $_POST['awbc_auth_code'] ) : null;

		// Set required params
		$params = array(
			"grant_type"    => "authorization_code",
			"code"          => $authCode,
			"client_id"     => $this->clientId,
			"code_verifier" => $codeVerifier,
		);

		// Send request
		$response = Requests::post( 'https://auth.aweber.com/oauth2/token?' . http_build_query( $params ), array( 'timeout' => 30000 ) );

		// Send error response
		if ( ! $response->success ) {
			error_log( json_encode( $response->body ) );
			wp_send_json_error( __( 'Invalid authorization code.', 'awb-cf7' ), $response->status_code );
		}

		// Prepare token data
		$tokenData = array_merge( json_decode( $response->body, true ), array( 'created_at' => current_time( 'Y-m-d H:i:s' ) ) );

		// Add token data to options
		add_option( 'awbc_' . $formId, $tokenData, '', false );

		// Send success response
		wp_send_json_success( __( 'Access token received.', 'awb-cf7' ), 200 );
	}

	/**
	 * Check token expiry and get refreshed token from AWeber API
	 *
	 * @param $formId int Contact form 7 id
	 *
	 * @since 1.0.0
	 */
	public static function awbc_refresh_token( $formId ) {
		// Change timezon to local wp timezone for date comparison
		date_default_timezone_set( get_option( 'timezone_string' ) );

		// Get options
		$options = get_option( 'awbc_' . $formId );

		// Check if token is created before 2 hours ago
		if ( strtotime( $options['created_at'] ) <= strtotime( '-2 hours' ) ) {

			// Check if options are set
			if ( $options && isset( $options['refresh_token'] ) && isset( $options['created_at'] ) ) {
				// Set required params
				$params = array(
					"grant_type"    => "refresh_token",
					"client_id"     => '5WPjA28THHVXbS8htwxtrP1QNqOFAO8d',
					"refresh_token" => $options['refresh_token'],
				);

				// Send request to refresh token
				$response = Requests::post( 'https://auth.aweber.com/oauth2/token?' . http_build_query( $params ), array( 'timeout' => 30000 ) );

				// Log error
				if ( ! $response->success ) {
					error_log( 'Unable to refresh access token.' );
				}

				// Prepare token data
				$updatedTokenData = array_merge(
					json_decode( $response->body, true ), array( 'created_at' => current_time( 'Y-m-d H:i:s' ) )
				);

				// Update token data in options
				update_option( 'awbc_' . $formId, array_merge( $options, $updatedTokenData ), false );

			}
		}
	}

	/**
	 * Revoke authorization
	 *
	 * @since 1.0.0
	 */
	public function awbc_revoke_auth() {
		// Get post data
		$formId = isset( $_POST['awbc_form_id'] ) ? sanitize_text_field( $_POST['awbc_form_id'] ) : null;

		// Delete options associated with form
		delete_option( 'awbc_' . $formId );

		// Send success response
		wp_send_json_success( __( 'Authorization revoked.', 'awb-cf7' ), 200 );
	}
}
