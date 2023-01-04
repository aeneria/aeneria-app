import { defineComponent, PropType } from 'vue';
import { FeedDataType } from '@/type/FeedData';
import * as d3 from 'd3';
import tippy from 'tippy.js';

export default defineComponent({
  name: 'Legende',
  props: {
    id: {
      type: String,
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
    feedDataType: {
      type: Object as PropType<FeedDataType>,
      required: true,
    },
    range: {
      type: Function as unknown as PropType<d3.ScaleQuantile<number, never>|null>,
      required: true,
    },
    maxValeur: {
      type: Number,
      required: true,
    },
    moyValeur: {
      type: Number,
      required: true,
    },
    minValeur: {
      type: Number,
      required: true,
    },
  },
  data() {
    return {
      daySize: 14,
      axeColor: '#6d6d6d',
    }
  },
  mounted() {
    this.rebuild()
  },
  watch: {
    maxValeur() {
      this.rebuild()
    },
    moyValeur() {
      this.rebuild()
    },
    minValeur() {
      this.rebuild()
    },
    min() {
      this.rebuild()
    },
    max() {
      this.rebuild()
    },
    range() {
      this.rebuild()
    }
  },
  methods: {
    rebuild() {

      if (!this.feedDataType || !this.range) {
        return
      }

      const nbRect = this.feedDataType.colors.length
      const totalHeight = nbRect * this.daySize + 20
      const totalWidth = 100
      const marginLeft = 33

      const element = d3.select('#' + this.id)

      element
        .selectAll('svg')
        .remove();

      element
        .selectAll('div')
        .remove();

      const svg = element
        .append('svg')
        .attr('class', 'chart')
        .attr('width', totalWidth)
        .attr('height', totalHeight);

      const chart = svg
        .append('g')
        .attr('class', 'chart')
        .attr('width', totalWidth)
        .attr('height', totalHeight);

      chart
        .selectAll('rect')
        .data(this.range.range())
        .enter()
        .append('rect')
        .attr('x', marginLeft)
        .attr('y', d => {
          return d * this.daySize;
        })
        .attr('rx', 10)
        .attr('width', this.daySize)
        .attr('height', this.daySize)
        .attr('fill', d => this.feedDataType.colors[nbRect - 1 - d])
        .each((d, i, element) => {
          let inf = 0
          let sup = 0
          const lastIndex = nbRect - 1

          switch (d) {
            case 0:
              sup = this.max ?? this.maxValeur
              inf = this.range?.quantiles()[lastIndex - 1] ?? 0
              break
            case lastIndex:
              sup = this.range?.quantiles()[0] ?? 0
              inf = this.min ?? this.minValeur
              break
            default:
              sup = this.range?.quantiles()[nbRect - 1 - d] ?? 0
              inf = this.range?.quantiles()[nbRect - 2 - d] ?? 0
              break
          }

          const html = 'de ' + inf.toFixed(this.feedDataType.precision) + this.feedDataType.unite +
            ' à ' + sup.toFixed(this.feedDataType.precision) + this.feedDataType.unite

          tippy(element[i] as SVGRectElement, {
            content: html,
            allowHTML: true,
            placement: 'right',
          })
        })

      let moyIndex = nbRect - 1 - this.range.range().indexOf(this.range(this.moyValeur))
      let minIndex = nbRect - 1 - this.range.range().indexOf(this.range(this.minValeur))
      let maxIndex = nbRect - 1 - this.range.range().indexOf(this.range(this.maxValeur))

      // Check overlapping.
      moyIndex = (moyIndex === maxIndex) ? moyIndex + 1 : moyIndex
      minIndex = (minIndex === moyIndex) ? minIndex + 1 : minIndex
      minIndex = (minIndex === maxIndex) ? minIndex + 2 : minIndex

      this.displayLegendTick(chart, marginLeft, minIndex, 'Min', this.minValeur.toFixed(this.feedDataType.precision) + this.feedDataType.unite)
      this.displayLegendTick(chart, marginLeft, moyIndex, 'Moy', this.moyValeur.toFixed(this.feedDataType.precision) + this.feedDataType.unite)
      this.displayLegendTick(chart, marginLeft, maxIndex, 'Max', this.maxValeur.toFixed(this.feedDataType.precision) + this.feedDataType.unite)
    },
    displayLegendTick(
      target: d3.Selection<SVGGElement, unknown, HTMLElement, any>,
      marginLeft: number,
      index: number,
      label: string,
      value: string
    ) {
      target.append("text")
        .style('fill', this.axeColor)
        .attr("x", marginLeft - 5)
        .attr("y", index * this.daySize + 11)
        .text(label)
        .style('text-anchor', 'end')

      target.append("text")
        .style('fill', this.axeColor)
        .attr("x", marginLeft + 20)
        .attr("y", index * this.daySize + 11)
        .text(value)
    },
  },
});
