<?php
include("../connection.php");

$dataReceived = json_decode(file_get_contents('php://input'), true);
//var_dump($dataReceived);

if((json_last_error() == JSON_ERROR_NONE))
{
   //call from iOS
	$id = $dataReceived['id'];

}
else
{
   //call from Android
	$id = $_POST['id'];

}

$result = mysql_query("SELECT * FROM provider_table AS pt, documents_table AS dt WHERE pt.id = '$id' AND dt.provider_id = pt.id");
if(mysql_num_rows($result))
{
	
			//the work of this page/////////////////////
			$results = mysql_query("SELECT pt.id, pt.first_name, pt.last_name, pt.email_address, pt.mobile_number, pt.date_of_birth, pt.type, pt.image_link, pt.vehicle_image_link, pt.registration_number, pt.manufacturing_year, pt.make, pt.model, pt.color, dt.id, dt.document_type, dt.issue_state, dt.issue_date, dt.validity_date, dt.document_number, dt.name_on_document, dt.image_link FROM provider_table AS pt, documents_table AS dt WHERE pt.id = '$id' AND dt.provider_id = pt.id
													");

			if(mysql_errno() == 0)
			{
			   $responseData = array();
			   $message = "Success";
			   $orders = array();

			   while($row = mysql_fetch_array($results))
			   {
			      $order = array();
			      $order['provider_id'] = $row[0];
			      $order['first_name'] = $row[1];
			      $order['last_name'] = $row[2];
			      $order['email_address'] = $row[3];
			      $order['mobile_number'] = $row[4];
			      $order['date_of_birth'] = $row[5];
			      $order['type'] = $row[6];
				  $order['provider_image_link'] = $row[7];
				  $order['vehicle_image_link'] = $row[8];
				  $order['registration_number'] = $row[9];
				  $order['manufacturing_year'] = $row[10];
				  $order['make'] = $row[11];
				  $order['model'] = $row[12];
				  $order['color'] = $row[13];
				  $order['document_id'] = $row[14];
				  $order['document_type'] = $row[15];
				  $order['issue_state'] = $row[16];
				  $order['issue_date'] = $row[17];
				  $order['validity_date'] = $row[18];
				  $order['document_number'] = $row[19];
				  $order['name_on_document'] = $row[20];
				  $order['document_image_link'] = $row[21];
			      array_push($orders, $order);
			   }
			   $responseData = array('message' => $message, 'orders' => $orders);
			}
			else {
			   # code...
			   $message = "Fail";
			   $responseData = array('message' => $message);
			}
			////////////////////////////////////////////
		}
		
else
{
	$message = "Invalid Account";
	$responseData = array('message' => $message);
}

header('Content-type: application/json');
 echo json_encode( $responseData );
?>
