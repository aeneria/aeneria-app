import { defineComponent, PropType } from 'vue';
import EnergieSelect from './EnergieSelect';
import MeteoSelect from './MeteoSelect';
import GranulariteSelect from './GranulariteSelect';
import PeriodeSelect from './PeriodeSelect';
import DoublePeriodeSelect from './DoublePeriodeSelect';

export default defineComponent({
  name: 'SelectionForm',
  components: {
    EnergieSelect,
    MeteoSelect,
    GranulariteSelect,
    PeriodeSelect,
    DoublePeriodeSelect,
  },
  props: {
    type: {
      type: String as PropType<'classique'|'analyse'>,
      required: true,
    },
  },
});
