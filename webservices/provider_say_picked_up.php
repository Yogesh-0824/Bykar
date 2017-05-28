<?php
include("../connection.php");

$dataReceived = json_decode(file_get_contents('php://input'), true);
//var_dump($dataReceived);


if((json_last_error() == JSON_ERROR_NONE))
{
   //call from iOS
	$id = $dataReceived['id'];
	$token = $dataReceived['token'];
	$order_id = $dataReceived['order_id'];
}
else
{
   //call from Android
	$id = $_POST['id'];
	$token = $_POST['token'];
	$order_id = $_POST['order_id'];
}

$result = mysql_query("SELECT token, status FROM provider_table
								WHERE id = '$id'");
if(mysql_num_rows($result) == 1)
{
	$row = mysql_fetch_array($result);
	if($row[0] == $token)
	{
		if($row[1] > 1)
		{
			//the work of this page/////////////////////
			$datetime = date ("Y-m-d H:i:s");

			$latitude = 0;
			$longitude = 0;

			$result = mysql_query("SELECT latitude, longitude FROM provider_table
														WHERE id = '$id'");
			if(mysql_errno() == 0)
			{
				$row = mysql_fetch_array($result);

				$latitude = $row[0];
				$longitude = $row[1];
			}

			mysql_query("UPDATE order_table SET pickup_datetime = '$datetime'
														, status = 2
														, real_pickup_latitude = '$latitude'
														, real_pickup_longitude = '$longitude'
										WHERE id = '$order_id'");
			if(mysql_errno() == 0 && mysql_affected_rows() == 1)
			{
				$message = "Success";
				$responseData = array('message' => $message);
			}
			else {
				$message = "Fail";
				$responseData = array('message' => $message, 'error' => mysql_errno());
			}
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
		$message = "Invalid Token";
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
