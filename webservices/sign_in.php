<?php
include("../connection.php");

function crypto_rand_secure($min, $max)
{
    $range = $max - $min;
    if ($range < 1) return $min; // not so random...
    $log = ceil(log($range, 2));
    $bytes = (int) ($log / 8) + 1; // length in bytes
    $bits = (int) $log + 1; // length in bits
    $filter = (int) (1 << $bits) - 1; // set all lower bits to 1
    do {
        $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
        $rnd = $rnd & $filter; // discard irrelevant bits
    } while ($rnd >= $range);
    return $min + $rnd;
}

function getToken($length)
{
    $token = "";
    $codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $codeAlphabet.= "abcdefghijklmnopqrstuvwxyz";
    $codeAlphabet.= "0123456789";
    $max = strlen($codeAlphabet) - 1;
    for ($i=0; $i < $length; $i++) {
        $token .= $codeAlphabet[crypto_rand_secure(0, $max)];
    }
    return $token;
}

//echo getToken(16);

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

$result = mysql_query("SELECT id, first_name, last_name, email_address, username, status, mobile_number, image_link
											FROM customer_table
											WHERE (username = '$username' OR email_address = '$username')
											AND password = '$password'");

if(mysql_num_rows($result) == 1)
{
	$row = mysql_fetch_array($result);
	$status = $row[5];
	if($status > 0)
	{
		$message = "Success";
		$id = $row[0];
		$first_name = $row[1];
		$last_name = $row[2];
		$email_address = $row[3];
		$username = $row[4];
      $mobile_number = $row[6];
      $image_link = $row[7];

		$token = getToken(32);
      mysql_query("UPDATE customer_table SET token = '$token' WHERE id = '$id' AND status = 1");
      	if(mysql_errno() == 0)
      	{
            $message = "Success";
      	}
      	else
      	{
      		$message = "Authentication Failed";
      		$token = "";
      	}
         $responseData = array('message' => $message, 'id' => $id,
         						 'first_name' => $first_name, 'last_name' => $last_name,
         						  'email_address' => $email_address, 'username' => $username,
                             'mobile_number' => $mobile_number ,'image_link' => $image_link,'token' => $token);
	}
	else
	{
		$message = "Inactive Account";
		$responseData = array('message' => $message);
	}
}
else
{
	$message = "Invalid Account: ".$username;
	$responseData = array('message' => $message);
}



header('Content-type: application/json');
 echo json_encode( $responseData );
?>
