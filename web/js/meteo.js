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
      displayGlobalRepartitionV(data, 'temp-repartition', color.reverse(), '°C', -5, 25);
    }
  });

  // Refresh temperature min.
  $.ajax({
    url: appRoute + 'data/temperature/min/day/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      var date = new Date(data[0].date);
      document.getElementById('temp-min').innerHTML = parseFloat(data[0].value).toFixed(1) + '°C (' + date.toLocaleDateString() + ')';
    }
  });

  // Refresh temperature moy.
  $.ajax({
    url: appRoute + 'data/temperature/avg/day/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      document.getElementById('temp-avg').innerHTML = parseFloat(data[0].value).toFixed(1) + '°C';
    }
  });

  // Refresh temperature max.
  $.ajax({
    url: appRoute + 'data/temperature/max/day/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      var date = new Date(data[0].date);
      document.getElementById('temp-max').innerHTML = parseFloat(data[0].value).toFixed(1) + '°C (' + date.toLocaleDateString() + ')';
    }
  });

  // Refresh nebulosity repartition.
  $.ajax({
    url: appRoute + 'data/nebulosity/repartition/year_v/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      var color = ['#5de2ff', '#62ddf7', '#67d7ef', '#6cd1e6', '#71cbde', '#76c6d6', '#7bbfce', '#80b9c6', '#84b4be', '#89afb6', '#89afb6'];
      displayGlobalRepartitionV(data, 'neb-repartition', color, '%', 0, 100);
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
      displayGlobalRepartitionV(data, 'rain-repartition', d3.schemeGnBu[9], 'mm');
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



