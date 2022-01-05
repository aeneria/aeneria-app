import { defineComponent, PropType } from 'vue';
import EnergieSelect from './EnergieSelect';
import GranulariteSelect from './GranulariteSelect';
import PeriodeSelect from './PeriodeSelect';

export default defineComponent({
  name: 'SelectionForm',
  components: {
    EnergieSelect,
    GranulariteSelect,
    PeriodeSelect,
  },
  props: {
    type: {
      type: String as PropType<'classique'|'versus'>,
      required: true,
    },
  },
});
