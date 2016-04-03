<?php 

/*
 * About Me:
 * GENERIC CONNECTION TO THE MYSQL DATABASE
 *
**/

$servername = "localhost";
$database = "oktumblebot";
$username = "application";
$password = "SUvexpL52KYv38VS";

// Create connection
$conn = new mysqli($servername, $username, $password, $database);
mysqli_set_charset($conn, "utf8");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}




/*
 * About Me:
 * CHECK FOR EXISTENCE OF REQUIRED TABLES.
 * CREATE THE TABLES IF THEY DON'T EXIST.
 *
**/


// Required Table Specifications Array 
$tables = array(
	array(
		"tableName" => "tumblebot_tweets", 
		"tableColumnsSql" => "tweet_id VARCHAR(30), interactions MEDIUMINT DEFAULT '0', date_added TIMESTAMP DEFAULT CURRENT_TIMESTAMP"
	)
);

// Loop through the tables in the spec, checking and creating where necessary
foreach($tables AS $table) {

	//Check whether table exists
	$table_name = $table["tableName"];
	$sql = "SELECT * FROM information_schema.tables WHERE table_schema = '$database' AND table_name = '$table_name' LIMIT 1;";
	$result = mysqli_fetch_array(mysqli_query($conn,$sql));

	//Create it if not
	if (count($result) == 0) {
		$sql = "CREATE TABLE $table_name (id INT(8) UNSIGNED AUTO_INCREMENT PRIMARY KEY, " . $table["tableColumnsSql"] . ");";
		//echo $sql;
		mysqli_query($conn,$sql);
		echo mysqli_error($conn);
	}
	
}

?>