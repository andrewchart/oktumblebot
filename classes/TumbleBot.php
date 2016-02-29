<?php

//
// ABOUT ME: 
/* Core functions of the TumbleBot - e.g. like, retweet, comment and create new tweets */
//
//
//




class TumbleBot
{

	//Like tweet
	public function sarcastic_thumbs_up($tweetID) {
		
		$tw = $this->twitter("oauth");
		$endpoint = "https://api.twitter.com/1.1/favorites/create.json";
		$fields = array(
			'id' => $tweetID
		);
		
		return $tw->post_request($endpoint, $fields);
	}
	
	//Retweet
	public function shame_that_tweet($tweetID, $comment = null) {
		
		$tw = $this->twitter("oauth");
		
		if(is_null($comment)) {
		
			$endpoint = "https://api.twitter.com/1.1/statuses/retweet/" . $tweetID . ".json";
			$fields = null;
		
		} else {
		
			$username = $this->get_username_from_tweet_id($tweetID);
			$endpoint = "https://api.twitter.com/1.1/statuses/update.json";
			$fields = array(
				'status' => $comment . " https://twitter.com/$username/status/$tweetID"
			);
			
		}
		
		return $tw->post_request($endpoint, $fields);
		
	}
	
	//Make a sarky comment
	public function make_stupid_comment($comment) {

		$tw = $this->twitter("oauth");
		$endpoint = "https://api.twitter.com/1.1/statuses/update.json";
		$fields = array(
			'status' => $comment,
		);
		
		return $tw->post_request($endpoint, $fields);
	 
	}

	//Follow a boring user
	public function follow_really_boring_people($userID) {
	
	}
	
	
	//Greetings
	public function say_hello_tumblebot() {
		return "Hello, I'm Tumblebot. Now fuck off.";
	}
	
	
	
	//Create a twitter OAuth Authentication Context
	private function twitter($type) {
		
		require_once('classes/TwitterAPIs.php');
		
		if($type == "oauth") {
			$tw = new OAuthTwitterAPI;
		} 
		
		elseif($type == "simple") {
			$tw = new TwitterSimpleAuth;
		}
		
		else {
			$tw = null;
		}
		
		return $tw;
		
	}
	
	//Get username from tweet ID
	private function get_username_from_tweet_id($id) {
		
		$tw = $this->twitter("oauth");
		$endpoint = "https://api.twitter.com/1.1/statuses/show.json";
		$fields = array(
			'id' => $id
		);
		
		$result = $tw->get_request($endpoint, $fields);
		
		$userID = $result->user->screen_name;
		
		return $userID;
		
	}




}







//
function shameThatTweet(){

}


// Query the twitter streaming API for a random selection of tweets
function findShitTweets() {

}



//
function notifyAuthor() {
	
	$msg = "You are tweeting to nobody";
}

//
function followReallyBoringPeople() {

	$msg = "I have saved your account in anticipation of yet more terrible tweets";
}

//
function makeStupidComment() {

}




?>