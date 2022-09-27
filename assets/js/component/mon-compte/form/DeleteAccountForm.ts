import { defineComponent } from 'vue';
import { mapState } from 'vuex';
import { required, sameAs } from "@vuelidate/validators";
import { USER_DELETE_ACCOUNT } from '@/store/actions';
import { useVuelidate } from "@vuelidate/core";
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Checkbox from 'primevue/checkbox';
import ConfirmDialog from 'primevue/confirmdialog';
import Message from 'primevue/message';
import Password from 'primevue/password';
import { useConfirm } from 'primevue/useconfirm';

export default defineComponent({
  name: 'DeleteAccountForm',
  components: {
    Button,
    ConfirmDialog,
    Dialog,
    Checkbox,
    Message,
    Password,
  },
  setup: () => ({
    confirmService: useConfirm(),
    v$: useVuelidate() ,
  }),
  props: {
    visible: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      submitted: false,
      password: '',
      yesIamSure: false,
    }
  },
  validations() {
    return {
      password: {
        required,
      },
      yesIamSure: {
        required,
        sameAsTrue : sameAs(true),
      },
    }
  },
  computed: {
    ...mapState([
      'utilisateur',
    ]),
  },
  methods: {
    closeBasic() {
      this.password = ''
      this.$emit('toggleVisible')
    },
    confirmation() {
      this.submitted = true

      this.closeBasic()
      this.confirmService.require({
        message: 'Désolé d\'insister, mais cette action étant irreversible : êtes-vous sûr·e de vouloir supprimer votre compte ?',
        header: 'Confirmation',
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Je confirme',
        accept: this.post,
        acceptClass: 'p-button-danger p-button-rounded',
        rejectLabel: 'Annuler',
        rejectClass: 'p-button-text p-button-rounded p-button-secondary',
      })
    },
    post() {
      this.submitted = true

      this.closeBasic()
      this.$store.dispatch(USER_DELETE_ACCOUNT, {
        password: this.password,
        yesIamSure: this.yesIamSure,
      })
      this.$emit('toggleVisible')
    },
  }
});
