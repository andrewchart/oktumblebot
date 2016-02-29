<html>
<head>
	<meta charset="UTF-8" />
</head>
<body>
<?php 


//
// ABOUT ME: 
/* Queries the twitter streaming API to get a random selection of all tweets */
/* Adds relevant tweets to the database ready for disparaging... */
//
//
//


//Create an authentication context
require_once('classes/TwitterAPIs.php');
$tw = new OAuthTwitterAPI();

//Execute the request
$endpoint = "https://stream.twitter.com/1.1/statuses/sample.json";
$fields = array(
	'language' => 'en'
);
$response = $tw->post_request($endpoint, $fields);




?>
</body>
</html>