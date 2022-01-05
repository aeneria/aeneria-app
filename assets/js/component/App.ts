import { INIT_PLACE_LIST } from '@/store/actions';
import { defineComponent } from 'vue';
import { mapGetters, mapState } from 'vuex';
import PlaceSelect from './selection/PlaceSelect';
import Spinner from './graphique/Spinner';
import SidebarLink from './misc/SidebarLink';
import { RouterLink, RouterView } from 'vue-router';

export default defineComponent({
  name: 'App',
  components: {
    PlaceSelect,
    Spinner,
    RouterLink,
    RouterView,
    SidebarLink,
  },
  mounted() {
    this.$store.dispatch(INIT_PLACE_LIST)
  },
  computed: {
    ...mapState([
      'selectedPlace',
    ]),
    ...mapGetters([
      'onlyOneEnergie',
    ])
  },
});
