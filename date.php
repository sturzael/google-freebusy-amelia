<?php
define( 'SHORTINIT', true );
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php');

global $wpdb;
$myArray = $_REQUEST['jsonString'];
$apiArray = json_decode($myArray, true);

    foreach ($apiArray as $key => $value) {
      $start = $value['start'];
      $end = $value['end'];
      $count = 0;
      $identifer = hexdec(uniqid());
      //change identifer on each for loop
      $count = $wpdb->get_var("SELECT * FROM `wp_amelia_appointments` WHERE `bookingStart` = '$start'  AND `bookingEnd` = '$end'");
      if ($count < 1) {
        $sql = $wpdb->insert('wp_amelia_appointments', array ('id' => $identifer, 'status' => 'approved', 'bookingStart' => $start, 'bookingEnd' => $end, 'notifyParticipants' => '0', 'serviceId' => '1', 'providerId' => '1', 'internalNotes' => 'freeBusy'
        ));
        $sql = $wpdb->insert('wp_amelia_customer_bookings', array ('id' => NULL, 'appointmentId' => $identifer, 'customerId' => '5', 'status' => 'approved', 'price' => '0', 'persons' => '1'));
      };
    }
?>
