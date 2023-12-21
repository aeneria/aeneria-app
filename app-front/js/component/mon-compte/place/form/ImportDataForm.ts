import { defineComponent, PropType } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import FileUpload, { FileUploadUploaderEvent } from 'primevue/fileupload';
import Message from 'primevue/message';
import { PLACE_IMPORT_DATA } from '@/store/actions';
import { required } from "@vuelidate/validators";
import { useVuelidate } from "@vuelidate/core";
import { Place } from '@/type/Place';

export default defineComponent({
  name: 'ImportDataForm',
  components: {
    Button,
    Dialog,
    FileUpload,
    Message,
  },
  setup: () => ({ v$: useVuelidate() }),
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
      file: null as File|null,
    }
  },
  validations() {
    return {
      file: {
        required,
      },
    }
  },
  methods: {
    closeBasic() {
      this.file = null
      this.$emit('toggleVisible')
    },
    onUpload(event: FileUploadUploaderEvent) {
      this.file = Array.isArray(event.files) ? event.files[0] : event.files
    },
    onRemove() {
      this.file = null
    },
    post(isValid: boolean) {
      this.submitted = true

      if (!isValid) {
        return
      }

      this.$store.dispatch(PLACE_IMPORT_DATA, {
        placeId: this.place.id,
        file: this.file,
      })
      this.$emit('toggleVisible')
    },
  },
  emits: ['toggleVisible'],
});
