<?php
$filepath="./uploads/";
$filepath=$filepath.basename($_FILES['file']['name']);
if (move_uploaded_file($_FILES['file']['tmp_name'], $filepath)) {
	echo $_FILES['file']['name'];
	 echo '<div>
            <img src="./uploads/'.$_FILES['file']['name'].'">
            </div>';
}
else
 {
 	echo "failed";
 }
?>