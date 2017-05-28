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
			/*
			$result = mysql_query("SELECT TIMESTAMPDIFF(minute, pickup_datetime, deliver_datetime)
													, real_pickup_latitude
													, real_pickup_longitude
													, real_drop_latitude
													, real_drop_longitude
													, pickup_latitude
													, pickup_longitude
													, drop_latitude
													, drop_longitude
													, require_verification
													, total_packages
													FROM order_table
													WHERE id = '$order_id'");
			if(mysql_errno() == 0 && mysql_num_rows($result))
			{
				$row = mysql_fetch_array($result);

				$time = $row[0];
				$real_pickup_latitude = $row[1];
				$real_pickup_longitude = $row[2];
				$real_drop_latitude = $row[3];
				$real_drop_longitude = $row[4];
				$pickup_latitude = $row[5];
				$pickup_longitude = $row[6];
				$drop_latitude = $row[7];
				$drop_longitude = $row[8];
				$require_verification = $row[9];
				$total_packages = $row[10];

				$message = "Success";
				$responseData = array('message' => $message
											, 'time' => $time
											, 'real_pickup_latitude' => $real_pickup_latitude
											, 'real_pickup_longitude' => $real_pickup_longitude
											, 'real_drop_latitude' => $real_drop_latitude
											, 'real_drop_longitude' => $real_drop_longitude
											, 'pickup_latitude' => $pickup_latitude
											, 'pickup_longitude' => $pickup_longitude
											, 'drop_latitude' => $drop_latitude
											, 'drop_longitude' => $drop_longitude
											, 'require_verification' => $require_verification
											, 'total_packages' => $total_packages
											);
				*/

				$result = mysql_query("SELECT   cost_estimate
														, destination_distance
														, destination_duration
														, require_verification
														FROM order_table
														WHERE id = '$order_id'");
				if(mysql_errno() == 0 && mysql_num_rows($result))
				{
					$row = mysql_fetch_array($result);

					$cost_estimate = $row[0];
					$destination_distance = $row[1];
					$destination_duration = $row[2];
					$require_verification = $row[3];					

					$message = "Success";
					$responseData = array('message' => $message
												, 'cost_estimate' => $cost_estimate
												, 'destination_distance' => $destination_distance
												, 'destination_duration' => $destination_duration
												, 'require_verification' => $require_verification												);
			}
			else
			{
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
