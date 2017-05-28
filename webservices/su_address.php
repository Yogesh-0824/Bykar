<?php
include("../connection.php");

$dataReceived = json_decode(file_get_contents('php://input'), true);
//var_dump($dataReceived);

if((json_last_error() == JSON_ERROR_NONE))
{
   //call from iOS
	$address1 = $dataReceived['address1'];
	$address2 = $dataReceived['address2'];
	$city = $dataReceived['city'];
	$state = $dataReceived['state'];
	$country = $dataReceived['country'];
	$zipcode = $dataReceived['zipcode'];
	$user_type = $dataReceived['user_type'];
	$address_type = $dataReceived['address_type'];

	$id = $dataReceived['id'];
	$token = $dataReceived['token'];
}
else
{
   //call from Android
   $address1 = $_POST['address1'];
	$address2 = $_POST['address2'];
	$city = $_POST['city'];
	$state = $_POST['state'];
	$country = $_POST['country'];
	$zipcode = $_POST['zipcode'];
	$user_type = $_POST['user_type'];
	$address_type = $_POST['address_type'];

	$id = $_POST['id'];
	$token = $_POST['token'];
}

mysql_query("INSERT INTO address_table
									(	address1
										, address2
										, city
										, state
										, country
										, zipcode
										, address_type
										, user_id
										, user_type
									)
									VALUES
									(
										'$address1'
										,'$address2'
										,'$city'
										,'$state'
										,'$country'
										,'$zipcode'
										,'$address_type'
										,'$id'
										,'$user_type'
									)
						");

if(mysql_errno() == 0 && mysql_affected_rows() == 1)
{
		$message = "Success";
}
else
{
	$message = "Insert Failed";
}


$responseData = array('message' => $message);

header('Content-type: application/json');
 echo json_encode( $responseData );
?>
