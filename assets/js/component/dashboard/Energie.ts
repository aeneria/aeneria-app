import { defineComponent } from 'vue';
import Card from 'primevue/card';
import Calendrier from '../graphique/Calendrier';
import Evolution from '../graphique/Evolution';
import SemaineHorizontal from '../graphique/SemaineHorizontal';
import SemaineVertical from '../graphique/SemaineVertical';
import JourSemaine from '../graphique/JourSemaine';
import SelectionForm from '../selection/SelectionForm';
import { mapGetters, mapState } from 'vuex';
import { screen, grid } from '../../composable/vue-screen';

export default defineComponent({
  name: 'DashboardEnergie',
  components: {
    Card,
    Calendrier,
    JourSemaine,
    Evolution,
    SelectionForm,
    SemaineHorizontal,
    SemaineVertical,
  },
  setup() {
    return {
      screen,
      grid
    }
  },
  computed: {
    ...mapState({
      periode: 'selectedPeriode',
      energie: 'selectedEnergie',
      granularite: 'selectedGranularite',
    }),
    ...mapGetters({
      feedDataId: 'selectedEnergieFeedDataId',
    })
  },
});
