import { defineComponent, PropType } from 'vue';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import { required } from "@vuelidate/validators";
import useVuelidate from '@vuelidate/core';
import { queryMeteoStationList } from '@/api/feed';

export default defineComponent({
  components: {
    InputText,
    Dropdown,
    Button
  },
  setup: () => ({ v$: useVuelidate() }),
  mounted() {
    queryMeteoStationList().then(result => this.meteoOptionList = result)
  },
  props: {
    name: {
      type: String,
    },
    meteo: {
      type: Object as PropType<{key: string, label: string}>,
    },
  },
  data() {
    return {
      meteoValue: this.meteo as null|{key: string, label: string},
      nameValue: this.name as null|string,
      submitted: false,
      meteoOptionList: null as null|Array<{key: string, label: string}>
    }
  },
  validations() {
    return {
      nameValue: {
        required,
      },
      meteoValue: {
        required,
      },
    }
  },
  methods: {
    cancel() {
      this.$emit('cancel')
    },
    next() {
      this.$emit('next', this.nameValue, this.meteoValue)
    },
  }
});
