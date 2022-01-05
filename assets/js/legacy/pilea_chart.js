(function() {
  const DAY_SIZE = 15;
  const GRID_COLOR = '#dddddd';
  const AXE_COLOR = '#6d6d6d';


  /**
   * Display a histogram if there's less than 20 data, curve after.
   *
   *   result: an object containing :
   *     - axeX: values to display
   *     - axeY: axeY labels
   *   target: id of the targetted DIV element
   *   color_class: a string, class that will determine color palette
   *   unit: a string, the unit of the displayed data
   *   precision: float precision for value
   *   height: height of graph area
   *   width: width of graph area
   *   margin_bottom: place for axe x ticks
   */
  var displayGlobalEvolution = function (result, target, color_class, unit, precision, height = 350, width = 800, margin_bottom = 90) {
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
      .attr('class', 'chart ' + color_class)
      .attr('width', margin_left + width + margin_right)
      .attr('height', margin_top + height + margin_bottom);

    var xScale = d3
      .scaleBand()
      .range([0, width])
      .padding(0.4)
      .domain(result.axeX.map(function (d) { return d;}))
    var yScale = d3
      .scaleLinear()
      .range([0, height])
      .domain([Math.max(...result.axeY), 0])

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
      .attr('fill', (type == 1) ? '' : 'transparent' )
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
   * Display a x/y repartition.
   *
   *   results: an objects containing :
   *     - xValue: values for x axe
   *     - yValue: values for y axe
   *     - dates: corresponding dates
   *   target: id of the targetted DIV element
   *   color_class: a string, class that will determine color palette
   *   unitx: a string, the unit of axe X
   *   unity: a string, the unit of axe Y
   *   precisionx: float precision for value for axex y
   *   precisiony: float precision for value for axe X
   *   height:  in pixels
   *   width: in pixels
   *   margin_bottom: in pixels
   */
  var displayXY = function (results, target, color_class, unitx, unity, precisionx, precisiony, height = 525, width = 800, margin_bottom = 40) {
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
      .attr('class', 'chart ' + color_class)
      .attr('width', margin_left + width + margin_right)
      .attr('height', margin_top + height + margin_bottom);

    var xAxeDatas = [];
    var yAxeDatas = [];
    for (result of results) {
      xAxeDatas.push(...result.axeX);
      yAxeDatas.push(...result.axeY);
    }

    var xScale = d3
      .scaleLinear()
      .range([0, width])
      .domain([0, Math.max(...xAxeDatas) * 1.05]);
    yScale = d3
      .scaleLinear()
      .range([0, height])
      .domain([Math.max(...yAxeDatas) * 1.05, 0]);

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

    for (const [index, result] of results.entries()) {
      chart
        .selectAll('.point' + index)
        .data(result.axeX)
        .enter()
        .append('circle')
        .attr('class', 'point' + index)
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
          .selectAll('.point' + index)
          .transition()
          .duration(400)
          .delay(function(d, i) { return i * 10; })
          .ease(d3.easeCubic)
          .attr('r', 4);
    }

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
   *   color_class1: a string, class that will determine color for bars
   *   color_class2: a string, class that will determine color for bars
   *   unit1: a string, the unit of the displayed data
   *   unit2: a string, the unit of the displayed data
   *   precision1: float precision for value
   *   precision2: float precision for value
   */
  var displayDoubleEvolution = function (result1, result2, target, color_class1, color_class2, unit1, unit2, precision1, precision2, sameTimeline = true, height = 460, width = 200) {
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
        .attr('class', 'area1 ' + color_class1)
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
        .attr('class', 'area2 ' + color_class2)
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

    } else {
      chart
          .selectAll('.bar1')
          .data(result1.axeX)
          .enter()
          .append('rect')
          .attr('class', 'bar1 ' + color_class1)
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
          .attr('class', 'bar2 ' + color_class2)
          .attr('stroke-width', 0)
          .attr('y', function (d, i) { return xScale(d) })
          .attr('height', xScale.bandwidth())
          .attr('x', width/2)
          .attr('width', 0);

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
        if (sameTimeline) {
          return result1.label[i] + '</br> ' + parseFloat(result1.axeY[i]).toFixed(precision1) + ' ' + unit1 + ' - ' + parseFloat(result2.axeY[i]).toFixed(precision2) + ' ' + unit2;
        } else {
          return result1.label[i] +' - ' + parseFloat(result1.axeY[i]).toFixed(precision1) + ' ' + unit1 + '</br> ' + result2.label[i] + ' - ' + parseFloat(result2.axeY[i]).toFixed(precision2) + ' ' + unit2;
        }
      })
      .on("mouseover", function (d, i) { d3.select(this).attr('fill', '#FFFFFFAA'); })
      .on("mouseout", function (d, i) { d3.select(this).attr('fill', 'transparent'); });

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


  exports.displayDoubleEvolution = displayDoubleEvolution;
  exports.displayGlobalEvolution = displayGlobalEvolution;
  exports.displayXY = displayXY;
})();
