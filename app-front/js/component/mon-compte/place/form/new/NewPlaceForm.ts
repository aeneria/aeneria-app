import { PLACE_CREATE } from '@/store/actions';
import { defineComponent } from 'vue';
import Dialog from 'primevue/dialog';
import Step1 from './Step1';
import Step2 from './Step2';
import Step3Enedis from './Step3Enedis';
import Step3Grdf from './Step3Grdf';

export default defineComponent({
  components: {
    Dialog,
    Step1,
    Step2,
    Step3Enedis,
    Step3Grdf,
  },
  props: {
    visible: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      currentStep: 1 as 1|2|'enedis'|'grdf',
      name: null as string|null,
      meteo: null as {key: string, isLabeledStatement: string}|null,
      type: null as 'enedis'|'grdf'|null,
    }
  },
  methods: {
    closeBasic() {
      this.name = null
      this.meteo = null
      this.type = null

      this.$emit('toggleVisible')
    },
    onStep1(name: string, meteo: {key: string, isLabeledStatement: string}) {
      this.name = name
      this.meteo = meteo

      this.currentStep = 2
    },
    onStep2(type: 'enedis'|'grdf') {
      this.type = type

      this.currentStep = type
    },
    onStep3() {
      if (this.name && this.meteo) {
        this.$store.dispatch(PLACE_CREATE, {
          name : this.name,
          meteo: this.meteo,
          type: this.type
        })
      }
    },
    onPrevious() {
      if (this.currentStep == 'enedis' || this.currentStep == 'grdf') {
        this.currentStep = 2
      } else if (this.currentStep == 2) {
        this.currentStep = 1
      }
    }
  }
});