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
			$results = mysql_query("SELECT  it.id
													, it.order_id
													, it.currency
													, it.calculated_amount
													, it.status
													, ot.image_link
			                              FROM order_table as ot, invoice_table AS it
													WHERE ot.provider_id = '$id'
													AND it.order_id = ot.id
													ORDER BY it.id DESC
													");

			if(mysql_errno() == 0)
			{
			   $responseData = array();
			   $message = "Success";
			   $orders = array();

			   while($row = mysql_fetch_array($results))
			   {
			      $order = array();
			      $order['id'] = $row[0];
			      $order['order_id'] = $row[1];
			      $order['currency'] = $row[2];
			      $order['calculated_amount'] = $row[3];
			      $order['status'] = $row[4];
					$order['image_link'] = $row[5];
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
