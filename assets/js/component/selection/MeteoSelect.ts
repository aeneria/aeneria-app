import { defineComponent } from 'vue';
import Dropdown, { DropdownChangeEvent } from 'primevue/dropdown';
import {  mapState } from 'vuex';
import { SET_SELECTED_METEO_DATA } from '@/store/mutations';
import { DataType, getFeedDataType } from '@/type/FeedData';

export default defineComponent({
  name: 'MeteoSelect',
  components: {
    Dropdown
  },
  data() {
    return {
      feedDataTypeMeteoList: [
        getFeedDataType(DataType.Dju),
        getFeedDataType(DataType.Temperature),
        getFeedDataType(DataType.Pressure),
        getFeedDataType(DataType.Humidity),
        getFeedDataType(DataType.Nebulosity),
        getFeedDataType(DataType.Rain),
      ]
    }
  },
  computed: {
    ...mapState([
      'selectedMeteoData',
    ]),
  },
  methods: {
    setSelectedMeteoData (event: DropdownChangeEvent) {
      this.$store.commit(SET_SELECTED_METEO_DATA, event.value)
    }
  }
});
