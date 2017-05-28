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

$dataReceived = json_decode(file_get_contents('php://input'), true);
//var_dump($dataReceived);
if((json_last_error() == JSON_ERROR_NONE))
{
   //call from iOS
	$otp_received = $dataReceived['otp'];
   $id = $dataReceived['id'];
}
else
{
   //call from Android
   $otp_received = $_POST['otp'];
   $id = $_POST['id'];
}

$token = getToken(32);

mysql_query("UPDATE customer_table SET status = 1, token = '$token' WHERE id = '$id' AND token = '$otp_received'");
	if(mysql_errno() == 0)
	{
      $message = "Success";
	}
	else
	{
		$message = "Authentication Failed";
		$token = "";
	}

$responseData = array('message' => $message, 'token'=>$token);

header('Content-type: application/json');
 echo json_encode( $responseData );

?>
