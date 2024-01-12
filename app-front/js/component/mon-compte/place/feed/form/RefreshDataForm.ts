import { defineComponent, PropType } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Message from 'primevue/message';
import Calendar from 'primevue/calendar';
import { Place } from '@/type/Place';
import { FEED_REFRESH_DATA } from '@/store/actions';
import { required } from "@vuelidate/validators";
import { useVuelidate } from "@vuelidate/core";
import { Feed, feedLabelShort } from '@/type/Feed';


export default defineComponent({
  name: 'RefreshDataForm',
  components: {
    Button,
    Dialog,
    Calendar,
    Message,
  },
  setup: () => ({ v$: useVuelidate() }),
  props: {
    visible: {
      type: Boolean,
      required: true,
    },
    title: {
      type: String,
      required: true,
    },
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
    closeBasic() {
      this.$emit('toggleVisible')
    },
    post(isValid: boolean) {
      this.submitted = true

      if (!isValid) {
        return
      }

      this.$store.dispatch(FEED_REFRESH_DATA, {
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
