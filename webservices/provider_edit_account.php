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
   $id = $dataReceived['id'];
   $token = $dataReceived['token'];
   $first_name = $dataReceived['first_name'];
   $last_name = $dataReceived['last_name'];
   $email_address = $dataReceived['email_address'];
   $mobile_number = $dataReceived['mobile_number'];
}
else
{
   //call from Android
   $id = $_POST['id'];
   $token = $_POST['token'];
   $first_name = $_POST['first_name'];
   $last_name = $_POST['last_name'];
   $email_address = $_POST['email_address'];
   $mobile_number = $_POST['mobile_number'];
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
			$newToken = getToken(32);
			//the work of this page/////////////////////
			mysql_query("UPDATE provider_table
								SET first_name = '$first_name'
									, last_name = '$last_name'
									, email_address = '$email_address'
									, mobile_number = '$mobile_number'
									, token = '$newToken'
								WHERE id = '$id'");
				if(mysql_errno() == 0)
				{
			      $message = "Success";
					$responseData = array('message' => $message, 'id' => $id,
											 'first_name' => $first_name, 'last_name' => $last_name,
											  'email_address' => $email_address,'mobile_number' => $mobile_number ,
											  'token' => $newToken);
				}
				else
				{
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
