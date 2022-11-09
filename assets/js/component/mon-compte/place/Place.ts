import { defineComponent, PropType, ref } from 'vue';
import Button from 'primevue/button';
import { findFeedByType, Place } from '@/type/Place';
import Menu from 'primevue/menu';
import Card from 'primevue/card';
import Feed from './feed/Feed';
import DeleteForm from './form/DeleteForm';
import EditNomForm from './form/EditNomForm';
import ExportDataForm from './form/ExportDataForm';
import ImportDataForm from './form/ImportDataForm';
import RefreshDataForm from './form/RefreshDataForm';
import AddLinkyForm from './feed/form/AddLinkyForm';
import AddGazparForm from './feed/form/AddGazparForm';
import { MenuItem } from 'primevue/menuitem';
import { mapState } from 'vuex';
import { Feed as FeedObject, FeedType } from '@/type/Feed';

export default defineComponent({
  name: 'Place',
  components: {
    Button,
    Card,
    Menu,
    Feed,
    DeleteForm,
    EditNomForm,
    ExportDataForm,
    ImportDataForm,
    RefreshDataForm,
    AddLinkyForm,
    AddGazparForm,
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
  },
  data() {
    return {
      displayDeleteForm: false,
      displayEditNomForm: false,
      displayExportDataForm: false,
      displayImportDataForm: false,
      displayRefreshDataForm: false,
      displayAddLinkyForm: false,
      displayAddGazparForm: false,
    }
  },
  methods: {
    toggleMenuEdition(event) {
      this.menuEdition.toggle(event);
    },
    toggleDeleteForm() {
      this.displayDeleteForm = !this.displayDeleteForm
    },
    toggleEditNomForm() {
      this.displayEditNomForm = !this.displayEditNomForm
    },
    toggleExportDataForm() {
      this.displayExportDataForm = !this.displayExportDataForm
    },
    toggleImportDataForm() {
      this.displayImportDataForm = !this.displayImportDataForm
    },
    toggleRefreshDataForm() {
      this.displayRefreshDataForm = !this.displayRefreshDataForm
    },
    toggleLinkyForm() {
      this.displayAddLinkyForm = !this.displayAddLinkyForm
    },
    toggleGazparForm() {
      this.displayAddGazparForm = !this.displayAddGazparForm
    },
  },
  computed: {
    ...mapState([
      'configuration',
    ]),
    feedMeteo(): null|FeedObject {
      return findFeedByType(this.place, FeedType.meteo)
    },
    feedGaz(): null|FeedObject {
      return findFeedByType(this.place, FeedType.gaz)
    },
    feedElectricite(): null|FeedObject {
      return findFeedByType(this.place, FeedType.electricity)
    },
    menuEditionItems(): Array<MenuItem>{
      const menuEditionItems = new Array<MenuItem>(
        {
          label: 'Modifier le nom',
          icon: 'pi pi-pencil',
          command: () => this.toggleEditNomForm(),
        },
        {
          separator: true
        },
      )

      if (this.configuration.userCanFetch) {
        menuEditionItems.push({
          label: 'Rafraîchir des données',
          icon: 'pi pi-refresh',
          command: () => this.toggleRefreshDataForm()
        })
      }

      if (this.configuration.userCanExport) {
        menuEditionItems.push({
          label: 'Exporter des données',
          icon: 'pi pi-upload',
          command: () => this.toggleExportDataForm()
        })
      }

      if (this.configuration.userCanImport) {
        menuEditionItems.push({
          label: 'Importer des données',
          icon: 'pi pi-download',
          command: () => this.toggleImportDataForm()
        })
      }

      if (this.configuration.userCanImport || this.configuration.userCanExport || this.configuration.userCanFetch) {
        menuEditionItems.push({separator: true})
      }

      menuEditionItems.push({
        label: 'Supprimer',
        icon: 'pi pi-trash',
        command: () => this.toggleDeleteForm()
      })

      return menuEditionItems
    }
  }
});
