<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://darpankulkarni.in
 * @since      1.0.0
 *
 * @package    Awb_Cf7
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Get all contact forms
$contactForms = get_posts( array(
	'post_type'   => 'wpcf7_contact_form',
	'numberposts' => - 1
) );

// Delete all plugin options associated with form
foreach ( $contactForms as $cf ) {
	if ( get_option( 'awbc_' . $cf->ID ) ) {
		delete_option( 'awbc_' . $cf->ID );
	}
}