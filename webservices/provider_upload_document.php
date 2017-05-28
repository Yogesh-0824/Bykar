<?php
include("../connection.php");

$id = $_POST['id'];
$token = $_POST['token'];

$document_type = $_POST['document_type'];
$issue_state = $_POST['issue_state'];
$issue_date = $_POST['issue_date'];
$validity_date = $_POST['validity_date'];
$document_number = $_POST['document_number'];
$name_on_document = $_POST['name_on_document'];

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
			$result = mysql_query("SELECT COUNT(id) FROM documents_table");
			$row = mysql_fetch_array($result);
			$id_count = $row[0];

			$id_count++;

			mysql_query("INSERT INTO documents_table
									(	 id
										,provider_id
										,document_type
										,issue_state
										,issue_date
										,validity_date
										,document_number
										,name_on_document
									)
									VALUES
									(
										 '$id_count'
										,'$id'
										,'$document_type'
										,'$issue_state'
										,'$issue_date'
										,'$validity_date'
										,'$document_number'
										,'$name_on_document'
									)
						");

			if(mysql_affected_rows() == 1)
			{
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
					 mysql_query("UPDATE documents_table SET image_link = '$nameToSaveInDB'
						 								WHERE id = '$id_count'");
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
