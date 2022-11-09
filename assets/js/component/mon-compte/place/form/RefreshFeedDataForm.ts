import { defineComponent, PropType } from 'vue';
import Button from 'primevue/button';
import Calendar from 'primevue/calendar';
import { PLACE_REFRESH_DATA } from '@/store/actions';
import { required } from "@vuelidate/validators";
import { useVuelidate } from "@vuelidate/core";
import { Place } from '@/type/Place';
import { Feed, feedLabelShort } from '@/type/Feed';

export default defineComponent({
  name: 'RefreshFeedDataForm',
  components: {
    Button,
    Calendar,
  },
  setup: () => ({ v$: useVuelidate() }),
  props: {
    place: {
      type: Object as PropType<Place>,
      required: true
    },
    feed: {
      type: Object as PropType<Feed>,
      required: true
    },
  },
  data() {
    return {
      range: [],
      submitted: false,
    }
  },
  validations() {
    return {
      range: {
        required
      },
    }
  },
  methods: {
    post(isValid: boolean) {
      this.submitted = true

      if (!isValid) {
        return
      }

      this.$store.dispatch(PLACE_REFRESH_DATA, {
        placeId: this.place.id,
        feedId: this.feed.id,
        start: this.range[0],
        end: this.range[1],
      })
      this.$emit('toggleVisible')
    },
    feedLabel(feed: Feed): string {
      return feedLabelShort(feed)
    },
    feedValues(feed: Feed): null|{feed: Feed, range: Date[], submitted: Boolean} {
      return this[feed.id] ?? null
    },
  },
  emits: ['toggleVisible'],
});
