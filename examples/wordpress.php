<?php
/**
 * Template Name: Discourse SSO
 * Author: Adam Capriola
 * Version: 1.0
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

	// Add fresh parameter onto redirect back here
	$redirect = add_query_arg( 'fresh', true );

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

	// wp_sanitize_redirect strips %A0 from payload so we need to add it back if we are freshly logged in
	if ( $_GET['fresh'] == true ) {

		$payload = urldecode( $payload . '%0A' );

	}

	// Validate signature
	$sso = new Discourse_SSO( $sso_secret );

	if ( ! ( $sso->validate( $payload, $sig ) ) ) {

		echo( 'Invalid Request' );
		exit;

	}

	// Nonce    
	$nonce = $sso->getNonce( $payload );

	// User information
	get_currentuserinfo();

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
