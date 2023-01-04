import { defineComponent } from 'vue';
import { Frequence } from '@/type/Granularite';
import { mapGetters } from 'vuex';
import { querySomme } from '@/api/data';
import { grid } from '@/composable/vue-screen';
import AideCalendrier from '@/component/aide/AideCalendrier';
import AideEvolution from '@/component/aide/AideEvolution';
import AideSemaineJours from '@/component/aide/AideSemaineJours';
import Calendrier from '@/component/graphique/Calendrier';
import Card from 'primevue/card';
import Evolution from '@/component/graphique/Evolution';
import Index from '@/component/graphique/Index';
import JourSemaine from '@/component/graphique/JourSemaine';
import SelectionForm from '@/component/selection/SelectionForm';
import SemaineHorizontal from '@/component/graphique/SemaineHorizontal';
import SemaineVertical from '@/component/graphique/SemaineVertical';

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
      indexEnergie: '-',
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
        this.indexEnergie = data.toFixed(0)
      })
    }
  },
});
