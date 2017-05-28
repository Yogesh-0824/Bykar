<?php
include("../connection.php");

$dataReceived = json_decode(file_get_contents('php://input'), true);
//var_dump($dataReceived);

if((json_last_error() == JSON_ERROR_NONE))
{
   //call from iOS
	$id = $dataReceived['id'];
	$token = $dataReceived['token'];
	$provider_id = $dataReceived['provider_id'];
}
else
{
   //call from Android
   $id = $_POST['id'];
	$token = $_POST['token'];
	$provider_id = $_POST['provider_id'];
}

$result = mysql_query("SELECT token, status FROM customer_table
								WHERE id = '$id'");
if(mysql_num_rows($result) == 1)
{
	$row = mysql_fetch_array($result);
	if($row[0] == $token)
	{
		if($row[1] > 0)
		{
			//the work of this page/////////////////////
			$results = mysql_query("SELECT
													  first_name
													, last_name
													, mobile_number
													, registration_number
													, make
													, model
													, color
													, latitude
													, longitude
													, type
													, thumb_link
													, vehicle_thumb_link
			                              FROM provider_table
													WHERE id = '$provider_id'");

			if(mysql_errno() == 0)
			{
			   $message = "Success";

			   $row = mysql_fetch_array($results);

				$first_name = $row[0];
				$last_name = $row[1];
				$mobile_number = $row[2];
				$registration_number = $row[3];
				$make = $row[4];
				$model = $row[5];
				$color = $row[6];
				$latitude = $row[7];
				$longitude = $row[8];
				$provider_type = $row[9];
				$thumb_link = $row[10];
				$vehicle_thumb_link = $row[11];

			   $responseData = array('message' => $message

											,'first_name' => $first_name
											,'last_name' => $last_name
											,'mobile_number' => $mobile_number
											,'registration_number' => $registration_number
											,'make' => $make
											,'model' => $model
											,'color' => $color
											,'latitude' => $latitude
											,'longitude' => $longitude
											,'provider_type' => $provider_type
											,'thumb_link' => $thumb_link
											,'vehicle_thumb_link' => $vehicle_thumb_link
											);
			}
			else {
			   $message = "Fail";
			   $responseData = array('message' => $message);
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
