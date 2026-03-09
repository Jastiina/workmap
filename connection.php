<?php

$servername = "localhost";
$username = "root"; 
$password = "";
$dbname = "managementsystem";

$conn = mysqli_connect ($servername, $username, $password, $dbname);

if (! $conn){
    die ("Oops! Error: ".mysqli_connect_error());
}

// echo "DONE";

?>
