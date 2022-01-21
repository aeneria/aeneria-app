import { defineComponent, PropType, ref } from 'vue';
import Button from 'primevue/button';
import { Place } from '@/type/Place';
import Menu from 'primevue/menu';
import Chip from 'primevue/chip';
import Card from 'primevue/card';
import { MenuItem } from 'primevue/menuitem';
import { useStore } from 'vuex';

export default defineComponent({
  name: 'Place',
  components: {
    Button,
    Card,
    Menu,
    Chip,
  },
  setup() {
    const store = useStore()

    const menuEdition = ref()

    const menuEditionItems = new Array<MenuItem>({
      label: 'Modifier',
      icon: 'pi pi-pencil',
      command: () => {
        // this.$router.push('/app/a-propos')
      }
    })

    if (store.state.configuration.userCanFetch) {
      menuEditionItems.push({
        label: 'Rafraîchir des données',
        icon: 'pi pi-refresh',
        command: () => {
          // window.open('https://docs.aeneria.com', '_blank')
        }
      })
    }

    if (store.state.configuration.userCanExport) {
      menuEditionItems.push({
          label: 'Exporter des données',
          icon: 'pi pi-upload',
          command: () => {
            // window.open('https://docs.aeneria.com', '_blank')
          }
      })
    }

    if (store.state.configuration.userCanImport) {
      menuEditionItems.push({
          label: 'Importer des données',
          icon: 'pi pi-download',
          command: () => {
            // window.open('https://docs.aeneria.com', '_blank')
          }
      })

    }

    menuEditionItems.push({
        label: 'Supprimer',
        icon: 'pi pi-trash',
        command: () => {
          // window.open('https://docs.aeneria.com', '_blank')
        }
    })

    return {
      menuEdition,
      menuEditionItems,
    }
  },
  props: {
    place: {
      type: Object as PropType<Place>,
      required: true,
    },
  },
  methods: {
    toggleMenuEdition(event) {
      this.menuEdition.toggle(event);
    }
  }
});
