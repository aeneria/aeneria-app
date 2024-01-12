import { defineComponent, PropType, ref } from 'vue';
import Button from 'primevue/button';
import EditLinkyForm from './form/EditLinkyForm';
import RefreshDataForm from '../form/RefreshDataForm';
import ImportDataForm from '../form/ImportDataForm';
import { Feed, feedDescription, feedIcon, feedLabelLong } from '@/type/Feed';
import { Place } from '@/type/Place';
import Menu from 'primevue/menu';
import { MenuItem } from 'primevue/menuitem';
import Message from 'primevue/message';
import { mapState } from 'vuex';

export default defineComponent({
  components: {
    Button,
    EditLinkyForm,
    Menu,
    ImportDataForm,
    RefreshDataForm,
    Message,
  },
  setup() {
    const menuEdition = ref()

    return {
      menuEdition,
    }
  },
  props: {
    place: {
      type: Object as PropType<Place>,
      required: true,
    },
    feed: {
      type: Object as PropType<Feed>,
      required: true,
    },
  },
  data() {
    return {
      displayCheckConnectionForm: false,
      displayRefreshDataForm: false,
      displayImportDataForm: false,
    }
  },
  methods: {
    toggleMenuEdition() {
      this.menuEdition.toggle(event);
    },
    toggleCheckConnectionForm() {
      this.displayCheckConnectionForm = !this.displayCheckConnectionForm
    },
    toggleRefreshDataForm() {
      this.displayRefreshDataForm = !this.displayRefreshDataForm
    },
    toggleImportDataForm() {
      this.displayImportDataForm = !this.displayImportDataForm
    },
  },
  computed: {
    ...mapState([
      'configuration',
    ]),
    label(): string {
      return feedLabelLong(this.feed)
    },
    icon(): string {
      return feedIcon(this.feed)
    },
    description(): string {
      return feedDescription(this.feed)
    },
    menuEditionItems(): Array<MenuItem>{
      const menuEditionItems = new Array<MenuItem>(
        {
          label: "Gérer la connexion à Enedis",
          icon: 'pi pi-link',
          command: () => this.toggleCheckConnectionForm(),
        },
        {
          separator: true
        },
      )

      if (this.configuration.userCanFetch) {
        menuEditionItems.push({
          label: 'Rafraîchir des données',
          icon: 'pi pi-cloud-download',
          command: () => this.toggleRefreshDataForm()
        })
      }

      if (this.configuration.userCanImport) {
        menuEditionItems.push({
          label: 'Importer un fichier de données Enedis',
          icon: 'pi pi-download',
          command: () => this.toggleImportDataForm()
        })
      }

      return menuEditionItems
    }
  },
});
