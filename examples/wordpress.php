<?php
/**
 * Template Name: Discourse SSO
 * Author: Adam Capriola
 * Version: 1.0
 * Author URI: https://meta.discourse.org/users/AdamCapriola/activity
 * Adapted From: https://github.com/ArmedGuy/discourse_sso_php
 *
 */

// Customize these two variables
$sso_secret = 'meow';
$discourse_url = 'http://discourse.example.com';

//
// Check if user is logged in to WordPress
//

// Not logged in to WordPress, redirect to WordPress login page with redirect back to SSO
if ( !is_user_logged_in() ) {

	// Build SSO redirect URL – Must redirect back here first since wp_login_url won't redirect to subdomain
	$redirect = add_query_arg( 'session', true, get_page_link() );

	// Build login URL
	$login = wp_login_url( $redirect );

	// Redirect to login
	wp_redirect( $login );
	exit;

}

// Logged in to WordPress, now try to log in to Discourse with WordPress user information
else {

	// Redirect back to /session/sso if we just logged in to WordPress
	if ( $_GET['session'] == true ) {

		wp_redirect( $discourse_url . '/session/sso' ); // your Discourse install
		exit;

	}

	// Validate signature
	$sso = new Discourse_SSO( $sso_secret ); // sso_secret

	$payload = $_GET['sso'];
	$sig = $_GET['sig'];

	if ( !( $sso->validate( $payload, $sig ) ) ) {

		echo( 'Invalid Request' );
		exit;

	}

	// Create nonce    
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

	// Redirect to Discourse login
	wp_redirect( $discourse_url . '/session/sso_login?' . $q ); // your Discourse install
	exit;

}
