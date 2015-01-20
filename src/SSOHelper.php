<?php
namespace Cviebrock\DiscoursePHP;

use Cviebrock\DiscoursePHP\Exception\PayloadException;


class SSOHelper {

	/**
	 * @var
	 */
	protected $secret;

	/**
	 * @param $secret
	 */
	public function setSecret($secret) {
		$this->secret = $secret;
	}

	/**
	 * @param $payload
	 * @param $signature
	 * @return mixed
	 */
	public function validatePayload($payload, $signature) {
		$payload = urldecode($payload);

		return $this->signPayload($payload) === $signature;
	}

	/**
	 * @param $payload
	 * @return mixed
	 * @throws PayloadException
	 */
	public function getNonce($payload) {
		$payload = urldecode($payload);
		$query = array();
		parse_str(base64_decode($payload), $query);
		if (!array_key_exists('nonce', $query)) {
			throw new PayloadException('Nonce not found in payload');
		}

		return $query['nonce'];
	}

	/**
	 * @param $nonce
	 * @param $id
	 * @param $email
	 * @param array $extraParameters
	 * @return string
	 */
	public function getSignInString($nonce, $id, $email, $extraParameters = array()) {

		$parameters = array(
			'nonce' => $nonce,
			'external_id' => $id,
			'email' => $email
		);

		$parameters = array_merge($parameters, $extraParameters);

		$payload = base64_encode(http_build_query($parameters));

		$data = array(
			'sso' => $payload,
			'sig' => $this->signPayload($payload)
		);

		return http_build_query($data);
	}


	/**
	 * @param $payload
	 * @return string
	 */
	protected function signPayload($payload) {
		return hash_hmac('sha256', $payload, $this->secret);
	}

}
