import { defineComponent, ref } from 'vue';
import { mapState } from 'vuex';
import Button from 'primevue/button';
import Card from 'primevue/card';
import Place from './Place';
import Menu from 'primevue/menu';
import Divider from 'primevue/divider';

export default defineComponent({
  name: 'MonCompte',
  components: {
    Button,
    Card,
    Divider,
    Place,
    Menu,
  },
  setup() {
    const menuMonCompte = ref()

    return {
      menuMonCompte
    }
  },
  data() {
    return {
      menuMonCompteItems: [
        {
            label: 'Modifier mon adresse e-mail',
            icon: 'pi pi-pencil',
            command: () => {
              // this.$router.push('/app/a-propos')
            }
        },
        {
            label: 'Modifier mon mot de passe',
            icon: 'pi pi-lock',
            command: () => {
              // window.open('https://docs.aeneria.com', '_blank')
            }
        },
      ]
    }
  },
  computed: {
    ...mapState([
      'placeList',
    ]),
  },
  methods: {
    toggleMenuMonCompte(event) {
      this.menuMonCompte.toggle(event);
    }
  }
});
