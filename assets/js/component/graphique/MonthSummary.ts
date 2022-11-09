import { DataType, FeedData, getFeedDataType, isFeedDataEnergie } from '@/type/FeedData';
import { defineComponent, PropType } from 'vue';
import { Frequence } from '@/type/Granularite';
import { mapGetters } from 'vuex';
import { querySomme } from '@/api/data';
import Button from 'primevue/button';
import Calendrier from './Calendrier';
import Index from '../graphique/Index';
import { Place } from '@/type/Place';

export default defineComponent({
  name: 'MonthSummary',
  components: {
    Button,
    Calendrier,
    Index,
  },
  props: {
    month: {
      type: Date,
      required: true,
    },
    place: {
      type: Object as PropType<Place>,
      required: true,
    },
  },
  data() {
    const monthEnd = new Date(this.month)
    monthEnd.setMonth(monthEnd.getMonth() + 1)
    monthEnd.setDate(- 1)

    return {
      indexDju: '-' as number|string,
      indexEnergy: {},
      monthStart: this.month,
      monthEnd: monthEnd
    }
  },
  computed: {
    ...mapGetters({
      djuFeedDataId: 'selectedDjuFeedDataId',
      temperatureFeedDataId: 'selectedTemperatureFeedDataId',
    }),
    monthId(): string {
      return `${this.month.getFullYear()}-${this.month.getMonth()}`
    },
    feedDataEnergieList(): FeedData[] {
      let ret = new Array<FeedData>()

      if (this.place.feedList) {
        for(const feed of this.place.feedList) {
          for(const feedData of feed.feedDataList)
          if (isFeedDataEnergie(feedData)) {
            ret.push(feedData)
          }
        }
      }

      return ret
    },
    onlyOneEnergie(): boolean {
      return this.feedDataEnergieList?.length <= 1
    },
    columns(): any {
      const columns = [] as any[]

      for (const feedData of this.feedDataEnergieList) {
        const feedDataType = getFeedDataType(feedData.type)
        columns.push({
          type: feedDataType,
          feedDataId: feedData.id,
          index: {
            type: feedDataType,
            texte: 'consommé sur la période',
            valeur: this.indexEnergy[feedData.id] ?? '-',
          }
        })
      }

      columns.push({
        type: getFeedDataType(DataType.Temperature),
        feedDataId: this.temperatureFeedDataId,
        index: {
          type: getFeedDataType(DataType.Dju),
          unite: 'DJU',
          texte: '(degrés jour unifié)',
          valeur: this.indexDju,
        }
      })

      return columns
    }
  },
  mounted() {
    for (const feedData of this.feedDataEnergieList) {
      querySomme(
        feedData.id,
        Frequence.Day,
        this.monthStart,
        this.monthEnd
      )
      .then (data => {
        this.indexEnergy[feedData.id] = data
      })
    }
    querySomme(
      this.djuFeedDataId,
      Frequence.Day,
      this.monthStart,
      this.monthEnd
    )
    .then (data => {
      this.indexDju = data
    })
  }
});
