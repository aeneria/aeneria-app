import { defineComponent, ref } from 'vue';
import { INIT_CONFIGURATION } from '@/store/actions';
import { mapGetters, mapState } from 'vuex';
import { MenuItem } from 'primevue/menuitem';
import { RouterLink, RouterView } from 'vue-router';
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import Welcome from './misc/Welcome';
import PlaceSelect from './selection/PlaceSelect';
import SidebarLink from './misc/SidebarLink';
import Spinner from './graphique/Spinner';
import Toast from 'primevue/toast';

export default defineComponent({
  name: 'App',
  components: {
    Button,
    Menu,
    Welcome,
    PlaceSelect,
    RouterLink,
    RouterView,
    SidebarLink,
    Spinner,
    Toast,
  },
  setup() {
    const menuMonCompte = ref()

    return {
      menuMonCompte
    }
  },
  mounted() {
    this.$store.dispatch(INIT_CONFIGURATION)
  },
  data() {
    return {
      sideBarLinks: {
        'home' : {
          to: "/app/",
          icon: "fa-solid fa-house",
          titre:"Accueil",
        },
        'energie' : {
          to: "/app/energie",
          icon: "fa-solid fa-bolt-lightning",
          titre:"La consommation d'énergie en détail",
        },
        'meteo' : {
          to: "/app/meteo",
          icon: "fa-solid fa-cloud-sun-rain",
          titre:"La météo sous tous les angles",
        },
        'analyse' : {
          to: "/app/analyse",
          icon: "fa-solid fa-magnifying-glass-chart",
          titre:"Analyse croisée énergie/météo",
        },
        'comparaison' : {
          to: "/app/comparaison",
          icon: "fa-solid fa-scale-balanced",
          titre:"Comparaison de 2 périodes",
        },
      }
    }
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
    menuMonCompteItems(): MenuItem[] {
      const menuMonCompteItems = new Array<MenuItem>()

      if(!(this.configuration?.isDemoMode)) {
        menuMonCompteItems.push({
            label: 'Mon compte',
            icon: 'pi pi-user',
            to: '/app/mon-compte',
        })
      }

      if (this.isAdmin) {
        menuMonCompteItems.push({
          label: 'Administration',
          icon: 'pi pi-shield',
          to: '/app/admin',
        })
      }

      menuMonCompteItems.push(
        {
            label: 'À Propos',
            icon: 'pi pi-info-circle',
            to: '/app/a-propos',
        },
        {
            label: 'Aide',
            icon: 'pi pi-question-circle',
            to: '/app/aide',
        }
      )

      menuMonCompteItems.push({
          label: 'Déconnexion',
          icon: 'pi pi-sign-out',
          command: () => {
              window.location.href = '/logout'
          }
      })

      return menuMonCompteItems
    },
    displayWelcome(): boolean {
      return this.hasNoPlace && !['mon-compte', 'new-place'].includes(this.$router.currentRoute?.value?.name?.toString() ?? '')
    }
  },
  methods: {
    toggleMenuMonCompte(event) {
      this.menuMonCompte.toggle(event);
    }
  }
});
