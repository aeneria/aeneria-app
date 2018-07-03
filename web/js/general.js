/**
 * @file
 *
 * Handles events on general dashboard.
 */

// Refresh all graph on the page base on start and end dates.
var refreshGraph = function (startDate, endDate, frequency, colors) {
  // Refresh week repartition.
  $.ajax({
    url: "/data/conso/repartition/week/" + startDate + "/" + endDate + "",
    success: function(result) {
      var data = JSON.parse(result);
      displayWeekRepartition(data, 'conso-week-repartition', colors, "kWh");
    }
  });

  // Refresh global repartition.
  $.ajax({
    url: "/data/conso/repartition/year/" + startDate + "/" + endDate + "",
    success: function(result) {
      var data = JSON.parse(result);
      displayGlobalRepartition(data, 'conso-global-repartition', colors, "kWh");
    }
  });

  // Refresh global evolution.
  $.ajax({
    url: "/data/conso/evolution/" + frequency + "/" + startDate + "/" + endDate + "",
    success: function( result ) {
      var data = JSON.parse(result);
      displayGlobalEvolution(data, 'conso-global-evolution', colors[1], "kWh");
    }
  });
}

$(document).ready(function () {
  // Defines color scale.
  var colors = ["#CAD7B2", "#97B5A7"];


  refreshGraph(localStorage.startDate, localStorage.endDate, localStorage.frequency, colors);
});


