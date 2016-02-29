<?php

//
/* Your tweets are shit. */
/* Follow me to the graveyard */
/* Now, let's go find some boring tweets */
//
// 
//



//Hello TumbleBot
require_once('classes/TumbleBot.php');
$weed = new TumbleBot();

//tweet something
//$result = $weed->make_stupid_comment("Last");



//like something
//$result = $weed->sarcastic_thumbs_up(701541638968049666);

//retweet
//$result = $weed->shame_that_tweet(701541638968049666);

//reply with comment
//$result = $weed->shame_that_tweet(701541638968049666, "BOOOOOORED");



//follow a boring user
$result = $weed->follow_a_boring_user();


print_r($result);



?>


