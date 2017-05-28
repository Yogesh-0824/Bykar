<?php
include("../connection.php");

$dataReceived = json_decode(file_get_contents('php://input'), true);
//var_dump($dataReceived);

if((json_last_error() == JSON_ERROR_NONE))
{
   //call from iOS
	$id = $dataReceived['id'];
}
else
{
   //call from Android
	$id = $_POST['id'];
}

$result = mysql_query("SELECT status FROM provider_table
								WHERE id = '$id'");
if(mysql_num_rows($result) == 1)
{
	$row = mysql_fetch_array($result);
		if($row[0] > 1)
		{
			//the work of this page/////////////////////
			$message = "Success";
			$responseData = array('message' => $message);
			////////////////////////////////////////////
		}
		else if($row[0] == 1)
		{
			//the work of this page/////////////////////
			$message = "Pending";
			$responseData = array('message' => $message);
			////////////////////////////////////////////
		}
		else
		{
			$message = "Inactive Account";
			$responseData = array('message' => $message);
		}
}
else
{
	$message = "Invalid Account";
	$responseData = array('message' => $message);
}

header('Content-type: application/json');
 echo json_encode( $responseData );
?>
