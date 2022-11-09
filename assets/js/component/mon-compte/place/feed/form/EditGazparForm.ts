import { defineComponent, PropType } from 'vue';
import Button from 'primevue/button';
import Dialog from 'primevue/dialog';
import InputText from 'primevue/inputtext';
import Message from 'primevue/message';
import { Place } from '@/type/Place';
import { queryGrdfConsentCheck, queryGrdfConsentUrl } from '@/api/feed';
import { Feed, feedTechnicalId } from '@/type/Feed';

export default defineComponent({
  name: 'EditGazparForm',
  components: {
    Button,
    Dialog,
    InputText,
    Message,
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
    feed: {
      type: Object as PropType<Feed>,
      required: true
    },
  },
  data() {
    return {
      checkingState: 'unchecked' as 'unchecked'|'checking'|'ok'|'ko',
    }
  },
  methods: {
    closeBasic() {
      this.$emit('toggleVisible')
    },
    update() {
      queryGrdfConsentUrl(this.place.id).then(url => {
        location.href = url
      })
    },
    check() {
      if (this.checkingState == 'checking') {
        // on est en cours de check, on n'en relance pas un
        return
      }
      this.checkingState = 'checking'
      queryGrdfConsentCheck(this.place.id).then(() => {
        this.checkingState = 'ok'
      }).catch(()=> {
        this.checkingState = 'ko'
      })
    },
  },
  computed: {
    checkingIcon(): string {
      if (this.checkingState == 'unchecked') {
        return 'pi-sync'
      } else if (this.checkingState == 'checking') {
        return 'pi-spin pi-spinner'
      } else if (this.checkingState == 'ok') {
        return 'pi-check'
      } else if (this.checkingState == 'ko') {
        return 'pi-times'
      }

      return ''
    },
    checkingDisabled(): boolean {
      return ['checking', 'ko', 'ok'].includes(this.checkingState)
    },
    checkingColor(): string {
      if (this.checkingState == 'unchecked') {
        return 'p-button-secondary'
      } else if (this.checkingState == 'checking') {
        return 'p-button-secondary'
      } else if (this.checkingState == 'ok') {
        return 'p-button-success'
      } else if (this.checkingState == 'ko') {
        return 'p-button-danger'
      }

      return ''
    },
    checkingLabel(): string {
      if (this.checkingState == 'unchecked') {
        return 'Vérifier la connexion'
      } else if (this.checkingState == 'checking') {
        return 'Vérification en cours'
      } else if (this.checkingState == 'ok') {
        return 'Connexion ok'
      } else if (this.checkingState == 'ko') {
        return 'Connexion ko'
      }

      return ''
    },
    checkingError(): boolean {
      return this.checkingState == 'ko'
    },
    pce(): string {
      return feedTechnicalId(this.feed)
    }
  },
  emits: ['toggleVisible'],
});
