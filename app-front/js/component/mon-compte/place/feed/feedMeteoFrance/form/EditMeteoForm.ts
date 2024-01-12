import { defineComponent, PropType } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import { PLACE_EDIT_METEO } from '@/store/actions';
import { required } from "@vuelidate/validators";
import { useVuelidate } from "@vuelidate/core";
import { Place } from '@/type/Place';
import { queryMeteoStationList } from '@/api/feed';

export default defineComponent({
  name: 'EditMeteoForm',
  components: {
    Button,
    Dialog,
    InputText,
    Dropdown,
  },
  setup: () => ({ v$: useVuelidate() }),
  mounted() {
    queryMeteoStationList().then(result => this.meteoOptionList = result)
  },
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
      meteoValue: null as null|{key: string, label: string},
      meteoOptionList: null as null|Array<{key: string, label: string}>
    }
  },
  validations() {
    return {
      meteoValue: {
        required,
      },
    }
  },
  methods: {
    closeBasic() {
      this.$emit('toggleVisible')
    },
    post() {
      this.submitted = true

      this.$store.dispatch(PLACE_EDIT_METEO, {
        placeId: this.place.id,
        meteo: this.meteoValue,
      })
      this.$emit('toggleVisible')
    },
  },
  emits: ['toggleVisible'],
});
