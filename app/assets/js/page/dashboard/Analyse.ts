import { defineComponent } from 'vue';
import AidePapillon from '@/component/aide/AidePapillon';
import AideAnalyseCroisee from '@/component/aide/AideAnalyseCroisee';
import Card from 'primevue/card';
import Papillon from '@/component/graphique/Papillon';
import NuagePoint from '@/component/graphique/NuagePoint';
import SelectionForm from '@/component/selection/SelectionForm';
import { mapGetters } from 'vuex';

export default defineComponent({
  name: 'DashboardAnalyse',
  components: {
    Card,
    Papillon,
    NuagePoint,
    SelectionForm,
    AidePapillon,
    AideAnalyseCroisee,
  },
  computed: {
    periode() { return this.$store.state.selection.periode },
    energie() { return this.$store.state.selection.energie },
    granularite() { return this.$store.state.selection.granularite },
    meteo() { return this.$store.state.selection.meteoData },
    ...mapGetters({
      feedDataId1: 'selectedEnergieFeedDataId',
      feedDataId2: 'selectedMeteoFeedDataId',
    })
  },
});
