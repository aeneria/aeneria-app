import { defineComponent } from 'vue';
import Button from 'primevue/button';
import { findFeedByType, Place } from '@/type/Place';
import AddLinkyForm from './form/AddLinkyForm';
import Spinner from '../../graphique/Spinner';
import { mapState } from 'vuex';
import { Feed as FeedObject, FeedType } from '@/type/Feed';
import { queryPlaces } from '@/api/configuration';
import { RouterLink } from 'vue-router';

export default defineComponent({
  name: 'CallbackGrdf',
  components: {
    Button,
    AddLinkyForm,
    Spinner,
    RouterLink,
  },
  data() {
    return {
      placeId: this.$route.params.placeId as string,
      displayAddLinkyForm: false,
    }
  },
  mounted() {
    queryPlaces()
  },
  computed: {
    ...mapState([
      'placeList',
    ]),
    place(): null|Place {
      if (this.placeList) {
        for(const place of this.placeList) {
          if (place.id == this.placeId) {
            return place
          }
        }
      }

      return null
    },
    feedGaz(): null|FeedObject {
      return this.place ? findFeedByType(this.place, FeedType.gaz) : null
    },
    feedElectricite(): null|FeedObject {
      return this.place ? findFeedByType(this.place, FeedType.electricity) : null
    },
  },
  methods: {
    toggleLinkyForm() {
      this.displayAddLinkyForm = !this.displayAddLinkyForm
    },
  }
});
