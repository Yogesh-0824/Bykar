<?php
include("../connection.php");

$dataReceived = json_decode(file_get_contents('php://input'), true);
//var_dump($dataReceived);

if((json_last_error() == JSON_ERROR_NONE))
{
   //call from iOS
	$id = $dataReceived['id'];
	$token = $dataReceived['token'];
	$job_id = $dataReceived['job_id'];
}
else
{
   //call from Android
   $id = $_POST['id'];
	$token = $_POST['token'];
	$job_id = $_POST['job_id'];
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
			$results = mysql_query("SELECT  ot.image_link
													, ot.pickup_address
													, ot.building_number
													, ot.delivery_address
													, it.currency
													, it.calculated_amount
													, it.status
													, ct.first_name
													, ct.last_name
													, ot.total_packages
                                       , ot.total_weight
											FROM invoice_table AS it, order_table AS ot, customer_table AS ct
											WHERE it.id = '$job_id'
											AND it.order_id = ot.id
											AND ot.customer_id = ct.id");

			if(mysql_errno() == 0)
			{
			   $message = "Success";

			   $row = mysql_fetch_array($results);

				$image_link = $row[0];
				$pickup_address = $row[1];
				$building_number = $row[2];
				$delivery_address = $row[3];
				$currency = $row[4];
				$calculated_amount = $row[5];
				$status = $row[6];
				$first_name = $row[7];
				$last_name = $row[8];
				$total_packages = $row[9];
				$total_weight = $row[10];

			   $responseData = array('message' => $message
											,'image_link' => $image_link
											,'pickup_address' => $pickup_address
											,'building_number' => $building_number
											,'delivery_address' => $delivery_address
											,'currency' => $currency
											,'calculated_amount' => $calculated_amount
											,'status' => $status
											,'first_name' => $first_name
											,'last_name' => $last_name
											,'total_packages' => $total_packages
											,'total_weight' => $total_weight
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
