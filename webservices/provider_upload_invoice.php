<?php
include("../connection.php");

$id = $_POST['id'];
$token = $_POST['token'];

$order_id = $_POST['order_id'];
$cost = $_POST['cost'];
$time = $_POST['time'];
$distance = $_POST['distance'];
$require_verification = $_POST['require_verification'];

$result = mysql_query("SELECT token, status FROM provider_table

						WHERE id = '$id'");

							//echo $token;

if(mysql_num_rows($result) == 1)
{
	$row = mysql_fetch_array($result);
	if($row[0] == $token)
	{
		if($row[1] > 0)
		{
			//the work of this page/////////////////////
			//first insert order, then upload image and after that update the order table entry with its link
			$result = mysql_query("SELECT COUNT(id) FROM documents_table");
			$row = mysql_fetch_array($result);
			$id_count = $row[0];

			$id_count++;

			mysql_query("INSERT INTO invoice_table
									(	 order_id
										,calculated_distance
										,calculated_time
										,calculated_amount
										,status
									)
									VALUES
									(
										  '$order_id'
										, '$distance'
										, '$time'
										, '$cost'
										, 0
									)");

			if(mysql_affected_rows() == 1)
			{
				if(strcmp($require_verification, "1") == 0)
				{

					$uploadDir = "./uploads/";      //Uploading to same directory as PHP file
					$file = basename($_FILES['userfile']['name']);
					$uploadFile = $file;


					$fileLabel = 'Order_'.$order_id;

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
						 mysql_query("UPDATE invoice_table SET image_link = '$nameToSaveInDB'
							 								WHERE order_id = '$order_id'");
						 	if(mysql_errno() == 0)
						 	{
								$message = "Success";
								$responseData = array('message' => $message);
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
				{
					$message = "Success";
					$responseData = array('message' => $message);
				}
			}
			else
			{
				$message = "Insert Failed";
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
