import { defineComponent } from 'vue';
import Button from 'primevue/button';
import MenuMonCompte from './MenuMonCompte';
import PlaceSelect from '../selection/PlaceSelect';

export default defineComponent({
  name: 'TopbarDesktop',
  components: {
    Button,
    MenuMonCompte,
    PlaceSelect,
  }
});
