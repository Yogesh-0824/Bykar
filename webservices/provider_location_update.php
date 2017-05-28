<?php
include("../connection.php");

$dataReceived = json_decode(file_get_contents('php://input'), true);
//var_dump($dataReceived);

if((json_last_error() == JSON_ERROR_NONE))
{
   //call from iOS
	$id = $dataReceived['id'];
	$token = $dataReceived['token'];
	$latitude = $dataReceived['latitude'];
	$longitude = $dataReceived['longitude'];
}
else
{
   //call from Android
	$id = $_POST['id'];
	$token = $_POST['token'];
	$latitude = $_POST['latitude'];
	$longitude = $_POST['longitude'];
}

/*
//calculate the direction of travel and store it in form of angle to rotate the marker
$base = 100;
$perpendicular = 100;
echo rad2deg(atan($perpendicular/$base)); // 45
*/
mysql_query("UPDATE provider_table
							SET latitude = '$latitude', longitude = '$longitude'
							WHERE id='$id' AND token = '$token'");

if(mysql_errno() == 0)
{
	$message = "Success";
}
else
{
	$message = "Fail";
}

$responseData = array('message' => $message);

header('Content-type: application/json');
 echo json_encode( $responseData );

?>
