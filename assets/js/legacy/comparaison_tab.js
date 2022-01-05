/**
 * @file
 *
 * Handles events on temperature vs conso dashboard.
 */
if (document.getElementById('comparaison_tab')) {
  // Refresh all graph on the page base on start and end dates.
  var refreshP1XP2Graph = function () {

    var place = pileaCurrent.getPlace();
    var startArray = pileaCurrent.getPeriod1StartDate().split('/');
    var period1StartDate = startArray[2] + '-' + startArray[1] + '-' + startArray[0];
    var endArray = pileaCurrent.getPeriod1EndDate().split('/');
    var period1EndDate = endArray[2] + '-' + endArray[1] + '-' + endArray[0];
    var startArray = pileaCurrent.getPeriod2StartDate().split('/');
    var period2StartDate = startArray[2] + '-' + startArray[1] + '-' + startArray[0];
    var endArray = pileaCurrent.getPeriod2EndDate().split('/');
    var period2EndDate = endArray[2] + '-' + endArray[1] + '-' + endArray[0];
    var frequency = pileaCurrent.getFrequency();
    var meteo = pileaCurrent.getMeteo();
    var meteoUnit = $('.aeneria-select-meteo').find('[data="' + meteo + '"]')[0].getAttribute('unit');


    // Refresh week repartition.
    pilea.loadingAnimation('conso-week-repartition-p1');
    $.ajax({
      url: appRoute + 'data/' + place + '/repartition/conso_elec/week/' + period1StartDate + '/' + period1EndDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        if ($(window).width() > 480 ) {
          pilea.displayWeekRepartitionH(data, 'conso-week-repartition-p1', 'conso_elec', 9, 'kWh', 1, 0);
        } else {
          pilea.displayWeekRepartitionV(data, 'conso-week-repartition-p1', 'conso_elec', 9, 'kWh', 1, 0);
        }
        // Hidding existing tooltips.
        document.querySelectorAll('.tooltip').forEach(function (tooltip) {
          tooltip.hidden = true;
        });
      },
      error: function(result) {
        pilea.displayError('conso-week-repartition-p1');
      }
    });
    pilea.loadingAnimation('conso-week-repartition-p2');
    $.ajax({
      url: appRoute + 'data/' + place + '/repartition/conso_elec/week/' + period2StartDate + '/' + period2EndDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        if ($(window).width() > 480 ) {
          pilea.displayWeekRepartitionH(data, 'conso-week-repartition-p2', 'conso_elec', 9, 'kWh', 1, 0);
        } else {
          pilea.displayWeekRepartitionV(data, 'conso-week-repartition-p2', 'conso_elec', 9, 'kWh', 1, 0);
        }
        // Hidding existing tooltips.
        document.querySelectorAll('.tooltip').forEach(function (tooltip) {
          tooltip.hidden = true;
        });
      },
      error: function(result) {
        pilea.displayError('conso-week-repartition-p2');
      }
    });

    // Refresh global repartition.
    pilea.loadingAnimation('conso-global-repartition-p1');
    pilea.loadingAnimation('conso-global-repartition-legend');
    $.ajax({
      url: appRoute + 'data/' + place + '/repartition/conso_elec/year_v/' + period1StartDate + '/' + period1EndDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        pilea.displayGlobalRepartitionV(data, 'conso-global-repartition-p1', 'conso_elec', 9, 'kWh', 1, 0);
        pilea.displayLegend(data, 'conso-global-repartition-legend', 'conso_elec', 9, 'kWh', 1);
      },
      error: function(result) {
        pilea.displayError('conso-global-repartition-p1');
        pilea.displayError('conso-global-repartition-legend');
      }
    });
    pilea.loadingAnimation('conso-global-repartition-p2');
    pilea.loadingAnimation('conso-global-repartition-legend');
    $.ajax({
      url: appRoute + 'data/' + place + '/repartition/conso_elec/year_v/' + period2StartDate + '/' + period2EndDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        pilea.displayGlobalRepartitionV(data, 'conso-global-repartition-p2', 'conso_elec', 9, 'kWh', 1, 0);
        pilea.displayLegend(data, 'conso-global-repartition-legend', 'conso_elec', 9, '' + meteo + '', 1);
      },
      error: function(result) {
        pilea.displayError('conso-globa l-repartition-p2');
        pilea.displayError('conso-global-repartition-legend');
      }
    });

    // Refresh conso x dju.
    pilea.loadingAnimation('conso-x-dju');
    $.ajax({
      url: appRoute + 'data/' + place + '/xy/' + meteo + '/conso_elec/' + frequency + '/' + period1StartDate + '/' + period1EndDate + '',
      success: function(result1) {
        $.ajax({
          url: appRoute + 'data/' + place + '/xy/' + meteo + '/conso_elec/' + frequency + '/' + period2StartDate + '/' + period2EndDate + '',
          success: function(result2) {
            var data1 = JSON.parse(result1);
            var data2 = JSON.parse(result2);
            pilea.displayXY([data1, data2], 'conso-x-dju', 'conso_elec', 'kWh', meteoUnit, 0, 1);
          }
        });
      },
      error: function(result) {
        pilea.displayError('conso-x-dju');
      }
    });

    // Refresh p1 vs p2.
    pilea.loadingAnimation('p1-vs-p2');
    $.ajax({
      url: appRoute + 'data/' + place + '/evolution/conso_elec/' + frequency + '/' + period1StartDate + '/' + period1EndDate + '',
      success: function(result1) {
        $.ajax({
          url: appRoute + 'data/' + place + '/evolution/conso_elec/' + frequency + '/' + period2StartDate + '/' + period2EndDate + '',
          success: function(result2) {
            var data1 = JSON.parse(result1);
            var data2 = JSON.parse(result2);
            pilea.displayDoubleEvolution(data1, data2, 'p1-vs-p2', 'conso_elec point0', 'conso_elec point1', 'kWh', 'kWh', 1, 0.1, false);
          }
        });
      },
      error: function(result) {
        pilea.displayError('p1-vs-p2');
      }
    });
  }

  $(document).ready(function () {

    refreshP1XP2Graph();

    document.addEventListener('selection', function() {
      refreshP1XP2Graph();
    });
  });
}
