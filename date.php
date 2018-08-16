<?php
$myArray = $_REQUEST['jsonString'];
$apiArray = json_decode($myArray, true);

    foreach ($apiArray as $key => $value) {
      echo $value["start"] . ", " . $value["end"] . "<br>";
    }

?>
