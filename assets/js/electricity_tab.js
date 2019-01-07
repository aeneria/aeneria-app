/**
 * @file
 *
 * Handles events on general dashboard.
 */

if (document.getElementById('electricity_tab')) {
  // Refresh all graph on the page base on start and end dates.
  var refreshElectricityGraph = function () {

    var startArray = localStorage.startDate.split('/');
    var startDate = startArray[2] + '-' + startArray[1] + '-' + startArray[0];
    var endArray = localStorage.endDate.split('/');
    var endDate = endArray[2] + '-' + endArray[1] + '-' + endArray[0];
    var frequency = localStorage.frequency;

    // Refresh conso total.
    $.ajax({
      url: appRoute + 'data/conso_elec/sum/' + startDate + '/' + endDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        document.getElementById('conso-total').innerHTML = parseFloat(data[0].value).toFixed(1);
      }
    });

    // Refresh week repartition.
    $.ajax({
      url: appRoute + 'data/conso_elec/repartition/week/' + startDate + '/' + endDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        pilea.displayWeekRepartition(data, 'conso-week-repartition', ELEC_COLOR, 'kWh', 1, 0);
        // Hidding existing tooltips.
        document.querySelectorAll('.tooltip').forEach(function (tooltip) {
          tooltip.hidden = true;
        });
      }
    });

    // Refresh global repartition.
    $.ajax({
      url: appRoute + 'data/conso_elec/repartition/year_v/' + startDate + '/' + endDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        pilea.displayGlobalRepartitionV(data, 'conso-global-repartition', ELEC_COLOR, 'kWh', 1, 0);
        pilea.displayLegend(data, 'conso-global-repartition-legend', ELEC_COLOR, 'kWh', 1);
      }
    });

    // Refresh global evolution.
    $.ajax({
      url: appRoute + 'data/conso_elec/evolution/' + frequency + '/' + startDate + '/' + endDate + '',
      success: function( result ) {
        var data = JSON.parse(result);
        pilea.displayGlobalEvolution(data, 'conso-global-evolution', ELEC_COLOR[6], 'kWh', 1);
      }
    });

    // Refresh global week frequency.
    $.ajax({
      url: appRoute + 'data/conso_elec/sum-group/day/weekDay/' + startDate + '/' + endDate + '',
      success: function( result ) {
        var data = JSON.parse(result);
        pilea.displayGlobalEvolution(data, 'conso-week-frequency', ELEC_COLOR[6], 'kWh', 1, 95, 180, 50);
      }
    });
  }

  $(document).ready(function () {
    refreshElectricityGraph();

    document.addEventListener('selection', function() {
      refreshElectricityGraph();
    });
  });
}




