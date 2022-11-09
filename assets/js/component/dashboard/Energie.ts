import { defineComponent } from 'vue';
import { Frequence } from '@/type/Granularite';
import { mapGetters } from 'vuex';
import { querySomme } from '@/api/data';
import { grid } from '../../composable/vue-screen';
import AideCalendrier from '../aide/graphique/AideCalendrier';
import AideEvolution from '../aide/graphique/AideEvolution';
import AideSemaineJours from '../aide/graphique/AideSemaineJours';
import Calendrier from '../graphique/Calendrier';
import Card from 'primevue/card';
import Evolution from '../graphique/Evolution';
import Index from '../graphique/Index';
import JourSemaine from '../graphique/JourSemaine';
import SelectionForm from '../selection/SelectionForm';
import SemaineHorizontal from '../graphique/SemaineHorizontal';
import SemaineVertical from '../graphique/SemaineVertical';

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
    Index,
    SelectionForm,
    SemaineHorizontal,
    SemaineVertical,
  },
  setup() {
    return {
      grid
    }
  },
  data() {
    return {
      indexEnergie: 0,
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
  mounted() {
    this.refreshIndexEnergie()
  },
  watch: {
    periode() {
      this.refreshIndexEnergie()
    },
    feedDataId() {
      this.refreshIndexEnergie()
    },
  },
  methods: {
    refreshIndexEnergie() {
      querySomme(
        this.feedDataId,
        Frequence.Day,
        this.periode[0] ?? new Date(),
        this.periode[1] ?? new Date(),
      )
      .then ((data) => {
        this.indexEnergie = data
      })
    }
  },
});
