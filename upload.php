<?php
if( $_FILES['file']['name'] != "" )
{
   $destFile = "../junk_2/uploads".$_FILES['file']['name'];
move_uploaded_file( $_FILES['file']['tmp_name'], $destFile );
}
else
{
    die("No file specified!");
}