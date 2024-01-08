import { defineComponent, PropType } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import { Place } from '@/type/Place';
import { queryEnedisConsentUrl } from '@/api/feed';
import ProxyWarningMessage from '@/component/misc/ProxyWarningMessage';

export default defineComponent({
  name: 'AddLinkyForm',
  components: {
    Button,
    Dialog,
    InputText,
    Message,
    ProxyWarningMessage,
  },
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
  methods: {
    closeBasic() {
      this.$emit('toggleVisible')
    },
    post(isValid: boolean) {
      queryEnedisConsentUrl(this.place.id).then(url => {
        location.href = url
      })
    },
  },
  emits: ['toggleVisible'],
});
