/**
 * @file
 *
 * Handles events on general dashboard.
 */

if (document.getElementById('meteo_tab')) {
  // Refresh all graph on the page base on start and end dates.
  var refreshMeteoGraph = function () {

    var place = pileaCurrent.getPlace();
    var startArray = pileaCurrent.getStartDate().split('/');
    var startDate = startArray[2] + '-' + startArray[1] + '-' + startArray[0];
    var endArray = pileaCurrent.getEndDate().split('/');
    var endDate = endArray[2] + '-' + endArray[1] + '-' + endArray[0];
    var frequency = pileaCurrent.getFrequency();

    // Refresh temperature repartition.
    pilea.loadingAnimation('temp-repartition');
    pilea.loadingAnimation('temp-repartition-legend');
    $.ajax({
      url: appRoute + 'data/' + place + '/repartition/temperature/year_v/' + startDate + '/' + endDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        pilea.displayGlobalRepartitionV(data, 'temp-repartition', TEMP_COLOR, '°C', 1, -5, 25);
        pilea.displayLegend(data, 'temp-repartition-legend', TEMP_COLOR, '°C', 1, -5, 25);
      },
      error: function(result) {
        pilea.displayError('temp-repartition');
        pilea.displayError('temp-repartition-legend');
      }
    });

    // Refresh dju evolution.
    pilea.loadingAnimation('temperature-evolution');
    $.ajax({
      url: appRoute + 'data/' + place + '/evolution/dju/' + frequency + '/' + startDate + '/' + endDate + '',
      success: function( result ) {
        var data = JSON.parse(result);
        pilea.displayGlobalEvolution(data, 'temperature-evolution', DJU_COLOR[6], 'DJU', 0, 200);
      },
      error: function(result) {
        pilea.displayError('temperature-evolution');
      }
    });

    // Refresh DJU total.
    document.getElementById('dju').innerHTML = "--";
    $.ajax({
      url: appRoute + 'data/' + place + '/sum/dju/' + startDate + '/' + endDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        document.getElementById('dju').innerHTML = parseFloat(data[0].value).toFixed(0);
      }
    });

    // Refresh nebulosity repartition.
    pilea.loadingAnimation('neb-repartition');
    pilea.loadingAnimation('neb-repartition-legend');
    $.ajax({
      url: appRoute + 'data/' + place + '/repartition/nebulosity/year_v/' + startDate + '/' + endDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        pilea.displayGlobalRepartitionV(data, 'neb-repartition', NEBULOSITY_COLOR, '%', 1, 0, 100);
        pilea.displayLegend(data, 'neb-repartition-legend', NEBULOSITY_COLOR, '%', 1, 0, 100);
      },
      error: function(result) {
        pilea.displayError('neb-repartition');
        pilea.displayError('neb-repartition-legend');
      }
    });

    // Refresh nebulosity evolution.
    pilea.loadingAnimation('nebulosity-evolution');
    $.ajax({
      url: appRoute + 'data/' + place + '/evolution/nebulosity/' + frequency + '/' + startDate + '/' + endDate + '',
      success: function( result ) {
        var data = JSON.parse(result);
        pilea.displayGlobalEvolution(data, 'nebulosity-evolution', NEBULOSITY_COLOR[6], '%', 1, 200);
      },
      error: function(result) {
        pilea.displayError('nebulosity-evolution');
      }
    });

    // Refresh day without cloud.
    document.getElementById('neb-day').innerHTML = "--";
    $.ajax({
      url: appRoute + 'data/' + place + '/inf/nebulosity/15/day/' + startDate + '/' + endDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        document.getElementById('neb-day').innerHTML = parseFloat(data[0].value).toFixed(0);
      }
    });

    // Refresh rain repartition.
    pilea.loadingAnimation('rain-repartition');
    pilea.loadingAnimation('rain-repartition-legend');
    $.ajax({
      url: appRoute + 'data/' + place + '/repartition/rain/year_v/' + startDate + '/' + endDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        pilea.displayGlobalRepartitionV(data, 'rain-repartition', RAIN_COLOR, 'mm', 1, 0);
        pilea.displayLegend(data, 'rain-repartition-legend', RAIN_COLOR, 'mm', 1, 0);
      },
      error: function(result) {
        pilea.displayError('rain-repartition');
        pilea.displayError('rain-repartition-legend');
      }
    });

    // Refresh rain evolution.
    pilea.loadingAnimation('rain-evolution');
    $.ajax({
      url: appRoute + 'data/' + place + '/evolution/rain/' + frequency + '/' + startDate + '/' + endDate + '',
      success: function( result ) {
        var data = JSON.parse(result);
        pilea.displayGlobalEvolution(data, 'rain-evolution', RAIN_COLOR[6], 'mm', 1, 200);
      },
      error: function(result) {
        pilea.displayError('rain-evolution');
      }
    });

    // Refresh day without rain.
    document.getElementById('rain-day').innerHTML = "--";
    $.ajax({
      url: appRoute + 'data/' + place + '/inf/rain/0/day/' + startDate + '/' + endDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        document.getElementById('rain-day').innerHTML = parseFloat(data[0].value).toFixed(0);
      }
    });

    // Refresh humidity repartition.
    pilea.loadingAnimation('humidity-repartition');
    pilea.loadingAnimation('humidity-repartition-legend');
    $.ajax({
      url: appRoute + 'data/' + place + '/repartition/humidity/year_v/' + startDate + '/' + endDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        pilea.displayGlobalRepartitionV(data, 'humidity-repartition', HUMIDITY_COLOR, '%', 1, 0, 100);
        pilea.displayLegend(data, 'humidity-repartition-legend', HUMIDITY_COLOR, '%', 1, 0, 100);
      },
      error: function(result) {
        pilea.displayError('humidity-repartition');
        pilea.displayError('humidity-repartition-legend');
      }
    });

    // Refresh humidity evolution.
    pilea.loadingAnimation('humidity-evolution');
    $.ajax({
      url: appRoute + 'data/' + place + '/evolution/humidity/' + frequency + '/' + startDate + '/' + endDate + '',
      success: function( result ) {
        var data = JSON.parse(result);
        pilea.displayGlobalEvolution(data, 'humidity-evolution', HUMIDITY_COLOR[6], '%', 1, 200);
      },
      error: function(result) {
        pilea.displayError('humidity-evolution');
      }
    });

    // Refresh day at less than 70% of humidity.
    document.getElementById('humidity-day').innerHTML = "--";
    $.ajax({
      url: appRoute + 'data/' + place + '/inf/humidity/70/day/' + startDate + '/' + endDate + '',
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

    refreshMeteoGraph();

    document.addEventListener('selection', function() {
      refreshMeteoGraph();
    });
  });
}



