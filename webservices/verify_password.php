<?php
include("../connection.php");

$dataReceived = json_decode(file_get_contents('php://input'), true);
//var_dump($dataReceived);

if((json_last_error() == JSON_ERROR_NONE))
{
   //call from iOS
	$id = $dataReceived['id'];
	$token = $dataReceived['token'];
	$password = $dataReceived['password'];
}
else
{
   //call from Android
   $id = $_POST['id'];
	$token = $_POST['token'];
	$password = $_POST['password'];

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
            $result = mysql_query("SELECT password FROM customer_table
            								WHERE id = '$id'");
            if(mysql_num_rows($result) == 1)
            {
            	$row = mysql_fetch_array($result);
            	if($row[0] == $password)
            	{
                  $message = "Success";
         			$responseData = array('message' => $message);
               }
               else {
                  $message = "Password Incorrect";
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
