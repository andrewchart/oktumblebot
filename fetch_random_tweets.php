<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<title></title>
	<link rel="stylesheet" href="styles.css" />
</head>
<body>

<?php 


//
// ABOUT ME: 
/* Run me to query the twitter streaming API for a random selection of all tweets */
/* Then processes the stream file to find relevant tweets to add to the database ready for disparaging... */
//
//
//


//Database connection
require_once('database_connection.php');

//Create an authentication context
require_once('classes/TumbleBot.php');
$weed = new TumbleBot();

//Spend a little time, get a few tweets
$t = 10;
$weed->collect_boring_tweets($t);
echo "Collected tweets for $t seconds. Yum yum.<br>";

//Read the tweets into the array
$tweets = $weed->read_tweets_from_file("stream.json");
echo count($tweets) . " tweets collected.<br>";

//Print the tweets, highlighting sensitive ones (for improving the sensitivity algo)
$s_words = $weed->get_sensitive_words();
foreach($tweets as $tweet) {
	echo $weed->touchy_touchy($tweet, $s_words, true);
}

//Filter the tweets, removing RTs, replies and sensitive content etc.
$tweet_ids = $weed->cleanse_tweets($tweets);

//Write the possibly-acceptable tweets to the tumblebot_tweets table
$sql = "INSERT INTO tumblebot_tweets (tweet_id) VALUES (" . implode("),(", $tweet_ids) . ")";
$result = mysqli_query($conn,$sql);

?>



</body>
</html>