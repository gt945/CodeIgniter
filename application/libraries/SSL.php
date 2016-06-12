<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SSL {
	
	public function gen()
	{
		$config = array(
				"digest_alg" => "sha512",
				"private_key_bits" => 2048,
				"private_key_type" => OPENSSL_KEYTYPE_RSA,
		);
		 
		// Create the private and public key
		$res = openssl_pkey_new($config);
		
		// Extract the private key from $res to $privKey
		openssl_pkey_export($res, $privKey);
		
		// Extract the public key from $res to $pubKey
		$pubKey = openssl_pkey_get_details($res);
		$pubKey = $pubKey["key"];
		return array("priv" => $privKey, "pub" => $pubKey);
		
	}
	
}