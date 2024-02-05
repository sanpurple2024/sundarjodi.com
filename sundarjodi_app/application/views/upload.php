<?php
if(isset($_FILES['image'])){
$file_name = $_FILES['image']['name'];
$file_tmp =$_FILES['image']['tmp_name'];
move_uploaded_file($file_tmp,"docs/".$file_name);
echo "<h3>Image Upload Success</h3>";
echo '<img src="docs/'.$file_name.'" style="width:100%">';

shell_exec('"docs/'.$file_name.'" ocr/out');

echo "<br><h3>OCR after reading</h3><br><pre>";

$myfile = fopen("ocr/out.txt", "r") or die("Unable to open file!");
echo fread($myfile,filesize("ocr/out.txt"));
fclose($myfile);
echo "</pre>";
}
?>