<?php
include("../connection.php");

$id = $_POST['id'];
$token = $_POST['token'];

$document_type = $_POST['document_type'];
$registration_number = $_POST['registration_number'];
$manufacturing_year = $_POST['manufacturing_year'];
$make = $_POST['make'];
$model = $_POST['model'];
$color = $_POST['color'];

$uploadDir = "./uploads/";      //Uploading to same directory as PHP file
$file = basename($_FILES['userfile']['name']);
$uploadFile = $file;


$fileLabel = 'Provider_'.$id.'_'.$document_type;

$newName = $uploadDir . $fileLabel . $uploadFile;

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
			//upload Image
         if (is_uploaded_file($_FILES['userfile']['tmp_name'])) {
         	//echo "Temp file uploaded. \r\n";
         } else {
         	//echo "Upload Fail";
         }
			//copy image to directory ./uploads/
         if (move_uploaded_file($_FILES['userfile']['tmp_name'], $newName))
			{
				////////////////  C O M P R E S S   F I L E  ////////////////
				$uploadimage = $newName;
				$resize_image = $uploadDir .$fileLabel ."_thumb". $uploadFile;
				$actual_image = $uploadDir .$fileLabel . $uploadFile;
				// It gets the size of the image
				list( $width,$height ) = getimagesize( $uploadimage );

				// It makes the new image width of 350
				$newwidth = 500;

				// It makes the new image height of 350
				$newheight = 500;

				// It loads the images we use jpeg function you can use any function like imagecreatefromjpeg
				$thumb = imagecreatetruecolor( $newwidth, $newheight );
				$source = imagecreatefromjpeg( $actual_image );

				// Resize the $thumb image.
				imagecopyresized($thumb, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

				// It then save the new image to the location specified by $resize_image variable

				imagejpeg( $thumb, $resize_image, 100 );
				//////////////

				$nameToSaveInDB = $fileLabel . $uploadFile;
				$thumbNameToSaveInDB = $fileLabel ."_thumb". $uploadFile;

				$nameToSaveInDB = $fileLabel . $uploadFile;
				mysql_query("UPDATE provider_table
										SET registration_number = '$registration_number'
											, manufacturing_year = '$manufacturing_year'
											, make = '$make'
											, model = '$model'
											, vehicle_image_link = '$nameToSaveInDB'
											, vehicle_thumb_link = '$thumbNameToSaveInDB'
											, color = '$color'
										WHERE id = '$id'");
				if(mysql_errno() == 0)
			 	{
					$message = "Success";
					   $responseData = array('message' => $message);
				}
				else {
					$message = "Update Fail";
					   $responseData = array('message' => $message);
				}
         }
         else
         {
				$message = "Copy Fail";
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
