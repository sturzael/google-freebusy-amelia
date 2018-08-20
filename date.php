<?php
$items = array();
$itemsToCompare = array();
$i = 0;

define( 'SHORTINIT', true );
require_once($_SERVER['DOCUMENT_ROOT'].'/wp-config.php'); //Calling everything needed to make the querys.

global $wpdb;
$myArray = $_REQUEST['jsonString']; //setting myarray variable as the dates specified in script.js

$apiArray = json_decode($myArray, true); //decode the json into a php array
$notCal = $wpdb->get_results("SELECT * FROM `wp_amelia_appointments` WHERE `internalNotes` = 'freeBusy' AND `serviceId` = 4" , ARRAY_A);

$i == 0;
$j == 0;
foreach($notCal as $row){
    $itemsToCompare[] = $row['bookingStart'];
    $i++;
}

//pushing start times inside an array
foreach ($apiArray as $startTime) {
  $items[] = $startTime['start'];
  $j++;
}

// if ($i > $j) {
//   // i is bigger
//   print_r($itemsToCompare);
// }else {
//   print_r($items);
// }

// $diff = array_values($items);
// print_r($diff);



    foreach ($apiArray as $key => $value) { //for each item in the array run the the following
      $start = $value['start']; //setting $start as the start date specified in the array
      $end = $value['end']; //setting $end as the start date specified in the array
      $count = 0; //set count as 0 to be used later
      $start = date('Y-m-d H:i:s', strtotime( "$start - 10 hours")); //converting the time in NZ to GMT +2 as WP_Amelia uses Serbia timezones to read it
      $end = date('Y-m-d H:i:s', strtotime( "$end - 10 hours"));//converting the time in NZ to GMT +2 as WP_Amelia uses Serbia timezones to read it
      $count = $wpdb->get_var("SELECT * FROM `wp_amelia_appointments` WHERE `bookingStart` = '$start'  AND `bookingEnd` = '$end'"); //checking wether it already exists in the database


      // $notCal = $wpdb->get_col("SELECT * FROM `wp_amelia_appointments` WHERE `bookingStart` <> '$start' AND `bookingEnd` <> '$end' AND `internalNotes` = 'freeBusy' AND `serviceId` = 4"); //query broken

      // foreach ($notCal as $key => $value) {
      //   echo "- $value -";
      //   // $sql = $wpdb->delete('wp_amelia_appointments', array ('id' => $value));
      // }


      if ($count < 1) { //if it doesnt exist run this
        $sql = $wpdb->insert('wp_amelia_appointments', array ('id' => NULL, 'status' => 'approved', 'bookingStart' => $start, 'bookingEnd' => $end, 'notifyParticipants' => '0', 'serviceId' => '4', 'providerId' => '1', 'internalNotes' => 'freeBusy'
      ));//insert the appointment
        $lastid =  $wpdb->insert_id; //grab the ID as the two tables need to be linked together
        $sql = $wpdb->insert('wp_amelia_customer_bookings', array ('id' => NULL, 'appointmentId' => $lastid, 'customerId' => '5', 'status' => 'approved', 'price' => '0', 'persons' => '1')); //insert the front end booking and link the ID's together.
      };
    }
?>
