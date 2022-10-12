import { defineComponent, PropType } from 'vue';
import { FeedDataType } from '@/type/FeedData';
import { Frequence, RepartitionColonne } from '@/type/Granularite';
import { queryDoubleRepartition } from '@/api/data';
import { DataDoubleRepartition, weekDayList } from '@/type/DataValue';
import * as d3 from 'd3';
import Erreur from './Erreur';
import Legende from './Legende';
import Spinner from './Spinner';
import tippy from 'tippy.js';
import { hourFormat, weekDayFormat } from './d3-helpers';

export default defineComponent({
  name: 'SemaineHorizontal',
  components: {
    Erreur,
    Legende,
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
    min: {
      type: Number,
      required: false,
    },
    max: {
      type: Number,
      required: false,
    },
  } ,
  data() {
    return {
      data: new Array<DataDoubleRepartition>(),

      daySize: 16,
      barWidth: 6,
      axeColor: '#6d6d6d',
      gridColor: '#dddddd',
      rows: 7,
      cols: 24,
      marginTop: 35,
      marginLeft: 35,
      marginRight: 20,
      marginBottom: 15,
      barChartWidth: 150,

      loading: true,
      error: false,
    }
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
  },
  computed: {
    range(): d3.ScaleQuantile<number, never> {
      return d3
          .scaleQuantile()
          .domain([
              this.min ?? d3.min(this.data, (data) => data.value),
              this.max ?? d3.max(this.data, (data) => data.value),
            ])
          .range([...Array(this.feedDataType.colors.length).keys()])
    },
    maxValeur(): number {
      return d3.max(d3.map(this.data, d => d.value)) ?? 0
    },
    moyValeur(): number {
      return d3.mean(d3.map(this.data, d => d.value))?? 0
    },
    minValeur(): number {
      return d3.min(d3.map(this.data, d => d.value)) ?? 0
    },
    totalHeight(): number {
      return this.marginTop + this.rows * this.daySize + this.marginBottom
    },
    totalWidth(): number {
      return this.marginLeft + this.cols * this.daySize + this.barChartWidth + this.marginRight
    },
    barChartX(): number {
      return this.totalWidth - this.barChartWidth - this.marginRight + 10
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
      queryDoubleRepartition(
        this.feedDataId,
        Frequence.Hour,
        RepartitionColonne.WeekDay,
        RepartitionColonne.Hour,
        this.periode[0],
        this.periode[1]
      ).then((data) => {
        this.data = d3.sort(
          d3.sort(
            data,
            d => d.axeX
          ),
          d => d.axeY
        )

        this.loading = false
        this.rebuildGraph()
      }).catch(error => {
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
        .selectAll('div')
        .remove()

      const svg = element
        .append('svg')
          .attr('class', 'chart')
          .attr('width', this.totalWidth)
          .attr('height', this.totalHeight)

      const chart = svg
        .append('g')
          .attr('class', 'chart')
          .attr('width', this.totalWidth)
          .attr('height', this.totalHeight)

      this.appendDayLabel(chart)
      this.appendHourLabel(chart)
      this.appendNoDataRect(chart)
      this.appendDataRect(chart)
      this.appendBarChart(chart)
    },
    appendDayLabel(chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>) {
      chart.append('g')
          .attr('class', 'dayLabel')
        .selectAll('text')
        .data(d3.range(7))
        .join('text')
        .text(weekDayFormat)
          .style('fill', this.axeColor)
          .attr('transform', (d, i) => {
            return 'translate(30,' + (i * this.daySize + this.marginTop + 12) + ')'
          })
          .style('text-anchor', 'end')
    },
    appendHourLabel(chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>) {
      chart.append('g')
          .attr('class', 'timeLabel')
        .selectAll('text')
        .data(d3.range(24))
        .join('text')
        .text((d, i) => i % 3 === 0 ? hourFormat(d) : null)
          .style('text-anchor', 'left')
          .style('fill', this.axeColor)
          .attr('transform', (d, i) => {
              return 'rotate(-90)translate(-30,' + (i * this.daySize + this.marginLeft + 12) + ')'
          })
          .attr('font-family', 'sans-serif')
          .attr('font-size', 10)
    },
    appendNoDataRect(chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>) {
      chart.append('g')
          .attr('class', 'no-data')
        .selectAll('rect')
        .data(d3.utcHours(new Date('2021-12-27 00:00'), new Date('2022-01-02 24:00')))
        .join('rect')
        .attr("x", d => d.getUTCHours() * this.daySize + this.marginTop)
        .attr("y", d => d.getUTCDay() * this.daySize + this.marginLeft)
          .attr('rx', 10)
          .attr('width', this.daySize)
          .attr('height', this.daySize)
          .attr('fill', '#eaebec')
    },
    appendDataRect(chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>) {
      chart.append('g')
          .attr('class', 'data')
        .selectAll('rect')
        .data(this.data)
        .join('rect')
        .attr("x", d => d.axeY * this.daySize + 0.5 * this.daySize + this.marginTop)
        .attr("y", d => d.axeX * this.daySize + 0.5 * this.daySize + this.marginLeft)
        .attr('rx', 10)
        .attr('width', 0)
        .attr('height', 0)
        .attr('fill', (d) => d.value ? this.feedDataType.colors[this.range(d.value)] : '#eaebec')
        .each((d, i, element) => {
          tippy(element[i] as SVGRectElement, {
            content: 'Le ' + weekDayList[d.axeX].toLowerCase() + ' de ' + hourFormat(d.axeY) + ' à ' + hourFormat(d.axeY + 1) + '</br> ' + d.value.toFixed(this.feedDataType.precision) + ' ' + this.feedDataType.unite,
            allowHTML: true,
            placement: 'right',
          })
        })

      chart
        .selectAll('.data rect')
        .data(this.data)
        .transition()
        .duration(300)
        .delay((d, i) => i * 3)
        .ease(d3.easeCubic)
        .attr("x", d => d.axeY * this.daySize + this.marginTop)
        .attr("y", d => d.axeX * this.daySize + this.marginLeft)
          .attr('width', this.daySize - 1)
          .attr('height', this.daySize - 1)
    },
    appendBarChart(chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>) {

      const weekdayData = d3.map(
        d3.groups(this.data, d => d.axeX),
        d => {return {axeX: d[0], value: d3.sum(d[1], d => d.value)}}
      )
      const barChart = chart.append('g')
          .attr('class', 'bar-chart')
          .attr('width', this.barChartWidth)
          .attr('height', this.totalHeight - this.marginTop - this.marginBottom)
          .attr('transform','translate(' + this.barChartX + ',' + this.marginTop + ')')

      const yScale = d3
        .scaleLinear()
        .range([this.barChartWidth, 0])
        .domain([d3.max(d3.map(weekdayData, d => d.value)) ?? 0, 0])

      const yGrid = barChart
        .append('g')
        .call(
          d3.axisTop(yScale)
            .tickSize(-this.totalHeight + this.marginTop + this.marginBottom)
            .tickFormat(null)
            .ticks(5)
        )

      yGrid
        .selectAll('text')
        .style('fill', this.axeColor)

      yGrid
        .selectAll('line')
        .attr('stroke', this.gridColor)

      yGrid
        .select('.domain')
        .attr('stroke-width', 0)


      barChart.append('g')
          .attr('class', 'bar')
        .selectAll('rect')
        .data(weekdayData)
        .join('rect')
        .attr("x", d => 0)
        .attr("y", d => d.axeX * this.daySize + this.daySize/2 - this.barWidth /2)
        .attr('rx', this.barWidth/2)
        .attr('width', 0)
        .attr('height', this.barWidth)
        .attr('fill', this.feedDataType.color)
        .attr('stroke-width', 0)
        .each((d, i, element) => {
          tippy(element[i] as SVGRectElement, {
            content: 'Le ' + weekDayList[d.axeX].toLowerCase() + '</br>en moyenne, ' + d.value.toFixed(this.feedDataType.precision) + ' ' + this.feedDataType.unite,
            allowHTML: true,
            placement: 'right',
          })
        })

      barChart
        .selectAll('.bar rect')
        .data(weekdayData)
        .transition()
        .duration(600)
        .delay((d, i) => (i + (24 * 7)) * 3)
        .ease(d3.easeCubic)
        .attr('width', d => yScale(d.value))
    }
  },
})
