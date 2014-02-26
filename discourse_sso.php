<?php
class Discourse_SSO {
	private $sso_secret;
	private $nonce;
	
	function __construct($secret) {
		$this->sso_secret = $secret;
	}
	
	public function validate($payload, $sig) {
		if(hash_hmac("sha256", $payload, $this->sso_secret) === $sig) {
			$query = array();
			parse_str(base64_decode($payload), &$query);
			
			$this->nonce = $query["nonce"];
			return true;
		} else {
			return false;
		}
	}
	
	public function loginstring($params) {
		$params['nonce'] = $this->nonce;
		$payload = http_build_query($params);
		$payload = base64_encode($payload);
		$sig = hash_hmac("sha256", $payload, $this->sso_secret);
		
		return http_build_query(array("sso" => $payload, "sig" => $sig));
	}
}