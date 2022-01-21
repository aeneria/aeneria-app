import { defineComponent, ref } from 'vue';
import { mapState } from 'vuex';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Place from './Place';
import Menu from 'primevue/menu';
import Divider from 'primevue/divider';
import EditEmailForm from './EditEmailForm';
import EditPasswordForm from './EditPasswordForm';

export default defineComponent({
  name: 'MonCompte',
  components: {
    Button,
    Card,
    Divider,
    Place,
    Menu,
    EditEmailForm,
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
  }
});
