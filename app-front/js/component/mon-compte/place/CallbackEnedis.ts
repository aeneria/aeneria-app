import { defineComponent } from 'vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import { findFeedByType, Place } from '@/type/Place';
import AddGazparForm from './feed/feedGrdf/form/AddGazparForm';
import Spinner from '../../graphique/Spinner';
import { mapState } from 'vuex';
import { Feed as FeedObject, FeedType } from '@/type/Feed';
import { queryPlaces } from '@/api/configuration';
import { RouterLink } from 'vue-router';

export default defineComponent({
  name: 'CallbackEnedis',
  components: {
    Button,
    Card,
    AddGazparForm,
    Spinner,
    RouterLink,
  },
  data() {
    return {
      placeId: this.$route.params.placeId as string,
      displayAddGazparForm: false,
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
    toggleGazparForm() {
      this.displayAddGazparForm = !this.displayAddGazparForm
    },
  }
});
