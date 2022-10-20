import { adaptToGranularite, formatWithGranularite } from './d3-helpers';
import { DataPoint } from '@/type/DataValue';
import { defineComponent, PropType } from 'vue';
import { FeedDataType, getFeedDataTypeColor } from '@/type/FeedData';
import { Granularite, GranulariteType } from '@/type/Granularite';
import { queryDataPoint } from '@/api/data';
import * as d3 from 'd3';
import Erreur from './Erreur';
import Spinner from './Spinner';
import tippy from 'tippy.js';

export default defineComponent({
  name: 'DoubleEvolution',
  components: {
    Erreur,
    Spinner,
  },
  props: {
    id: {
      type: String,
      required: true,
    },
    periode1: {
      type: Array as unknown as PropType<[Date, Date]>,
      required: true,
    },
    periode2: {
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
  },
  data() {
    return {
      data1: new Array<DataPoint>(),
      data2: new Array<DataPoint>(),

      barWidth: 4,
      axeColor: '#6d6d6d',
      gridColor: '#dddddd',
      marginTop: 20,
      marginBottom: 25,
      marginLeft: 10,
      marginRight: 10,
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
    height(): number {
      switch (this.granularite.type) {
        case GranulariteType.Jour:
          return (d3.max([
            d3.utcDay.count(this.periode1[0], this.periode1[1]),
            d3.utcDay.count(this.periode2[0], this.periode2[1]),

          ]) ?? 0) * 4
        case GranulariteType.Semaine:
          return (d3.max([
            d3.utcWeek.count(this.periode1[0], this.periode1[1]),
            d3.utcWeek.count(this.periode2[0], this.periode2[1]),

          ]) ?? 0) * 12
        case GranulariteType.Mois:
          return (d3.max([
            d3.utcMonth.count(this.periode1[0], this.periode1[1]),
            d3.utcMonth.count(this.periode2[0], this.periode2[1]),

          ]) ?? 0) * 50
        case GranulariteType.Annee:
          return (d3.max([
            d3.utcYear.count(this.periode1[0], this.periode1[1]),
            d3.utcYear.count(this.periode2[0], this.periode2[1]),

          ]) ?? 0) * 100
      }
    },
    width(): number {
      return this.totalWidth - this.marginLeft - this.marginRight
    },
    maxValeur1(): number {
      return d3.max(d3.map(this.data1, d => d.value)) ?? 0
    },
    maxValeur2(): number {
      return d3.max(d3.map(this.data2, d => d.value)) ?? 0
    },
    nbDate(): number {
      return d3.max([this.nbDatePeriode1, this.nbDatePeriode2]) ?? 0
    },
    nbDatePeriode1(): number {
      return this.nbDateFor(this.periode1)
    },
    nbDatePeriode2(): number {
      return this.nbDateFor(this.periode2)
    },
    resizedPeriode1(): [Date, Date] {
      if (this.nbDate == this.nbDatePeriode1) {
        return this.periode1
      }

      return [
        this.periode1[0],
        this.adaptEndDate(this.periode1[0])
      ]
    },
    resizedPeriode2(): [Date, Date] {
      if (this.nbDate == this.nbDatePeriode2) {
        return this.periode2
      }

      return [
        this.periode2[0],
        this.adaptEndDate(this.periode2[0])
      ]
    },
  },
  mounted() {
    this.refresh()
  },
  watch: {
    periode1() {
      this.refresh()
    },
    periode2() {
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
        this.periode1[0],
        this.periode1[1]
      ).then((data) => {
        this.data1 = d3.sort<DataPoint>(
          data,
          d => d.date
        )
      }).then(() => {
        queryDataPoint(
          this.feedDataId,
          this.granularite.frequence,
          this.periode2[0],
          this.periode2[1]
        ).then((data) => {
          this.data2 = d3.sort<DataPoint>(
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
        .attr('transform','translate(0,' + this.marginTop + ')')

      const x1 = this.appendXaxis(chart, 1)
      const x2 = this.appendXaxis(chart, 2)
      const y1 = this.appendYaxis(chart, 1)
      const y2 = this.appendYaxis(chart, 2)
      this.appendArea(chart, x1, y1, 1)
      this.appendArea(chart, x2, y2, 2)
      this.appendCourbe(chart, x1, y1, 1)
      this.appendCourbe(chart, x2, y2, 2)
      this.appendBar(chart, x1, y1, 1)
      this.appendBar(chart, x2, y2, 2)
      this.appendEventArea(chart, x1, y1, 1)
      this.appendEventArea(chart, x2, y2, 2)
      this.appendCircle(chart, x1, y1, 1)
      this.appendCircle(chart, x2, y2, 2)
    },
    appendXaxis(
      chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>,
      dataNb: 1|2,
    ): d3.ScaleTime<number, number, never> {
      const x = d3.scaleTime()
        .domain([
          adaptToGranularite(this.granularite, dataNb == 1 ? this.resizedPeriode1[0] : this.resizedPeriode2[0]),
          adaptToGranularite(this.granularite, dataNb == 1 ? this.resizedPeriode1[1] : this.resizedPeriode2[1]),
        ])
        .range([0, this.height])

      const xAxis = chart.append("g")
        .attr('transform', 'translate(' + this.width/2 + ',0)')
        .attr("class", "axe-x")
        .call(g => g.append("text")
          .attr("x", -30)
          .attr("text-anchor", "end")
          .attr("y", this.marginTop + this.height + 5)
          .text(this.feedDataType.unite)
          .style('font-size', '1em')
          .style('fill', this.axeColor)
        )
        .call(g => g.append("text")
          .attr("x", 30)
          .attr("y", this.marginTop + this.height + 5)
          .text(this.feedDataType.unite)
          .style('font-size', '1em')
          .style('fill', this.axeColor)
        )

      xAxis.selectAll(".tick text").remove()

      xAxis.select(".domain")
        .style('stroke', this.axeColor)

      xAxis.selectAll(".tick line")
        .attr('stroke-width', '0.5px')
        .attr('transform', 'translate(3,0)')
        .attr('stroke', this.gridColor)

      return x
    },
    appendYaxis(
      chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>,
      dataNb: 1|2,
    ): d3.ScaleLinear<number, number, never> {
      const y = d3.scaleLinear()
        .domain(dataNb === 1 ? [0, this.maxValeur1] : [0, this.maxValeur2])
        .nice()
        .range(dataNb === 1 ? [this.width/2, 0] : [this.width/2, this.width])

      const yAxis = chart.append("g")
        .attr("class", "axe-y-" + dataNb)
        .call(
          d3.axisBottom(y)
            .tickSize(this.height)
            .tickFormat(null)
            .ticks(5)
        )

      yAxis.selectAll(".tick text").remove()

      yAxis.select(".domain")
      .style('stroke', this.axeColor)

      yAxis.selectAll(".tick line")
        .attr('stroke-width', '0.5px')
        .attr('stroke', this.gridColor)

      return y
    },
    appendCircle(
      chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>,
      x: d3.ScaleTime<number, number, never>,
      y: d3.ScaleLinear<number, number, never>,
      dataNb: 1|2,
    ) {
      chart.append("g")
        .attr('class', 'circle-' + dataNb)
        .selectAll('circle')
        .data(this['data' + dataNb] as DataPoint[])
        .join('circle')
        .attr('fill', this.feedDataTypeXColor(dataNb))
        .attr('stroke-width', 1)
        .attr('stroke', 'white')
        .attr('cy', d => x(d.date))
        .attr('cx', this.width/2)
        .attr('r', this.barWidth/2)
        .attr('display', 'none')
        .each((d, i, element) => {
          tippy(element[i] as SVGRectElement, {
            content: formatWithGranularite(this.granularite, d.date) + '</br> ' + d.value.toFixed(this.feedDataType.precision) + ' ' + this.feedDataType.unite,
            allowHTML: true,
            triggerTarget: Array.from(chart.node()?.querySelectorAll<Element>('[data-point-id="' + d.date.toISOString() + '"]') ?? []),
            placement: dataNb ==1 ? 'top' : 'bottom',
          })
        })

      chart
        .selectAll('.circle-' + dataNb + ' circle')
        .data(this['data' + dataNb] as DataPoint[])
        .transition()
        .duration(800)
        .ease(d3.easeCubic)
        .attr('cx', (d, i) => y(d.value))
    },
    appendBar(
      chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>,
      x: d3.ScaleTime<number, number, never>,
      y: d3.ScaleLinear<number, number, never>,
      dataNb: 1|2,
    ) {
      chart.append("g")
        .attr('class', 'bar-' + dataNb)
        .selectAll('text')
        .data(this['data' + dataNb] as DataPoint[])
        .join('rect')
        .attr('fill', 'white' )
        .attr('fill-opacity', '0.4')
        .attr('stroke-width', 0)
        .attr('data-point-id', d => d.date.toISOString())
        .attr('y', d => x(d.date) - this.barWidth / 2)
        .attr('height', this.barWidth)
        .attr('x', this.width/2)
        .attr('width', 0)
        .attr('display', 'none')

      chart
        .selectAll('.bar-' + dataNb + ' rect')
        .data(this['data' + dataNb] as DataPoint[])
        .transition()
        .duration(800)
        .ease(d3.easeCubic)
        .attr('x', (d, i) => dataNb == 1 ? y(d.value) : this.width/2)
        .attr('width', (d, i) => dataNb == 1 ? y(d.value) + this.width/2 : this.width/2 - y(d.value))
    },
    appendCourbe(
      chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>,
      x: d3.ScaleTime<number, number, never>,
      y: d3.ScaleLinear<number, number, never>,
      dataNb: 1|2,
    ) {
      chart.append("g")
        .append("path")
        .attr('stroke', this.feedDataTypeXColor(dataNb))
        .attr('stroke-width', this.barWidth)
        .attr('stroke-linecap', 'round')
        .attr('fill', 'transparent')
        .attr('class', 'courbe-' + dataNb)
        .datum(this['data' + dataNb])
        .attr("d",
          d3.line<DataPoint>()
            .curve(d3.curveMonotoneX)
            .y(d => x(d.date))
            .x(this.width/2)
        )

      chart
        .select('.courbe-' + dataNb)
        .datum(this['data' + dataNb])
        .transition()
        .duration(800)
        .ease(d3.easeCubic)
        .attr('d',
          d3.line<DataPoint>()
            .curve(d3.curveMonotoneY)
            .y(d => x(d.date))
            .x(d => y(d.value))
        )
    },
    appendArea(
      chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>,
      x: d3.ScaleTime<number, number, never>,
      y: d3.ScaleLinear<number, number, never>,
      dataNb: 1|2,
    ) {
      chart.append("g")
        .append("path")
        .attr('stroke-width', 0)
        .attr('stroke-linecap', 'round')
        .attr('fill', this.feedDataTypeXColor(dataNb))
        .attr('fill-opacity', '0.3')
        .attr('class', 'area-' + dataNb)
        .datum(this['data' + dataNb])
        .attr("d",
          d3.area<DataPoint>()
            .curve(d3.curveMonotoneY)
            .y(d => x(d.date))
            .x0(this.width/2)
            .x1(this.width/2)
        )

      chart
        .select('.area-' + dataNb)
        .datum(this['data' + dataNb])
        .transition()
        .duration(800)
        .ease(d3.easeCubic)
        .attr('d',
          d3.area<DataPoint>()
            .curve(d3.curveMonotoneY)
            .y(d => x(d.date))
            .x0(this.width/2)
            .x1(d => y(d.value))
        )
    },
    appendEventArea(
      chart: d3.Selection<SVGGElement, unknown, HTMLElement, any>,
      x: d3.ScaleTime<number, number, never>,
      y: d3.ScaleLinear<number, number, never>,
      dataNb: 1|2,
    ) {
      chart.append("g")
        .attr('class', 'area-event-' + dataNb)
        .selectAll('path')
        .data(this['data' + dataNb] as DataPoint[])
        .join("path")
        .attr('stroke-width', 0)
        .attr('stroke-linecap', 'round')
        .attr('fill', 'transparent')
        .attr('data-point-id', d => d.date.toISOString())
        .datum((d, i) => {
          const ret = [] as DataPoint[]
          if (i > 0) {
            ret.push({
              id: d.date.toISOString(),
              date: new Date((this['data' + dataNb][i-1].date.getTime() + d.date.getTime()) / 2),
              value: (this['data' + dataNb][i-1].value + d.value) / 2
            })
          }
          d.id = d.date.toISOString()
          ret.push(d)
          if (i < (this['data' + dataNb].length - 1)) {
            ret.push({
              id: d.date.toISOString(),
              date: new Date((this['data' + dataNb][i+1].date.getTime() + d.date.getTime()) / 2),
              value: (this['data' + dataNb][i+1].value + d.value) / 2
            })
          }

          return ret
        })
        .attr("d",
          d3.area<DataPoint>()
            .curve(d3.curveMonotoneY)
            .y(d => x(d.date))
            .x0(this.width/2)
            .x1(this.width/2)
        )
        .on('mouseenter', (event, d) => {
          chart
            .selectAll('circle')
            .attr('display', '')
          chart
            .selectAll('rect[data-point-id="' + d[0].id + '"]')
            .attr('display', '')
        })
        .on('mouseleave', (event, d) => {
          chart
            .selectAll('circle')
            .attr('display', 'none')
          chart
            .selectAll('rect[data-point-id="' + d[0].id + '"]')
            .attr('display', 'none')
        })

      chart
        .selectAll('.area-event-' + dataNb + ' path')
        .data(this['data' + dataNb] as DataPoint[])
        .datum((d, i) => {
          const ret = [] as DataPoint[]
          if (i > 0) {
            ret.push({
              id: d.id,
              date: new Date((this['data' + dataNb][i-1].date.getTime() + d.date.getTime()) / 2),
              value: (this['data' + dataNb][i-1].value + d.value) / 2
            })
          }
          ret.push(d)
          if (i < (this['data' + dataNb].length - 1)) {
            ret.push({
              id: d.id,
              date: new Date((this['data' + dataNb][i+1].date.getTime() + d.date.getTime()) / 2),
              value: (this['data' + dataNb][i+1].value + d.value) / 2
            })
          }

          return ret
        })
        .transition()
        .duration(800)
        .ease(d3.easeCubic)
        .attr('d',
          d3.area<DataPoint>()
            .curve(d3.curveMonotoneY)
            .y(d => x(d.date))
            .x0(this.width/2)
            .x1(d => y(d.value))
        )
    },
    nbDateFor(periode: [Date, Date]): number {
      switch (this.granularite.type) {
        case GranulariteType.Jour:
          return d3.utcDay.count(periode[0], periode[1])
        case GranulariteType.Semaine:
          return d3.utcWeek.count(periode[0], periode[1])
        case GranulariteType.Mois:
          return d3.utcMonth.count(periode[0], periode[1])
        case GranulariteType.Annee:
          return d3.utcYear.count(periode[0], periode[1])
      }
    },
    adaptEndDate(date: Date): Date {
      const ret = new Date(date)

      switch (this.granularite.type) {
        case GranulariteType.Jour:
          ret.setDate(ret.getDate() + this.nbDate)
          break
        case GranulariteType.Semaine:
          ret.setDate(ret.getDate() + (this.nbDate*7))
          break
        case GranulariteType.Mois:
          ret.setMonth(ret.getMonth() + this.nbDate)
          break
        case GranulariteType.Annee:
          ret.setFullYear(ret.getFullYear() + this.nbDate)
          break
      }

      return ret
    },
    feedDataTypeXColor(color: 1|2): string {
      return getFeedDataTypeColor(this.feedDataType, color)
    },
  },
})
