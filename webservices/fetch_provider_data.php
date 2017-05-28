<?php
include("../connection.php");

$dataReceived = json_decode(file_get_contents('php://input'), true);
//var_dump($dataReceived);

if((json_last_error() == JSON_ERROR_NONE))
{
   //call from iOS
	$username = $dataReceived['username'];
	$password = $dataReceived['password'];
}
else
{
   //call from Android
	$username = $_POST['username'];
	$password = $_POST['password'];
}

$result = mysql_query("SELECT id, username, password FROM admin_table
								WHERE username = '$username' AND password = '$password'");
if(mysql_num_rows($result) == 1)
{
	
			//the work of this page/////////////////////
			$results = mysql_query("SELECT id, first_name, last_name, email_address, mobile_number, type, image_link FROM provider_table
								WHERE status = 1 ORDER BY id DESC
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
			      $order['first_name'] = $row[1];
			      $order['last_name'] = $row[2];
			      $order['email_address'] = $row[3];
			      $order['mobile_number'] = $row[4];
			      $order['type'] = $row[5];
					$order['image_link'] = $row[6];
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
	$message = "Invalid Account";
	$responseData = array('message' => $message);
}

header('Content-type: application/json');
 echo json_encode( $responseData );
?>
