<?php
$googleArray = array(); //used for delete functions
$databaseArray = array();

date_default_timezone_set("NZ");

define('SHORTINIT', true);

require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php'); //Calling everything needed to make the querys.

global $wpdb;

$myArray = $_REQUEST['jsonString']; //setting myarray variable as the dates specified in script.js

$apiArray = json_decode($myArray, true); //decode the json into a php array

//DELETING FROM DATABASE IF IT HAS BEEN DELETED IN GOOGLE CALENDAR

$notCal = $wpdb->get_results("SELECT * FROM `wp_amelia_appointments` WHERE `internalNotes` = 'freeBusy' AND `serviceId` = 4", ARRAY_A); //Get all the appointments that have been creataed by this program. CHANGE SERVICE ID TO WHATEVER SERVICE ID YOU HAVE SET BELOW

foreach ($notCal as $row) { //adding only the start times from the database to an array called databaseArray
  $databaseArray[] = $row['bookingStart'];
}

foreach ($apiArray as $rows) { //adding only the start times from google into an array
  $formattedItem = $rows['start'];
  $unixTime = strtotime($formattedItem);

  $formattedItem = date('Y-m-d H:i:s', strtotime( "$formattedItem - 10 hours")); //gotta convert times from google so that it mathces the time format used in the db
  $googleArray[] =  $formattedItem;
}


function daylightSaving($year)
{
    $months = array('September');

    foreach ($months as $month) {
        echo $month . ': ' . date('Y-m-d', strtotime('last sunday of ' . $month . ' ' . $year)) . "\n";
    }
}

daylightSaving($argv[1]);




$result = array_diff($databaseArray, $googleArray); //getting the differance between the two start times - this way i can see what items were in google calendar and the db but have been since deleted.

foreach ($result as $itemtoremove) { //for every difference in the array do this
  $sql = $wpdb->delete('wp_amelia_appointments', array ('bookingStart' => $itemtoremove)); //delete each row that matches the booking start
}

//ADDING TO DATABASE
foreach ($apiArray as $key => $value) { //for each item in the array run the the following

        $start = $value['start']; //setting $start as the start date specified in the array
        $end   = $value['end']; //setting $end as the start date specified in the array
        $count = 0; //set count as 0 to be used later

        $start = date('Y-m-d H:i:s', strtotime( "$start - 12 hours")); //converting the time in NZ to GMT +2 as WP_Amelia uses Serbia timezones to read it

        $end = date('Y-m-d H:i:s', strtotime( "$end - 12 hours"));//converting the time in NZ to GMT +2 as WP_Amelia uses Serbia timezones to read it

        $count = $wpdb->get_var("SELECT * FROM `wp_amelia_appointments` WHERE `bookingStart` = '$start'  AND `bookingEnd` = '$end'"); //checking wether it already exists in the database

        if ($count < 1) { //if it doesnt exist run this
                // $sql = $wpdb->insert('wp_amelia_appointments', array(
                //   'id' => NULL, //DON'T CHANGE THIS AS THIS WILL AUTO INCREMENT ITSELF
                //   'status' => 'approved', //Approved is probably the best option, if you change to pending you will have to manually approve
                //   'bookingStart' => $start, //DON'T CHANGE
                //   'bookingEnd' => $end, // DON'T CHANGE
                //   'notifyParticipants' => '0', //DON'T CHANGE UNLESS YOU WANT TO SEND EMAILS TO CUSTOMER / SERVICE PROVIDER - CHANGE TO 1 IF YOU WANT THAT
                //   'serviceId' => '4', // MAKE A NEW SERVICE OR SOMETHING CALLED BUSY AND GRAB THE ID FROM: wp_amelia_services
                //   'providerId' => '4', //MAKE A NEW EMPLOYEE OR WHOEVER'S CALENDAR IS LINKED UP AND THEN GRAB THE ID FROM: wp_amelia_users
                //   'internalNotes' => 'freeBusy' //DON'T CHANGE
                // )); //insert the appointment
                //
                // $lastid = $wpdb->insert_id; //grab the ID as the two tables need to be linked together
                //
                // $sql = $wpdb->insert('wp_amelia_customer_bookings', array(
                //   'id' => NULL, //DON'T CHANGE THIS AS THIS WILL AUTO INCREMENT ITSELF
                //   'appointmentId' => $lastid, // DON'T CHANGE THIS
                //   'customerId' => '8', //GET WHATEVER CUSTOMER ID YOU WANT FROM: wp_amelia_users - make sure it is a 'type = customer'
                //   'status' => 'approved', //Approved is probably the best option, if you change to pending you will have to manually approve
                //   'price' => '0', //LEAVE AS 0
                //   'persons' => '1' //1 is fine, or change to whatever.
                // )); //insert the front end booking and link the ID's together.
        };
}
?>
