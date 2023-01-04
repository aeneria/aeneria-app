
import { defineComponent } from 'vue';
import SidebarLink from './SidebarLink';

export default defineComponent({
  name: 'SidebarMenu',
  components: {
    SidebarLink,
  },
  props: {
    isMobile: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      sideBarLinks: {
        'home' : {
          to: "/app/",
          icon: "fa-solid fa-house",
          titre: "Accueil",
          label: "Accueil",
        },
        'energie' : {
          to: "/app/energie",
          icon: "fa-solid fa-bolt-lightning",
          titre: "La consommation d'énergie en détail",
          label: "Énergie",
        },
        'meteo' : {
          to: "/app/meteo",
          icon: "fa-solid fa-cloud-sun-rain",
          titre: "La météo sous tous les angles",
          label: "Météo",
        },
        'analyse' : {
          to: "/app/analyse",
          icon: "fa-solid fa-magnifying-glass-chart",
          titre: "Analyse croisée énergie/météo",
          label: "Analyse",
        },
        'comparaison' : {
          to: "/app/comparaison",
          icon: "fa-solid fa-scale-balanced",
          titre: "Comparaison de 2 périodes",
          label: "Comparaison",
        },
      }
    }
  },
});
