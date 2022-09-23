import { PLACE_CREATE } from '@/store/actions';
import { defineComponent } from 'vue';
import Step1 from './Step1';
import Step2 from './Step2';

export default defineComponent({
  name: 'NewPlace',
  components: {
    Step1,
    Step2,
  },
  data() {
    return {
      currentStep: 1 as 1|2,
      name: null as string|null,
      meteo: null as {key: string, isLabeledStatement: string}|null,
    }
  },
  methods: {
    onStep1(name: string, meteo: {key: string, isLabeledStatement: string}) {
      this.name = name
      this.meteo = meteo

      this.currentStep = 2
    },
    onStep2(type: 'enedis'|'grdf') {
      if (this.name && this.meteo) {
        this.$store.dispatch(PLACE_CREATE, {
          name : this.name,
          meteo: this.meteo,
          type: type
        })
      }
    },
  }
});