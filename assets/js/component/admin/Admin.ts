import { defineComponent } from 'vue';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Column from 'primevue/column';
import DataTable from 'primevue/datatable';
import Dropdown from 'primevue/dropdown';
import InputSwitch from 'primevue/inputswitch';
import InputText from 'primevue/inputtext';
import Logs from './Logs';
import Message from 'primevue/message';
import Users from './Users';

export default defineComponent({
  name: 'Admin',
  components: {
    Button,
    Card,
    Column,
    DataTable,
    Dropdown,
    InputSwitch,
    InputText,
    Logs,
    Message,
    Users,
  },
  data() {
    return {
      configs: [
        {
          libelle: "Nombre d'adresses max par utilisateur",
          valeur: this.$store.state.configuration?.userMaxPlaces,
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
