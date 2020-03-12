(function() {

  /**
   * Display a hour/weekDay heatmap repartition graphic horizontally.
   *
   *   result: an object containing :
   *     - axe.x: axeX labels
   *     - axe.y: axeY labels
   *     - data.values: values to display
   *     - data.dates: corresponding dates
   *   target: id of the targetted DIV element
   *   colors: a tab with 2 elements for the color scale
   *   unit: a string, the unit of the displayed data
   *   precision: float precision for value
   *   min: min value for scale
   *   max: max value for scale
   */
  var displayWeekRepartitionH = function (result, target, colors, unit, precision, min, max) {
    var rows = 7; // Number of hours in a day
    var cols = 24; // Number of days in a week
    var margin_top = 50;
    var margin_left = 30;
    var margin_right = 30;
    var margin_bottom = 10;
    var total_height = margin_top + rows * DAY_SIZE + margin_bottom;
    var total_width = margin_left + cols * DAY_SIZE + margin_right;

    var element = d3
      .select('#' + target);

    element
    .selectAll('svg')
    .remove();

    element
    .selectAll('div')
    .remove();

    var svg = element
      .append('svg')
      .attr('class', 'chart')
      .attr('width', total_width)
      .attr('height', total_height);

    var chart = svg
      .append('g')
      .attr('class', 'chart')
      .attr('width', total_width)
      .attr('height', total_height);

    var color = d3
      .scaleQuantile()
      .domain([
          (typeof min !== 'undefined') ? min : Math.min(...result.data.values),
          (typeof max !== 'undefined') ? max : Math.max(...result.data.values)
        ])
      .range(colors);

    // Define the div for the tooltip
    var div = element
      .append('div')
      .attr('class', 'tooltip')
      .style('opacity', 0);

    chart
      .selectAll('.dayLabel')
      .data(result.axe.y)
      .enter()
      .append('text')
      .text(function (d, i) {
        if (i % 3 == 0) {
          return d;
        } else {
          return '';
        }
      })
      .style('text-anchor', 'left')
      .style('fill', AXE_COLOR)
      .attr('transform', function (d, i) {
          return 'rotate(-90)translate(-45,' + (i * DAY_SIZE + margin_left + 2) + ')'
      })
      .attr('font-family', 'sans-serif')
      .attr('font-size', 10);

    chart
      .selectAll('.timeLabel')
      .data(result.axe.x)
      .enter()
      .append('text')
      .text(function (d) { return d;})
      .style('text-anchor', 'left')
      .style('fill', AXE_COLOR)
      .attr('transform', function (d, i) {
        return 'translate(0,' + (i * DAY_SIZE + margin_top + 15) + ')'
      })
      .attr('font-family', 'sans-serif')
      .attr('font-size', 10);

    chart
      .selectAll('rect')
      .data(result.data.values)
      .enter()
      .append('rect')
      .attr('x', function (d, i) {
        return i % cols * DAY_SIZE + margin_left + DAY_SIZE/2;
      })
      .attr('y', function (d, i) {
        return Math.floor(i / cols) * DAY_SIZE + margin_top + DAY_SIZE/2;
      })
      .attr('width', 0)
      .attr('height', 0)
      .attr('display', function (d, i) {
        if (d === "") {
          return 'none';
        }
      })
      .attr('fill', color)
      .attr('data-toggle', 'tooltip')
      .attr('data-placement', 'left')
      .attr('data-html', 'true')
      .attr('title', function (d, i) {
        return result.data.dates[i] + '</br> ' + parseFloat(d).toFixed(precision) + ' ' + unit;
      });

    chart
      .selectAll('rect')
      .transition()
      .duration(300)
      .delay(function (d, i) { return i/24 * 15 + i%24 * 15; })
      .ease(d3.easeCubic)
      .attr('x', function (d, i) {
        return i % cols * DAY_SIZE + margin_left;
      })
      .attr('y', function (d, i) {
        return Math.floor(i / cols) * DAY_SIZE + margin_top;
      })
      .attr('width', DAY_SIZE)
      .attr('height', DAY_SIZE);

    $('[data-toggle=\'tooltip\']').tooltip();
  }

  /**
   * Display a hour/weekDay heatmap repartition graphic vertically.
   *
   *   result: an object containing :
   *     - axe.x: axeX labels
   *     - axe.y: axeY labels
   *     - data.values: values to display
   *     - data.dates: corresponding dates
   *   target: id of the targetted DIV element
   *   colors: a tab with 2 elements for the color scale
   *   unit: a string, the unit of the displayed data
   *   precision: float precision for value
   *   min: min value for scale
   *   max: max value for scale
   */
  var displayWeekRepartitionV = function (result, target, colors, unit, precision, min, max) {
    var rows = 24; // Number of days in a week
    var cols = 7; // Number of hours in a day
    var margin_top = 50;
    var margin_left = 30;
    var margin_right = 30;
    var margin_bottom = 10;
    var total_height = margin_top + rows * DAY_SIZE + margin_bottom;
    var total_width = margin_left + cols * DAY_SIZE + margin_right;

    var element = d3
      .select('#' + target);

    element
    .selectAll('svg')
    .remove();

    element
    .selectAll('div')
    .remove();

    var svg = element
      .append('svg')
      .attr('class', 'chart')
      .attr('width', total_width)
      .attr('height', total_height);

    var chart = svg
      .append('g')
      .attr('class', 'chart')
      .attr('width', total_width)
      .attr('height', total_height);

    var color = d3
      .scaleQuantile()
      .domain([
          (typeof min !== 'undefined') ? min : Math.min(...result.data.values),
          (typeof max !== 'undefined') ? max : Math.max(...result.data.values)
        ])
      .range(colors);

    // Define the div for the tooltip
    var div = element
      .append('div')
      .attr('class', 'tooltip')
      .style('opacity', 0);

    chart
      .selectAll('.dayLabel')
      .data(result.axe.x)
      .enter()
      .append('text')
      .text(function (d) {return d;})
      .style('text-anchor', 'left')
      .style('fill', AXE_COLOR)
      .attr('transform', function (d, i) {
          return 'rotate(-90)translate(-45,' + (i * DAY_SIZE + margin_left + 14) + ')'
      })
      .attr('font-family', 'sans-serif')
      .attr('font-size', 10);

    chart
      .selectAll('.timeLabel')
      .data(result.axe.y)
      .enter()
      .append('text')
      .text(function (d, i) {
        if (i % 3 == 0) {
          return d;
        } else {
          return '';
        }
      })
      .style('text-anchor', 'left')
      .style('fill', AXE_COLOR)
      .attr('transform', function (d, i) {
        return 'translate(0,' + (i * DAY_SIZE + margin_top + 5) + ')'
      })
      .attr('font-family', 'sans-serif')
      .attr('font-size', 10);

    chart
      .selectAll('rect')
      .data(result.data.values)
      .enter()
      .append('rect')
      .attr('x', function (d, i) {
        return Math.floor(i / rows) * DAY_SIZE + margin_left + DAY_SIZE/2;
      })
      .attr('y', function (d, i) {
        return i % rows * DAY_SIZE + margin_top + DAY_SIZE/2;
      })
      .attr('width', 0)
      .attr('height', 0)
      .attr('display', function (d, i) {
        if (d === "") {
          return 'none';
        }
      })
      .attr('fill', color)
      .attr('data-toggle', 'tooltip')
      .attr('data-placement', 'left')
      .attr('data-html', 'true')
      .attr('title', function (d, i) {
        return result.data.dates[i] + '</br> ' + parseFloat(d).toFixed(precision) + ' ' + unit;
      });

    chart
      .selectAll('rect')
      .transition()
      .duration(300)
      .delay(function (d, i) { return i/24 * 15 + i%24 * 15; })
      .ease(d3.easeCubic)
      .attr('x', function (d, i) {
        return Math.floor(i / rows) * DAY_SIZE + margin_left;
      })
      .attr('y', function (d, i) {
        return i % rows * DAY_SIZE + margin_top;
      })
      .attr('width', DAY_SIZE)
      .attr('height', DAY_SIZE);

    $('[data-toggle=\'tooltip\']').tooltip();
  }

  /**
   * Display a weekDay/week heatmap repartition.
   *
   *   result: an object containing :
   *     - axe.x: axeX labels
   *     - axe.y: axeY labels
   *     - data.values: values to display
   *     - data.dates: corresponding dates
   *   target: id of the targetted DIV element
   *   colors: a tab with 2 elements for the color scale
   *   unit: a string, the unit of the displayed data
   *   precision: float precision for value
   *   min: min value for scale
   *   max: max value for scale
   */
  var displayGlobalRepartitionH = function (result, target, colors, unit, precision, min, max) {
    var rows = result.axe.y.length; // Number of days in a week
    var cols = result.axe.x.length; // Number of weeks we want to display
    var margin_top = 30;
    var margin_left = 30;
    var margin_bottom = 10;
    var total_height = margin_top + rows * DAY_SIZE + margin_bottom;
    var total_width = margin_left + cols * DAY_SIZE;

    var element = d3
      .select('#' + target);

    element
      .selectAll('svg')
      .remove();

    element
      .selectAll('div')
      .remove();

    var svg = element
      .append('svg')
      .attr('class', 'chart')
      .attr('width', total_width)
      .attr('height', total_height);

    var chart = svg
      .append('g')
      .attr('class', 'chart')
      .attr('width', total_width)
      .attr('height', total_height);

    var color = d3
      .scaleQuantile()
      .domain([
          (typeof min !== 'undefined') ? min : Math.min(...result.data.values),
          (typeof max !== 'undefined') ? max : Math.max(...result.data.values)
        ])
      .range(colors);

    // Define the div for the tooltip
    var div = element
      .append('div')
      .attr('class', 'tooltip')
      .style('opacity', 0);

    chart
      .selectAll('.weekLabel')
      .data(result.axe.x)
      .enter()
      .append('text')
      .text( function (d, i) {
        if (i % 3 == 1) {
          return d;
        } else {
          return '';
        }
      })
      .style('text-anchor', 'left')
      .style('fill', AXE_COLOR)
      .attr('transform', function (d, i) {
          return 'translate(' + (i * DAY_SIZE + margin_left - 10) + ',25)rotate(-45)'
      })
      .attr('font-family', 'sans-serif')
      .attr('font-size', 10);

    chart
      .selectAll('.dayLabel')
      .data(result.axe.y)
      .enter()
      .append('text')
      .text(function (d) { return d; })
      .style('text-anchor', 'left')
      .style('fill', AXE_COLOR)
      .attr('transform', function (d, i) {
        return 'translate(0,' + (i * DAY_SIZE + margin_top + 14) + ')'
      })
      .attr('font-family', 'sans-serif')
      .attr('font-size', 10);

    chart
      .selectAll('rect')
      .data(result.data.values)
      .enter()
      .append('rect')
      .attr('x', function (d, i) {
        return Math.floor(i / rows) * DAY_SIZE + margin_left;
      })
      .attr('y', function (d, i) {
        return i % rows * DAY_SIZE + margin_top;
      })
      .attr('width', DAY_SIZE)
      .attr('height', DAY_SIZE)
      .attr('fill', color)
      .attr('display', function (d, i) {
        if (d === "") {
          return 'none';
        }
      })
      .attr('data-toggle', 'tooltip')
      .attr('data-placement', 'left')
      .attr('data-html', 'true')
      .attr('title', function (d, i) {
          return result.data.dates[i] + '</br> ' + parseFloat(d).toFixed(precision) + ' ' + unit;
      });

    $('[data-toggle=\'tooltip\']').tooltip();
  }

  /**
   * Display a weekDay/week heatmap repartition.
   *
   *   result: an object containing :
   *     - axe.x: axeX labels
   *     - axe.y: axeY labels
   *     - data.values: values to display
   *     - data.dates: corresponding dates
   *   target: id of the targetted DIV element
   *   colors: a tab with 2 elements for the color scale
   *   unit: a string, the unit of the displayed data
   *   precision: float precision for value
   *   min: min value for scale
   *   max: max value for scale
   */
  var displayGlobalRepartitionV = function (result, target, colors, unit, precision, min, max) {
    var rows = result.axe.y.length; // Number of days in a week
    var cols = result.axe.x.length; // Number of weeks we want to display
    var margin_top = 50;
    var margin_left = 25;
    var margin_bottom = 10;
    var total_height = margin_top + rows * DAY_SIZE + margin_bottom;
    var total_width = margin_left + cols * DAY_SIZE;

    var element = d3
      .select('#' + target);

    element
      .selectAll('svg')
      .remove();

    element
      .selectAll('div')
      .remove();

    var svg = element
      .append('svg')
      .attr('class', 'chart')
      .attr('width', total_width)
      .attr('height', total_height);

    var chart = svg
      .append('g')
      .attr('class', 'chart')
      .attr('width', total_width)
      .attr('height', total_height);

    var color = d3
      .scaleQuantile()
      .domain([
          (typeof min !== 'undefined') ? min : Math.min(...result.data.values),
          (typeof max !== 'undefined') ? max : Math.max(...result.data.values)
        ])
      .range(colors);

    // Define the div for the tooltip
    var div = element
      .append('div')
      .attr('class', 'tooltip')
      .style('opacity', 0);

    chart
      .selectAll('.weekLabel')
      .data(result.axe.x)
      .enter()
      .append('text')
      .text( function (d, i) { return d; })
      .style('text-anchor', 'left')
      .style('fill', AXE_COLOR)
      .attr('transform', function (d, i) {
        return 'rotate(-90)translate(-45,' + (i * DAY_SIZE + margin_left + 15) + ')'
      })
      .attr('font-family', 'sans-serif')
      .attr('font-size', 10);

    chart
      .selectAll('.dayLabel')
      .data(result.axe.y)
      .enter()
      .append('text')
      .text( function (d, i) {
        if (i % 3 == 1) {
          return d;
        } else {
          return '';
        }
      })
      .style('text-anchor', 'left')
      .style('fill', AXE_COLOR)
      .attr('transform', function (d, i) {
        return 'translate(0,' + (i * DAY_SIZE + margin_top + 14) + ')'
      })
      .attr('font-family', 'sans-serif')
      .attr('font-size', 10);

    chart
      .selectAll('rect')
      .data(result.data.values)
      .enter()
      .append('rect')
      .attr('x', function (d, i) {
        return i % cols * DAY_SIZE + margin_left + DAY_SIZE/2;
      })
      .attr('y', function (d, i) {
        return Math.floor(i / cols) * DAY_SIZE + margin_top + DAY_SIZE/2;
      })
      .attr('width', 0)
      .attr('height', 0)
      .attr('display', function (d, i) {
        if (d === "") {
          return 'none';
        }
      })
      .attr('fill', color)
      .attr('data-toggle', 'tooltip')
      .attr('data-placement', 'left')
      .attr('data-html', 'true')
      .attr('title', function (d, i) {
          return result.data.dates[i] + '</br> ' + parseFloat(d).toFixed(precision) + ' ' + unit;
      });

    chart
      .selectAll('rect')
      .transition()
      .duration(300)
      .delay(function (d, i) { return i * 6; })
      .ease(d3.easeCubic)
      .attr('x', function (d, i) {
        return i % cols * DAY_SIZE + margin_left;
      })
      .attr('y', function (d, i) {
        return Math.floor(i / cols) * DAY_SIZE + margin_top;
      })
      .attr('width', DAY_SIZE)
      .attr('height', DAY_SIZE);

      $('[data-toggle=\'tooltip\']').tooltip();
  }


  /**
   * Display a histogram if there's less than 20 data, curve after.
   *
   *   result: an object containing :
   *     - axeX: values to display
   *     - axeY: axeY labels
   *   target: id of the targetted DIV element
   *   color:  hexadecimal color for bar
   *   unit: a string, the unit of the displayed data
   *   precision: float precision for value
   *   height: height of graph area
   *   width: width of graph area
   *   margin_bottom: place for axe x ticks
   */
  var displayGlobalEvolution = function (result, target, color, unit, precision, height = 350, width = 800, margin_bottom = 90) {
    var margin_top = 20;
    var margin_left = 20;
    var margin_right = 20;
    // If there's more than 20 data to dislpay, we display a curve.
    var type = result.axeX.length < 20 ? 1 : 2;

    var element = d3
      .select('#' + target);

    width = document.getElementById(target).clientWidth - margin_right - margin_left;

    element
      .selectAll('svg')
      .remove();

    element
      .selectAll('div')
      .remove();

    var svg = element
      .append('svg')
      .attr('class', 'chart')
      .attr('width', margin_left + width + margin_right)
      .attr('height', margin_top + height + margin_bottom);

    var xScale = d3
      .scaleBand()
      .range([0, width])
      .padding(0.4)
      .domain(result.axeX.map(function (d) { return d;})),
    yScale = d3
      .scaleLinear()
      .range([0, height])
      .domain([Math.max(...result.axeY), 0]);

    var chart = svg
      .append('g')
      .attr('transform','translate(' + margin_left + ',' + margin_top + ')');

    var yGrid = chart
      .append('g')
      .call(
        d3.axisLeft(yScale)
          .tickSize(-width, 0, 0)
          .tickFormat("")
      );

    yGrid
      .selectAll('line')
      .attr('stroke', GRID_COLOR);

    yGrid
      .select('.domain')
      .attr('stroke-width', 0);

    if (type == 2) {
      chart.append('path')
        .datum(result.axeX)
        .attr('fill', color)
        .attr('class', 'area')
        .attr('d', d3.area()
            .curve(d3.curveMonotoneX)
            .x(function (d, i) { return xScale(d) + xScale.bandwidth()/2; })
            .y0(height)
            .y1(height)
        );

      chart
        .selectAll('.area')
        .transition()
        .duration(800)
        .ease(d3.easeCubic)
        .attr('d', d3.area()
            .curve(d3.curveMonotoneX)
            .x(function (d, i) { return xScale(d) + xScale.bandwidth()/2; })
            .y0(height)
            .y1(function (d, i) { return  yScale(result.axeY[i]); })
        )
    }

    chart
      .selectAll('.bar')
      .data(result.axeX)
      .enter()
      .append('rect')
      .attr('class', 'bar')
      .attr('fill', (type == 1) ? color : 'transparent' )
      .attr('stroke-width', 0)
      .attr('x', function (d, i) { return xScale(d) })
      .attr('y', height)
      .attr('width', xScale.bandwidth())
      .attr('height', 0)
      .attr('data-toggle', 'tooltip')
      .attr('data-placement', 'top')
      .attr('data-html', 'true')
      .attr('title', function (d, i) {
        return result.label[i] + '</br> ' + parseFloat(result.axeY[i]).toFixed(precision) + ' ' + unit;
      });

    chart
      .selectAll('.bar')
      .transition()
      .duration(600)
      .delay(function (d, i) { return i * 20; })
      .ease(d3.easeCubic)
      .attr('y', function (d, i) { return yScale(result.axeY[i]); })
      .attr('height', function (d, i) { return height - yScale(result.axeY[i]); });


    $('[data-toggle=\'tooltip\']').tooltip();

    var tickInterval = result.axeX.length > 15 ? parseInt(result.axeX.length/15) : 1;

    var xAxe = chart
      .append('g')
      .attr('transform', 'translate(0,' + height + ')')
      .call(d3.axisBottom(xScale));

    xAxe
      .selectAll('text')
      .style('text-anchor', 'end')
      .attr('dx', '-.8em')
      .attr('dy', '.15em')
      .style('fill', function (d,i) {return i%tickInterval == 0 ? AXE_COLOR : 'transparent';})
      .style('font-size', '1.3em')
      .attr('transform', 'rotate(-65)');

    xAxe
      .select('.domain')
      .attr('stroke', AXE_COLOR);

    xAxe
    .selectAll('line')
    .attr('stroke', AXE_COLOR);
  }

  /**
   * Display a legend heatmap repartition.
   *
   *   result: an object containing :
   *     - axe.x: axeX labels
   *     - axe.y: axeY labels
   *     - data.values: values to display
   *     - data.dates: corresponding dates
   *   target: id of the targetted DIV element
   *   colors: a tab with 2 elements for the color scale
   *   unit: a string, the unit of the displayed data
   *   precision: float precision for value
   *   min: min value for scale
   *   max: max value for scale
   */
  var displayLegend = function (result, target, colors, unit, precision, min, max) {
    var total_height = colors.length * DAY_SIZE + 20;
    var total_width = 100;
    var margin_left = 33;

    var element = d3
    .select('#' + target);

    element
    .selectAll('svg')
    .remove();

    element
      .selectAll('div')
      .remove();

    var svg = element
      .append('svg')
      .attr('class', 'chart')
      .attr('width', total_width)
      .attr('height', total_height);

    var chart = svg
      .append('g')
      .attr('class', 'chart')
      .attr('width', total_width)
      .attr('height', total_height);

    var color = d3
      .scaleQuantile()
      .domain([
          (typeof min !== 'undefined') ? min : Math.min(...result.data.values),
          (typeof max !== 'undefined') ? max : Math.max(...result.data.values)
        ])
      .range(colors);

    // Define the div for the tooltip
    var div = element
      .append('div')
      .attr('class', 'tooltip')
      .style('opacity', 0);

    chart
      .selectAll('rect')
      .data(colors)
      .enter()
      .append('rect')
      .attr('x', margin_left)
      .attr('y', function(d, i) {
        return i * DAY_SIZE;
      })
      .attr('width', DAY_SIZE)
      .attr('height', DAY_SIZE)
      .attr('fill', function (d, i) {return colors[colors.length - 1 - i];})
      .attr('display', function (d, i) {
        if (d === "") {
          return 'none';
        }
      })
      .attr('data-toggle', 'tooltip')
      .attr('data-placement', 'left')
      .attr('data-html', 'true')
      .attr('title', function (d, i) {
          var inf, sup;
          var lastIndex = colors.length - 1;
          switch (i) {
            case 0:
              sup = (typeof max !== 'undefined') ? max : Math.max(...result.data.values);
              inf = color.quantiles()[lastIndex - 1];
              break;
            case lastIndex:
              sup = color.quantiles()[0];
              inf = (typeof min !== 'undefined') ? min : Math.min(...result.data.values);
              break;
            default:
              sup = color.quantiles()[colors.length - 1 - i];
              inf = color.quantiles()[colors.length - 2 - i];
              break;
          }
          return parseFloat(inf).toFixed(precision) + unit + ' -> ' + parseFloat(sup).toFixed(precision) + unit;
      });

    // Get min, max, avg.
    var valuesArray = result.data.values;
    var datesArray = result.data.dates;
    var avgValue = 0;
    for( var i = valuesArray.length-1; i >= 0; i--){
      if ( valuesArray[i] === "") {
        valuesArray.splice(i, 1);
        datesArray.splice(i, 1);
      }
      else {
        valuesArray[i] = parseFloat(valuesArray[i]);
        avgValue = avgValue + valuesArray[i];
      }
    }

    // Calculate & display average.
    if (valuesArray.length>0) {
      avgValue = avgValue / valuesArray.length;
    }
    var avgIndex = color.range().length - 1 - color.range().indexOf(color(avgValue));

    // Calculate & display in value.
    var minValue = Math.min(...valuesArray);
    var minDate = datesArray[valuesArray.indexOf(minValue)];
    var minIndex = color.range().length - 1 - color.range().indexOf(color(minValue));

    // Calucltate & display max value.
    var maxValue  = Math.max(...valuesArray);
    var maxDate = datesArray[valuesArray.indexOf(maxValue)];
    var maxIndex = color.range().length - 1 - color.range().indexOf(color(maxValue));

    // Check overlapping.
    avgIndex =  (avgIndex === maxIndex) ? avgIndex + 1 : avgIndex;
    minIndex = (minIndex === avgIndex) ? minIndex + 1 : minIndex;
    minIndex = (minIndex === maxIndex) ? minIndex + 2 : minIndex;

    displayLegendTick(chart, margin_left, minIndex, 'Min', minValue.toFixed(precision) + unit);
    displayLegendTick(chart, margin_left, avgIndex, 'Moy', avgValue.toFixed(precision) + unit);
    displayLegendTick(chart, margin_left, maxIndex, 'Max', maxValue.toFixed(precision) + unit);

    $('[data-toggle=\'tooltip\']').tooltip();
  }

  var displayLegendTick = function (target, margin_left, index, label, value) {
    target.append("text")
    .attr("x", 0)
    .attr("y", index * DAY_SIZE + 10)
    .text(label);

    target.append("text")
    .attr("x", margin_left + 21)
    .attr("y", index * DAY_SIZE + 10)
    .text(value);
  }

  /**
   * Display a weekDay/week heatmap repartition.
   *
   *   result: an object containing :
   *     - xValue: values for x axe
   *     - yValue: values for y axe
   *     - dates: corresponding dates
   *   target: id of the targetted DIV element
   *   colors: a tab with 2 elements for the color scale
   *   unit: a string, the unit of the displayed data
   *   precision: float precision for value
   */
  var displayXY = function (result, target, color, unitx, unity, precisionx, precisiony, height = 525, width = 800, margin_bottom = 40) {
    var margin_top = 20;
    var margin_left = 50;
    var margin_right = 20;

    var element = d3
      .select('#' + target);

    width = document.getElementById(target).clientWidth - margin_right - margin_left;
    element
      .selectAll('svg')
      .remove();

    element
      .selectAll('div')
      .remove();

    var svg = element
      .append('svg')
      .attr('class', 'chart')
      .attr('width', margin_left + width + margin_right)
      .attr('height', margin_top + height + margin_bottom);

    var xScale = d3
      .scaleLinear()
      .range([0, width])
      .domain([0, Math.max(...result.axeX) * 1.05]);
    yScale = d3
      .scaleLinear()
      .range([0, height])
      .domain([Math.max(...result.axeY) * 1.05, 0]);

    var chart = svg
    .append('g')
    .attr('transform','translate(' + margin_left + ',' + margin_top + ')');

    var xGrid = chart
    .append('g')
    .attr('transform', 'translate(0,' + height + ')')
    .call(
        d3.axisBottom(xScale)
        .tickSize(-height, 0, 0)
        .tickFormat("")
    );

    xGrid
    .selectAll('line')
    .attr('stroke', GRID_COLOR);

    var yGrid = chart
      .append('g')
      .call(
          d3.axisLeft(yScale)
          .tickSize(-width, 0, 0)
          .tickFormat("")
      );

    yGrid
      .selectAll('line')
      .attr('stroke', GRID_COLOR);

    var xAxe = chart
      .append('g')
      .attr('transform', 'translate(0,' + height + ')')
      .call(d3.axisBottom(xScale));

    xAxe
      .selectAll('text')
      .style('text-anchor', 'end')
      .attr('dx', '.3em')
      .style('fill', AXE_COLOR)
      .style('font-size', '1.3em')

    xAxe
      .select('.domain')
      .attr('stroke', AXE_COLOR);

    xAxe
      .selectAll('line')
      .attr('stroke', AXE_COLOR);

    var yAxe = chart
      .append('g')
      .call(d3.axisLeft(yScale));

    yAxe
      .selectAll('text')
      .style('text-anchor', 'end')
      .style('fill', AXE_COLOR)
      .style('font-size', '1.3em')

    yAxe
      .select('.domain')
      .attr('stroke', AXE_COLOR);

    yAxe
      .selectAll('line')
      .attr('stroke', AXE_COLOR);

    chart
      .selectAll('.point')
      .data(result.axeX)
      .enter()
      .append('circle')
      .attr('class', 'point')
      .attr('fill', color)
      .attr('cx', function (d, i) { return xScale(d); })
      .attr('cy', function (d, i) { return yScale(result.axeY[i]); })
      .attr('r', 0)
      .attr('data-toggle', 'tooltip')
      .attr('data-placement', 'right')
      .attr('data-html', 'true')
      .attr('title', function (d, i) {
          return result.date[i] + '</br> ' + parseFloat(result.axeY[i]).toFixed(precisiony) + ' ' + unity + ' - ' + parseFloat(d).toFixed(precisionx) + ' ' + unitx;
      })
      .on("mouseover", function (d, i) { d3.select(this).attr('r', '8'); })
      .on("mouseout", function (d, i) { d3.select(this).attr('r', '4'); });

    chart
      .selectAll('.point')
      .transition()
      .duration(400)
      .delay(function(d, i) { return i * 10; })
      .ease(d3.easeCubic)
      .attr('r', 4);

    chart
      .append('text')
      .attr("x", width/2 - 10)
      .attr("y", height + 40)
      .text(unitx)
      .style('font-size', '1.1em');

    chart
      .append('text')
      .attr("x", - height/2)
      .attr("y", -35)
      .attr('transform', 'rotate(-90)')
      .text(unity)
      .style('font-size', '1.1em');

    $('[data-toggle=\'tooltip\']').tooltip();
  }

  /**
   * Display a vertical double plain curve.
   *
   *   result1: an object containing :
   *     - axeX: values to display
   *     - axeY: axeY labels
   *   result2: an object containing :
   *     - axeX: values to display
   *     - axeY: axeY labels
   *   target: id of the targetted DIV element
   *   color1:  hexadecimal color for bar
   *   color2:  hexadecimal color for bar
   *   unit1: a string, the unit of the displayed data
   *   unit2: a string, the unit of the displayed data
   *   precision1: float precision for value
   *   precision2: float precision for value
   */
  var displayDoubleEvolution = function (result1, result2, target, color1, color2, unit1, unit2, precision1, precision2, height = 460, width = 200) {
    var margin_top = 20;
    var margin_bottom = 25;

    // If there's more than 20 data to dislpay, we display a curve.
    var type = result1.axeX.length < 20 ? 1 : 2;

    var element = d3
      .select('#' + target);

    element
      .selectAll('svg')
      .remove();

    element
      .selectAll('div')
      .remove();

    var svg = element
      .append('svg')
      .attr('class', 'chart')
      .attr('width', width)
      .attr('height', margin_top + height + margin_bottom);

    var xScale = d3
      .scaleBand()
      .range([0, height])
      .padding(0.4)
      .domain(result1.axeX.map(function(d) { return d;})),
    yScale1 = d3
      .scaleLinear()
      .range([0, width/2])
      .domain([0, Math.max(...result1.axeY)]),
    yScale2 = d3
      .scaleLinear()
      .range([0, width/2])
      .domain([0, Math.max(...result2.axeY)]);

    var chart = svg
      .append('g')
      .attr('transform','translate(0,' + margin_top + ')');

    var yGrid1 = chart
      .append('g')
      .call(
        d3.axisBottom(yScale1)
          .tickSize(height, 0, 0)
          .tickFormat("")
          .ticks(5)
      );

    yGrid1
      .selectAll('line')
      .attr('stroke', GRID_COLOR);

    yGrid1
      .select('.domain')
      .attr('stroke-width', 0);

    var yGrid2 = chart
    .append('g')
    .attr('transform','translate(' + width/2 + ')')
    .call(
        d3.axisBottom(yScale1)
        .tickSize(height, 0, 0)
        .tickFormat("")
        .ticks(5)
    );

    yGrid2
      .selectAll('line')
      .attr('stroke', GRID_COLOR);

    yGrid2
      .select('.domain')
      .attr('stroke-width', 0);

    if (type == 2) {
      chart.append('path')
        .datum(result1.axeX)
        .attr('fill', color1)
        .attr('class', 'area1')
        .attr('d', d3.area()
          .curve(d3.curveMonotoneY)
          .x0(width/2)
          .x1(width/2)
          .y(function(d, i) { return xScale(d) + xScale.bandwidth()/2; })
        );

      chart
        .selectAll('.area1')
        .transition()
        .duration(800)
        .ease(d3.easeCubic)
        .attr('d', d3.area()
          .curve(d3.curveMonotoneY)
          .x0(width/2)
          .x1(function (d, i) { return  width/2 - yScale1(result1.axeY[i]); })
          .y(function (d, i) { return xScale(d) + xScale.bandwidth()/2; })
        );

      chart.append('path')
        .datum(result1.axeX)
        .attr('fill', color2)
        .attr('class', 'area2')
        .attr('d', d3.area()
          .curve(d3.curveMonotoneY)
          .x0(width/2)
          .x1(width/2)
          .y(function (d, i) { return xScale(d) + xScale.bandwidth()/2; })
        );

      chart
        .selectAll('.area2')
        .transition()
        .duration(800)
        .ease(d3.easeCubic)
        .attr('d', d3.area()
          .curve(d3.curveMonotoneY)
          .x0(width/2)
          .x1(function (d, i) { return width/2 + yScale2(result2.axeY[i]); })
          .y(function (d, i) { return xScale(d) + xScale.bandwidth()/2; })
        );

        chart
          .selectAll('.bar')
          .data(result1.axeX)
          .enter()
          .append('rect')
          .attr('class', 'bar')
          .attr('fill', 'transparent')
          .attr('stroke-width', 0)
          .attr('y', function (d, i) { return xScale(d) })
          .attr('height', xScale.bandwidth())
          .attr('x', function (d, i) { return width/2 - yScale1(result1.axeY[i]); })
          .attr('width', function (d, i) { return yScale1(result1.axeY[i]) + yScale2(result2.axeY[i]); })
          .attr('data-toggle', 'tooltip')
          .attr('data-placement', 'right')
          .attr('data-html', 'true')
          .attr('title', function (d, i) {
            return result1.label[i] + '</br> ' + parseFloat(result1.axeY[i]).toFixed(precision1) + ' ' + unit1 + ' - ' + parseFloat(result2.axeY[i]).toFixed(precision2) + ' ' + unit2;
          })
          .on("mouseover", function (d, i) { d3.select(this).attr('fill', '#FFFFFFAA'); })
          .on("mouseout", function (d, i) { d3.select(this).attr('fill', 'transparent'); });
    }
    else {
      chart
          .selectAll('.bar1')
          .data(result1.axeX)
          .enter()
          .append('rect')
          .attr('class', 'bar1')
          .attr('fill', color1)
          .attr('stroke-width', 0)
          .attr('y', function (d, i) { return xScale(d) })
          .attr('height', xScale.bandwidth())
          .attr('x', width / 2)
          .attr('width', 0);

      chart
          .selectAll('.bar2')
          .data(result1.axeX)
          .enter()
          .append('rect')
          .attr('class', 'bar2')
          .attr('fill', color2)
          .attr('stroke-width', 0)
          .attr('y', function (d, i) { return xScale(d) })
          .attr('height', xScale.bandwidth())
          .attr('x', width/2)
          .attr('width', 0)
          .attr('data-toggle', 'tooltip')
          .attr('data-placement', 'right')
          .attr('data-html', 'true')
          .attr('title', function (d, i) {
            return result1.label[i] + '</br> ' + parseFloat(result1.axeY[i]).toFixed(precision1) + ' ' + unit1 + ' - ' + parseFloat(result2.axeY[i]).toFixed(precision2) + ' ' + unit2;
          })

      chart
          .selectAll('.bar1')
          .transition()
          .duration(600)
          .delay(function (d, i) { return i * 20; })
          .ease(d3.easeCubic)
          .attr('x', function (d, i) { return width / 2 - yScale1(result1.axeY[i]); })
          .attr('width', function (d, i) { return yScale1(result1.axeY[i]); });

      chart
          .selectAll('.bar2')
          .transition()
          .duration(600)
          .delay(function (d, i) { return i * 20; })
          .ease(d3.easeCubic)
          .attr('width', function (d, i) { return yScale2(result2.axeY[i]); });
    }

    $('[data-toggle=\'tooltip\']').tooltip();

    var xAxe = chart
      .append('g')
      .attr('transform', 'translate(' + width/2 + ',0)')
      .call(d3.axisLeft(xScale).tickFormat(''));

    xAxe
      .select('.domain')
      .attr('stroke', AXE_COLOR);

    xAxe
      .selectAll('line')
      .attr('transform', 'translate(3,0)')
      .attr('stroke', AXE_COLOR);

    svg
      .append('text')
      .attr("x", width/2 - 60)
      .attr("y", margin_top + height + margin_bottom)
      .text(unit1)
      .style('font-size', '1.1em');

    svg
      .append('text')
      .attr("x", width/2 + 30)
      .attr("y", margin_top + height + margin_bottom)
      .text(unit2)
      .style('font-size', '1.1em');
  }

  function loadingAnimation(target) {
    var element = d3
      .select('#' + target);

    element
      .selectAll('svg')
      .remove();

    element
      .selectAll('div')
      .remove();

    $('#' + target).append(
      '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; display: block;" width="100px" height="100px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid">' +
      '<circle cx="84" cy="50" r="10" fill="#eaebec">' +
      '    <animate attributeName="r" repeatCount="indefinite" dur="0.5102040816326531s" calcMode="spline" keyTimes="0;1" values="10;0" keySplines="0 0.5 0.5 1" begin="0s"></animate>' +
      '    <animate attributeName="fill" repeatCount="indefinite" dur="2.0408163265306123s" calcMode="discrete" keyTimes="0;0.25;0.5;0.75;1" values="#eaebec;#eaebec;#eaebec;#eaebec;#eaebec" begin="0s"></animate>' +
      '</circle><circle cx="16" cy="50" r="10" fill="#eaebec">' +
      '  <animate attributeName="r" repeatCount="indefinite" dur="2.0408163265306123s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="0s"></animate>' +
      '  <animate attributeName="cx" repeatCount="indefinite" dur="2.0408163265306123s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="0s"></animate>' +
      '</circle><circle cx="50" cy="50" r="10" fill="#eaebec">' +
      '  <animate attributeName="r" repeatCount="indefinite" dur="2.0408163265306123s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.5102040816326531s"></animate>' +
      '  <animate attributeName="cx" repeatCount="indefinite" dur="2.0408163265306123s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-0.5102040816326531s"></animate>' +
      '</circle><circle cx="84" cy="50" r="10" fill="#eaebec">' +
      '  <animate attributeName="r" repeatCount="indefinite" dur="2.0408163265306123s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-1.0204081632653061s"></animate>' +
      '  <animate attributeName="cx" repeatCount="indefinite" dur="2.0408163265306123s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-1.0204081632653061s"></animate>' +
      '</circle><circle cx="16" cy="50" r="10" fill="#eaebec">' +
      '  <animate attributeName="r" repeatCount="indefinite" dur="2.0408163265306123s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="0;0;10;10;10" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-1.530612244897959s"></animate>' +
      '  <animate attributeName="cx" repeatCount="indefinite" dur="2.0408163265306123s" calcMode="spline" keyTimes="0;0.25;0.5;0.75;1" values="16;16;16;50;84" keySplines="0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1;0 0.5 0.5 1" begin="-1.530612244897959s"></animate>' +
      '</circle>' +
      '</svg>'
    );
  }

  function displayError(target) {
    var element = d3
      .select('#' + target);

    element
      .selectAll('svg')
      .remove();

    element
      .selectAll('div')
      .remove();

    $('#' + target).append(
      '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" width="50px" height="50px"  viewBox="0 0 100 100" style="margin: auto; display: block;" xml:space="preserve">' +
      '<switch>' +
      '  <g i:extraneous="self" fill="#eaebec"><g>' +
      '    <path d="M67.4,40.6c2.2-2.2,2.2-5.8,0-8c-2.2-2.2-5.8-2.2-8,0l-6.6,6.6c-1.6,1.6-4.1,1.6-5.7,0l-6.6-6.6c-2.2-2.2-5.8-2.2-8,0     c-2.2,2.2-2.2,5.8,0,8l6.6,6.6c1.6,1.6,1.6,4.1,0,5.7l-6.6,6.6c-2.2,2.2-2.2,5.8,0,8c1.1,1.1,2.6,1.7,4,1.7s2.9-0.6,4-1.7     l6.6-6.6c1.6-1.6,4.1-1.6,5.7,0l6.6,6.6c1.1,1.1,2.6,1.7,4,1.7s2.9-0.6,4-1.7c2.2-2.2,2.2-5.8,0-8l-6.6-6.6     c-1.6-1.6-1.6-4.1,0-5.7L67.4,40.6z"/>' +
      '    <path d="M50,2.5C23.8,2.5,2.5,23.8,2.5,50S23.8,97.5,50,97.5c26.2,0,47.5-21.3,47.5-47.5S76.2,2.5,50,2.5z M50,86.2     C30,86.2,13.8,70,13.8,50C13.8,30,30,13.8,50,13.8S86.2,30,86.2,50C86.2,70,70,86.2,50,86.2z"/>' +
      '  </g></g>' +
      '</switch>' +
      '</svg>'
    );
  }

  exports.displayDoubleEvolution = displayDoubleEvolution;
  exports.displayGlobalEvolution = displayGlobalEvolution;
  exports.displayGlobalRepartitionH = displayGlobalRepartitionH;
  exports.displayGlobalRepartitionV = displayGlobalRepartitionV;
  exports.displayLegend = displayLegend;
  exports.displayWeekRepartitionH = displayWeekRepartitionH;
  exports.displayWeekRepartitionV = displayWeekRepartitionV;
  exports.displayXY = displayXY;
  exports.loadingAnimation = loadingAnimation;
  exports.displayError = displayError;
})();
