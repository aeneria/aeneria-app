/**
 * @file
 *
 * Handles events on general dashboard.
 */

// Refresh all graph on the page base on start and end dates.
var refreshGraph = function () {

  var colors = d3.schemeGnBu[9];

  var startArray = localStorage.startDate.split('/');
  var startDate = startArray[2] + '-' + startArray[1] + '-' + startArray[0];
  var endArray = localStorage.endDate.split('/');
  var endDate = endArray[2] + '-' + endArray[1] + '-' + endArray[0];
  var frequency = localStorage.frequency;

  // Refresh conso total.
  $.ajax({
    url: appRoute + 'data/conso_elec/sum/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      document.getElementById('conso-total').innerHTML = '<b>' + parseFloat(data[0].value).toFixed(1) + ' kWh</b>';
    }
  });

  // Refresh week repartition.
  $.ajax({
    url: appRoute + 'data/conso_elec/repartition/week/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      displayWeekRepartition(data, 'conso-week-repartition', colors, 'kWh', 1, 0);
    }
  });

  // Refresh global repartition.
  $.ajax({
    url: appRoute + 'data/conso_elec/repartition/year_h/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      displayGlobalRepartitionH(data, 'conso-global-repartition', colors, 'kWh', 1, 0);
      displayLegend(data, 'conso-global-repartition-legend', colors, 'kWh', 1);
    }
  });

  // Refresh global evolution.
  $.ajax({
    url: appRoute + 'data/conso_elec/evolution/' + frequency + '/' + startDate + '/' + endDate + '',
    success: function( result ) {
      var data = JSON.parse(result);
      displayGlobalEvolution(data, 'conso-global-evolution', colors[7], 'kWh', 1);
    }
  });
}

$(document).ready(function () {

  refreshGraph();

  document.addEventListener('selection', function() {
    refreshGraph();
  });
});




