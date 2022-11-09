import { defineComponent } from 'vue';
import { grid } from '../composable/vue-screen';
import { INIT_CONFIGURATION } from '@/store/actions';
import { mapGetters, mapState } from 'vuex';
import { RouterLink, RouterView } from 'vue-router';
import Welcome from './misc/Welcome';
import SidebarMenu from './nav/SidebarMenu';
import Spinner from './graphique/Spinner';
import Toast from 'primevue/toast';
import TopbarDesktop from './nav/TopbarDesktop';
import TopbarMobile from './nav/TopbarMobile';

export default defineComponent({
  name: 'App',
  components: {
    Welcome,
    RouterLink,
    RouterView,
    SidebarMenu,
    Spinner,
    Toast,
    TopbarDesktop,
    TopbarMobile,
  },
  setup() {
    return {
      grid,
    }
  },
  mounted() {
    this.$store.dispatch(INIT_CONFIGURATION)
  },
  computed: {
    ...mapState([
      'initialized',
      'configuration',
      'selectedPlace',
      'hasNoPlace',
    ]),
    ...mapGetters([
      'onlyOneEnergie',
      'isAdmin',
    ]),
    displayWelcome(): boolean {
      return this.hasNoPlace && !['mon-compte', 'new-place'].includes(this.$router.currentRoute?.value?.name?.toString() ?? '')
    }
  },
});
