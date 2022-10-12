import Dialog from 'primevue/dialog';
import Button from 'primevue/button';
import { defineComponent } from 'vue';
import { mapGetters, mapState } from 'vuex';
import { Place } from '@/type/Place';
import { SET_SELECTED_PLACE } from '@/store/mutations';

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
    place() { return this.$store.state.selection.place },
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
      this.$store.commit(SET_SELECTED_PLACE, place)
      this.displayDialog = false
    }
  }
});
