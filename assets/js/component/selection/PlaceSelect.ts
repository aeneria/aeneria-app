import { defineComponent } from 'vue';
import { mapGetters, mapState } from 'vuex';
import { Place } from '@/type/Place';
import { SET_SELECTED_PLACE } from '@/store/mutations';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';

export default defineComponent({
  name: 'PlaceSelect',
  components: {
    Button,
    Dialog,
    Dropdown,
  },
  props: {
    isMobile: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      displayDialog: false,
    }
  },
  computed: {
    place(): Place|null { return this.$store.state.selection.place },
    ...mapState({
      placeList: 'placeList',
    }),
    ...mapGetters([
      'onlyOnePlace'
    ]),
  },
  methods: {
    openDialog() {
      this.displayDialog = true;
    },
    setSelectedPlace (place: Place) {
      this.place = place
      this.$store.commit(SET_SELECTED_PLACE, place)
      this.displayDialog = false
    }
  }
});
