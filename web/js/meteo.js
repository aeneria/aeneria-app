/**
 * @file
 *
 * Handles events on general dashboard.
 */

// Refresh all graph on the page base on start and end dates.
var refreshGraph = function () {

  var startArray = localStorage.startDate.split('/');
  var startDate = startArray[2] + '-' + startArray[1] + '-' + startArray[0];
  var endArray = localStorage.endDate.split('/');
  var endDate = endArray[2] + '-' + endArray[1] + '-' + endArray[0];
  var frequency = localStorage.frequency;

  // Refresh temperature repartition.
  $.ajax({
    url: appRoute + 'data/temperature/repartition/year_v/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      var color = d3.schemeRdYlBu[9].slice();
      displayGlobalRepartitionV(data, 'temp-repartition', color.reverse(), '°C', 1, -5, 25);
      displayLegend(data, 'temp-repartition-legend', color, '°C', 1, -5, 25);
    }
  });

  //Refresh DJU total.
  $.ajax({
    url: appRoute + 'data/dju/sum/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      document.getElementById('dju').innerHTML = parseFloat(data[0].value).toFixed(0);
    }
  });

  // Refresh nebulosity repartition.
  $.ajax({
    url: appRoute + 'data/nebulosity/repartition/year_v/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      var color = ['#62ddf7', '#67d7ef', '#6cd1e6', '#71cbde', '#76c6d6', '#7bbfce', '#80b9c6', '#84b4be', '#89afb6'];
      displayGlobalRepartitionV(data, 'neb-repartition', color, '%', 1, 0, 100);
      displayLegend(data, 'neb-repartition-legend', color, '%', 1, 0, 100);
    }
  });

  // Refresh day without cloud.
  $.ajax({
    url: appRoute + 'data/nebulosity/inf/15/day/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      document.getElementById('neb-day').innerHTML = parseFloat(data[0].value).toFixed(0);
    }
  });

  // Refresh rain repartition.
  $.ajax({
    url: appRoute + 'data/rain/repartition/year_v/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      displayGlobalRepartitionV(data, 'rain-repartition', d3.schemeGnBu[9], 'mm', 1, 0);
      displayLegend(data, 'rain-repartition-legend', d3.schemeGnBu[9], 'mm', 1, 0);
    }
  });

  // Refresh day without rain.
  $.ajax({
    url: appRoute + 'data/rain/inf/0/day/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      document.getElementById('rain-day').innerHTML = parseFloat(data[0].value).toFixed(0);
    }
  });
}

$(document).ready(function () {

  refreshGraph();

  document.addEventListener('selection', function() {
    refreshGraph();
  });
});



