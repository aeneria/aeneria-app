import { defineComponent } from 'vue';
import { FeedType } from '@/type/Feed';
import { FilterMatchMode } from 'primevue/api';
import { Place, findFeedByType } from '@/type/Place';
import { postUserDelete, postUserDisable, postUserEnable, queryUtilisateurs } from '@/api/admin';
import { required, email } from "@vuelidate/validators";
import { Utilisateur } from '@/type/Utilisateur';
import Button from 'primevue/button';
import Column from 'primevue/column';
import ConfirmDialog from 'primevue/confirmdialog';
import ConfirmPopup from 'primevue/confirmpopup';
import DataTable from 'primevue/datatable';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import useVuelidate from '@vuelidate/core';
import UserAddForm from './UserAddForm';
import UserEditForm from './UserEditForm';

export default defineComponent({
  name: 'Users',
  components: {
    Button,
    Column,
    ConfirmDialog,
    ConfirmPopup,
    DataTable,
    Dialog,
    InputText,
    Password,
    UserAddForm,
    UserEditForm,
  },
  setup: () => ({ v$: useVuelidate() }),
  data() {
    return {
      loading: true,
      list: new Array<Utilisateur>(),
      filters: {
        'username': {value: null, matchMode: FilterMatchMode.CONTAINS}
      },
      expandedRows: [],
      editedUtilisateur: null as null|Utilisateur,
      showUserAddForm: false,
    }
  },
  validations() {
    return {
      nameValue: {
        required,
        email,
      },
    }
  },
  mounted() {
    this.loadUsers()
  },
  methods: {
    loadUsers () {
      this.loading = true
      queryUtilisateurs(-1, 0).then(data => {
        this.list = data
        this.loading = false
      })
    },
    hasLinky(place: Place): boolean {
      return !!findFeedByType(place, FeedType.electricity)
    },
    hasGazpar(place: Place): boolean {
      return !!findFeedByType(place, FeedType.gaz)
    },
    getMeteo(place: Place): string {
      return findFeedByType(place, FeedType.meteo)?.param['CITY'] ?? ''
    },
    toggleActif(event, utilisateur: Utilisateur) {
      this.$confirm.require({
        target: event.currentTarget,
        group: 'popup',
        message: 'Êtes-vous sûr·e de vouloir ' + (utilisateur.active ? 'désactiver' : 'activer') + ' cette utilisateur ?',
        icon: 'pi pi-exclamation-triangle',
        accept: () => {
          if (utilisateur.active) {
            postUserDisable(utilisateur.id, true).then(() => {
              utilisateur.active = !utilisateur.active
            })
          } else {
            postUserEnable(utilisateur.id, true).then(() => {
              utilisateur.active = !utilisateur.active
            })
          }
        },
        reject: () => {

        },
        acceptClass: "p-button-rounded p-button-danger",
        rejectClass: "p-button-rounded p-button-text p-button-secondary",
      })
    },
    remove(event, utilisateur: Utilisateur) {
      this.$confirm.require({
        target: event.currentTarget,
        group: 'dialog',
        message: 'Êtes-vous sûr·e de vouloir supprimer cette utilisateur **ainsi que toutes ses données** ?',
        icon: 'pi pi-exclamation-triangle',
        accept: () => {
          if (utilisateur.active) {
            postUserDelete(utilisateur.id, true).then(() => {
              this.$toast.add({
                severity:'success',
                summary: "Utilisateur supprimé.",
                detail: `L'utilisateur ${utilisateur.username} a bien été supprimé.`
              })
              this.loadUsers()
            })
          } else {
            postUserEnable(utilisateur.id, true).then(() => {
              utilisateur.active = !utilisateur.active
            })
          }
        },
        acceptClass: "p-button-rounded p-button-danger",
        rejectClass: "p-button-rounded p-button-text p-button-secondary",
      })
    },
    openEditUserForm(event, utilisateur: Utilisateur) {
      this.editedUtilisateur = utilisateur
    },
    closeEditUserForm() {
      this.editedUtilisateur = null
    },
    toggleUserAddForm() {
      this.showUserAddForm = !this.showUserAddForm
    }
  },
});
