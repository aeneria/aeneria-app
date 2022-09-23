import { INIT_PLACE_LIST, INIT_CONFIGURATION } from '@/store/actions';
import { defineComponent, ref } from 'vue';
import { mapGetters, mapState } from 'vuex';
import PlaceSelect from './selection/PlaceSelect';
import Spinner from './graphique/Spinner';
import SidebarLink from './misc/SidebarLink';
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import { RouterLink, RouterView } from 'vue-router';
import { MenuItem } from 'primevue/menuitem';

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
    this.$store.dispatch(INIT_CONFIGURATION)
    this.$store.dispatch(INIT_PLACE_LIST)
  },
  computed: {
    ...mapState([
      'configuration',
      'selectedPlace',
    ]),
    ...mapGetters([
      'onlyOneEnergie',
      'isAdmin',
    ]),
    menuMonCompteItems(): MenuItem[] {
      const menuMonCompteItems = new Array<MenuItem>(
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
        }
      )

      if (this.isAdmin) {
        menuMonCompteItems.push({
          label: 'Administration',
          icon: 'pi pi-shield',
          to: '/app/admin',
        })
      }

      if(!(this.configuration?.isDemoMode)) {
        menuMonCompteItems.push({
            label: 'Mon compte',
            icon: 'pi pi-user',
            to: '/app/mon-compte',
        })
      }

      menuMonCompteItems.push({
          label: 'Déconnexion',
          icon: 'pi pi-sign-out',
          command: () => {
              window.location.href = '/logout'
          }
      })

      return menuMonCompteItems
    }
  },
  methods: {
    toggleMenuMonCompte(event) {
      this.menuMonCompte.toggle(event);
    }
  }
});
