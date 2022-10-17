import { defineComponent, PropType } from 'vue';
import Button from 'primevue/button';
import EditMeteoForm from './form/EditMeteoForm';
import EditLinkyForm from './form/EditLinkyForm';
import EditGazparForm from './form/EditGazparForm';
import { Feed, feedDescription, feedIcon, feedLabelLong, FeedType } from '@/type/Feed';
import { Place } from '@/type/Place';

export default defineComponent({
  name: 'Feed',
  components: {
    Button,
    EditMeteoForm,
    EditLinkyForm,
    EditGazparForm,
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
    label(): string {
      return feedLabelLong(this.feed)
    },
    icon(): string {
      return feedIcon(this.feed)
    },
    description(): string {
      return feedDescription(this.feed)
    },
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
