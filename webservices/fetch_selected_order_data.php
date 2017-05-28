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
			$results = mysql_query("SELECT  provider_id
													, TIMESTAMPDIFF(second, order_datetime, NOW())
													, TIMESTAMPDIFF(second, pickup_datetime, NOW())
													, TIMESTAMPDIFF(second, deliver_datetime, NOW())
													, shipment_details
													, pickup_address
													, pickup_latitude
													, pickup_longitude
													, building_number
													, delivery_address
													, drop_latitude
													, drop_longitude
													, ot.image_link
													, ot.status
													, first_name
													, last_name
													, mobile_number
													, registration_number
													, make
													, model
													, color
													, latitude
													, longitude
													, pt.thumb_link
													, total_weight
													, total_packages
													, provider_type
													, pt.vehicle_thumb_link
													, require_verification
			                              FROM order_table as ot, provider_table AS pt
													WHERE ot.id = '$order_id'
                                       AND ot.provider_id = pt.id");

			if(mysql_errno() == 0)
			{
			   $message = "Success";

			   $row = mysql_fetch_array($results);

				$provider_id = $row[0];
				$order_datetime = $row[1];
				$pickup_datetime = $row[2];
				$deliver_datetime = $row[3];
				$shipment_details = $row[4];
				$pickup_address = $row[5];
				$pickup_latitude = $row[6];
				$pickup_longitude = $row[7];
				$building_number = $row[8];
				$delivery_address = $row[9];
				$drop_latitude = $row[10];
				$drop_longitude = $row[11];
				$image_link = $row[12];
				$status = $row[13];
				$first_name = $row[14];
				$last_name = $row[15];
				$mobile_number = $row[16];
				$registration_number = $row[17];
				$make = $row[18];
				$model = $row[19];
				$color = $row[20];
				$latitude = $row[21];
				$longitude = $row[22];
				$provider_image = $row[23];
				$total_weight = $row[24];
				$total_packages = $row[25];
				$provider_type = $row[26];
				$vehicle_thumb_link = $row[27];
				$require_verification = $row[28];
				$signature_link = "";

				if(strcmp($require_verification, "1") == 0)
				{
					$result = mysql_query("SELECT image_link FROM invoice_table
															WHERE order_id = '$order_id'");
					if(mysql_errno() == 0 && mysql_num_rows($result) == 1)
					{
						$row = mysql_fetch_array($result);
						$signature_link = $row[0];
					}
				}

			   $responseData = array('message' => $message
											,'provider_id' => $provider_id
											,'order_datetime' => $order_datetime
											,'pickup_datetime' => $pickup_datetime
											,'deliver_datetime' => $deliver_datetime
											,'shipment_details' => $shipment_details
											,'pickup_address' => $pickup_address
											,'pickup_latitude' => $pickup_latitude
											,'pickup_longitude' => $pickup_longitude
											,'building_number' => $building_number
											,'delivery_address' => $delivery_address
											,'drop_latitude' => $drop_latitude
											,'drop_longitude' => $drop_longitude
											,'image_link' => $image_link
											,'status' => $status
											,'first_name' => $first_name
											,'last_name' => $last_name
											,'mobile_number' => $mobile_number
											,'registration_number' => $registration_number
											,'make' => $make
											,'model' => $model
											,'color' => $color
											,'latitude' => $latitude
											,'longitude' => $longitude
											,'provider_image' => $provider_image
											,'total_weight' => $total_weight
											,'total_packages' => $total_packages
											,'provider_type' => $provider_type
											,'vehicle_thumb_link' => $vehicle_thumb_link
											,'require_verification' => $require_verification
											,'signature_link' => $signature_link
											);
			}
			else {
			   # code...
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
