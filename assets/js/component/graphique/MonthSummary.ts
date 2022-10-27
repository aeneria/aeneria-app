import { DataType, getFeedDataType } from '@/type/FeedData';
import { defineComponent } from 'vue';
import { Frequence } from '@/type/Granularite';
import { mapGetters } from 'vuex';
import { querySomme } from '@/api/data';
import Button from 'primevue/button';
import Calendrier from './Calendrier';
import Index from '../graphique/Index';

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
  },
  data() {
    return {
      indexDju: '-' as number|string,
      indexEnergy: {},
    }
  },
  computed: {
    ...mapGetters({
      onlyOneEnergie: 'onlyOneEnergie',
      djuFeedDataId: 'selectedDjuFeedDataId',
      temperatureFeedDataId: 'selectedTemperatureFeedDataId',
    }),
    monthId(): string {
      return `${this.month.getFullYear()}-${this.month.getMonth()}`
    },
    monthStart(): Date {
      return this.month
    },
    monthEnd(): Date {
      const monthEnd = new Date(this.month)
      monthEnd.setMonth(monthEnd.getMonth() + 1)
      monthEnd.setDate(- 1)
      return monthEnd
    },
    columns(): any {
      const columns = [] as any[]

      for (const feedData of this.$store.getters.feedDataEnergieList) {
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
    for (const feedData of this.$store.getters.feedDataEnergieList) {
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
