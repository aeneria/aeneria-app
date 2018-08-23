/**
 * @file
 *
 * Handles events on general dashboard.
 */

// Refresh all graph on the page base on start and end dates.
var refreshGraph = function (target) {
  var targetMonth = parseInt(target.getAttribute('data-month'));

  var start = '';
  var end = '';

  var date = new Date(), y = date.getFullYear(), m = date.getMonth();
  var start = new Date(y, m + targetMonth, 1);
  var end = new Date(y, m + targetMonth + 1, 0);

  document.getElementById('title-' + targetMonth).innerHTML = MONTHS_NAME[start.getMonth()] + ' ' + start.getFullYear();


  var start = start.toLocaleDateString();
  var end = end.toLocaleDateString();

  var startArray = start.split('/');
  var startDate = startArray[2] + '-' + startArray[1] + '-' + startArray[0];
  var endArray = end.split('/');
  var endDate = endArray[2] + '-' + endArray[1] + '-' + endArray[0];

  // Refresh global conso repartition.
  $.ajax({
    url: appRoute + 'data/conso_elec/repartition/year_v/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      displayGlobalRepartitionV(data, 'conso-repartition-' + targetMonth, d3.schemeGnBu[9], 'kWh', 1, 0);
      displayLegend(data, 'conso-repartition-legend-' + targetMonth, d3.schemeGnBu[9], 'kWh', 1, 0);
    }
  });

  // Refresh conso total.
  $.ajax({
    url: appRoute + 'data/conso_elec/sum/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      document.getElementById('conso-total-' + targetMonth).innerHTML = '<b>' + parseFloat(data[0].value).toFixed(1) + ' kWh</b>';
    }
  });

  // Refresh global temp repartition.
  $.ajax({
    url: appRoute + 'data/temperature/repartition/year_v/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      var color = d3.schemeRdYlBu[9].slice();
      displayGlobalRepartitionV(data, 'temp-repartition-' + targetMonth, color.reverse(), '°C', 1, -5, 25);
      displayLegend(data, 'temp-repartition-legend-' + targetMonth, color, '°C', 1, -5, 25);
    }
  });

  // Refresh DJU total.
  $.ajax({
    url: appRoute + 'data/dju/sum/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      document.getElementById('dju-total-' + targetMonth).innerHTML = '<b>' + parseFloat(data[0].value).toFixed(0) + ' DJU</b>';
    }
  });
}

$(document).ready(function () {
  var monthSummaries = document.getElementsByClassName('month-summary');

  for (var j in monthSummaries) {
    refreshGraph(monthSummaries[j]);
  }
});




