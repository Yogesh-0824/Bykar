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
			$results = mysql_query("SELECT ot.id
													, provider_id
													, TIMESTAMPDIFF(second, order_datetime, NOW())
													, TIMESTAMPDIFF(second, pickup_datetime, NOW())
													, TIMESTAMPDIFF(second, deliver_datetime, NOW())
													, shipment_details
													, ot.image_link
													, ot.status
													, first_name
													, last_name
													, mobile_number
			                              FROM order_table as ot, provider_table AS pt
													WHERE (ot.status >= 0 AND ot.status <= 3)
                                       AND ot.provider_id = pt.id
													AND customer_id = '$id' ORDER BY id DESC");

			if(mysql_errno() == 0)
			{
			   $responseData = array();
			   $message = "Success";
			   $orders = array();

			   while($row = mysql_fetch_array($results))
			   {
			      $order = array();
			      $order['id'] = $row[0];
			      $order['provider_id'] = $row[1];
			      $order['order_datetime'] = $row[2];
			      $order['pickup_datetime'] = $row[3];
			      $order['deliver_datetime'] = $row[4];
			      $order['shipment_details'] = $row[5];
			      $order['image_link'] = $row[6];
			      $order['status'] = $row[7];
					$order['first_name'] = $row[8];
					$order['last_name'] = $row[9];
					$order['mobile_number'] = $row[10];
			      array_push($orders, $order);
			   }
			   $responseData = array('message' => $message, 'orders' => $orders);
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
