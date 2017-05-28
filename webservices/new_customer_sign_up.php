<?php
include("../connection.php");

$dataReceived = json_decode(file_get_contents('php://input'), true);

if((json_last_error() == JSON_ERROR_NONE))
{
   //call from iOS
	$first_name = $dataReceived['first_name'];
	$last_name = $dataReceived['last_name'];
	$email_address = $dataReceived['email_address'];
	$mobile_number = $dataReceived['mobile_number'];
	$password = $dataReceived['password'];
}
else
{
   //call from Android
   $first_name = $_POST['first_name'];
	$last_name = $_POST['last_name'];
	$email_address = $_POST['email_address'];
	$mobile_number = $_POST['mobile_number'];
	$password = $_POST['password'];
}

$result = mysql_query("SELECT COUNT(id) FROM customer_table
								WHERE email_address = '$email_address'
								OR mobile_number = '$mobile_number'");
$row = mysql_fetch_array($result);
$id_count = $row[0];

if($id_count >= 1)
{
	$message = "Account Exists";
	$otp = "";
}
else
{
	$result = mysql_query("SELECT COUNT(id) FROM customer_table");
	$row = mysql_fetch_array($result);
	$id_count = $row[0];

	$id_count++;
	$otp = rand(100000, 999999);

	mysql_query("INSERT INTO customer_table
							(	id
								, first_name
								, last_name
								, email_address
								, mobile_number
								, password
								, token
							)
							VALUES
							(
								'$id_count'
								,'$first_name'
								,'$last_name'
								,'$email_address'
								,'$mobile_number'
								,'$password'
								, '$otp'
							)
				");

	if(mysql_affected_rows() == 1)
	{
		$message = "Success";
	}
	else
	{
		$message = "Insert Failed";
	}
}

$responseData = array('message' => $message, 'otp' => $otp, 'id' => $id_count);

header('Content-type: application/json');
 echo json_encode( $responseData );
?>
