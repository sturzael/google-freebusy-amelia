<?php
$googleArray = array();
$databaseArray = array();

date_default_timezone_set("NZ");

define('SHORTINIT', true);

require_once($_SERVER['DOCUMENT_ROOT'] . '/wp-config.php');

global $wpdb;

$myArray = $_REQUEST['jsonString'];

$apiArray = json_decode($myArray, true);

$notCal = $wpdb->get_results("SELECT * FROM `wp_amelia_appointments` WHERE `internalNotes` = 'freeBusy' AND `serviceId` = 4", ARRAY_A);

foreach ($notCal as $row) {
  $databaseArray[] = $row['bookingStart'];
}

$startDST =  date('Y-m-d H:i:s', strtotime('first sunday of April ' .  date("Y", strtotime('+1 years')))) . "\n";
$currentStartDST =  date('Y-m-d H:i:s', strtotime('first sunday of April ' .  date("Y"))) . "\n";
$endDST =  date('Y-m-d H:i:s', strtotime('last sunday of September ' .  date("Y"))) . "\n";
$currentEndDST =  date('Y-m-d H:i:s', strtotime('last sunday of September ' .  date("Y", strtotime('-1 years')))) . "\n";


foreach ($apiArray as $rows) {
  $formattedItem = $rows['start'];
  $unixTime = date('Y-m-d H:i:s',strtotime($formattedItem));
  if($unixTime < $startDST && $unixTime > $endDST) {
    $dst = '9';
  }elseif ($unixTime < $endDST && $unixTime > $currentStartDST) {
    $dst = '10';
  }elseif ($unixTime < $currentStartDST && $unixTime > $currentEndDST) {
    $dst = '9';
  }
  $formattedItem = date('Y-m-d H:i:s', strtotime( "$formattedItem - $dst hours"));
  $googleArray[] =  $formattedItem;
}

$result = array_diff($databaseArray, $googleArray);

foreach ($result as $itemtoremove) {
  $sql = $wpdb->delete('wp_amelia_appointments', array ('bookingStart' => $itemtoremove));
}

foreach ($apiArray as $key => $value) {

        $start = $value['start'];
        $end   = $value['end'];
        $count = 0;

        $start = date('Y-m-d H:i:s', strtotime( "$start - 12 hours"));

        $end = date('Y-m-d H:i:s', strtotime( "$end - 12 hours"));

        $count = $wpdb->get_var("SELECT * FROM `wp_amelia_appointments` WHERE `bookingStart` = '$start'  AND `bookingEnd` = '$end'");

        if ($count < 1) { //if it doesnt exist run this
                $sql = $wpdb->insert('wp_amelia_appointments', array(
                  'id' => NULL, //DON'T CHANGE THIS AS THIS WILL AUTO INCREMENT ITSELF
                  'status' => 'approved', //Approved is probably the best option, if you change to pending you will have to manually approve
                  'bookingStart' => $start, //DON'T CHANGE
                  'bookingEnd' => $end, // DON'T CHANGE
                  'notifyParticipants' => '0', //DON'T CHANGE UNLESS YOU WANT TO SEND EMAILS TO CUSTOMER / SERVICE PROVIDER - CHANGE TO 1 IF YOU WANT THAT
                  'serviceId' => '4', // MAKE A NEW SERVICE OR SOMETHING CALLED BUSY AND GRAB THE ID FROM: wp_amelia_services
                  'providerId' => '4', //MAKE A NEW EMPLOYEE OR WHOEVER'S CALENDAR IS LINKED UP AND THEN GRAB THE ID FROM: wp_amelia_users
                  'internalNotes' => 'freeBusy' //DON'T CHANGE
                )); //insert the appointment

                $lastid = $wpdb->insert_id; //grab the ID as the two tables need to be linked together

                $sql = $wpdb->insert('wp_amelia_customer_bookings', array(
                  'id' => NULL, //DON'T CHANGE THIS AS THIS WILL AUTO INCREMENT ITSELF
                  'appointmentId' => $lastid, // DON'T CHANGE THIS
                  'customerId' => '8', //GET WHATEVER CUSTOMER ID YOU WANT FROM: wp_amelia_users - make sure it is a 'type = customer'
                  'status' => 'approved', //Approved is probably the best option, if you change to pending you will have to manually approve
                  'price' => '0', //LEAVE AS 0
                  'persons' => '1' //1 is fine, or change to whatever.
                )); //insert the front end booking and link the ID's together.
        };
}
// ?>
