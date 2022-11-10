import { DataType, getFeedDataType } from '@/type/FeedData';
import { defineComponent } from 'vue';
import { Frequence, Granularite } from '@/type/Granularite';
import { queryNombreInferieur, querySomme } from '@/api/data';
import { utcDay } from 'd3';
import Calendrier from '../graphique/Calendrier';
import Card from 'primevue/card';
import Evolution from '../graphique/Evolution';
import Index from '../graphique/Index';
import SelectionForm from '../selection/SelectionForm';

export default defineComponent({
  name: 'DashboardMeteo',
  components: {
    Card,
    Calendrier,
    Evolution,
    Index,
    SelectionForm,
  },
  data() {
    return {
      indexDju: 0,
      indexNebulosite: 0,
      indexPrecipitations: 0,
      indexHumidite: 0,
    }
  },
  computed: {
    periode(): [Date|null, Date|null] {
      return this.$store.state.selection.periode
    },
    granularite(): null|Granularite {
      return this.$store.state.selection.granularite
    },
    nbJours(): number {
      return utcDay.count(this.periode[0] ?? new Date(), this.periode[1] ?? new Date())
    },
    meteoList(): any {
      return [
        {
          type: getFeedDataType(DataType.Temperature),
          feedDataId: this.$store.getters.selectedTemperatureFeedDataId,
          index: {
            type: getFeedDataType(DataType.Dju),
            unite: 'DJU',
            texte: '(degrés jour unifié)',
            valeur: this.indexDju,
            total: 0,
          }
        },
        {
          type: getFeedDataType(DataType.Nebulosity),
          feedDataId: this.$store.getters.selectedNebulosityFeedDataId,
          index: {
            type: getFeedDataType(DataType.Nebulosity),
            unite: 'jours',
            texte: 'sans nuage',
            valeur: this.indexNebulosite,
            total: this.nbJours,
          }
        },
        {
          type: getFeedDataType(DataType.Rain),
          feedDataId: this.$store.getters.selectedRainFeedDataId,
          index: {
            type: getFeedDataType(DataType.Rain),
            unite: 'jours',
            texte: 'sans pluie',
            valeur: this.indexPrecipitations,
            total: this.nbJours,
          }
        },
        {
          type: getFeedDataType(DataType.Humidity),
          feedDataId: this.$store.getters.selectedHumidityFeedDataId,
          index: {
            type: getFeedDataType(DataType.Humidity),
            unite: 'jours',
            texte: 'à moins de 70%',
            valeur: this.indexHumidite,
            total: this.nbJours,
          }
        },
      ]
    },
  },
  mounted() {
    this.refreshIndexes()
  },
  watch: {
    periode() {
      this.refreshIndexes()
    },
  },
  methods: {
    refreshIndexes() {
      // DJU
      querySomme(
        this.$store.getters.selectedDjuFeedDataId,
        Frequence.Day,
        this.periode[0] ?? new Date(),
        this.periode[1] ?? new Date(),
      )
      .then((data) => {
        this.indexDju = data
      })
      // Nebulosite
      queryNombreInferieur(
        this.$store.getters.selectedNebulosityFeedDataId,
        15,
        Frequence.Day,
        this.periode[0] ?? new Date(),
        this.periode[1] ?? new Date(),
      )
      .then((data) => {
        this.indexNebulosite = data
      })
      // Precipitations
      queryNombreInferieur(
        this.$store.getters.selectedRainFeedDataId,
        0,
        Frequence.Day,
        this.periode[0] ?? new Date(),
        this.periode[1] ?? new Date(),
      )
      .then((data) => {
        this.indexPrecipitations = data
      })

      // Humidite
      queryNombreInferieur(
        this.$store.getters.selectedHumidityFeedDataId,
        70,
        Frequence.Day,
        this.periode[0] ?? new Date(),
        this.periode[1] ?? new Date(),
      )
      .then((data) => {
        this.indexHumidite = data
      })

    }
  },
});
