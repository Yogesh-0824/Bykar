<?php session_start();

$hostname = "bykardb.db.11178633.hostedresource.com";
$username = "bykardb";
$dbname = "bykardb";
$password = "hareKrishna0#";

//connect to the database 
$connect = mysql_connect($hostname, $username, $password); 
    if (!$connect)
        {
            die('Could not connect: ' . mysql_error());
        }
    mysql_selectdb($dbname,$connect);
?>