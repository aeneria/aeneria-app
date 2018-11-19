/**
 * @file
 *
 * Handles events on temperature vs conso dashboard.
 */

// Refresh all graph on the page base on start and end dates.
var refreshGraph = function () {

  var colors = d3.schemeGnBu[9];

  var startArray = localStorage.startDate.split('/');
  var startDate = startArray[2] + '-' + startArray[1] + '-' + startArray[0];
  var endArray = localStorage.endDate.split('/');
  var endDate = endArray[2] + '-' + endArray[1] + '-' + endArray[0];
  var frequency = localStorage.frequency;

  // Refresh conso x dju.
  $.ajax({
    url: appRoute + 'data/xy/dju/conso_elec/day/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      displayXY(data, 'conso-x-dju', '#6b4450', 'DJU', 'kWh', 0, 1);
    }
  });

  // Refresh conso vs dju.
  $.ajax({
    url: appRoute + 'data/conso_elec/evolution/day/' + startDate + '/' + endDate + '',
    success: function(result1) {
      $.ajax({
        url: appRoute + 'data/dju/evolution/day/' + startDate + '/' + endDate + '',
        success: function(result2) {
          var data1 = JSON.parse(result1);
          var data2 = JSON.parse(result2);
          displayDoubleEvolution(data1, data2, 'conso-vs-dju', ELEC_COLOR[6], DJU_COLOR[1], 'kWh', 'DJU', 1, 0.1);
        }
      });
    }
  });
}

$(document).ready(function () {

  refreshGraph();

  document.addEventListener('selection', function() {
    refreshGraph();
  });
});
