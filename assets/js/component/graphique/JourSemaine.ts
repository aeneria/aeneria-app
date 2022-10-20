import { DataRepartition, weekDayList } from '@/type/DataValue';
import { defineComponent, PropType } from 'vue';
import { FeedDataType } from '@/type/FeedData';
import { Frequence, RepartitionColonne } from '@/type/Granularite';
import { queryRepartition } from '@/api/data';
import { weekDayFormat } from './d3-helpers';
import * as d3 from 'd3';
import Erreur from './Erreur';
import Legende from './Legende';
import Spinner from './Spinner';
import tippy from 'tippy.js';

export default defineComponent({
  name: 'JourSemaine',
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
  } ,
  data() {
    return {
      data: new Array<DataRepartition>(),

      barWidth: 6,
      barWithMarge: 18,
      axeColor: '#6d6d6d',
      gridColor: '#dddddd',
      marginTop: 35,
      marginLeft: 35,
      marginRight: 20,
      marginBottom: 5,
      width: 150,
      height: 150,

      loading: true,
      error: false,
    }
  },
  computed: {
    totalHeight(): number {
      return this.marginTop + this.height + this.marginBottom
    },
    totalWidth(): number {
      return this.marginLeft + this.width + this.marginRight
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
      queryRepartition(
        this.feedDataId,
        Frequence.Day,
        RepartitionColonne.WeekDay,
        this.periode[0],
        this.periode[1]
      ).then((data) => {
        this.data = d3.sort<DataRepartition>(
          data,
          d => d.groupBy
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

      const yScale = d3
        .scaleLinear()
        .range([this.width, 0])
        .domain([d3.max(d3.map(this.data, d => d.value)) ?? 0, 0])

      const chart = svg
        .append('g')
        .attr('transform','translate(0,' + this.marginTop + ')')

      const yGrid = chart.append('g')
        .attr('transform','translate(' + this.marginLeft + ',0)')
        .call(
          d3.axisTop(yScale)
            .tickSize(-this.height + this.marginTop)
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

      this.appendDayLabel(chart)


      chart.append('g')
        .attr('transform','translate(' + this.marginLeft + ',0)')
        .attr('class', 'bar')
        .selectAll('rect')
        .data(this.data)
        .join('rect')
        .attr('fill', this.feedDataType.color)
        .attr('stroke-width', 0)
        .attr("x", d => 0)
        .attr("y", d => this.barWithMarge + d.groupBy * (this.barWithMarge) - this.barWithMarge /2)
        .attr('rx', this.barWidth/2)
        .attr('width', 0)
        .attr('height', this.barWidth)
        .each((d, i, element) => {
          tippy(element[i] as SVGRectElement, {
            content: 'Le ' + weekDayList[d.groupBy].toLowerCase() + '</br>en moyenne, ' + d.value.toFixed(this.feedDataType.precision) + ' ' + this.feedDataType.unite,
            allowHTML: true,
            placement: 'right',
          })
        })

      chart
        .selectAll('.bar rect')
        .data(this.data)
        .transition()
        .duration(600)
        .delay(function (d, i) { return i * 20; })
        .ease(d3.easeCubic)
        .attr('width', d => yScale(d.value))
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
            return 'translate(30,' + (i * this.barWithMarge + 16) + ')'
          })
          .style('text-anchor', 'end')
    },
  },
})
