import { defineComponent, PropType } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Calendar from 'primevue/calendar';
import { PLACE_EXPORT_DATA } from '@/store/actions';
import { required } from "@vuelidate/validators";
import { useVuelidate } from "@vuelidate/core";
import { Place } from '@/type/Place';

export default defineComponent({
  name: 'ExportDataForm',
  components: {
    Button,
    Dialog,
    Calendar,
  },
  setup: () => ({ v$: useVuelidate() }),
  props: {
    visible: {
      type: Boolean,
      required: true,
    },
    place: {
      type: Object as PropType<Place>,
      required: true
    },
  },
  data() {
    return {
      submitted: false,
      range: [] as Date[],
    }
  },
  validations() {
    return {
      range: {
        required,
      },
    }
  },
  methods: {
    closeBasic() {
      this.range = []
      this.$emit('toggleVisible')
    },
    exportData(isValid: boolean) {
      this.submitted = true

      if (!isValid) {
        return
      }

      this.$store.dispatch(PLACE_EXPORT_DATA, {
        placeId: this.place.id,
        start: this.range[0],
        end: this.range[1],
      })
      this.$emit('toggleVisible')
    },
    exportAllData() {
      this.$store.dispatch(PLACE_EXPORT_DATA, {
        placeId: this.place.id,
        start: null,
        end: null,
      })
      this.$emit('toggleVisible')
    },
  },
  emits: ['toggleVisible'],
});
