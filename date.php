<?php
$myArray = $_REQUEST['jsonString'];
$someArray = json_decode($myArray, true);
foreach ($variable as $key => $value) {
  // code...
}
  print_r($someArray[0]['start']);


?>
