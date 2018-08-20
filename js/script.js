var APIKey, endDate, startDate, currentTime, isotime, endTime, endisotime;
var arr = []; //empty array
var currentDate = new Date(); //current dates
var endDate = new Date();

console.log("hello");

endDate.setDate(currentDate.getDate() + 31); //set end date as the current date + a month
endDate.setMilliseconds(0); //remove milliseconds
endTime = endDate.toISOString(); //convert to iso formatted end date
endisotime = endTime.substr(0, endTime.indexOf('.')); //removes everything after the dot .

currentDate.setMilliseconds(0); //remove milliseconds
currentTime = currentDate.toISOString(); //convert to iso formatted date
isotime = currentTime.substr(0, currentTime.indexOf('.')); //removes everything after the dot .
jQuery(document).ready(function($) { //once jquery has been loaded

  $.ajax({
    url: "/data.json", //pulling the confidential info from a local json file
    dataType: "json",
    beforeSend: function(xhr) {
      if (xhr.overrideMimeType) {
        xhr.overrideMimeType("application/json");
      }
    },
    success: function(DataFromJson) {
      APIKey = DataFromJson.apikey; //This variable will equal the apikey pulled from the json file
      start(); //run start
    },
    error: function() {
      console.log("Something Went Wrong");
    }
  })


  function start() {
    $.ajax({ //this request pulls the freebusy times in an array using the apikey pulled from beforehand
      url: 'https://www.googleapis.com/calendar/v3/freeBusy?fields=calendars%2Cgroups%2Ckind%2CtimeMax%2CtimeMin&key=' + APIKey,
      type: 'POST',
      data: '{"apiUrl": "https://www.googleapis.com","timeMin": "' + isotime + '+12:00","timeMax": "' + endisotime + '+12:00",  "items": [{"id":"elliot.sturzaker@nettl.com"}],"timeZone": "UTC+12:00","groupExpansionMax": 1,"calendarExpansionMax": 1}',
      contentType: 'application/json; charset=utf-8', //setting the requirements of the request. It will only pull data from the current date until the end date specified above. Also setting the calendar in which the data is pulled from. You also need to set your calendar to public. You can hide event details and only display freebusy times if you are worried about privacy.
      dataType: 'json',
      success: function(DataFromJson) {
        list(DataFromJson); //Run the list function and send through the request results
      },
      error: function() {
        console.log("Something Went Wrong");
      }
    })
  }


  function list(DataFromJson) {
    var busydates = DataFromJson.calendars['elliot.sturzaker@nettl.com'].busy //this will grab the array details, i reccomend logging the datafromjson so you can see it for yourself.
    for (var i = 0; i < busydates.length; i++) { //loop through all the freebusy results, this will run for each event you have
      startDate = busydates[i].start.substr(0, busydates[i].start.indexOf('+')).replace('T', ' '); //make the date into a readable db format
      endDate = busydates[i].end.substr(0, busydates[i].end.indexOf('+')).replace('T', ' '); //make the date into a readable db format
      arr.push({ //push the start and end dates into an array
        'start': startDate,
        'end': endDate
      });

    }
    var jsonString = JSON.stringify(arr); //turn the array into a json array
    $.ajax({
      type: "POST",
      url: "/wp-content/themes/Divi-child/date.php",
      data: {
        jsonString: jsonString //post through the array to date.php so i can access it there
      },
      success: function(data) {
        $('body').append(data) //append date.php
      }
    });

  }
});