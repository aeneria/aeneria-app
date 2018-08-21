/**
 * @file
 *
 * Handles events on general dashboard.
 */

// Refresh all graph on the page base on start and end dates.
var refreshGraph = function (target) {
  var targetMonth = target.getAttribute('data-month');

  var start = '';
  var end = '';

  var date = new Date(), y = date.getFullYear(), m = date.getMonth();
  switch(targetMonth) {
    case 'current':
      var start = new Date(y, m, 1).toLocaleDateString();
      var end = new Date(y, m + 1, 0).toLocaleDateString();
      break;
    case 'previous':
      var start = new Date(y, m - 1, 1).toLocaleDateString();
      var end = new Date(y, m, 0).toLocaleDateString();
      break;
  }

  var startArray = start.split('/');
  var startDate = startArray[2] + '-' + startArray[1] + '-' + startArray[0];
  var endArray = end.split('/');
  var endDate = endArray[2] + '-' + endArray[1] + '-' + endArray[0];

  // Refresh global conso repartition.
  $.ajax({
    url: appRoute + 'data/conso_elec/repartition/year_v/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      displayGlobalRepartitionV(data, 'conso-repartition-' + targetMonth, d3.schemeGnBu[9], 'kWh', 0);
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

  // Refresh conso min.
  $.ajax({
    url: appRoute + 'data/conso_elec/min/day/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      var date = new Date(data[0].date);
      document.getElementById('conso-min-' + targetMonth).innerHTML = parseFloat(data[0].value).toFixed(1) + ' kWh (' + date.toLocaleDateString() + ')';
    }
  });

  // Refresh conso moy.
  $.ajax({
    url: appRoute + 'data/conso_elec/avg/day/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      document.getElementById('conso-avg-' + targetMonth).innerHTML = parseFloat(data[0].value).toFixed(1) + ' kWh/jour';
    }
  });

  // Refresh conso max.
  $.ajax({
    url: appRoute + 'data/conso_elec/max/day/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      document.getElementById('conso-max-' + targetMonth).innerHTML = parseFloat(data[0].value).toFixed(1) + ' kWh (' + data[0].date + ')';
    }
  });


  // Refresh global temp repartition.
  $.ajax({
    url: appRoute + 'data/temperature/repartition/year_v/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      var color = d3.schemeRdYlBu[9].slice();
      displayGlobalRepartitionV(data, 'temp-repartition-' + targetMonth, color.reverse(), '°C', -5, 25);
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

  // Refresh temperature min.
  $.ajax({
    url: appRoute + 'data/temperature/min/day/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      document.getElementById('temp-min-' + targetMonth).innerHTML = parseFloat(data[0].value).toFixed(1) + '°C (' + data[0].date + ')';
    }
  });

  // Refresh temperature moy.
  $.ajax({
    url: appRoute + 'data/temperature/avg/day/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      document.getElementById('temp-avg-' + targetMonth).innerHTML = parseFloat(data[0].value).toFixed(1) + '°C';
    }
  });

  // Refresh temperature max.
  $.ajax({
    url: appRoute + 'data/temperature/max/day/' + startDate + '/' + endDate + '',
    success: function(result) {
      var data = JSON.parse(result);
      document.getElementById('temp-max-' + targetMonth).innerHTML = parseFloat(data[0].value).toFixed(1) + '°C (' + data[0].date + ')';
    }
  });
}

$(document).ready(function () {
  var monthSummaries = document.getElementsByClassName('month-summary');

  for (var j in monthSummaries) {
    refreshGraph(monthSummaries[j]);
  }
});




