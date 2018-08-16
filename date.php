<?php
define( 'SHORTINIT', true );
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');

global $wpdb;
$myArray = $_REQUEST['jsonString'];
$apiArray = json_decode($myArray, true);

    foreach ($apiArray as $key => $value) {
      $start = $value['start'];
      $end = $value['end'];
      $sql = $wpdb->insert('wp_amelia_appointments', array ('id' => NULL, 'status' => 'approved', 'bookingStart' => $start, 'bookingEnd' => $end, 'notifyParticipants' => '0', 'serviceId' => '1', 'providerId' => '1', 'internalNotes' => 'freeBusy'
));

    }

?>
