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
			$result = mysql_query("SELECT status, provider_id FROM order_buffer_table WHERE customer_id = '$id'");
				if(mysql_num_rows($result) == 1)
				{
					$row = mysql_fetch_array($result);
					if($row[0] == "A")
					{
						$message = "Accepted";
						$responseData = array('message' => $message, 'provider_id' => $row[1]);
					}
					else {
						$message = "Waiting";
						$responseData = array('message' => $message);
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
