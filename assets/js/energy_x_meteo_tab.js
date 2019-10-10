/**
 * @file
 *
 * Handles events on temperature vs conso dashboard.
 */
if (document.getElementById('dju_x_conso_tab')) {
  // Refresh all graph on the page base on start and end dates.
  var refreshEnergyXMeteoGraph = function () {

    var colors = d3.schemeGnBu[9];

    var place = pileaCurrent.getPlace();
    var startArray = pileaCurrent.getStartDate().split('/');
    var startDate = startArray[2] + '-' + startArray[1] + '-' + startArray[0];
    var endArray = pileaCurrent.getEndDate().split('/');
    var endDate = endArray[2] + '-' + endArray[1] + '-' + endArray[0];
    var frequency = pileaCurrent.getFrequency();
    var meteo = pileaCurrent.getMeteo();
    var meteoUnit = $('.pilea-select-meteo').find('[data="' + meteo + '"]')[0].getAttribute('unit');

    // Refresh conso x dju.
    $.ajax({
      url: appRoute + 'data/' + place + '/xy/' + meteo + '/conso_elec/' + frequency + '/' + startDate + '/' + endDate + '',
      success: function(result) {
        var data = JSON.parse(result);
        pilea.displayXY(data, 'conso-x-dju', '#6b4450', meteoUnit, 'kWh', 0, 1);
      }
    });

    // Refresh conso vs dju.
    $.ajax({
      url: appRoute + 'data/' + place + '/evolution/conso_elec/' + frequency + '/' + startDate + '/' + endDate + '',
      success: function(result1) {
        $.ajax({
          url: appRoute + 'data/' + place + '/evolution/' + meteo + '/' + frequency + '/' + startDate + '/' + endDate + '',
          success: function(result2) {
            var data1 = JSON.parse(result1);
            var data2 = JSON.parse(result2);
            pilea.displayDoubleEvolution(data1, data2, 'conso-vs-dju', ELEC_COLOR[6], DJU_COLOR[1], 'kWh', meteoUnit, 1, 0.1);
          }
        });
      }
    });
  }

  $(document).ready(function () {

    refreshEnergyXMeteoGraph();

    document.addEventListener('selection', function() {
      refreshEnergyXMeteoGraph();
    });
  });
}
