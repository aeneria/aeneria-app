import { defineComponent } from 'vue';
import { mapState } from 'vuex';
import AideEvolution from '@/component/aide/AideEvolution';
import AideCalendrier from '@/component/aide/AideCalendrier';
import AideSemaineJours from '@/component/aide/AideSemaineJours';
import AidePapillon from '@/component/aide/AidePapillon';
import AideAnalyseCroisee from '@/component/aide/AideAnalyseCroisee';
import Card from 'primevue/card';
import Fieldset from 'primevue/fieldset';
import TabPanel from 'primevue/tabpanel';
import TabView from 'primevue/tabview';

export default defineComponent({
  name: 'Aide',
  components: {
    AideCalendrier,
    AideSemaineJours,
    AideEvolution,
    AidePapillon,
    AideAnalyseCroisee,
    Card,
    Fieldset,
    TabPanel,
    TabView,
  },
  computed: {
    ...mapState([
      'configuration',
    ]),
  }
});
