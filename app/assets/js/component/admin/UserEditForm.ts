import { defineComponent, PropType } from 'vue';
import { required, email } from "@vuelidate/validators";
import { Utilisateur } from '@/type/Utilisateur';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputSwitch from 'primevue/inputswitch';
import InputText from 'primevue/inputtext';
import Password from 'primevue/password';
import useVuelidate from '@vuelidate/core';
import { postUserUpdate } from '@/api/admin';

export default defineComponent({
  name: 'UserEditForm',
  components: {
    Button,
    Dialog,
    InputText,
    InputSwitch,
    Password,
  },
  setup: () => ({ v$: useVuelidate() }),
  props: {
    visible: {
      type: Boolean,
    },
    utilisateur: {
      type: Object as PropType<null|Utilisateur>,
    },
  },
  watch: {
    utilisateur() {
      this.email = this.utilisateur?.username ?? null
      this.password = null
      this.active = this.utilisateur?.active ?? null
      this.admin = this.utilisateur?.roles.includes('ROLE_ADMIN') ?? null
      this.submitted = false
    }
  },
  data() {
    return {
      email: null as null|string,
      password: null as null|string,
      active: null as null|boolean,
      admin: null as null|boolean,
      submitted: false
    }
  },
  validations() {
    return {
      email: {
        required,
        email,
      },
    }
  },
  methods: {
    confirmEdition(isValid: boolean) {
      this.submitted = true

      if (!isValid) {
        return
      }

      postUserUpdate(
        this.utilisateur?.id ?? null,
        this.email,
        this.password,
        this.active,
        this.admin
      ).then(() => {
        if (this.utilisateur){
          this.utilisateur.username = this.email ?? ''
          this.utilisateur.active = this.active ?? false
          this.utilisateur.roles = this.admin ? ['ROLE_ADMIN'] : []
        }
        this.$emit('toggleVisible')
      })
    },
    cancelEdition() {
      this.$emit('toggleVisible')
    },
  },
});
