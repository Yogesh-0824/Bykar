<?php
include("../connection.php");

$dataReceived = json_decode(file_get_contents('php://input'), true);
//var_dump($dataReceived);

if((json_last_error() == JSON_ERROR_NONE))
{
   //call from iOS

	$drop_address = $dataReceived['drop_address'];
	$building_number = $dataReceived['building_number'];
	$drop_name = $dataReceived['drop_name'];
	$drop_phone = $dataReceived['drop_phone'];
	$drop_email = $dataReceived['drop_email'];
	$drop_label = $dataReceived['drop_label'];
	$drop_latitude = $dataReceived['drop_latitude'];
	$drop_longitude = $dataReceived['drop_longitude'];
   $id = $dataReceived['id'];
	$token = $dataReceived['token'];
}
else
{
   //call from Android
	$drop_address = $_POST['drop_address'];
	$building_number = $_POST['building_number'];
	$drop_name = $_POST['drop_name'];
	$drop_phone = $_POST['drop_phone'];
	$drop_email = $_POST['drop_email'];
	$drop_label = $_POST['drop_label'];
	$drop_latitude = $_POST['drop_latitude'];
	$drop_longitude = $_POST['drop_longitude'];
   $id = $_POST['id'];
	$token = $_POST['token'];
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
			$result = mysql_query("SELECT id FROM drop_locations_table
												WHERE customer_id = '$id'
												AND drop_label = '$drop_label'");
			if(mysql_errno() == 0 && mysql_num_rows($result) > 0)
			{
				//edit existing label
				$row = mysql_fetch_array($result);
				$labelId = $row[0];

				mysql_query("UPDATE drop_locations_table
									SET drop_address = '$drop_address'
										,building_number = '$building_number'
										,drop_name = '$drop_name'
										,drop_phone = '$drop_phone'
										,drop_email = '$drop_email'
										,drop_label = '$drop_label'
										,drop_latitude = '$drop_latitude'
										,drop_longitude = '$drop_longitude'
									WHERE id = '$labelId'");
				if(mysql_errno() == 0 && mysql_affected_rows() == 1)
				{
					$message = "Success";
					$responseData = array('message' => $message);
				}
				else {
					$message = "Fail";
					$responseData = array('message' => $message);
				}
			}
			else {
				//add new label
				mysql_query("INSERT INTO drop_locations_table
									(
										 customer_id
										,drop_address
										,building_number
										,drop_name
										,drop_phone
										,drop_email
										,drop_label
										,drop_latitude
										,drop_longitude
									)
									VALUES
									(
										'$id'
									  ,'$drop_address'
									  ,'$building_number'
									  ,'$drop_name'
									  ,'$drop_phone'
									  ,'$drop_email'
									  ,'$drop_label'
									  ,'$drop_latitude'
									  ,'$drop_longitude'
								  )");

			  if(mysql_errno() == 0 && mysql_affected_rows() == 1)
			  {
				  $message = "Success";
				  $responseData = array('message' => $message);
			  }
			  else {
				  $message = "Fail";
				  $responseData = array('message' => $message);
			  }
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
