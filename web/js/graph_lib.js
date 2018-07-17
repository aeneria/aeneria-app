/**
 * Display a hour/weekDay heatmap repartition graphic.
 *
 *   result : an object containing :
 *     - axe.x : axeX labels
 *     - axe.y : axeY labels
 *     - data.values : values to display
 *     - data.dates : corresponding dates
 *   target : id of the targetted DIV element
 *   colors : a tab with 2 elements for the color scale
 *   unit : a string, the unit of the displayed data
 *
 */
var displayWeekRepartition = function(result, target, colors, unit) {
  var rows = 24; // Number of hours in a day
  var cols = 7; // Number of days in a week
  var row_height = 20;
  var col_width = 20;
  var margin_top = 25;
  var margin_left = 25;
  var margin_bottom = 10;
  var total_height = margin_top + rows * row_height + margin_bottom;
  var total_width = margin_left + cols * col_width;

  var svg = d3
    .select("#" + target)
    .append("svg")
    .attr("class", "chart")
    .attr("width", total_width)
    .attr("height", total_height);

  var chart = svg
    .append("g")
    .attr("class", "chart")
    .attr("width", total_width)
    .attr("height", total_height);

  var color = d3
    .scaleQuantile()
    .domain([ d3.min(result.data.values), d3.max(result.data.values) ])
    .range(colors);

  // Define the div for the tooltip
  var div = d3
    .select("#" + target)
    .append("div")
    .attr("class", "tooltip")
    .style("opacity", 0);

  chart
    .selectAll(".dayLabel")
    .data(result.axe.x)
    .enter()
    .append("text")
    .text(function(d) { return d;})
    .style("text-anchor", "left")
    .attr("transform", function(d, i) {
        return "rotate(-90)translate(-20," + (i * col_width + margin_left + 15) + ")"
    })
    .attr("font-family", "sans-serif")
    .attr("font-size", 10);

  chart
    .selectAll(".timeLabel")
    .data(result.axe.y)
    .enter()
    .append("text")
    .text(function(d, i) {
        if (i % 3 == 0) {
          return d;
        } else {
          return "";
        }
    })
    .style("text-anchor", "left")
    .attr("transform", function(d, i) {
      return "translate(0," + (i * row_height + margin_top + 4) + ")"
    })
    .attr("font-family", "sans-serif")
    .attr("font-size", 10);

  chart
    .selectAll("rect")
    .data(result.data.values)
    .enter()
    .append("rect")
    .attr("x", function(d, i) {
      return Math.floor(i / rows) * col_width + margin_left;
    })
   .attr("y", function(d, i) {
      return i % rows * row_height + margin_top;
    })
  .attr("width", col_width)
  .attr("height", row_height)
  .attr("fill", color)
  .attr("data-toggle", "tooltip")
  .attr("data-placement", "left")
  .attr("data-html", "true")
  .attr("title", function(d, i) {
      return result.data.dates[i] + "</br> " + parseFloat(d).toFixed(2) + " " + unit;
  });
}

/**
 * Display a weekDay/week heatmap repartition.
 *
 *   result : an object containing :
 *     - axe.x : axeX labels
 *     - axe.y : axeY labels
 *     - data.values : values to display
 *     - data.dates : corresponding dates
 *   target : id of the targetted DIV element
 *   colors : a tab with 2 elements for the color scale
 *   unit : a string, the unit of the displayed data
 */
var displayGlobalRepartition = function(result, target, colors, unit) {
  var rows = result.axe.y.length; // Number of days in a week
  var cols = result.axe.x.length; // Number of weeks we want to display
  var row_height = 20;
  var col_width = 20;
  var margin_top = 50;
  var margin_left = 25;
  var margin_bottom = 10;
  var total_height = margin_top + rows * row_height + margin_bottom;
  var total_width = margin_left + cols * col_width;

  var svg = d3
    .select("#" + target)
    .append("svg")
    .attr("class", "chart")
    .attr("width", total_width)
    .attr("height", total_height);

  var chart = svg
    .append("g")
    .attr("class", "chart")
    .attr("width", total_width)
    .attr("height", total_height);

  var color = d3
    .scaleQuantile()
    .domain([ d3.min(result.data.values), d3.max(result.data.values) ])
    .range(colors);

  // Define the div for the tooltip
  var div = d3
    .select("#" + target)
    .append("div")
    .attr("class", "tooltip")
    .style("opacity", 0);

  chart
    .selectAll(".weekLabel")
    .data(result.axe.x)
    .enter()
    .append("text")
    .text( function(d, i) {
      if (i % 3 == 1) {
        return d;
      } else {
        return "";
      }
    })
    .style("text-anchor", "left")
    .attr("transform", function(d, i) {
        return "translate(" + (i * col_width + margin_left - 10) + ",45)rotate(-45)"
    })
    .attr("font-family", "sans-serif")
    .attr("font-size", 10);

  chart
    .selectAll(".dayLabel")
    .data(result.axe.y)
    .enter()
    .append("text")
    .text(function(d) { return d; })
    .style("text-anchor", "left")
    .attr("transform", function(d, i) {
      return "translate(0," + (i * row_height + margin_top + 14) + ")"
    })
    .attr("font-family", "sans-serif")
    .attr("font-size", 10);

  chart
    .selectAll("rect")
    .data(result.data.values)
    .enter()
    .append("rect")
    .attr("x", function(d, i) {
      return Math.floor(i / rows) * col_width + margin_left;
    })
    .attr("y", function(d, i) {
      return i % rows * row_height + margin_top;
    })
    .attr("width", col_width)
    .attr("height", row_height)
    .attr("fill", color)
    .attr("data-toggle", "tooltip")
    .attr("data-placement", "left")
    .attr("data-html", "true")
    .attr("title", function(d, i) {
        return result.data.dates[i] + "</br> " + parseFloat(d).toFixed(2) + " " + unit;
    });
}

/**
 * Display a histogram.
 *
 *   result : an object containing :
 *     - axe.x : values to display
 *     - axe.y : axeY labels
 *   target : id of the targetted DIV element
 *   color :  hexadecimal color for bar
 *   unit : a string, the unit of the displayed data
 */
var displayGlobalEvolution = function(result, target, color, unit) {
  var margin_top = 20;
  var margin_left = 60;
  var margin_bottom = 80;
  var margin_right = 20;
  var height = 415;
  var width = 800;

  var svg = d3
    .select("#" + target)
    .append("svg")
    .attr("class", "chart")
    .attr("width", margin_left + width + margin_right)
    .attr("height", margin_top + height + margin_bottom);

  var xScale = d3
    .scaleBand()
    .range([0, width])
    .padding(0.4)
    .domain(result.axeX.map(function(d) { return d;})),
  yScale = d3
    .scaleLinear()
    .range([0, height])
    .domain([0, d3.max(result.axeY)]);

  var chart = svg
    .append("g")
    .attr("transform","translate(" + margin_left + "," + margin_top + ")");

  chart
    .selectAll(".bar")
    .data(result.axeX)
    .enter()
    .append("rect")
    .attr("class", "bar")
    .attr("fill", color)
    .attr("x", function(d, i) { return xScale(d); })
    .attr("y", function(d, i) { return yScale(result.axeY[i]); })
    .attr("width", xScale.bandwidth())
    .attr("height", function(d, i) { return height - yScale(result.axeY[i]); });

  chart
    .append("g")
    .attr("transform", "translate(0," + height + ")")
    .call(d3.axisBottom(xScale))
    .selectAll("text")
    .style("text-anchor", "end")
    .attr("dx", "-.8em")
    .attr("dy", ".15em")
    .attr("transform", "rotate(-65)");

  chart
    .append("g")
    .call(d3.axisLeft(yScale)
    .tickFormat(function(d) { return d; })
    .ticks(10))
    .append("text")
    .attr("transform", "rotate(-90)")
    .attr("y", 6)
    .attr("dy", "-5.1em")
    .attr("text-anchor", "end")
    .attr("stroke", "black")
    .text(unit);
}
