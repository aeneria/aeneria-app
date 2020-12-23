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

    var startDate = start.getFullYear();
    startDate += '-';
    startDate += start.getMonth() < 9 ? '0' + (start.getMonth() + 1) : (start.getMonth() + 1);
    startDate += '-';
    startDate += start.getDate() < 10 ? '0' + start.getDate() : start.getDate();

    var endDate = end.getFullYear();
    endDate += '-';
    endDate += end.getMonth() < 9 ? '0' + (end.getMonth() + 1) : (end.getMonth() + 1);
    endDate += '-';
    endDate += end.getDate() < 10 ? '0' + end.getDate() : end.getDate();

    // Refresh global conso repartition.
    pilea.loadingAnimation('conso-repartition-' + targetMonth);
    pilea.loadingAnimation('conso-repartition-legend-' + targetMonth);
    $.ajax({
      url: appRoute + 'data/' + place + '/repartition/conso_elec/year_v/' + startDate + '/' + endDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        pilea.displayGlobalRepartitionV(data, 'conso-repartition-' + targetMonth, 'conso_elec', 9, 'kWh', 1, 0);
        pilea.displayLegend(data, 'conso-repartition-legend-' + targetMonth, 'conso_elec', 9, 'kWh', 1, 0);
      },
      error: function(result) {
        pilea.displayError('conso-repartition-' + targetMonth);
        pilea.displayError('conso-repartition-legend-' + targetMonth);
      }
    });

    // Refresh conso total.
    document.getElementById('conso-total-' + targetMonth).innerHTML = "--";
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
    pilea.loadingAnimation('temp-repartition-' + targetMonth);
    pilea.loadingAnimation('temp-repartition-legend-' + targetMonth);
    $.ajax({
      url: appRoute + 'data/' + place + '/repartition/temperature/year_v/' + startDate + '/' + endDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        pilea.displayGlobalRepartitionV(data, 'temp-repartition-' + targetMonth, 'temperature', 9, '°C', 1, -5, 25);
        pilea.displayLegend(data, 'temp-repartition-legend-' + targetMonth, 'temperature', 9, '°C', 1, -5, 25);
      },
      error: function(result) {
        pilea.displayError('temp-repartition-' + targetMonth);
        pilea.displayError('temp-repartition-legend-' + targetMonth);
      }
    });

    // Refresh DJU total.
    document.getElementById('dju-total-' + targetMonth).innerHTML = "--";
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




