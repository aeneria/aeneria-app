import { defineComponent } from 'vue';
import { mapState } from 'vuex';
import { postDeleteAccount } from '@/api/configuration';
import { required, sameAs } from "@vuelidate/validators";
import { useConfirm } from 'primevue/useconfirm';
import { useVuelidate } from "@vuelidate/core";
import Button from 'primevue/button';
import Checkbox from 'primevue/checkbox';
import ConfirmDialog from 'primevue/confirmdialog';
import Dialog from 'primevue/dialog';
import Message from 'primevue/message';
import Password from 'primevue/password';

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
    confirmation(isValid: boolean) {
      this.submitted = true
      if (!isValid) {
        return
      }
      this.confirmService.require({
        message: 'Désolé d\'insister, mais cette action étant irreversible : êtes-vous sûr·e de vouloir supprimer votre compte ?',
        header: 'Confirmation',
        group: this.utilisateur.username,
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
      postDeleteAccount(this.password, this.yesIamSure)
    },
  }
});
