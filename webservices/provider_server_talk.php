<?php
include("../connection.php");

$dataReceived = json_decode(file_get_contents('php://input'), true);
//var_dump($dataReceived);

if((json_last_error() == JSON_ERROR_NONE))
{
   //call from iOS
	$latitude = $dataReceived['latitude'];
	$longitude = $dataReceived['longitude'];
	$type = $dataReceived['type'];
	$id = $dataReceived['id'];
	$token = $dataReceived['token'];
}
else
{
   //call from Android
	$id = $_POST['id'];
	$token = $_POST['token'];
	$latitude = $_POST['latitude'];
	$longitude = $_POST['longitude'];
	$type = $_POST['type'];
}

/*
//calculate the direction of travel and store it in form of angle to rotate the marker
$base = 100;
$perpendicular = 100;
echo rad2deg(atan($perpendicular/$base)); // 45
*/

$update_enforcer = rand(1, 999);

mysql_query("UPDATE provider_table
							SET latitude = '$latitude', longitude = '$longitude', update_enforcer = '$update_enforcer'
							WHERE id='$id' AND token = '$token'");

if(mysql_errno() == 0)
{
	$message = "Success".$type;
	$job = "NO";
	$responseData = array('message' => $message);

	$result = mysql_query("SELECT   pickup_latitude
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
											, customer_id
											, TIMESTAMPDIFF(second, `timestamp`, NOW())
											, ( 3959 * acos( cos( radians('$latitude') ) * cos( radians( pickup_latitude ) ) * cos( radians( pickup_longitude ) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians( pickup_latitude ) ) ) ) AS distance
											 FROM order_buffer_table
											 WHERE status = 'O'
											 AND type = '$type'
											 AND timestamp > date_sub(now(), interval 180 second)
											 HAVING distance < 10
											 ORDER BY `timestamp` DESC
											 LIMIT 1 ");
	if(mysql_errno() == 0)
	{
		$test = mysql_num_rows($result);
		if(mysql_num_rows($result) > 0)
		{
			$job = "YES";
			$row = mysql_fetch_array($result);

			$pickup_latitude = $row[0];
			$pickup_longitude = $row[1];
			$pickup_address = $row[2];
			$total_packages = $row[3];
			$total_weight = $row[4];
			$drop_address = $row[5];
			$drop_latitude = $row[6];
			$drop_longitude = $row[7];
			$destination_distance = $row[8];
			$destination_duration = $row[9];
			$cost_estimate = $row[10];
			$customer_id = $row[11];
			$timestamp = $row[12];

			//AND timestamp > date_sub(now(), interval 2 minute)
			$result = mysql_query("SELECT id, latitude, longitude, type,
			   ( 3959 * acos( cos( radians('$latitude') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians( latitude ) ) ) ) AS distance
			   FROM provider_table
			   WHERE status > 1
				AND availibility = 'A'
				AND type = '$type'

			   HAVING distance < 10 ORDER BY distance LIMIT 0 , 20");

			if(mysql_errno() == 0)
			{
			   $responseData = array();
			   $message = "Success";

			   $markers = array();

			   while($row = mysql_fetch_array($result))
			   {
			      $marker = array();
			      $marker['id'] = $row[0];
			      $marker['latitude'] = $row[1];
			      $marker['longitude'] = $row[2];
			      $marker['type'] = $row[3];
			      $marker['distance'] = $row[4];
			      array_push($markers, $marker);
			   }
			   //$responseData = array('message' => $message, 'markers' => $markers, 'job' => $job,);

							$responseData = array(	'message' => $message,
															'job' => $job,
								 							'pickup_latitude' => $pickup_latitude,
															'pickup_longitude' => $pickup_longitude,
															'pickup_address' => $pickup_address,
															'total_packages' => $total_packages,
															'total_weight' => $total_weight,
															'drop_address' => $drop_address,
															'drop_latitude' => $drop_latitude,
															'drop_longitude' => $drop_longitude,
															'destination_distance' => $destination_distance,
															'destination_duration' => $destination_duration,
															'cost_estimate' => $cost_estimate,
															'customer_id' => $customer_id,
															'timestamp' => $timestamp,
															'markers' => $markers
														);
			}
			else
			{
			   $message = "Fail";
			   $responseData = array('message' => $message);
			}
		}
		else {
			//AND timestamp > date_sub(now(), interval 2 minute)
			$result = mysql_query("SELECT id, latitude, longitude, type,
			   ( 3959 * acos( cos( radians('$latitude') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians( latitude ) ) ) ) AS distance
			   FROM provider_table
			   WHERE status > 1
				AND availibility = 'A'
				AND type = '$type'

			   HAVING distance < 10 ORDER BY distance LIMIT 0 , 20");

			if(mysql_errno() == 0)
			{
			   $responseData = array();
			   $message = "Success";

			   $markers = array();

			   while($row = mysql_fetch_array($result))
			   {
			      $marker = array();
			      $marker['id'] = $row[0];
			      $marker['latitude'] = $row[1];
			      $marker['longitude'] = $row[2];
			      $marker['type'] = $row[3];
			      $marker['distance'] = $row[4];
			      array_push($markers, $marker);
			   }
			   $responseData = array('message' => $message, 'markers' => $markers, 'job' => $job,);
			}
			else
			{
			   $message = "Fail";
			   $responseData = array('message' => $message);
			}
		}
	}
}
else
{
	$message = "Fail";
	$responseData = array('message' => $message);
}



header('Content-type: application/json');
 echo json_encode( $responseData );

?>
