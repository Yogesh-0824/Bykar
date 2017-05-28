<?php
include("../connection.php");

$dataReceived = json_decode(file_get_contents('php://input'), true);
//var_dump($dataReceived);

if((json_last_error() == JSON_ERROR_NONE))
{
   //call from iOS
	$username_received = $dataReceived['username'];
	$id = $dataReceived['id'];
	$token = $dataReceived['token'];
}
else
{
   //call from Android
   $username_received = $_POST['username'];
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
			$result = mysql_query("SELECT COUNT(id)
									FROM customer_table
									WHERE username = '$username_received'");

			$row = mysql_fetch_array($result);
			$id_count = $row[0];

			if($id_count >= 1)
			{
				$message = "Username Taken";
			}
			else
			{
				mysql_query("UPDATE customer_table SET username = '$username_received' WHERE id = '$id' AND token = '$token'");
				if(mysql_errno() == 0)
				{
					$message = "Success";
				}
				else
				{
					$message = "Update Error";
				}
			}
		}
		else
		{
			$message = "Inactive Account";
		}
	}
	else
	{
		$message = "Invalid Token";
	}
}
else
{
	$message = "Invalid Account";
}

$responseData = array('message' => $message);

header('Content-type: application/json');
 echo json_encode( $responseData );

?>
