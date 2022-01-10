import { defineComponent, PropType } from 'vue';
import { FeedDataType } from '@/type/FeedData';
import { Granularite } from '@/type/Granularite';
import { queryDataPoint } from '@/api/data';
import { DataPoint } from '@/type/DataValue';
import * as d3 from 'd3';
import Erreur from './Erreur';
import Spinner from './Spinner';
import tippy from 'tippy.js';
import { formatWithGranularite } from './d3-helpers';

export default defineComponent({
  name: 'NuagePoint',
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
    feedDataIdX: {
      type: Number,
      required: true,
    },
    feedDataTypeX: {
      type: Object as PropType<FeedDataType>,
      required: true,
    },
    feedDataIdY: {
      type: Number,
      required: true,
    },
    feedDataTypeY: {
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
      dataX: [] as Array<DataPoint>,
      dataY: [] as Array<DataPoint>,

      barWidth: 8,
      axeColor: '#6d6d6d',
      gridColor: '#dddddd',
      marginTop: 20,
      marginLeft: 40,
      marginRight: 30,
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
      return this.totalWidth - this.marginLeft - this.marginRight
    },
    maxValeurX(): number {
      return d3.max(d3.map(this.dataX, d => d.value)) ?? 0
    },
    maxValeurY(): number {
      return d3.max(d3.map(this.dataY, d => d.value)) ?? 0
    },
  },
  mounted() {
    this.refresh()
  },
  watch: {
    periode() {
      this.refresh()
    },
    feedDataIdX() {
      this.refresh()
    },
    feedDataIdY() {
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

      if (!this.feedDataIdX && !this.feedDataIdY) {
        this.error = true
        this.loading = false
        return
      }

      queryDataPoint(
        this.feedDataIdX,
        this.granularite.frequence,
        this.periode[0],
        this.periode[1]
      ).then((data) => {
        this.dataX = d3.sort<DataPoint>(
          data,
          d => d.date
        )
      }).then(() => {
        queryDataPoint(
          this.feedDataIdY,
          this.granularite.frequence,
          this.periode[0],
          this.periode[1]
        ).then((data) => {
          this.dataY = d3.sort<DataPoint>(
            data,
            d => d.date
          )

          this.loading = false
          this.rebuildGraph()
        })
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
      this.appendCircle(chart, x, y)
    },
    appendXaxis(chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>): d3.ScaleLinear<number, number, never> {
      const x = d3.scaleLinear()
        .domain([0, this.maxValeurX])
        .range([0, this.width])

      const xAxis = chart.append("g")
        .attr("transform", `translate(0,${this.marginTop + this.height})`)
        .attr("class", "axe-x")
        .call(
          d3.axisBottom(x)
            .tickSize(-this.height)
            .ticks(8)
        )
        .call(g => g.append("text")
          .attr("x", this.width + 10)
          .attr("y", 25)
          .style('fill', this.axeColor)
          .attr("text-anchor", "end")
          .text(this.feedDataTypeX.unite)
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
        .domain([0, this.maxValeurY])
        .nice()
        .range([this.marginTop + this.height, this.marginTop])

      const yAxis = chart.append("g")
        .attr("class", "axe-y")
        .call(
          d3.axisLeft(y)
            .tickSize(-this.width)
            .ticks(8)
        )
        .call(g => g.append("text")
          .attr("x", -5)
          .attr("y", 5)
          .style('fill', this.axeColor)
          .attr("text-anchor", "end")
          .text(this.feedDataTypeY.unite)
        )

      yAxis.select(".domain")
        .style('stroke', this.axeColor)

      yAxis.selectAll(".tick text")
        .style('fill', this.axeColor)

      yAxis.selectAll(".tick line")
        .attr('stroke-width', '0.5px')
        .attr('stroke', this.gridColor)

      return y
    },
    appendCircle(
      chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>,
      x: d3.ScaleLinear<number, number, never>,
      y: d3.ScaleLinear<number, number, never>,
    ) {
      const dataX = d3.map(this.dataX, d => {return {axe: 'x', date: d.date, value: d.value}}) as {axe: 'x'|'y', date: Date, value: number}[]
      const dataY = d3.map(this.dataY, d => {return {axe: 'y', date: d.date, value: d.value}}) as {axe: 'x'|'y', date: Date, value: number}[]
      const data = d3.group([...dataX, ...dataY], d => d.date, d => d.axe) as d3.InternMap<Date, d3.InternMap<'x'|'y', {axe: 'x'|'y',date: Date,value: number}[]>>

      chart.append("g")
        .attr('class', 'point')
        .selectAll('circle')
        .data(data)
        .join('circle')
        .attr('fill', this.feedDataTypeX.color)
        .attr('stroke-width', 0)
        .attr('stroke', 'white')
        .attr('cx', ([date, d]) => {
          const dx = d.get('x')
          return x(dx ? dx[0].value : 0)
        })
        .attr('cy', ([date, d]) => {
          const dy = d.get('y')
          return y(dy ? dy[0].value : 0)
        })
        .attr('r', 0)
        .each(([date, d], i, element) => {
          const dx = d.get('x')
          const dy = d.get('y')
          tippy(element[i] as SVGRectElement, {
            content: formatWithGranularite(this.granularite, date) +
              '</br> ' +
              (dx ? dx[0].value : 0).toFixed(this.feedDataTypeX.precision) + ' ' + this.feedDataTypeX.unite +
              '</br> ' +
              (dy ? dy[0].value : 0).toFixed(this.feedDataTypeY.precision) + ' ' + this.feedDataTypeY.unite ,
            allowHTML: true
          })
        })
        .on('mouseenter', (event, d) => d3.select(event.currentTarget).attr('r', this.barWidth))
        .on('mouseleave', (event, d) => d3.select(event.currentTarget).attr('r', this.barWidth/2))

      chart
        .selectAll('.point circle')
        .data(data)
        .transition()
        .duration(800)
        .delay((d, i) => i)
        .ease(d3.easeCubic)
        .attr('r', this.barWidth/2)
    },
  },
})
