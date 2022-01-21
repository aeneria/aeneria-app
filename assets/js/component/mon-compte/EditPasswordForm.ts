import { defineComponent } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import Password from 'primevue/password';
import { UPDATE_USER_PASSWORD } from '@/store/actions';
import { required, sameAs } from "@vuelidate/validators";
import { useVuelidate } from "@vuelidate/core";

export default defineComponent({
  name: 'EditPasswordForm',
  components: {
    Button,
    Dialog,
    Password,
  },
  setup: () => ({ v$: useVuelidate() }),
  props: {
    visible: {
      type: Boolean,
      required: true,
    },
  },
  data() {
    return {
      submitted: false,
      oldPassword: '',
      newPassword: '',
      newPassword2: '',
    }
  },
  validations() {
    return {
      oldPassword: {
        required
      },
      newPassword: {
        required
      },
      newPassword2: {
        required,
        sameAsPassword: sameAs(this.newPassword),
      },
    }
  },
  methods: {
    closeBasic() {
      this.$emit('toggleVisible')
    },
    post(isValid: boolean) {
      this.submitted = true

      if (!isValid) {
        return
      }

      this.$store.dispatch(UPDATE_USER_PASSWORD, {
        oldPassword: this.oldPassword,
        newPassword: this.newPassword,
        newPassword2: this.newPassword2,
      })
      this.$emit('toggleVisible')
      this.oldPassword = ''
      this.newPassword = ''
      this.newPassword2 = ''
    }
  }
});
