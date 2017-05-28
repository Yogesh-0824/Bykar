<?php
include("../connection.php");

$dataReceived = json_decode(file_get_contents('php://input'), true);
//var_dump($dataReceived);

$id = $dataReceived['id'];
$token = $dataReceived['token'];
$order_id = $dataReceived['order_id'];

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
			$result = mysql_query("SELECT status FROM order_table
														WHERE id = '$order_id'");

			if(mysql_errno() == 0 && mysql_num_rows($result) == 1)
			{
				$row = mysql_fetch_array($result);
				if($row[0] > 0)
				{
					$message = "Success";
					$responseData = array('message' => $message);
				}
				else {
					$message = "Waiting";
					$responseData = array('message' => $message);
				}
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
