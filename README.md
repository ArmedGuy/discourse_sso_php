# Discourse Single-Sign-On Helper for PHP

This is a small class to help with providing an SSO source for Discourse forums. It provides 3 helper functions for validating incoming requests, extracting nonce, and building the returning query string.

For more information on the SSO settings in Discourse, visit <https://meta.discourse.org/t/official-single-sign-on-for-discourse/13045>

> Original code from Johan Jatko: https://github.com/ArmedGuy/discourse_sso_php


## Installation

```
composer require "cviebrock/discourse-sso-helper"
```


## Usage

```php

$sso = new DiscourseSSOHelper();

// this should be the same in your code and in your Discourse settings:
$secret = 'super_secret_sso_key';
$sso->setSecret( $secret );

// load the payload passed in by Discourse
$payload = $_GET['sso'];
$signature = $_GET['sig'];

// validate the payload
if (!($sso->validate($payload,$signature))) {
    // invaild, deny
    header("HTTP/1.1 403 Forbidden");
    echo("Bad SSO request");
    die();
}

$nonce = $sso->getNonce($payload);

// Insert your user authentication code here ...


$userParameters = array(
    "nonce" => $nonce,

    // required - this needs to be unique to your application
    "external_id" => $userId,

    // required
    "email" => $email,

    // optional - if you don't set these, Discourse will generate suggestions
    // based on the email address
    "username" => $suggested_username,
    "name" => $suggested_full_name
);


// build query string and redirect back to the Discourse site
$query = $sso->buildLoginString($userParameters);
header('Location: http://discourse.example.com/session/sso_login?' . $q);
exit(0);
```
