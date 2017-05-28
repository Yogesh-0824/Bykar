<?php
include("../connection.php");

$dataReceived = json_decode(file_get_contents('php://input'), true);
//var_dump($dataReceived);

if((json_last_error() == JSON_ERROR_NONE))
{
   //call from iOS
   $latitude = $dataReceived['latitude'];
	$longitude = $dataReceived['longitude'];
	$id = $dataReceived['id'];
	$token = $dataReceived['token'];
}
else
{
   //call from Android
   $latitude = $_POST['latitude'];
	$longitude = $_POST['longitude'];
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
			//AND timestamp > date_sub(now(), interval 2 minute)
			///////////////////////////////////////
			$result = mysql_query("SELECT id, first_name, last_name, latitude, longitude, type, angle,
			   ( 3959 * acos( cos( radians('$latitude') ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians('$longitude') ) + sin( radians('$latitude') ) * sin( radians( latitude ) ) ) ) AS distance
			   FROM provider_table
			   WHERE status > 1
				AND availibility = 'A'

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
			      $marker['first_name'] = $row[1];
			      $marker['last_name'] = $row[2];
			      $marker['latitude'] = $row[3];
			      $marker['longitude'] = $row[4];
			      $marker['type'] = $row[5];
					$marker['angle'] = $row[6];
			      $marker['distance'] = $row[7];
			      array_push($markers, $marker);
			   }
			   $responseData = array('message' => $message, 'markers' => $markers);
			}
			else
			{
			   $message = "Fail";
			   $responseData = array('message' => $message);
			}

			///////////////////////////////////////
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
