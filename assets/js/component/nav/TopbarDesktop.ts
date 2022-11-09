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
  },
  data() {
    return {
      sideBarLinks: {
        'home' : {
          to: "/app/",
          icon: "fa-solid fa-house",
          titre:"Accueil",
        },
        'energie' : {
          to: "/app/energie",
          icon: "fa-solid fa-bolt-lightning",
          titre:"La consommation d'énergie en détail",
        },
        'meteo' : {
          to: "/app/meteo",
          icon: "fa-solid fa-cloud-sun-rain",
          titre:"La météo sous tous les angles",
        },
        'analyse' : {
          to: "/app/analyse",
          icon: "fa-solid fa-magnifying-glass-chart",
          titre:"Analyse croisée énergie/météo",
        },
        'comparaison' : {
          to: "/app/comparaison",
          icon: "fa-solid fa-scale-balanced",
          titre:"Comparaison de 2 périodes",
        },
      }
    }
  },
});
