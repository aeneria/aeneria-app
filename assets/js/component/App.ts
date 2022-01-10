import { INIT_PLACE_LIST } from '@/store/actions';
import { defineComponent, ref } from 'vue';
import { mapGetters, mapState } from 'vuex';
import PlaceSelect from './selection/PlaceSelect';
import Spinner from './graphique/Spinner';
import SidebarLink from './misc/SidebarLink';
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import { RouterLink, RouterView } from 'vue-router';

export default defineComponent({
  name: 'App',
  components: {
    PlaceSelect,
    Spinner,
    RouterLink,
    RouterView,
    SidebarLink,
    Menu,
    Button,
  },
  setup() {
    const menuMonCompte = ref()

    return {
      menuMonCompte
    }
  },
  mounted() {
    this.$store.dispatch(INIT_PLACE_LIST)
  },
  data() {
    return {
      menuMonCompteItems: [
        {
            label: 'À Propos',
            icon: 'pi pi-info-circle',
            to: '/app/a-propos',
        },
        {
            label: 'Aide',
            icon: 'pi pi-question-circle',
            url: 'https://docs.aeneria.com',
            target: '_blank',
        },
        {
            label: 'Administration',
            icon: 'pi pi-shield',
            to: '/app/admin',
        },
        {
            label: 'Mon compte',
            icon: 'pi pi-user',
            to: '/app/mon-compte',
        },
        {
            label: 'Déconnexion',
            icon: 'pi pi-sign-out',
            command: () => {
                window.location.href = '/logout'
            }
        },
      ]
    }
  },
  computed: {
    ...mapState([
      'selectedPlace',
    ]),
    ...mapGetters([
      'onlyOneEnergie',
    ])
  },
  methods: {
    toggleMenuMonCompte(event) {
      this.menuMonCompte.toggle(event);
    }
  }
});
