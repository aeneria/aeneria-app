import { defineComponent } from 'vue';
import { mapGetters } from 'vuex';
import { screen, grid } from '../../composable/vue-screen';
import AideAnalyseCroisee from '../aide/graphique/AideAnalyseCroisee';
import AideCalendrier from '../aide/graphique/AideCalendrier';
import AidePapillon from '../aide/graphique/AidePapillon';
import AideSemaineJours from '../aide/graphique/AideSemaineJours';
import Calendrier from '../graphique/Calendrier';
import Card from 'primevue/card';
import DoubleEvolution from '../graphique/DoubleEvolution';
import JourSemaine from '../graphique/JourSemaine';
import NuagePoint from '../graphique/NuagePoint';
import SelectionForm from '../selection/SelectionForm';
import SemaineVertical from '../graphique/SemaineVertical';

export default defineComponent({
  name: 'DashboardComparaison',
  components: {
    AideAnalyseCroisee,
    AideCalendrier,
    AidePapillon,
    AideSemaineJours,
    Calendrier,
    Card,
    DoubleEvolution,
    JourSemaine,
    NuagePoint,
    SelectionForm,
    SemaineVertical,
  },
  setup() {
    return {
      screen,
      grid
    }
  },
  computed: {
    periode1() { return this.$store.state.selection.periode },
    periode2() { return this.$store.state.selection.periode2 },
    energie() { return this.$store.state.selection.energie },
    granularite() { return this.$store.state.selection.granularite },
    meteo() { return this.$store.state.selection.meteoData },
    ...mapGetters({
      feedDataId1: 'selectedEnergieFeedDataId',
      feedDataId2: 'selectedMeteoFeedDataId',
    })
  },
});
