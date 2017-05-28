<?php
include("../connection.php");

$id = $_POST['id'];
$token = $_POST['token'];

$provider_id = $_POST['provider_id'];

$pickup_address = $_POST['pickup_address'];
$pickup_latitude = $_POST['pickup_latitude'];
$pickup_longitude = $_POST['pickup_longitude'];

$shipment_details = $_POST['shipment_details'];
$total_packages = $_POST['total_packages'];
$total_weight = $_POST['total_weight'];
$package_1 = $_POST['package_1'];
$package_2 = $_POST['package_2'];
$package_3 = $_POST['package_3'];
$package_4 = $_POST['package_4'];
$package_5 = $_POST['package_5'];

$drop_address = $_POST['drop_address'];
$drop_latitude = $_POST['drop_latitude'];
$drop_longitude = $_POST['drop_longitude'];
$destination_distance = $_POST['destination_distance'];
$destination_duration = $_POST['destination_duration'];

$provider_type = $_POST['provider_type'];
$cost_estimate = $_POST['cost_estimate'];

$delivery_name = $_POST['delivery_name'];
$delivery_mobile = $_POST['delivery_mobile'];
$delivery_email = $_POST['delivery_email'];
$building_number = $_POST['building_number'];
$delivery_address = $_POST['delivery_address'];

$promo_code = $_POST['promo_code'];
$require_verification = $_POST['require_verification'];


$uploadDir = "./uploads/";      //Uploading to same directory as PHP file
$file = basename($_FILES['userfile']['name']);
$uploadFile = $file;

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
			//first insert order, then upload image and after that update the order table entry with its link

			$result = mysql_query("SELECT COUNT(id) FROM order_table");
			$row = mysql_fetch_array($result);
			$id_count = $row[0];

			$id_count++;

			$datetime = date ("Y-m-d H:i:s");

			mysql_query("INSERT INTO order_table
									(	id,
										customer_id,
										provider_id,
										order_datetime,
										pickup_address,
										pickup_latitude,
										pickup_longitude,
										shipment_details,
										total_packages,
										total_weight,
										package_1,
										package_2,
										package_3,
										package_4,
										package_5,
										drop_address,
										drop_latitude,
										drop_longitude,
										destination_distance,
										destination_duration,
										provider_type,
										cost_estimate,
										delivery_name,
										delivery_mobile,
										delivery_email,
										building_number,
										delivery_address,
										promo_code,
										require_verification
									)
									VALUES
									(
										'$id_count',
										'$id',
										'$provider_id',
										'$datetime',
										'$pickup_address',
										'$pickup_latitude',
										'$pickup_longitude',
										'$shipment_details',
										'$total_packages',
										'$total_weight',
										'$package_1',
										'$package_2',
										'$package_3',
										'$package_4',
										'$package_5',
										'$drop_address',
										'$drop_latitude',
										'$drop_longitude',
										'$destination_distance',
										'$destination_duration',
										'$provider_type',
										'$cost_estimate',
										'$delivery_name',
										'$delivery_mobile',
										'$delivery_email',
										'$building_number',
										'$delivery_address',
										'$promo_code',
										'$require_verification'
									)
						");

			if(mysql_affected_rows() == 1)
			{

				//$date = date_create();
				//$fileLabel = $id.'_'.date_timestamp_get($date);
				$fileLabel = $id.'_'.$id_count;

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
					 mysql_query("UPDATE order_table SET image_link = '$nameToSaveInDB'
						 								WHERE id = '$id_count'");
					 	if(mysql_errno() == 0)
					 	{
							$message = "Success";
				 			$responseData = array('message' => $message, 'order_id' => $id_count);
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
				mysql_query("DELETE FROM order_buffer_table WHERE customer_id = '$id'");
			}
			else
			{
				$message = "Insert Fail";
				$responseData = array('message' => $message);
			}
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
