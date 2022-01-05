import { defineComponent } from 'vue';
import Card from 'primevue/card';
import Calendrier from '../graphique/Calendrier';
import Evolution from '../graphique/Evolution';
import SelectionForm from '../selection/SelectionForm';
import { mapGetters, mapState } from 'vuex';
import { DataType, getFeedDataType } from '@/type/FeedData';

export default defineComponent({
  name: 'DashboardMeteo',
  components: {
    Card,
    Calendrier,
    Evolution,
    SelectionForm,
  },
  computed: {
    ...mapState({
      periode: 'selectedPeriode',
      granularite: 'selectedGranularite',
    }),
    ...mapGetters({
      temperatureFeedDataId: 'selectedTemperatureFeedDataId',
      djuFeedDataId: 'selectedDjuFeedDataId',
      nebulosityFeedDataId: 'selectedNebulosityFeedDataId',
      rainFeedDataId: 'selectedRainFeedDataId',
      humidityFeedDataId: 'selectedHumidityFeedDataId',
    }),
    meteoList(): any {
      return [
        {
          type: getFeedDataType(DataType.Temperature),
          feedDataId: this.temperatureFeedDataId,
        },
        {
          type: getFeedDataType(DataType.Nebulosity),
          feedDataId: this.nebulosityFeedDataId,
        },
        {
          type: getFeedDataType(DataType.Rain),
          feedDataId: this.rainFeedDataId,
        },
        {
          type: getFeedDataType(DataType.Humidity),
          feedDataId: this.humidityFeedDataId,
        },
      ]
    }
  },
});
