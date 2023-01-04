import { defineComponent } from 'vue';
import Button from 'primevue/button';
import Divider from 'primevue/divider';
import MenuMonCompte from './MenuMonCompte';
import PlaceSelect from '../selection/PlaceSelect';
import SidebarMenu from './SidebarMenu';
import Sidebar from 'primevue/sidebar';

export default defineComponent({
  name: 'TopbarMobile',
  components: {
    Button,
    Divider,
    MenuMonCompte,
    PlaceSelect,
    Sidebar,
    SidebarMenu,
  },
  data() {
    return {
      displaySidebar: false,
    }
  },
  methods: {
    toggleSidebar() {
      this.displaySidebar = !this.displaySidebar
    }
  },
  watch:{
    $route(){
      this.displaySidebar = false;
    }
}
});
