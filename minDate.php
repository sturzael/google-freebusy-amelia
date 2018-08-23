<?php
define('SHORTINIT', true);

require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');

global $wpdb;

$apiArray = json_decode($_REQUEST['jsonString'], true); //decode the json into a php array

$notCal = $wpdb->get_results("SELECT * FROM `wp_amelia_appointments` WHERE `internalNotes` = 'freeBusy' AND `serviceId` = 4", ARRAY_A);

$result = array_diff(array_column($notCal, 'bookingStart'), array_map(function($item){
    return date('Y-m-d H:i:s', strtotime("{$item} - 10 hours"));
}, array_column($apiArray, 'start')));

foreach ($result as $itemtoremove) {
  $sql = $wpdb->delete('wp_amelia_appointments', array ('bookingStart' => $itemtoremove));
}

foreach ($apiArray as $key => $value) {

        $start = $value['start'];
        $end   = $value['end'];
        $count = 0;

        $start = date('Y-m-d H:i:s', strtotime( "$start - 10 hours"));
        $end = date('Y-m-d H:i:s', strtotime( "$end - 10 hours"));

        $count = $wpdb->get_var("SELECT * FROM `wp_amelia_appointments` WHERE `bookingStart` = '$start'  AND `bookingEnd` = '$end'");

        if ($count < 1) {
                $sql = $wpdb->insert('wp_amelia_appointments', array(
                  'id' => NULL,
                  'status' => 'approved',
                  'bookingStart' => $start,
                  'bookingEnd' => $end,
                  'notifyParticipants' => '0',
                  'serviceId' => '4',
                  'providerId' => '1',
                  'internalNotes' => 'freeBusy'
                ));

                $lastid = $wpdb->insert_id;

                $sql = $wpdb->insert('wp_amelia_customer_bookings', array(
                  'id' => NULL,
                  'appointmentId' => $lastid,
                  'customerId' => '8',
                  'status' => 'approved',
                  'price' => '0',
                  'persons' => '1'
                ));
        };
}
?>
