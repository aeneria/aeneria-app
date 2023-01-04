import { DataPoint } from '@/type/DataValue';
import { defineComponent, PropType } from 'vue';
import { FeedDataType } from '@/type/FeedData';
import { Frequence } from '@/type/Granularite';
import { monthFormat, shortWeekDayFormat } from './d3-helpers';
import { queryDataPoint } from '@/api/data';
import * as d3 from 'd3';
import Erreur from './Erreur';
import Legende from './Legende';
import Spinner from './Spinner';
import tippy from 'tippy.js';

export default defineComponent({
  name: 'Calendrier',
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
    rawPeriode: {
      type: Array as unknown as PropType<[Date|null, Date|null]>,
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
  },
  data() {
    return {
      data: new Array<DataPoint>(),

      daySize: 16,
      axeColor: '#6d6d6d',
      cols : 7,
      marginTop : 35,
      marginLeft : 35,
      marginBottom : 30,

      loading: true,
      error: false,
    }
  },
  computed: {
    totalHeight(): number {
      return this.marginTop + this.rows * this.daySize + this.marginBottom
    },
    totalWidth(): number {
      return this.marginLeft + this.cols * this.daySize
    },
    range(): d3.ScaleQuantile<number, never> {
      return d3
        .scaleQuantile()
        .domain([
            this.min ?? d3.min(this.data, (data) => data.value),
            this.max ?? d3.max(this.data, (data) => data.value),
          ])
        .range([...Array(this.feedDataType.colors.length).keys()])
    },
    rows(): number {
      return d3.utcMonday.count(
        this.periode[0],
        this.periode[1]
      )
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
    periode(): [Date, Date] {
      return [
        this.rawPeriode[0] ?? new Date(),
        this.rawPeriode[1] ?? new Date(),
      ]
    }
  },
  mounted() {
    this.refresh()
  },
  watch: {
    periode(newValue, oldValue) {
      if (newValue[0] != oldValue[0] && newValue[1] != oldValue[1]) {
        this.refresh()
      }
    },
    feedDataId(newValue, oldValue) {
      if (newValue != oldValue) {
        this.refresh()
      }
    },
    max(newValue, oldValue) {
      if (newValue != oldValue) {
        this.rebuildGraph()
      }
    }
  },
  methods: {
    refresh() {
      this.error = false
      this.loading = true

      if (!this.feedDataId || !this.rawPeriode[0] || !this.rawPeriode[1]) {
        this.error = true
        this.loading = false
        return
      }

      queryDataPoint(
        this.feedDataId,
        Frequence.Day,
        this.rawPeriode[0],
        this.rawPeriode[1]
      )
      .then((data) => {
        this.data = d3.sort(data, d => d.date)
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
      this.appendMonthLabel(chart)
      this.appendNoDataRect(chart)
      this.appendDataRect(chart)
    },
    appendDayLabel(chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>) {
      chart.append('g')
          .attr('class', 'dayLabel')
        .selectAll('text')
        .data(d3.range(7))
        .join('text')
        .text(shortWeekDayFormat)
          .style('fill', this.axeColor)
          .attr('transform', (d, i) => {
            return 'rotate(-90)translate(-30,' + (i * this.daySize + this.marginTop + 12) + ')'
          })
    },
    appendMonthLabel(chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>) {
      chart.append('g')
        .attr('class', 'monthLabel')
        .selectAll('text')
        .data(d3.utcMonths(this.periode[0], this.periode[1]))
        .join("text")
          .attr("x", this.marginLeft -5)
          .attr("y", d => d3.utcSunday.count(this.periode[0], d) * this.daySize + 10 + this.marginLeft)
          .text((d,i) => monthFormat(d))
          .style('text-anchor', 'end')
          .style('fill', this.axeColor)
    },
    appendNoDataRect(chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>) {
      chart.append('g')
          .attr('class', 'no-data')
        .selectAll('rect')
        .data(d3.utcDays(this.periode[0], this.periode[1]))
        .join('rect')
          .attr("x", d => d.getDay()%7 * this.daySize + this.marginLeft)
          .attr("y", d => d3.utcSunday.count(this.periode[0], d) * this.daySize + this.marginTop)
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
          .attr("x", d => d.date.getUTCDay()%7 * this.daySize + 0.5 * this.daySize + this.marginLeft)
          .attr("y", d => d3.utcSunday.count(this.periode[0], d.date) * this.daySize + 0.5 * this.daySize + this.marginTop)
          .attr('rx', 10)
          .attr('width', 0)
          .attr('height', 0)
          .attr('fill', (d) => d.value !== null ? this.feedDataType.colors[this.range(d.value)] : '#eaebec')
          .each((d, i, element) => {
            tippy(element[i] as SVGRectElement, {
              content: d3.timeFormat("%d/%m/%Y")(d.date) + '</br> ' + d.value.toFixed(this.feedDataType.precision) + ' ' + this.feedDataType.unite,
              allowHTML: true,
              placement: 'right',
            })
          })

      chart
        .select('g.data')
        .selectAll('rect')
        .data(this.data)
        .transition()
        .duration(300)
        .delay((d, i) => i )
        .ease(d3.easeCubic)
          .attr("x", d => d.date.getUTCDay()%7 * this.daySize + this.marginLeft)
          .attr("y", d => d3.utcSunday.count(this.periode[0], d.date) * this.daySize + this.marginTop)
          .attr('width', this.daySize - 1)
          .attr('height', this.daySize - 1)
    },
  },
})
