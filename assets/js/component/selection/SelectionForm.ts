import { defineComponent, PropType } from 'vue';
import EnergieSelect from './EnergieSelect';
import MeteoSelect from './MeteoSelect';
import GranulariteSelect from './GranulariteSelect';
import PeriodeSelect from './PeriodeSelect';

export default defineComponent({
  name: 'SelectionForm',
  components: {
    EnergieSelect,
    MeteoSelect,
    GranulariteSelect,
    PeriodeSelect,
  },
  props: {
    type: {
      type: String as PropType<'classique'|'analyse'>,
      required: true,
    },
  },
});
