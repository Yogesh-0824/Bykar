<?php
include("../connection.php");

$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email_address = $_POST['email_address'];
$mobile_number = $_POST['mobile_number'];
$password = $_POST['password'];
$state = $_POST['state'];
$date_of_birth = $_POST['date_of_birth'];
$type = $_POST['type'];

$uploadDir = "./uploads/";      //Uploading to same directory as PHP file
$file = basename($_FILES['userfile']['name']);
$uploadFile = $file;

$result = mysql_query("SELECT id, status FROM provider_table
								WHERE email_address = '$email_address'
								OR mobile_number = '$mobile_number'");

if(mysql_num_rows($result) > 0)
{
	$row = mysql_fetch_array($result);
	$id = $row[0];
	if($row[1] > 1) // account is verified
	{
		$message = "Account Exists";
		$responseData = array('message' => $message);
	}
	else
	{
		$otp = rand(100000, 999999);

		mysql_query("UPDATE provider_table
								SET  first_name = '$first_name'
									, last_name = '$last_name'
									, email_address = '$email_address'
									, mobile_number = '$mobile_number'
									, password = '$password'
									, state = '$state'
									, type = '$type'
									, token =  '$otp'
									, status = 0
									, date_of_birth = '$date_of_birth'
								WHERE id = '$id'
					");

		if(mysql_affected_rows() == 1)
		{

			$fileLabel = 'Provider_'.$id.'_profile_pic';

			$newName = $uploadDir . $fileLabel . $uploadFile;

			//upload Image
			if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
				//echo "Temp file uploaded. \r\n";
			} else {
				//echo "Upload Fail";
			}
			//copy image to directory ./uploads/
			if (move_uploaded_file($_FILES['userfile']['tmp_name'], $newName))
			{
				$nameToSaveInDB = $fileLabel . $uploadFile;
				 mysql_query("UPDATE provider_table SET image_link = '$nameToSaveInDB'
													WHERE id = '$id'");
					if(mysql_errno() == 0)
					{
							$message = "Success";
						   $responseData = array('message' => $message, 'otp' => $otp, 'id' => $id);
					}
					else
					{
						$message = "Update Fail";
						$responseData = array('message' => $message);
					}
			}
			else
			{
				$message = "Copy Fail";
				$responseData = array('message' => $message);
			}
		}
		else
		{
			$message = "Update Failed";
			$responseData = array('message' => $message);
		}
	}

}
else
{
	$result = mysql_query("SELECT COUNT(id) FROM provider_table");
	$row = mysql_fetch_array($result);
	$id_count = $row[0];

	$id_count++;
	$otp = rand(100000, 999999);

	mysql_query("INSERT INTO provider_table
							(	id
								, first_name
								, last_name
								, email_address
								, mobile_number
								, password
								, state
								, date_of_birth
								, type
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
								,'$state'
								,'$date_of_birth'
								,'$type'
								, '$otp'
							)
				");

	if(mysql_affected_rows() == 1)
	{

		$fileLabel = 'Provider_'.$id_count.'_profile_pic';

		$newName = $uploadDir . $fileLabel . $uploadFile;

		//upload Image
		if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
			//echo "Temp file uploaded. \r\n";
		} else {
			//echo "Upload Fail";
		}
		//copy image to directory ./uploads/
		if (move_uploaded_file($_FILES['userfile']['tmp_name'], $newName))
		{
			$nameToSaveInDB = $fileLabel . $uploadFile;
			 mysql_query("UPDATE provider_table SET image_link = '$nameToSaveInDB'
												WHERE id = '$id_count'");
				if(mysql_errno() == 0)
				{
						$message = "Success";
					   $responseData = array('message' => $message, 'otp' => $otp, 'id' => $id_count);
				}
				else
				{
					$message = "Update Fail";
					$responseData = array('message' => $message);
				}
		}
		else
		{
			$message = "Copy Fail";
			$responseData = array('message' => $message);
		}
	}
	else
	{
		$message = "Insert Failed";
		$responseData = array('message' => $message);
	}
}
header('Content-type: application/json');
 echo json_encode( $responseData );
?>
