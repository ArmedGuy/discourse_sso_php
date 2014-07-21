<?php
/**
 * Template Name: Discourse SSO
 * Author: Adam Capriola
 * Version: 1.1
 * Author URI: https://meta.discourse.org/users/AdamCapriola/activity
 * Adapted From: https://github.com/ArmedGuy/discourse_sso_php
 * Uses: https://meta.discourse.org/t/official-single-sign-on-for-discourse/13045
 *
 */

// Customize these two variables
$sso_secret = 'meow';
$discourse_url = 'http://discourse.example.com'; // Note: No trailing slash!

//
// Check if user is logged in to WordPress
//

// Not logged in to WordPress, redirect to WordPress login page with redirect back to here
if ( ! is_user_logged_in() ) {

	// Preserve sso and sig parameters
	$redirect = add_query_arg();

	// Build login URL
	$login = wp_login_url( $redirect );

	// Redirect to login
	wp_redirect( $login );
	exit;

}

// Logged in to WordPress, now try to log in to Discourse with WordPress user information
else {

	// Payload and signature
	$payload = $_GET['sso'];
	$sig = $_GET['sig'];

	// Make sure %0A is at the end of the payload
	if ( substr( urlencode( $payload ), -3 ) != '%0A' ) {

		$payload = urldecode( $payload . '%0A' );

	}
	
	// Check for helper class
	if ( ! class_exists( 'Discourse_SSO' ) ) {

		// Error message
		echo( 'Helper class is not properly included.' );

		// Terminate
		exit;

	}

	// Validate signature
	$sso = new Discourse_SSO( $sso_secret );

	if ( ! ( $sso->validate( $payload, $sig ) ) ) {
		
		// Error message
		echo( 'Invalid request.' );
		
		// Terminate
		exit;

	}

	// Nonce    
	$nonce = $sso->getNonce( $payload );

	// Current user info
	get_currentuserinfo();

	// Map information
	$params = array(
		'nonce' => $nonce,
		'name' => $current_user->display_name,
		'username' => $current_user->user_login,
		'email' => $current_user->user_email,
		'about_me' => $current_user->description,
		'external_id' => $current_user->ID
	);

	// Build login string
	$q = $sso->buildLoginString( $params );

	// Redirect back to Discourse
	wp_redirect( $discourse_url . '/session/sso_login?' . $q );
	exit;

}
