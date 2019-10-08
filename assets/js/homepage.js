/**
 * @file
 *
 * Handles events on general dashboard.
 */

if (document.getElementById('homepage')) {

  // Refresh all graph on the page base on start and end dates.
  var refreshIndexGraph = function (target) {
    var targetMonth = parseInt(target.getAttribute('data-month'));

    var place = pileaCurrent.getPlace();

    var start = '';
    var end = '';

    var date = new Date();
    date.setDate(date.getDate() - 1);

    var y = date.getFullYear();
    var m = date.getMonth();

    var start = new Date(y, m + targetMonth, 1);
    if (targetMonth == 0) {
      var end = date;
    }
    else {
      var end = new Date(y, m + targetMonth + 1, 0);
    }

    document.getElementById('title-' + targetMonth).innerHTML = MONTHS_NAME[start.getMonth()] + ' ' + start.getFullYear();

    var startDate = start.toISOString().substring(0, 10);
    var endDate = end.toISOString().substring(0, 10);

    // Refresh global conso repartition.
    $.ajax({
      url: appRoute + 'data/' + place + '/repartition/conso_elec/year_v/' + startDate + '/' + endDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        pilea.displayGlobalRepartitionV(data, 'conso-repartition-' + targetMonth, ELEC_COLOR, 'kWh', 1, 0);
        pilea.displayLegend(data, 'conso-repartition-legend-' + targetMonth, ELEC_COLOR, 'kWh', 1, 0);
      }
    });

    // Refresh conso total.
    $.ajax({
      url: appRoute + 'data/' + place + '/sum/conso_elec/' + startDate + '/' + endDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        if (data.length > 0) {
          document.getElementById('conso-total-' + targetMonth).innerHTML = '<b>' + parseFloat(data[0].value).toFixed(1) + ' kWh</b>';
        }
        else {
          document.getElementById('conso-total-' + targetMonth).innerHTML = '<b>-- kWh</b>';
        }
      }
    });

    // Refresh global temp repartition.
    $.ajax({
      url: appRoute + 'data/' + place + '/repartition/temperature/year_v/' + startDate + '/' + endDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        pilea.displayGlobalRepartitionV(data, 'temp-repartition-' + targetMonth, TEMP_COLOR, '°C', 1, -5, 25);
        pilea.displayLegend(data, 'temp-repartition-legend-' + targetMonth, TEMP_COLOR, '°C', 1, -5, 25);
      }
    });

    // Refresh DJU total.
    $.ajax({
      url: appRoute + 'data/' + place + '/sum/dju/' + startDate + '/' + endDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        if (data.length > 0) {
          document.getElementById('dju-total-' + targetMonth).innerHTML = '<b>' + parseFloat(data[0].value).toFixed(0) + ' DJU</b>';
        }
        else {
          document.getElementById('dju-total-' + targetMonth).innerHTML = '<b>-- DJU</b>';
        }
      }
    });
  }

  document.getElementById('load-more').onclick = function(event) {
    var rowElement = this.closest('.row');
    var newMonthSummary = rowElement.previousElementSibling.cloneNode(true);

    var monthDiv = newMonthSummary.getElementsByClassName('month-summary')[0];
    var oldId = monthDiv.getAttribute('data-month');
    var newId = parseInt(oldId)-1;

    monthDiv.setAttribute('data-month', newId);
    newMonthSummary.getElementsByClassName('card-title')[0].setAttribute('id', 'title-' + newId);

    newMonthSummary.getElementsByClassName('conso-total')[0].setAttribute('id', 'conso-total-' + newId);
    newMonthSummary.getElementsByClassName('conso-repartition')[0].setAttribute('id', 'conso-repartition-' + newId);
    newMonthSummary.getElementsByClassName('conso-repartition-legend')[0].setAttribute('id', 'conso-repartition-legend-' + newId);

    newMonthSummary.getElementsByClassName('dju-total')[0].setAttribute('id', 'dju-total-' + newId);
    newMonthSummary.getElementsByClassName('temp-repartition')[0].setAttribute('id', 'temp-repartition-' + newId);
    newMonthSummary.getElementsByClassName('temp-repartition-legend')[0].setAttribute('id', 'temp-repartition-legend-' + newId);

    rowElement.parentElement.insertBefore(newMonthSummary, rowElement);
    refreshIndexGraph(monthDiv);
  };

  $(document).ready(function () {
    var monthSummaries = document.getElementsByClassName('month-summary');

    for (var j = 0; j < monthSummaries.length; j++) {
      refreshIndexGraph(monthSummaries[j]);
    }

    document.addEventListener('selection', function() {
      var monthSummaries = document.getElementsByClassName('month-summary');

      for (var j = 0; j < monthSummaries.length; j++) {
        refreshIndexGraph(monthSummaries[j]);
      }
    });
  });
}




