
$.ajax({
  url: "data/week-repartition",
  data: {
    test: 97201
  },
  success: function( result ) {
    var data = JSON.parse(result);
    var colors = ["#CAD7B2", "#97B5A7"]

    displayWeekRepartition(data, 'conso-week-repartition', colors, "kWh");
  }
});

