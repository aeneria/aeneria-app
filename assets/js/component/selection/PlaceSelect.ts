import { UPDATE_SELECTED_PLACE } from '@/store/actions';
import Dropdown, { DropdownChangeEvent } from 'primevue/dropdown';
import { defineComponent } from 'vue';
import { mapGetters, mapState } from 'vuex';

export default defineComponent({
  name: 'PlaceSelect',
  components: {
    Dropdown
  },
  computed: {
    ...mapState([
      'placeList',
      'selectedPlace',
    ]),
    ...mapGetters([
      'onlyOnePlace'
    ]),
  },
  methods: {
    setSelectedPlace (event: DropdownChangeEvent) {
      this.$store.dispatch(UPDATE_SELECTED_PLACE, event.value)
    }
  }
});
