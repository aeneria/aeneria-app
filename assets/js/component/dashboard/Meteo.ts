import { defineComponent } from 'vue';
import Card from 'primevue/card';
import Calendrier from '../graphique/Calendrier';
import Evolution from '../graphique/Evolution';
import SelectionForm from '../selection/SelectionForm';
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
    periode() { return this.$store.state.selection.periode },
    granularite() { return this.$store.state.selection.granularite },
    meteoList(): any {
      return [
        {
          type: getFeedDataType(DataType.Temperature),
          feedDataId: this.$store.getters.selectedTemperatureFeedDataId,
        },
        {
          type: getFeedDataType(DataType.Nebulosity),
          feedDataId: this.$store.getters.selectedNebulosityFeedDataId,
        },
        {
          type: getFeedDataType(DataType.Rain),
          feedDataId: this.$store.getters.selectedRainFeedDataId,
        },
        {
          type: getFeedDataType(DataType.Humidity),
          feedDataId: this.$store.getters.selectedHumidityFeedDataId,
        },
      ]
    }
  },
});
