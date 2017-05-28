<?php
include("../connection.php");

$dataReceived = json_decode(file_get_contents('php://input'), true);
//var_dump($dataReceived);

if((json_last_error() == JSON_ERROR_NONE))
{
   //call from iOS
	$pickup_latitude = $dataReceived['pickup_latitude'];
	$pickup_longitude = $dataReceived['pickup_longitude'];
	$pickup_address = $dataReceived['pickup_address'];
	$total_packages = $dataReceived['total_packages'];
	$total_weight = $dataReceived['total_weight'];
	$drop_address = $dataReceived['drop_address'];
	$drop_latitude = $dataReceived['drop_latitude'];
	$drop_longitude = $dataReceived['drop_longitude'];
	$destination_distance = $dataReceived['destination_distance'];
	$destination_duration = $dataReceived['destination_duration'];
	$cost_estimate = $dataReceived['cost_estimate'];
	$type = $dataReceived['type'];
	$id = $dataReceived['id'];
	$token = $dataReceived['token'];
}
else
{
   //call from Android
	$pickup_latitude = $_POST['pickup_latitude'];
	$pickup_longitude = $_POST['pickup_longitude'];
	$pickup_address = $_POST['pickup_address'];
	$total_packages = $_POST['total_packages'];
	$total_weight = $_POST['total_weight'];
	$drop_address = $_POST['drop_address'];
	$drop_latitude = $_POST['drop_latitude'];
	$drop_longitude = $_POST['drop_longitude'];
	$destination_distance = $_POST['destination_distance'];
	$destination_duration = $_POST['destination_duration'];
	$cost_estimate = $_POST['cost_estimate'];
	$type = $_POST['type'];
	$id = $_POST['id'];
	$token = $_POST['token'];
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
			mysql_query("DELETE FROM order_buffer_table WHERE customer_id = '$id'");
			//the work of this page/////////////////////
			//Add order to buffer
			mysql_query("INSERT INTO order_buffer_table
										(
											  customer_id
											, pickup_latitude
											, pickup_longitude
											, pickup_address
											, total_packages
											, total_weight
											, drop_address
											, drop_latitude
											, drop_longitude
											, destination_distance
											, destination_duration
											, cost_estimate
											, status
											, type
										)
										VALUES
										(
											  '$id'
											, '$pickup_latitude'
											, '$pickup_longitude'
											, '$pickup_address'
											, '$total_packages'
											, '$total_weight'
											, '$drop_address'
											, '$drop_latitude'
											, '$drop_longitude'
											, '$destination_distance'
											, '$destination_duration'
											, '$cost_estimate'
											, 'O'
											, '$type'
										)");

			if(mysql_errno() == 0)
			{
				$result = mysql_query("SELECT id,
				   ( 3959 * acos( cos( radians('$pickup_latitude') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('$pickup_longitude') ) + sin( radians('$pickup_latitude') ) * sin( radians( latitude ) ) ) ) AS distance
				   FROM provider_table
				   WHERE status > 1
							AND type = '$type'
							AND availibility = 'A'
							AND timestamp > date_sub(now(), interval 300 second)
				   HAVING distance < 10 ORDER BY distance LIMIT 0 , 20");

				if(mysql_errno() == 0)
				{
					if(mysql_num_rows($result) < 1)
					{
						mysql_query("UPDATE order_buffer_table SET status = 'C'
																		WHERE customer_id = '$id'");
					}
					$message = "Success";
					$responseData = array('message' => $message, 'providers_count' => mysql_num_rows($result));
				}
				else
				{
					$message = "Fail";
					$responseData = array('message' => $message, 'error' => mysql_errno());
				}
			}
			else
			{
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
