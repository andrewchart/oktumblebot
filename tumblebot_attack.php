<?php

//
// ABOUT ME: 
/* Run me to get the tumblebot to post a tweet... */
//
//
//

//Database
include_once('database_connection.php');

//Create an authentication context
require_once('classes/TumbleBot.php');
$weed = new TumbleBot();


/*Start with a little housekeeping*/

//Delete tweets added more than 3 days ago


//Check all tweets with 0 interactions for interactions 


//Remove tweets that have had interactions




//Get a tweet randomly that is more than 24 hours old and has no interactions




//Randomly determine an action and execute
$action = $weed->get_tumblebot_action();





?>