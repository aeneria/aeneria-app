import { defineComponent, ref } from 'vue';
import { mapGetters, mapState } from 'vuex';
import { MenuItem } from 'primevue/menuitem';
import Button from 'primevue/button';
import Menu from 'primevue/menu';

export default defineComponent({
  name: 'TopbarDesktop',
  components: {
    Button,
    Menu,
  },
  setup() {
    const menuMonCompte = ref()

    return {
      menuMonCompte,
    }
  },
  props: {
    isMobile: {
      type: Boolean,
      default: false,
    },
  },
  computed: {
    ...mapState([
      'configuration',
    ]),
    ...mapGetters([
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
  },
  methods: {
    toggleMenuMonCompte(event) {
      this.menuMonCompte.toggle(event);
    }
  },
});
