<?php
include("../connection.php");

$id = $_POST['id'];
$token = $_POST['token'];

$uploadDir = "./uploads/";      //Uploading to same directory as PHP file
$file = basename($_FILES['userfile']['name']);
$uploadFile = $file;

//$date = date_create();
$fileLabel = $id;

$newName = $uploadDir . $fileLabel . $uploadFile;

$result = mysql_query("SELECT token, status FROM provider_table
								WHERE id = '$id'");
if(mysql_num_rows($result) == 1)
{
	$row = mysql_fetch_array($result);
	if($row[0] == $token)
	{
		if($row[1] > 1)
		{
			//the work of this page/////////////////////
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
				 mysql_query("UPDATE provider_table
					 								SET image_link = '$nameToSaveInDB'
													, thumb_link = '$thumbNameToSaveInDB'
													WHERE id = '$id'");
				
					if(mysql_errno() == 0)
					{
						 echo "Success";
					}
					else
					{
						echo "Update Fail";
					}
			}
			else
			{
				echo  "Copy Fail";
			}
			////////////////////////////////////////////
		}
      else
      {
         echo  "Inactive Account";
      }
   }
   else
   {
      echo  "Invalid Token";
   }
}
else
{
   echo  "Invalid Account";
}
?>
