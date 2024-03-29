import { defineComponent, PropType } from 'vue';
import { Place } from '@/type/Place';
import { PLACE_DELETE } from '@/store/actions';
import { required } from "@vuelidate/validators";
import { useConfirm } from "primevue/useconfirm";
import { useVuelidate } from "@vuelidate/core";
import Button from 'primevue/button';
import ConfirmDialog from 'primevue/confirmdialog';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';


export default defineComponent({
  name: 'DeleteForm',
  components: {
    Button,
    ConfirmDialog,
    Dialog,
    InputText,
    Message,
  },
  setup: () => ({
    confirmService: useConfirm(),
    v$: useVuelidate(),
  }),
  props: {
    visible: {
      type: Boolean,
      required: true,
    },
    place: {
      type: Object as PropType<Place>,
      required: true
    },
  },
  data() {
    return {
      submitted: false,
      confirmationTexte: '',
    }
  },
  validations() {
    const sameAsPlaceName = (value) => value === this.place.name

    return {
      confirmationTexte: {
        required,
        sameAsPlaceName,
      },
    }
  },
  methods: {
    closeBasic() {
      this.confirmationTexte = ''
      this.$emit('toggleVisible')
    },
    confirmation(isValid: boolean) {
      this.submitted = true
      if (!isValid) {
        return
      }
      this.confirmService.require({
        message: 'Êtes-vous sûr·e de vouloir supprimer l\'adresse \'' + this.place.name + '\' ?',
        header: 'Confirmation',
        group: this.place.id.toString(),
        icon: 'pi pi-exclamation-triangle',
        acceptLabel: 'Je confirme',
        accept: this.post,
        acceptClass: 'p-button-danger p-button-rounded',
        rejectLabel: 'Annuler',
        rejectClass: 'p-button-text p-button-rounded p-button-secondary',
      })
    },
    post() {
      this.$store.dispatch(PLACE_DELETE, {
        placeId: this.place.id,
      }).then(() => {
        this.confirmService.close()
        this.closeBasic()
      })
    },
  },
  emits: ['toggleVisible'],
});
