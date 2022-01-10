import { UPDATE_SELECTED_PLACE } from '@/store/actions';
import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { defineComponent } from 'vue';
import { mapGetters, mapState } from 'vuex';
import { Place } from '@/type/Place';

export default defineComponent({
  name: 'PlaceSelect',
  components: {
    Dialog,
    Button,
  },
  data() {
    return {
      displayDialog: false,
    }
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
    openDialog() {
      this.displayDialog = true;
    },
    setSelectedPlace (place: Place) {
      this.$store.dispatch(UPDATE_SELECTED_PLACE, place)
      this.displayDialog = false
    }
  }
});
