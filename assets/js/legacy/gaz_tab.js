/**
 * @file
 *
 * Handles events on general dashboard.
 */

if (document.getElementById('gaz_tab')) {
  // Refresh all graph on the page base on start and end dates.
  var refreshGazGraph = function () {

    var place = pileaCurrent.getPlace();
    var startArray = pileaCurrent.getStartDate().split('/');
    var startDate = startArray[2] + '-' + startArray[1] + '-' + startArray[0];
    var endArray = pileaCurrent.getEndDate().split('/');
    var endDate = endArray[2] + '-' + endArray[1] + '-' + endArray[0];
    var frequency = pileaCurrent.getFrequency();

    // Refresh conso total.
    document.getElementById('conso-total').innerHTML = "--";
    $.ajax({
      url: appRoute + 'data/' + place + '/sum/conso_gaz/' + startDate + '/' + endDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        document.getElementById('conso-total').innerHTML = parseFloat(data[0].value).toFixed(1);
      }
    });

    // Refresh week repartition.
    // pilea.loadingAnimation('conso-week-repartition');

    // $.ajax({
    //   url: appRoute + 'data/' + place + '/repartition/conso_gaz/week/' + startDate + '/' + endDate + '',
    //   success: function(result) {
    //     var data = JSON.parse(result);
    //     if ($(window).width() > 480 ) {
    //       pilea.displayWeekRepartitionH(data, 'conso-week-repartition', 'conso_gaz', 9, 'kWh', 1, 0);
    //     } else {
    //       pilea.displayWeekRepartitionV(data, 'conso-week-repartition', 'conso_gaz', 9, 'kWh', 1, 0);
    //     }
    //     // Hidding existing tooltips.
    //     document.querySelectorAll('.tooltip').forEach(function (tooltip) {
    //       tooltip.hidden = true;
    //     });
    //   },
    //   error: function(result) {
    //     pilea.displayError('conso-week-repartition');
    //   }
    // });

    // Refresh global repartition.
    pilea.loadingAnimation('conso-global-repartition');
    pilea.loadingAnimation('conso-global-repartition-legend');
    $.ajax({
      url: appRoute + 'data/' + place + '/repartition/conso_gaz/year_v/' + startDate + '/' + endDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        pilea.displayGlobalRepartitionV(data, 'conso-global-repartition', 'conso_gaz', 9, 'kWh', 1, 0);
        pilea.displayLegend(data, 'conso-global-repartition-legend', 'conso_gaz', 9, 'kWh', 1);
      },
      error: function(result) {
        pilea.displayError('conso-global-repartition');
        pilea.displayError('conso-global-repartition-legend');
      }
    });

    // Refresh global evolution.
    pilea.loadingAnimation('conso-global-evolution');
    $.ajax({
      url: appRoute + 'data/' + place + '/evolution/conso_gaz/' + frequency + '/' + startDate + '/' + endDate + '',
      success: function( result ) {
        var data = JSON.parse(result);
        pilea.displayGlobalEvolution(data, 'conso-global-evolution', 'conso_gaz', 'kWh', 1);
      },
      error: function(result) {
        pilea.displayError('conso-global-evolution');
      }
    });

    // Refresh global week frequency.
    pilea.loadingAnimation('conso-week-frequency');
    $.ajax({
      url: appRoute + 'data/' + place + '/sum-group/conso_gaz/day/weekDay/' + startDate + '/' + endDate + '',
      success: function( result ) {
        var data = JSON.parse(result);
        pilea.displayGlobalEvolution(data, 'conso-week-frequency', 'conso_gaz', 'kWh', 1, 95, 180, 50);
      },
      error: function(result) {
        pilea.displayError('conso-week-frequency');
      }
    });
  }

  $(document).ready(function () {
    refreshGazGraph();

    document.addEventListener('selection', function() {
      refreshGazGraph();
    });
  });
}




