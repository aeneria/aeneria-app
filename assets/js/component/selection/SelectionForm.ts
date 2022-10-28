import { defineComponent, PropType } from 'vue';
import { grid } from '../../composable/vue-screen';
import { mapGetters } from 'vuex';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import DoublePeriodeSelect from './DoublePeriodeSelect';
import EnergieSelect from './EnergieSelect';
import GranulariteSelect from './GranulariteSelect';
import MeteoSelect from './MeteoSelect';
import PeriodeSelect from './PeriodeSelect';

export default defineComponent({
  name: 'SelectionForm',
  components: {
    Button,
    Dialog,
    DoublePeriodeSelect,
    EnergieSelect,
    GranulariteSelect,
    MeteoSelect,
    PeriodeSelect,
  },
  setup() {
    return {
      grid,
    }
  },
  props: {
    type: {
      type: String as PropType<'classique'|'analyse'>,
      required: true,
    },
  },
  data() {
    return {
      displayDialog: false
    }
  },
  computed: {
    ...mapGetters({
      onlyOneEnergie: 'onlyOneEnergie',
    }),
  },
  methods: {
    toggleDialog() {
      this.displayDialog = !this.displayDialog
    }
  }
});
