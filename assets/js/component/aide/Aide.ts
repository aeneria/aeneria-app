import { defineComponent } from 'vue';
import { mapState } from 'vuex';
import AideEvolution from './graphique/AideEvolution';
import AideCalendrier from './graphique/AideCalendrier';
import AideSemaineJours from './graphique/AideSemaineJours';
import AidePapillon from '../aide/graphique/AidePapillon';
import AideAnalyseCroisee from '../aide/graphique/AideAnalyseCroisee';
import Card from 'primevue/card';
import Fieldset from 'primevue/fieldset';
import TabPanel from 'primevue/tabpanel';
import TabView from 'primevue/tabview';

export default defineComponent({
  name: 'About',
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
