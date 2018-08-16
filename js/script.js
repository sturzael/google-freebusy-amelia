var APIKey, endDate, startDate, currentTime, isotime, endTime, endisotime;
var arr = [];
var currentDate = new Date();
var endDate = new Date();
endDate.setDate(currentDate.getDate() + 31);
endDate.setMilliseconds(0);
endTime = endDate.toISOString();
endisotime = endTime.substr(0, endTime.indexOf('.'));

currentDate.setMilliseconds(0);
currentTime = currentDate.toISOString();
isotime = currentTime.substr(0, currentTime.indexOf('.'));
jQuery(document).ready(function($) {

  $.ajax({
    url: "/data.json",
    dataType: "json",
    beforeSend: function(xhr) {
      if (xhr.overrideMimeType) {
        xhr.overrideMimeType("application/json");
      }
    },
    success: function(DataFromJson) {
      APIKey = DataFromJson.apikey;
      start();
    },
    error: function() {
      console.log("Something Went Wrong");
    }
  })


  function start() {
    $.ajax({
      url: 'https://www.googleapis.com/calendar/v3/freeBusy?fields=calendars%2Cgroups%2Ckind%2CtimeMax%2CtimeMin&key=' + APIKey,
      type: 'POST',
      data: '{"apiUrl": "https://www.googleapis.com","timeMin": "' + isotime + '+12:00","timeMax": "' + endisotime + '+12:00",  "items": [{"id":"elliot.sturzaker@nettl.com"}],"timeZone": "UTC+12:00","groupExpansionMax": 1,"calendarExpansionMax": 1}',
      contentType: 'application/json; charset=utf-8',
      dataType: 'json',
      success: function(DataFromJson) {
        list(DataFromJson);
      },
      error: function() {
        console.log("Something Went Wrong");
      }
    })
  }


  function list(DataFromJson) {
    var busydates = DataFromJson.calendars['elliot.sturzaker@nettl.com'].busy
    for (var i = 0; i < busydates.length; i++) {
      startDate = busydates[i].start.substr(0, busydates[i].start.indexOf('+')).replace('T', ' ');
      endDate = busydates[i].end.substr(0, busydates[i].end.indexOf('+')).replace('T', ' ');
      arr.push({
        'start': startDate,
        'end': endDate
      });

    }
    var jsonString = JSON.stringify(arr);
    $.ajax({
      type: "POST",
      url: "/wp-content/themes/Divi-child/date.php",
      data: {
        jsonString: jsonString
      },
      success: function(data) {
        $('body').append(data)
      }
    });

  }
});