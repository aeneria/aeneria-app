import { defineComponent } from 'vue';
import Card from 'primevue/card';
import AideCalendrier from '../aide/graphique/AideCalendrier';
import AideSemaineJours from '../aide/graphique/AideSemaineJours';
import AideEvolution from '../aide/graphique/AideEvolution';
import Calendrier from '../graphique/Calendrier';
import Evolution from '../graphique/Evolution';
import SemaineHorizontal from '../graphique/SemaineHorizontal';
import SemaineVertical from '../graphique/SemaineVertical';
import JourSemaine from '../graphique/JourSemaine';
import SelectionForm from '../selection/SelectionForm';
import { mapGetters } from 'vuex';
import { screen, grid } from '../../composable/vue-screen';

export default defineComponent({
  name: 'DashboardEnergie',
  components: {
    AideCalendrier,
    AideSemaineJours,
    AideEvolution,
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
    periode() { return this.$store.state.selection.periode },
    energie() { return this.$store.state.selection.energie },
    granularite() { return this.$store.state.selection.granularite },
    ...mapGetters({
      feedDataId: 'selectedEnergieFeedDataId',
    })
  },
});
