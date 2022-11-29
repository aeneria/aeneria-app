import { defineComponent } from 'vue';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Logs from '@/component/admin/Logs';
import Message from 'primevue/message';
import TabPanel from 'primevue/tabpanel';
import TabView from 'primevue/tabview';
import Users from '@/component/admin/Users';

export default defineComponent({
  name: 'Admin',
  components: {
    Column,
    DataTable,
    Logs,
    Message,
    Users,
    TabPanel,
    TabView,
  },
  data() {
    return {
      configs: [
        {
          libelle: "Nombre d'adresses max par utilisateur",
          valeur: this.$store.state.configuration?.userMaxPlaces == -1 ? "Illimité" : this.$store.state.configuration?.userMaxPlaces,
        },
        {
          libelle: "Partage d'adresses entre utilisateurs",
          valeur: this.$store.state.configuration?.userCanSharePlace ? 'Oui' : 'Non',
        },
        {
          libelle: "Les adresses peuvent être publiques",
          valeur: this.$store.state.configuration?.placeCanBePublic ? 'Oui' : 'Non',
        },
        {
          libelle: "Les utilisateurs peuvent recharger leur données via l'UI",
          valeur: this.$store.state.configuration?.userCanFetch ? 'Oui' : 'Non',
        },
        {
          libelle: "Les utilisateurs peuvent exporter leur données via l'UI",
          valeur: this.$store.state.configuration?.userCanExport ? 'Oui' : 'Non',
        },
        {
          libelle: "Les utilisateurs peuvent importer leur données via l'UI",
          valeur: this.$store.state.configuration?.userCanImport ? 'Oui' : 'Non',
        },
        {
          libelle: "Démo mode activé",
          valeur: this.$store.state.configuration?.isDemoMode ? 'Oui' : 'Non',
        },
      ],
    }
  },
});
