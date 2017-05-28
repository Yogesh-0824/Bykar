<?php
include("../connection.php");

$dataReceived = json_decode(file_get_contents('php://input'), true);
//var_dump($dataReceived);

if((json_last_error() == JSON_ERROR_NONE))
{
   //call from iOS
	$id = $dataReceived['id'];
	$token = $dataReceived['token'];
}
else
{
   //call from Android
	$id = $_POST['id'];
	$token = $_POST['token'];
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
			$result = mysql_query("SELECT ot.id
				 									, customer_id
													, TIMESTAMPDIFF(second, order_datetime, NOW())
													, pickup_datetime
													, deliver_datetime
													, pickup_address
													, pickup_latitude
													, pickup_longitude
													, shipment_details
													, total_packages
													, total_weight
													, drop_address
													, drop_latitude
													, drop_longitude
													, delivery_name
													, delivery_mobile
													, delivery_email
													, building_number
													, delivery_address
													, ot.image_link
													, require_verification
													, ot.status
                                       , first_name
                                     	, last_name
                                     	, mobile_number

                                     FROM order_table AS ot, customer_table AS ct
														WHERE ot.provider_id = '$id'
                                          AND ot.customer_id = ct.id
														AND ot.status = 0
														AND ot.timestamp > date_sub(now(), interval 2 minute)");
			if(mysql_errno() == 0 && mysql_num_rows($result) == 1)
			{
				$row = mysql_fetch_array($result);
				$order_id = $row[0];
				$customer_id = $row[1];
				$order_datetime = $row[2];
				$pickup_datetime = $row[3];
				$deliver_datetime = $row[4];
				$pickup_address = $row[5];
				$pickup_latitude = $row[6];
				$pickup_longitude = $row[7];
				$shipment_details = $row[8];
				$total_packages = $row[9];
				$total_weight = $row[10];
				$drop_address = $row[11];
				$drop_latitude = $row[12];
				$drop_longitude = $row[13];
				$delivery_name = $row[14];
				$delivery_mobile = $row[15];
				$delivery_email = $row[16];
				$building_number = $row[17];
				$delivery_address = $row[18];
				$image_link = $row[19];
				$require_verification = $row[20];
				$status = $row[21];
				$first_name = $row[22];
				$last_name = $row[23];
				$mobile_number = $row[24];

				mysql_query("UPDATE order_table SET status = 1 WHERE id = '$order_id'");
				if(mysql_errno() == 0 && mysql_affected_rows() == 1)
				{
					$status = 1;

					mysql_query("UPDATE provider_table SET availibility = 'B' WHERE id = '$id'");
					if(mysql_errno() == 0 && mysql_affected_rows() == 1)
					{
						$message = "Received";
						$responseData = array
						(
							 'message' => $message
							,'order_id' => $order_id
							,'customer_id' => $customer_id
							,'order_datetime' => $order_datetime
							,'pickup_datetime' => $pickup_datetime
							,'deliver_datetime' => $deliver_datetime
							,'pickup_address' => $pickup_address
							,'pickup_latitude' => $pickup_latitude
							,'pickup_longitude' => $pickup_longitude
							,'shipment_details' => $shipment_details
							,'total_packages' => $total_packages
							,'total_weight' => $total_weight
							,'drop_address' => $drop_address
							,'drop_latitude' => $drop_latitude
							,'drop_longitude' => $drop_longitude
							,'delivery_name' => $delivery_name
							,'delivery_mobile' => $delivery_mobile
							,'delivery_email' => $delivery_email
							,'building_number' => $building_number
							,'delivery_address' => $delivery_address
							,'image_link' => $image_link
							,'require_verification' => $require_verification
							,'status' => $status
							,'first_name' => $first_name
							,'last_name' => $last_name
							,'mobile_number' => $mobile_number
						);
					}
				}
			}
			else {
				$message = "Waiting";
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
