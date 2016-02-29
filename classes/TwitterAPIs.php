<?php

//
// ABOUT ME: 
/* Creates Oauth authentication contexts for querying the twitter APIs */
//
//
//


/* Full OAuth 1.0 Authorisation */
class OAuthTwitterAPI
{

	/* Construct a Nonce */
	private function get_nonce() {
		return substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 1).substr(md5(time()),1);
	}

	/* Parse the endpoint */
	private function endpoint_base($ep){
		$parts = parse_url($ep);

		if(isset($parts['scheme']) && isset($parts['host']) && isset($parts['path'])) {
			
			return $parts['scheme'] 
				. "://" 
				. $parts['host'] 
				. $parts['path'];
				
		} else {
			return null;
		}
	}
	
	private function endpoint_querystring($ep){
	
		if(isset(parse_url($ep)['query'])) {
			return parse_url($ep)['query']; 
		} else {
			return null;
		}
	
	}
	

	/* Get the oauth signature */
	private function get_oauth_signature($endpoint, $request_type, $oauth_header_params, $fields) {
			
			
		//Create a new array from header_params
		$params_for_signing = $oauth_header_params;
				
				
		//Add the endpoint querystring to array for signing if necessary
		$ep_qst = $this::endpoint_querystring($endpoint);
		if(!is_null($ep_qst)) {
			parse_str($ep_qst, $qst_array);
			$params_for_signing = array_merge($params_for_signing, $qst_array);
		} 
		
		
		//Add the request body to array for signing if necessary
		if(is_array($fields)) {
			$params_for_signing = array_merge($params_for_signing, $fields);
		}
		
		elseif (!is_null($fields)) {
			parse_str($fields, $fields_array);
			$params_for_signing = array_merge($params_for_signing, $fields_array);
		} 
			
				
		//Order it alphabetically by key
		ksort($params_for_signing);
		
		
		//Stringify	and Url-encode the vars
		$param_str = "";
		$i = 0;
		$len = count($params_for_signing); 
		$param_str = http_build_query($params_for_signing, null, ini_get('arg_separator.output'), PHP_QUERY_RFC3986);

		
		// Create the signature base string
		$signature_base = $request_type 
			. "&" 
			. urlencode($this->endpoint_base($endpoint))
			. "&"
			. urlencode($param_str);
				
				
		// Create the signing key
		$signing_key = urlencode(CONSUMER_SECRET) . "&" . urlencode(ACCESS_TOKEN_SECRET);
	
		
		// Get the signature
		$signature = hash_hmac("sha1", $signature_base, $signing_key, true);
	
		
		// And return it
		return urlencode(base64_encode($signature));
		
		
	}
	
	
	
	/* Build the oauth header */
	private function build_oauth_header($endpoint, $request_type, $fields) {

	
		// Define constants for API Key & Secret
		require_once('private/credentials.php');

			
		// Basic parameters for the OAuth header
		$oauth_header_params = array(
			'oauth_consumer_key' => CONSUMER_KEY,
			'oauth_nonce' => $this->get_nonce(),
			'oauth_signature_method' => "HMAC-SHA1",
			'oauth_timestamp' => time(),
			'oauth_token' => ACCESS_TOKEN,
			'oauth_version' => "1.0"	
		);


		// Add an Oauth Signature
		$oauth_header_params['oauth_signature'] = $this->get_oauth_signature($endpoint, $request_type, $oauth_header_params, $fields);
		
		
		// Stringify
		$oauth_header_string = 'OAuth ';
		$i = 0;
		$len = count($oauth_header_params);
		foreach($oauth_header_params AS $key => $val) {
			
			$i++;
		
			$oauth_header_string .= $key . '="' . $val;
			
			if($i < $len) {
				$oauth_header_string .= '", ';
			} else {
				$oauth_header_string .= '"';
			}
			
		}



		// Return the header string
		return $oauth_header_string;
		
	}



	
	/* Main API Request function */
	private function make_request($endpoint, $type, $fields, $file) {
		
		// Build the full OAuth Header
		$oauth_header = $this->build_oauth_header($endpoint, $type, $fields);
		
		//Parse the fields 
		if(is_array($fields)) {
			$fields_to_pass = http_build_query($fields);
		} else {
			$fields_to_pass = $fields;
		}
		
		//Add GET parameters to endpoint
		if($type == "GET") {
			$endpoint .= "?" . $fields_to_pass; 
		}
		
		// cURL the endpoint
		$headers = array(
		    "Content-Type: application/x-www-form-urlencoded;charset=UTF-8",
		    "User-Agent: OK Tumblebot v1.0",
		    //"Accept-Encoding: gzip",
		    "Authorization: " . $oauth_header
		); 
		  		        
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $endpoint);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		
		//Add POST fields to headers
		if($type == "POST") {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_to_pass);
		}
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		/*if(!is_null($file)) {
			curl_setopt($ch, CURLOPT_FILE, $file);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		}*/
		
		$result = curl_exec($ch);
		curl_close($ch);
		
		return json_decode($result);
			
	}

	
	/* Public access to make requests */
	public function post_request($endpoint, $postfields = null, $file = null) {
		return $this->make_request($endpoint, "POST", $postfields, $file);
	}
	
	public function get_request($endpoint, $fields = null, $file = null) {
		return $this->make_request($endpoint, "GET", $fields, $file);
	}
	
}




/* Application only authorisation */
class TwitterSimpleAuth 
{
	function get_token() {
     
     	// Define constants for API Key & Secret
		require_once('private/credentials.php');
        $creds = base64_encode(urlencode(CONSUMER_KEY) . ":" . urlencode(CONSUMER_SECRET));
        
        
		// cURL the Oauth endpoint
        $endpoint = "https://api.twitter.com/oauth2/token";

  		$postfields = "grant_type=client_credentials";

        $headers = array(
            "Content-Type: application/x-www-form-urlencoded;charset=UTF-8", 
            "Content-Length: " . strlen($postfields),
            "User-Agent: OK Tumblebot v1.0",
            //"Accept-Encoding: gzip",
            "Authorization: Basic " . $creds
        ); 
                
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
  		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  		curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
  		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$result = curl_exec($ch);
		curl_close($ch);

		$response = json_decode($result);
		
		return $response->access_token;
       
    }
    
}



?>