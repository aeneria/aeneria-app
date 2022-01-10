import { defineComponent, PropType, ref } from 'vue';
import Button from 'primevue/button';
import { Place } from '@/type/Place';
import Menu from 'primevue/menu';
import Chip from 'primevue/chip';
import Card from 'primevue/card';

export default defineComponent({
  name: 'Place',
  components: {
    Button,
    Card,
    Menu,
    Chip,
  },
  setup() {
    const menuEdition = ref()

    return {
      menuEdition
    }
  },
  props: {
    place: {
      type: Object as PropType<Place>,
      required: true,
    },
  },
  data() {
    return {
      menuEditionItems: [
        {
            label: 'Modifier',
            icon: 'pi pi-pencil',
            command: () => {
              // this.$router.push('/app/a-propos')
            }
        },
        {
            label: 'Rafraîchir des données',
            icon: 'pi pi-refresh',
            command: () => {
              // window.open('https://docs.aeneria.com', '_blank')
            }
        },
        {
            label: 'Exporter des données',
            icon: 'pi pi-upload',
            command: () => {
              // window.open('https://docs.aeneria.com', '_blank')
            }
        },
        {
            label: 'Importer des données',
            icon: 'pi pi-download',
            command: () => {
              // window.open('https://docs.aeneria.com', '_blank')
            }
        },
        {
            label: 'Supprimer',
            icon: 'pi pi-trash',
            command: () => {
              // window.open('https://docs.aeneria.com', '_blank')
            }
        },
      ]
    }
  },
  methods: {
    toggleMenuEdition(event) {
      this.menuEdition.toggle(event);
    }
  }
});
