<?php
$googleArray = array(); //used for delete functions
$databaseArray = array();

define('SHORTINIT', true);

require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php'); //Calling everything needed to make the querys.

global $wpdb;

$myArray = $_REQUEST['jsonString']; //setting myarray variable as the dates specified in script.js

$apiArray = json_decode($myArray, true); //decode the json into a php array

//DELETING FROM DATABASE IF IT HAS BEEN DELETED IN GOOGLE CALENDAR

$notCal = $wpdb->get_results("SELECT * FROM `wp_amelia_appointments` WHERE `internalNotes` = 'freeBusy' AND `serviceId` = 4", ARRAY_A); //Get all the appointments that have been creataed by this program.

foreach ($notCal as $row) { //adding only the start times from the database to an array called databaseArray
  $databaseArray[] = $row['bookingStart'];
}

foreach ($apiArray as $rows) { //adding only the start times from google into an array
  $formattedItem = $rows['start'];
  $formattedItem = date('Y-m-d H:i:s', strtotime( "$formattedItem - 10 hours")); //gotta convert times from google so that it mathces the time format used in the db
  $googleArray[] =  $formattedItem;
}

$result = array_diff($databaseArray, $googleArray); //getting the differance between the two start times - this way i can see what items were in google calendar and the db but have been since deleted.

foreach ($result as $itemtoremove) { //for every difference in the array do this
  $sql = $wpdb->delete('wp_amelia_appointments', array ('bookingStart' => $itemtoremove)); //delete each row that matches the booking start
}

//ADDING TO DATABASE
foreach ($apiArray as $key => $value) { //for each item in the array run the the following

        $start = $value['start']; //setting $start as the start date specified in the array
        $end   = $value['end']; //setting $end as the start date specified in the array
        $count = 0; //set count as 0 to be used later

        $start = date('Y-m-d H:i:s', strtotime( "$start - 10 hours")); //converting the time in NZ to GMT +2 as WP_Amelia uses Serbia timezones to read it

        $end = date('Y-m-d H:i:s', strtotime( "$end - 10 hours"));//converting the time in NZ to GMT +2 as WP_Amelia uses Serbia timezones to read it

        $count = $wpdb->get_var("SELECT * FROM `wp_amelia_appointments` WHERE `bookingStart` = '$start'  AND `bookingEnd` = '$end'"); //checking wether it already exists in the database

        if ($count < 1) { //if it doesnt exist run this
                $sql = $wpdb->insert('wp_amelia_appointments', array(
                  'id' => NULL,
                  'status' => 'approved',
                  'bookingStart' => $start,
                  'bookingEnd' => $end,
                  'notifyParticipants' => '0',
                  'serviceId' => '4',
                  'providerId' => '1',
                  'internalNotes' => 'freeBusy'
                )); //insert the appointment

                $lastid = $wpdb->insert_id; //grab the ID as the two tables need to be linked together

                $sql = $wpdb->insert('wp_amelia_customer_bookings', array(
                  'id' => NULL,
                  'appointmentId' => $lastid,
                  'customerId' => '5',
                  'status' => 'approved',
                  'price' => '0',
                  'persons' => '1'
                )); //insert the front end booking and link the ID's together.
        };
}
?>
