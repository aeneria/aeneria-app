import { defineComponent, ref } from 'vue';
import { mapState } from 'vuex';
import Button from 'primevue/button';
import Place from './place/Place';
import Menu from 'primevue/menu';
import Divider from 'primevue/divider';
import EditEmailForm from './form/EditEmailForm';
import DeleteAccountForm from './form/DeleteAccountForm';
import EditPasswordForm from './form/EditPasswordForm';

export default defineComponent({
  name: 'MonCompte',
  components: {
    Button,
    Divider,
    Place,
    Menu,
    EditEmailForm,
    DeleteAccountForm,
    EditPasswordForm,
  },
  setup() {
    const menuMonCompte = ref()

    return {
      menuMonCompte
    }
  },
  data() {
    return {
      displayEditEmailForm: false,
      displayEditPasswordForm: false,
      displayDeleteAccountForm: false,
      menuMonCompteItems: [
        {
            label: 'Modifier mon adresse e-mail',
            icon: 'pi pi-pencil',
            command: () => this.toggleEditEmailForm()
        },
        {
            label: 'Modifier mon mot de passe',
            icon: 'pi pi-lock',
            command: () => this.toggleEditPasswordForm()
        },
        {
            separator: true
        },
        {
            label: 'Supprimer mon compte',
            icon: 'pi pi-trash',
            command: () => this.toggleDeleteAccountForm()
        },
      ]
    }
  },
  computed: {
    ...mapState([
      'utilisateur',
    ]),
  },
  methods: {
    toggleMenuMonCompte(event) {
      this.menuMonCompte.toggle(event)
    },
    toggleEditEmailForm() {
      this.displayEditEmailForm = !this.displayEditEmailForm
    },
    toggleEditPasswordForm() {
      this.displayEditPasswordForm = !this.displayEditPasswordForm
    },
    toggleDeleteAccountForm() {
      this.displayDeleteAccountForm = !this.displayDeleteAccountForm
    },
    goToNewPLace() {
      this.$router.push({name: 'new-place'})
    },
  },
});
