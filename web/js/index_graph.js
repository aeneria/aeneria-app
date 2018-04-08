// Week Repartition
/////////////////////////////////////////

$.ajax({
  url: "data/week-repartition",
  data: {
    test: 97201
  },
  success: function( result ) {
      var data = JSON.parse(result);
      var layout = {
          xaxis: {
              ticks: '',
              side: 'top'
          },
          yaxis: {
              ticks: '',
              ticksuffix: ' '
          },
          zaxis: {
              ticks: ' KWh',
              ticksuffix: ' '
          },
          height: 450,
          margin: {
              l: 50,
              r: 30,
              b: 10,
              t: 50,
              pad: 10
            }
      };


      Plotly.newPlot('week-repartition', data, layout, {displayModeBar: false});
  }
});

