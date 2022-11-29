import { defineComponent } from 'vue';
import { Frequence } from '@/type/Granularite';
import { mapGetters } from 'vuex';
import { max } from 'd3';
import { queryMax } from '@/api/data';
import { screen, grid } from '@/composable/vue-screen';
import AideAnalyseCroisee from '@/component/aide/AideAnalyseCroisee';
import AideCalendrier from '@/component/aide/AideCalendrier';
import AidePapillon from '@/component/aide/AidePapillon';
import AideSemaineJours from '@/component/aide/AideSemaineJours';
import Calendrier from '@/component/graphique/Calendrier';
import Card from 'primevue/card';
import DoubleEvolution from '@/component/graphique/DoubleEvolution';
import JourSemaine from '@/component/graphique/JourSemaine';
import NuagePoint from '@/component/graphique/NuagePoint';
import SelectionForm from '@/component/selection/SelectionForm';
import SemaineVertical from '@/component/graphique/SemaineVertical';

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
  mounted() {
    this.refreshMaxEnergieP1()
    this.refreshMaxEnergieP2()
  },
  watch: {
    periode1() {
      this.refreshMaxEnergieP1()
    },
    periode2() {
      this.refreshMaxEnergieP2()
    },
    feedDataId1() {
      this.refreshMaxEnergieP1()
      this.refreshMaxEnergieP2()
    }
  },
  data() {
    return {
      maxEnergieP1: 0,
      maxEnergieP2: 0,
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
    }),
    maxEnergie() {
      return max([this.maxEnergieP1, this.maxEnergieP2]) ?? 0
    }
  },
  methods: {
    refreshMaxEnergieP1() {
      queryMax(
        this.feedDataId1,
        Frequence.Day,
        this.periode1[0] ?? new Date(),
        this.periode1[1] ?? new Date()
      )
      .then((data) =>{
        this.maxEnergieP1 = data
      })
    },
    refreshMaxEnergieP2() {
      queryMax(
        this.feedDataId1,
        Frequence.Day,
        this.periode2[0] ?? new Date(),
        this.periode2[1] ?? new Date()
      )
      .then((data) =>{
        this.maxEnergieP2 = data
      })
    },
  },
});
