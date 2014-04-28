# Single-sign-on for Discourse via PHP
This is a small class to help with providing an SSO source for Discourse forums.
It provides 3 help functions for validating incoming requests, extracting nonce, and building the returning queryString.

For more information on the SSO settings in Discourse, visit <https://meta.discourse.org/t/official-single-sign-on-for-discourse/13045>



### How to use the class


Notes:

 - The sso_secret can be anything. Make it long and make sure it's the same on Discourse and your site.
 - The `$user_id` should be your database's unique column in your user table.
 - If you aren't using PDO, you should be. But that's out of scope here ;)
 - This repository has examples for some common applications here: [examples/](examples/)

Here is a template for using the class:

```php
<?php
include 'discourse_sso.php';
$sso = new Discourse_SSO("-your-sso_secret-goes-here-");

$payload = $_GET['sso'];
$sig = $_GET['sig'];
if (!($sso->validate($payload,$sig))) {
    // invaild, deny
    header("HTTP/1.1 403 Forbidden");
    echo("Bad SSO request");
    die();
}

$nonce = $sso->getNonce($payload);

/*
 * Insert your user authentication code here.
 */

// BEGIN your site's auth code

// ...

// Fill these variables in from your DB

$user_id = ...; // Remember - this can be *anything*, as long as you keep it consistent!
$email = ...;
// These two are optional - feel free to delete them from the array() below
// If you do, Discourse will generate suggestions from the provided email.
$suggested_username = ...;
$suggested_full_name = ...;

// END your auth code

$userparams = array(
    "nonce" => $nonce,
    "external_id" => $user_id,
    "email" => $email,

    // Optional - feel free to delete these two
    "username" => $suggested_username,
    "name" => $suggested_full_name
);
$q = $sso->buildLoginString($userparams);
header('Location: http://discourse.example.com/session/sso_login?' . $q);

exit(0);

?>
```
