import { defineComponent, PropType } from 'vue';
import EnergieSelect from './EnergieSelect';
import MeteoSelect from './MeteoSelect';
import GranulariteSelect from './GranulariteSelect';
import PeriodeSelect from './PeriodeSelect';
import Periode2Select from './Periode2Select';

export default defineComponent({
  name: 'SelectionForm',
  components: {
    EnergieSelect,
    MeteoSelect,
    GranulariteSelect,
    PeriodeSelect,
    Periode2Select,
  },
  props: {
    type: {
      type: String as PropType<'classique'|'analyse'>,
      required: true,
    },
  },
});
