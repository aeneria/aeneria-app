import { defineComponent, PropType } from 'vue';
import Button from 'primevue/button';
import FeedEnedis from './feedEnedis/FeedEnedis';
import FeedGrdf from './feedGrdf/FeedGrdf';
import FeedMeteoFrance from './feedMeteoFrance/FeedMeteoFrance';
import { Feed, FeedType } from '@/type/Feed';
import { Place } from '@/type/Place';

export default defineComponent({
  name: 'Feed',
  components: {
    Button,
    FeedEnedis,
    FeedGrdf,
    FeedMeteoFrance,
  },
  props: {
    place: {
      type: Object as PropType<Place>,
      required: true,
    },
    feed: {
      type: Object as PropType<Feed>,
      required: true,
    },
  },
  data() {
    return {
      displayForm: false,
    }
  },
  methods: {
    toggleForm() {
      this.displayForm = !this.displayForm
    },
  },
  computed: {
    isMeteoFeed(): boolean {
      return this.feed.type == FeedType.meteo
    },
    isElectricityFeed(): boolean {
      return this.feed.type == FeedType.electricity
    },
    isGazFeed(): boolean {
      return this.feed.type == FeedType.gaz
    },
  },
});
