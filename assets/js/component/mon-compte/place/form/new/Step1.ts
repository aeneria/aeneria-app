import { defineComponent, PropType } from 'vue';
import Button from 'primevue/button';
import Dropdown from 'primevue/dropdown';
import InputText from 'primevue/inputtext';
import Card from 'primevue/card';
import { required } from "@vuelidate/validators";
import useVuelidate from '@vuelidate/core';
import { queryMeteoStationList } from '@/api/feed';

export default defineComponent({
  name: 'CreatePlace',
  components: {
    InputText,
    Dropdown,
    Card,
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
      type: Object as PropType<{key: string, isLabeledStatement: string}>,
    },
  },
  data() {
    return {
      meteoValue: null as null|{key: string, isLabeledStatement: string},
      nameValue: null as null|string,
      submitted: false,
      meteoOptionList: null as null|Array<{key: string, isLabeledStatement: string}>
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
    goToMonCompte() {
      this.$router.push({name: 'mon-compte'})
    },
    next() {
      this.$emit('next', this.nameValue, this.meteoValue)
    },
  }
});
