import { defineComponent } from 'vue';
import Card from 'primevue/card';
import Papillon from '../graphique/Papillon';
import NuagePoint from '../graphique/NuagePoint';
import SelectionForm from '../selection/SelectionForm';
import { mapGetters, mapState } from 'vuex';

export default defineComponent({
  name: 'DashboardAnalyse',
  components: {
    Card,
    Papillon,
    NuagePoint,
    SelectionForm,
  },
  computed: {
    ...mapState({
      periode: 'selectedPeriode',
      energie: 'selectedEnergie',
      granularite: 'selectedGranularite',
      meteo: 'selectedMeteoData'
    }),
    ...mapGetters({
      feedDataId1: 'selectedEnergieFeedDataId',
      feedDataId2: 'selectedMeteoFeedDataId',
    })
  },
});
