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
            command: () => {
                this.$router.push({name: 'mon-compte'})
            },
        })
      }

      if (this.isAdmin) {
        menuMonCompteItems.push({
          label: 'Administration',
          icon: 'pi pi-shield',
          command: () => {
              this.$router.push({name: 'admin'})
          },
        })
      }

      menuMonCompteItems.push(
        {
            label: 'À Propos',
            icon: 'pi pi-info-circle',
            command: () => {
                this.$router.push({name: 'about'})
            },
        },
        {
            label: 'Aide',
            icon: 'pi pi-question-circle',
            command: () => {
                this.$router.push({name: 'aide'})
            },
        }
      )

      menuMonCompteItems.push({
          label: 'Déconnexion',
          icon: 'pi pi-sign-out',
          url: '/logout',
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
