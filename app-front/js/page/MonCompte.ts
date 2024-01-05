import { defineComponent, ref } from 'vue';
import { mapState } from 'vuex';
import Button from 'primevue/button';
import Place from '@/component/mon-compte/place/Place';
import Menu from 'primevue/menu';
import Divider from 'primevue/divider';
import EditEmailForm from '@/component/mon-compte/form/EditEmailForm';
import DeleteAccountForm from '@/component/mon-compte/form/DeleteAccountForm';
import EditPasswordForm from '@/component/mon-compte/form/EditPasswordForm';
import NewPlaceForm from '@/component/mon-compte/place/form/new/NewPlaceForm';

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
    NewPlaceForm,
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
      displayNewPlaceForm: false,
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
    toggleNewPlaceForm() {
      this.displayNewPlaceForm = !this.displayNewPlaceForm
    },
  },
});
