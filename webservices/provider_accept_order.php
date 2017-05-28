<?php
include("../connection.php");

$dataReceived = json_decode(file_get_contents('php://input'), true);
//var_dump($dataReceived);

if((json_last_error() == JSON_ERROR_NONE))
{
   //call from iOS
	$id = $dataReceived['id'];
	$token = $dataReceived['token'];
	$customer_id = $dataReceived['customer_id'];
}
else
{
   //call from Android
	$id = $_POST['id'];
	$token = $_POST['token'];
	$customer_id = $_POST['customer_id'];
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
			$result = mysql_query("SELECT status FROM order_buffer_table WHERE customer_id = '$customer_id'");
			if(mysql_errno() == 0 && mysql_num_rows($result) == 1)
			{
				$row = mysql_fetch_array($result);
				if(strcmp($row[0], "A") == 0)
				{
					$message = "Lost";
					$responseData = array('message' => $message);
				}
				else {
					mysql_query("UPDATE order_buffer_table SET status = 'A', provider_id = '$id'
										WHERE customer_id = '$customer_id'");
					if(mysql_errno() == 0 && mysql_affected_rows() == 1)
					{
						$message = "Success";
						$responseData = array('message' => $message);
					}
					else {
						$message = "Fail";
						$responseData = array('message' => $message);
					}
				}
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
