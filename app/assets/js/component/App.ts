import { defineComponent, State, watch } from 'vue';
import { grid } from '../composable/vue-screen';
import { INIT_CONFIGURATION } from '@/store/actions';
import { mapGetters, mapState, useStore } from 'vuex';
import { RouterLink, RouterView } from 'vue-router';
import { useToast } from "primevue/usetoast";
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import SidebarMenu from './nav/SidebarMenu';
import Spinner from './graphique/Spinner';
import Toast from 'primevue/toast';
import TopbarDesktop from './nav/TopbarDesktop';
import TopbarMobile from './nav/TopbarMobile';
import Welcome from './misc/Welcome';
import { RESET_NOTIFICATIONS } from '@/store/mutations';

export default defineComponent({
  name: 'App',
  components: {
    Button,
    Dialog,
    RouterLink,
    RouterView,
    SidebarMenu,
    Spinner,
    Toast,
    TopbarDesktop,
    TopbarMobile,
    Welcome,
  },
  setup() {
    const store = useStore<State>();
    const toast = useToast();

    watch(
      () =>
       store.state.notifications,
       (notifications, prevNotifications) => {
        if(notifications.length) {
          for(const notification of notifications) {
            toast.add(notification)
          }
          store.commit(RESET_NOTIFICATIONS)
        }
      },
      {deep: true}
    );

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
      'isDisconnected',
    ]),
    ...mapGetters([
      'onlyOneEnergie',
      'isAdmin',
    ]),
    displayWelcome(): boolean {
      return this.hasNoPlace && !['mon-compte', 'new-place'].includes(this.$router.currentRoute?.value?.name?.toString() ?? '')
    }
  },
  methods: {
    goToLogin() {
      window.location.assign('/login')
    },
  }
});
