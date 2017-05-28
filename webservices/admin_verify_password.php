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

			//the work of this page/////////////////////
 $result = mysql_query("SELECT username, password FROM admin_table
 								WHERE username = '$username' AND password = '$password'");
 if(mysql_num_rows($result) == 1)
            {
            	$row = mysql_fetch_array($result);
            	if($row[1] == $password)
            	{
                  $message = "Success";
         			$responseData = array('message' => $message);
               }
               else {
                  $message = "Password Incorrect";
         			$responseData = array('message' => $message);
               }
            }
           

else
{
	$message = "Invalid Account".$id;
	$responseData = array('message' => $message);
}

header('Content-type: application/json');
 echo json_encode( $responseData );
?>
