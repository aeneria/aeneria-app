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
      displayGlobalRepartitionV(data, 'temp-repartition', TEMP_COLOR, '°C', 1, -5, 25);
      displayLegend(data, 'temp-repartition-legend', TEMP_COLOR, '°C', 1, -5, 25);
    }
  });

  // Refresh DJU total.
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
      displayGlobalRepartitionV(data, 'neb-repartition', NEBULOSITY_COLOR, '%', 1, 0, 100);
      displayLegend(data, 'neb-repartition-legend', NEBULOSITY_COLOR, '%', 1, 0, 100);
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
      displayGlobalRepartitionV(data, 'rain-repartition', RAIN_COLOR, 'mm', 1, 0);
      displayLegend(data, 'rain-repartition-legend', RAIN_COLOR, 'mm', 1, 0);
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

  // Refresh humidity repartition.
  $.ajax({
    url: appRoute + 'data/humidity/repartition/year_v/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      displayGlobalRepartitionV(data, 'humidity-repartition', HUMIDITY_COLOR, '%', 1, 0, 100);
      displayLegend(data, 'humidity-repartition-legend', HUMIDITY_COLOR, '%', 1, 0, 100);
    }
  });

  // Refresh day at less than 70% of humidity.
  $.ajax({
    url: appRoute + 'data/humidity/inf/70/day/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      document.getElementById('humidity-day').innerHTML = parseFloat(data[0].value).toFixed(0);
    }
  });

  // Refresh total of days.
  nbDay = (new Date(endDate) - new Date(startDate)) / (1000*60*60*24);
  var nbDayElement = document.getElementsByClassName('nb-day');
  for(var i = 0; i < nbDayElement.length; i++) {
    nbDayElement.item(i).innerHTML = nbDay;
  }

}

$(document).ready(function () {

  refreshGraph();

  document.addEventListener('selection', function() {
    refreshGraph();
  });
});



