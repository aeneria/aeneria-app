import { adaptToGranularite, formatMulti, formatWithGranularite } from './d3-helpers';
import { DataPoint } from '@/type/DataValue';
import { defineComponent, PropType } from 'vue';
import { FeedDataType } from '@/type/FeedData';
import { Granularite } from '@/type/Granularite';
import { queryDataPoint } from '@/api/data';
import * as d3 from 'd3';
import Erreur from './Erreur';
import Spinner from './Spinner';
import tippy from 'tippy.js';

export default defineComponent({
  name: 'Evolution',
  components: {
    Erreur,
    Spinner,
  },
  props: {
    id: {
      type: String,
      required: true,
    },
    periode: {
      type: Array as unknown as PropType<[Date, Date]>,
      required: true,
    },
    feedDataId: {
      type: Number,
      required: true,
    },
    feedDataType: {
      type: Object as PropType<FeedDataType>,
      required: true,
    },
    granularite: {
      type: Object as PropType<Granularite>,
      required: true,
    },
  } ,
  data() {
    return {
      data: new Array<DataPoint>(),

      barWidth: 4,
      axeColor: '#6d6d6d',
      gridColor: '#dddddd',
      marginTop: 20,
      marginLeft: 40,
      marginRight: 40,
      marginBottom: 50,
      height: 450,

      loading: true,
      error: false,
    }
  },
  computed: {
    totalWidth(): number {
      const node = d3.select('#' + this.id).node()
      let totalWidth = 320
      if (node instanceof HTMLElement) {
        totalWidth = (node.parentElement?.parentElement?.clientWidth ?? 0)
      }

      return totalWidth
    },
    totalHeight(): number {
      return this.height + this.marginTop + this.marginBottom
    },
    width(): number {
      return this.totalWidth - this.marginTop - this.marginBottom
    },
    maxValeur(): number {
      return d3.max(d3.map(this.data, d => d.value)) ?? 0
    },
  },
  mounted() {
    this.refresh()
  },
  watch: {
    periode() {
      this.refresh()
    },
    feedDataId() {
      this.refresh()
    },
    granularite() {
      this.refresh()
    },
  },
  methods: {
    refresh() {
      this.error = false
      this.loading = true

      if (!this.feedDataId) {
        this.error = true
        this.loading = false
        return
      }

      queryDataPoint(
        this.feedDataId,
        this.granularite.frequence,
        this.periode[0],
        this.periode[1]
      )
      .then((data) => {
        this.data = d3.sort<DataPoint>(
          data,
          d => d.date
        )

        this.loading = false
        this.rebuildGraph()
      })
      .catch(error => {
        this.error = true
        this.loading = false
        console.log(error)
      })
    },
    rebuildGraph() {
      const element = d3
        .select('#' + this.id)

      element
        .selectAll('svg')
        .remove()

      element
        .selectAll('g')
        .remove()

      const svg = element
        .append('svg')
          .attr('class', 'chart')
          .attr('width', this.totalWidth)
          .attr('height', this.totalHeight)

      const chart = svg
        .append('g')
        .attr('transform','translate(' + this.marginLeft + ',' + this.marginTop + ')')

      const x = this.appendXaxis(chart)
      const y = this.appendYaxis(chart)
      this.appendArea(chart, x, y)
      this.appendCourbe(chart, x, y)
      this.appendBar(chart, x, y)
      this.appendEventArea(chart, x, y)
      this.appendCircle(chart, x, y)
    },
    appendXaxis(chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>): d3.ScaleTime<number, number, never> {
      const x = d3.scaleTime()
        .domain([
          adaptToGranularite(this.granularite, this.periode[0]),
          adaptToGranularite(this.granularite, this.periode[1]),
        ])
        .range([0, this.width])

      const xAxis = chart.append("g")
        .attr("transform", `translate(0,${this.height})`)
        .attr("class", "axe-x")
        .call(
          d3.axisBottom(x)
            .tickSizeOuter(0)
            .ticks(d3.min([this.width/100, this.data.length]))
            .tickFormat(date => formatMulti(date))
        )

      xAxis.select(".domain")
        .style('stroke', this.axeColor)

      xAxis.selectAll(".tick text")
        .style('fill', this.axeColor)

      xAxis.selectAll(".tick line")
        .attr('stroke-width', '0.5px')
        .attr('stroke', this.gridColor)

      return x
    },
    appendYaxis(chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>): d3.ScaleLinear<number, number, never> {
      const y = d3.scaleLinear()
        .domain([0, this.maxValeur])
        .nice()
        .range([this.height, this.marginTop])

      const yAxis = chart.append("g")
      .attr("transform", `translate(-5,0)`)
        .attr("class", "axe-y")
        .call(
          d3.axisLeft(y)
            .tickSize(-this.width - 10)
            .ticks(5)
        )
        .call(g => g.append("text")
          .attr("x", -5)
          .attr("y", 5)
          .style('fill', this.axeColor)
          .attr("text-anchor", "end")
          .text(this.feedDataType.unite)
        )

      yAxis.select(".domain").remove()

      yAxis.selectAll(".tick text")
        .style('fill', this.axeColor)

      yAxis.selectAll(".tick line")
        .attr('stroke-width', '0.5px')
        .attr('stroke', this.gridColor)

      return y
    },
    appendCircle(
      chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>,
      x: d3.ScaleTime<number, number, never>,
      y: d3.ScaleLinear<number, number, never>,
    ) {
      chart.append("g")
        .attr('class', 'circle')
        .selectAll('circle')
        .data(this.data)
        .join('circle')
        .attr('fill', this.feedDataType.color)
        .attr('stroke-width', 1)
        .attr('stroke', 'white')
        .attr('cx', d => x(d.date))
        .attr('cy', this.height)
        .attr('r', this.barWidth/2)
        .attr('display', 'none')
        .each((d, i, element) => {
          tippy(element[i] as SVGRectElement, {
            content: formatWithGranularite(this.granularite, d.date) + '</br> ' + d.value.toFixed(this.feedDataType.precision) + ' ' + this.feedDataType.unite,
            allowHTML: true,
            triggerTarget: Array.from(chart.node()?.querySelectorAll<Element>('[data-point-id="' + d.id + '"]') ?? [])
          })
        })

      chart
        .selectAll('.circle circle')
        .data(this.data)
        .transition()
        .duration(800)
        .ease(d3.easeCubic)
        .attr('cy', (d, i) => y(d.value))
    },
    appendBar(
      chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>,
      x: d3.ScaleTime<number, number, never>,
      y: d3.ScaleLinear<number, number, never>,
    ) {
      chart.append("g")
        .attr('class', 'bar')
        .selectAll('text')
        .data(this.data)
        .join('rect')
        .attr('fill', 'white' )
        .attr('fill-opacity', '0.4')
        .attr('stroke-width', 0)
        .attr('data-point-id', d => d.id)
        .attr('x', d => x(d.date) - this.barWidth / 2)
        .attr('y', this.height)
        .attr('width', this.barWidth)
        .attr('height', 0)
        .attr('display', 'none')

      chart
        .selectAll('.bar rect')
        .data(this.data)
        .transition()
        .duration(800)
        .ease(d3.easeCubic)
        .attr('y', (d, i) => y(d.value))
        .attr('height', (d, i) => this.height - y(d.value))
    },
    appendCourbe(
      chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>,
      x: d3.ScaleTime<number, number, never>,
      y: d3.ScaleLinear<number, number, never>,
    ) {
      chart.append("g")
        .append("path")
        .attr('stroke', this.feedDataType.color)
        .attr('stroke-width', this.barWidth)
        .attr('stroke-linecap', 'round')
        .attr('fill', 'transparent')
        .attr('class', 'courbe')
        .datum(this.data)
        .attr("d",
          d3.line<DataPoint>()
            .curve(d3.curveMonotoneX)
            .x(d => x(d.date))
            .y(this.height)
        )

      chart
        .select('.courbe')
        .datum(this.data)
        .transition()
        .duration(800)
        .ease(d3.easeCubic)
        .attr('d',
          d3.line<DataPoint>()
            .curve(d3.curveMonotoneX)
            .x(d => x(d.date))
            .y(d => y(d.value))
        )
    },
    appendArea(
      chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>,
      x: d3.ScaleTime<number, number, never>,
      y: d3.ScaleLinear<number, number, never>,
    ) {
      chart.append("g")
        .append("path")
        .attr('stroke-width', 0)
        .attr('stroke-linecap', 'round')
        .attr('fill', this.feedDataType.color)
        .attr('fill-opacity', '0.3')
        .attr('class', 'area')
        .datum(this.data)
        .attr("d",
          d3.area<DataPoint>()
            .curve(d3.curveMonotoneX)
            .x(d => x(d.date))
            .y0(this.height)
            .y1(this.height)
        )

      chart
        .select('.area')
        .datum(this.data)
        .transition()
        .duration(800)
        .ease(d3.easeCubic)
        .attr('d',
          d3.area<DataPoint>()
            .curve(d3.curveMonotoneX)
            .x(d => x(d.date))
            .y0(this.height)
            .y1(d => y(d.value))
        )
    },
    appendEventArea(
      chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>,
      x: d3.ScaleTime<number, number, never>,
      y: d3.ScaleLinear<number, number, never>,
    ) {
      chart.append("g")
        .attr('class', 'area-event')
        .selectAll('path')
        .data(this.data)
        .join("path")
        .attr('stroke-width', 0)
        .attr('stroke-linecap', 'round')
        .attr('fill', 'transparent')
        .attr('data-point-id', d => d.id)
        .datum((d, i) => {
          const ret = new Array<DataPoint>()
          if (i > 0) {
            ret.push({
              id: d.id,
              date: new Date((this.data[i-1].date.getTime() + d.date.getTime()) / 2),
              value: (this.data[i-1].value + d.value) / 2
            })
          }
          ret.push(d)
          if (i < (this.data.length - 1)) {
            ret.push({
              id: d.id,
              date: new Date((this.data[i+1].date.getTime() + d.date.getTime()) / 2),
              value: (this.data[i+1].value + d.value) / 2
            })
          }

          return ret
        })
        .attr("d",
          d3.area<DataPoint>()
            .curve(d3.curveMonotoneX)
            .x(d => x(d.date))
            .y0(this.height)
            .y1(this.height)
        )
        .on('mouseenter', (event, d) => {
          chart
            .selectAll('.circle circle')
            .attr('display', '')
          chart
            .selectAll('.bar rect[data-point-id="' + d[0].id + '"]')
            .attr('display', '')
        })
        .on('mouseleave', (event, d) => {
          chart
            .selectAll('.circle circle')
            .attr('display', 'none')
          chart
            .selectAll('.bar rect[data-point-id="' + d[0].id + '"]')
            .attr('display', 'none')
        })

      chart
        .selectAll('.area-event path')
        .data(this.data)
        .datum((d, i) => {
          const ret = new Array<DataPoint>()
          if (i > 0) {
            ret.push({
              id: d.id,
              date: new Date((this.data[i-1].date.getTime() + d.date.getTime()) / 2),
              value: (this.data[i-1].value + d.value) / 2
            })
          }
          ret.push(d)
          if (i < (this.data.length - 1)) {
            ret.push({
              id: d.id,
              date: new Date((this.data[i+1].date.getTime() + d.date.getTime()) / 2),
              value: (this.data[i+1].value + d.value) / 2
            })
          }

          return ret
        })
        .transition()
        .duration(800)
        .ease(d3.easeCubic)
        .attr('d',
          d3.area<DataPoint>()
            .curve(d3.curveMonotoneX)
            .x(d => x(d.date))
            .y0(this.height)
            .y1(d => y(d.value))
        )
    },
  },
})
