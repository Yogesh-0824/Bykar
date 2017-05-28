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
				 mysql_query("UPDATE customer_table SET image_link = '$nameToSaveInDB'
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
