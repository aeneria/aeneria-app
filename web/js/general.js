/**
 * @file
 *
 * Handles events on general dashboard.
 */

// Refresh all graph on the page base on start and end dates.
var refreshGraph = function (startDate, endDate, frequency, colors) {
  // Refresh week repartition.
  $.ajax({
    url: appRoute + "data/conso_elec/repartition/week/" + startDate + "/" + endDate + "",
    success: function(result) {
      var data = JSON.parse(result);
      displayWeekRepartition(data, 'conso-week-repartition', colors, "kWh");
      $('[data-toggle="tooltip"]').tooltip();
    }
  });

  // Refresh global repartition.
  $.ajax({
    url: appRoute + "data/conso_elec/repartition/year_h/" + startDate + "/" + endDate + "",
    success: function(result) {
      var data = JSON.parse(result);
      displayGlobalRepartition(data, 'conso-global-repartition', colors, "kWh");
      $('[data-toggle="tooltip"]').tooltip();
    }
  });

  // Refresh global evolution.
  $.ajax({
    url: appRoute + "data/conso_elec/evolution/" + frequency + "/" + startDate + "/" + endDate + "",
    success: function( result ) {
      var data = JSON.parse(result);
      displayGlobalEvolution(data, 'conso-global-evolution', colors[8], "kWh");
    }
  });
}

$(document).ready(function () {
  // Defines color scale.
  var colors = ["#ebedf0", "#196127"];

  refreshGraph(localStorage.startDate, localStorage.endDate, localStorage.frequency, d3.schemeGnBu[9]);

});


