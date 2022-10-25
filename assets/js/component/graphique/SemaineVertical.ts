import { DataDoubleRepartition, shortWeekDayList, weekDayList } from '@/type/DataValue';
import { defineComponent, PropType } from 'vue';
import { FeedDataType } from '@/type/FeedData';
import { Frequence, RepartitionColonne } from '@/type/Granularite';
import { queryDoubleRepartition } from '@/api/data';
import * as d3 from 'd3';
import Erreur from './Erreur';
import Legende from './Legende';
import Spinner from './Spinner';
import tippy from 'tippy.js';

export default defineComponent({
  name: 'SemaineVertical',
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
      daySize: 17,
      axeColor: '#6d6d6d',

      maxValeur: 0,
      moyValeur: 0,
      minValeur: 0,
      range: d3.scaleQuantile(),

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
      )
      .then((data) => {
        const sortedData = d3.sort<DataDoubleRepartition>(
          d3.sort(
            data,
            d => d.axeX
          ),
          d => d.axeY
        )
        const values = d3.map(sortedData, d => d.value)

        this.maxValeur = d3.max(values) ?? 0
        this.moyValeur = d3.mean(values)?? 0
        this.minValeur = d3.min(values) ?? 0

        this.range = d3
          .scaleQuantile()
          .domain([
              this.min ?? d3.min(data, (data) => data.value),
              this.max ?? d3.max(data, (data) => data.value),
            ])
          .range([...Array(this.feedDataType.colors.length).keys()])

        this.loading = false
        this.rebuildGraph(sortedData)
      }).catch(error => {
        this.error = true
        this.loading = false
        console.log(error)
      })
    },
    rebuildGraph(data: Array<DataDoubleRepartition>) {
      const rows = 24
      const cols = 7
      const marginTop = 35
      const marginLeft = 35
      const marginBottom = 30
      const totalHeight = marginTop + rows * this.daySize + marginBottom
      const totalWidth = marginLeft + cols * this.daySize
      const weekDayFormat = (i: number) => shortWeekDayList[(i + 1)%7]
      const hourFormat = (i: number) => ('00' + i % 24).slice(-2) + 'h'

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
          .attr('width', totalWidth)
          .attr('height', totalHeight)

      const chart = svg
        .append('g')
          .attr('class', 'chart')
          .attr('width', totalWidth)
          .attr('height', totalHeight)

      chart.append('g')
      .attr('class', 'dayLabel')
        .selectAll('text')
        .data(d3.range(7))
        .join('text')
        .text(weekDayFormat)
          .style('fill', this.axeColor)
          .attr('transform', (d, i) => {
            return 'rotate(-90)translate(-30,' + (i * this.daySize + marginLeft + 12) + ')'
          })

      chart.append('g')
         .attr('class', 'timeLabel')
        .selectAll('text')
        .data(d3.range(24))
        .join('text')
        .text((d, i) => i % 3 === 0 ? hourFormat(d) : null)
          .style('text-anchor', 'left')
          .style('fill', this.axeColor)
          .attr('transform', (d, i) => {
            return 'translate(10,' + (i * this.daySize + marginTop + 12) + ')'
          })
          .attr('font-family', 'sans-serif')
          .attr('font-size', 10)

      chart.append('g')
          .attr('class', 'no-data')
        .selectAll('rect')
        .data(d3.utcHours(new Date('2021-12-27 00:00'), new Date('2022-01-02 24:00')))
        .join('rect')
        .attr("x", d => d.getUTCDay() * this.daySize + marginLeft)
        .attr("y", d => d.getUTCHours() * this.daySize + marginTop)
          .attr('rx', 10)
          .attr('width', this.daySize)
          .attr('height', this.daySize)
          .attr('fill', '#eaebec')

      chart.append('g')
          .attr('class', 'data')
        .selectAll('rect')
        .data(data)
        .join('rect')
          .attr("x", d => d.axeX * this.daySize + 0.5 * this.daySize + marginLeft)
          .attr("y", d => d.axeY * this.daySize + 0.5 * this.daySize + marginTop)
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
        .data(data)
        .transition()
        .duration(300)
        .delay((d, i) => i * 5)
        .ease(d3.easeCubic)
        .attr("x", d => d.axeX * this.daySize + marginLeft)
        .attr("y", d => d.axeY * this.daySize + marginTop)
          .attr('width', this.daySize - 1)
          .attr('height', this.daySize - 1)
    },
  },
})
