<?php

//
// ABOUT ME: 
/* Core functions of the TumbleBot - e.g. like, retweet, comment and create new tweets */
//
//
//




class TumbleBot
{

	/* 1. TUMBLEBOT TWEETING ACTIONS */

	// 1a -- Like tweet
	public function sarcastic_thumbs_up($tweetID) {
		
		$tw = $this->twitter("oauth");
		$endpoint = "https://api.twitter.com/1.1/favorites/create.json";
		$fields = array(
			'id' => $tweetID
		);
		
		return $tw->post_request($endpoint, $fields);
	}
	
	
	// 1b -- Retweet
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
	
	
	// 1c -- Make a sarky comment
	public function make_stupid_comment($comment) {

		$tw = $this->twitter("oauth");
		$endpoint = "https://api.twitter.com/1.1/statuses/update.json";
		$fields = array(
			'status' => $comment,
		);
		
		return $tw->post_request($endpoint, $fields);
	 
	}
	

	// 1d -- Follow a boring user
	public function follow_really_boring_people($userID) {
		
		$tw = $this->twitter("oauth");
		$endpoint = "https://api.twitter.com/1.1/friendships/create.json";
		$fields = array(
			'user_id' => $userID
		);
		
		return $tw->post_request($endpoint, $fields);
		
	}
	
	// 1e -- Get username from tweet ID
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
		
	
	// 1f -- Randomly choose an action for tumblebot to execute
	public function get_tumblebot_action() {


		//Weight the possible actions
		$actions = array(
			'tweetonly' => 8,
			'like' => 18,
			'retweet' => 12,
			'reply' => 24,
			//'follow' => 5, //TODO: Follows should include a followcomment
			'like_retweet' => 8,
			'like_reply' => 15,
			//'like_follow' => 3,
			'retweet_reply' => 0, //Not much point doing this
			//'retweet_follow' => 3,
			//'like_retweet_follow' => 1, //TODO: make a 'jackpot' criteria to do all 3 of these
			'like_reply_follow' => 0 //Not much point replying and followcommenting
		);
		
		
		//Turn this into a biased array
		$weightedActions = array();
		foreach($actions as $action => $weighting) {
			while($weighting > 0) {
				array_push($weightedActions, $action);
				$weighting--;
			}
		}
		
		//Pick a random action and return it
		$rand = rand(0,array_sum($actions)-1);
		shuffle($weightedActions);
		return $weightedActions[$rand];
		
	}
 
	
	
	/* 2. FETCHING TWEETS YUM YUM */
	
	// 2a -- Get some tweets from the public stream
	public function collect_boring_tweets($streamtime) {
	
		$tw = $this->twitter("oauth");
		$endpoint = "https://stream.twitter.com/1.1/statuses/sample.json";
		$fields = array(
			'language' => 'en',
			'delimit' => 'length'
		);
		
		return $tw->stream_request($endpoint, "POST", $fields, $streamtime);
		
	}
	
	
	// 2b -- Read tweets from a file into an array
	public function read_tweets_from_file($filename) {
		
		if(file_exists($filename)) {
			
			$str = file_get_contents($filename);
			$arr = explode("\r\n", $str);
			
			$tweets = array();
			
			foreach($arr AS $line) {
			
				$tweet = json_decode($line);
			
				if(!is_null($tweet)){
					$tweets[] = $tweet;
				} 
				
			}
			
			return $tweets;
			
			return $arr;
			
			
		} else { 
			return "File not found"; 
		}
		
	}
	
	
	/* 3. CLEANSE TWEETS */
	/* Functions for cleansing tweets before adding them to the database */
	
	// 3a -- Process an array of tweet objects, return a new array of appropriate tweet IDs
	public function cleanse_tweets($tweets) {
	
		$ids = array();
		$s_words = $this->get_sensitive_words();
	
		foreach($tweets AS $tweet) {
		
		
			//Reject replies and retweets
			if( $this->is_reply($tweet) || $this->is_retweet($tweet) ) continue;
			
			//Reject tweets which already have engagement
			if( $this->has_engagement($tweet) ) continue;
			
			//Reject non english
			if( ! $this->is_english($tweet) ) continue;
			
			//Reject tweets with potentially sensitive content
			if( $this->touchy_touchy($tweet, $s_words) ) continue;
			
		
			//All good? Add ID to array
			//echo $tweet->text . "<br>";
			array_push($ids, $tweet->id_str);
		
		}
		
		return $ids;
		
	}
	
	
	// 3b -- Tweet is a reply
	private function is_reply($tweet) {
		if(is_null($tweet->in_reply_to_status_id_str)) return false;
		if($tweet->is_quote_status) return false;
		return true;	
	}
	
	// 3c -- Tweet is a retweet
	private function is_retweet($tweet) {
		return isset($tweet->retweeted_status);
	}
	
	// 3d -- Tweet is in english
	private function is_english($tweet) {
		return $tweet->lang == "en";
	}
	
	// 3e -- Tweet has likes, replies or retweets
	private function has_engagement($tweet) {
		return false;
	}
	
	// 3f -- Get the sensitive words from a file
	public function get_sensitive_words() {
		return explode("\n", file_get_contents('sensitive.txt'));
	}
	
	//3g -- Tweet contains sensitive words
	public function touchy_touchy($tweet, $sensitive_words, $print=false) {

		$t_words = explode(" ", $tweet->text); //nooope

		foreach($t_words as $t_word) {
			if(in_array($t_word, $sensitive_words)) { //actually needs to be a regex match..... 
				
				if($print) {
					return '<p class="sensitive">' . $tweet->text . '</p>';
				} 
				
				else {
					return true;
				}
			}
		}
		
		
		if($print){ 
			return '<p>' . $tweet->text . '</p>';
		}
		
		else {
			return false;
		}
		
	}



	/* 4. GENERAL */
	
	// 4a -- Greetings
	public function say_hello_tumblebot() {
		return "Hello, I'm Tumblebot. Now fuck off.";
	}
		
	
	// 4b -- Create a twitter OAuth Authentication Context
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


}








//
function notifyAuthor() {
	
	$msg = "You are tweeting to nobody";
}

//
function followReallyBoringPeople() {

	$msg = "I have saved your account in anticipation of yet more terrible tweets";
}






?>