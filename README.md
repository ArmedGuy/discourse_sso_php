# Single-sign-on for Discourse via PHP
This is a small class to help with providing an SSO source for Discourse forums.
It provides 3 help functions for validating incoming requests, extracting nonce, and building the returning queryString.

For more information on the SSO settings in Discourse, visit <https://meta.discourse.org/t/official-single-sign-on-for-discourse/13045>



### How to use the class


Simply include the file and make an instance of the class, providing the SSO secret defined in Discourse
```php
include 'discourse_sso.php';
$sso = new Discourse_SSO("-your-sso_secret-goes-here-");
```

To validate incoming logins, you can do:
```php
$payload = $_GET['sso'];
$sig = $_GET['sig'];
if($sso->validate($payload,$sig)) {
	// vaild
}
```


To extract the nonce(the little piece of data that identifies the login, read more in the above link), use:
```php
$nonce = $sso->getNonce($payload);
```


At last, to produce the query string that is to be sent back to discourse, do:
```php
$userparams = array(
	// Required, will throw exception otherwise
	"nonce" => $nonce,
	"external_id" => "some user id here",
	"email" => "some user email",
	// Optional
	"username" => "some username",
	"name" => "some real name"
);
$q = $sso->buildLoginString($userparams);

// To send the data back, do:
// header('Location: http://discourse.example.com/session/sso_login?' . $q);
// or similar
```