jQuery(document).ready(function($) {
  console.log($("span:contains('Busy')"));
  $("span:contains('Busy')").parent().css("display", "none");
});