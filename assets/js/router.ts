import { createRouter, createWebHistory } from 'vue-router';
import Admin from './component/admin/Admin';
import Analyse from './component/dashboard/Analyse';
import About from './component/misc/APropos';
import Comparaison from './component/dashboard/Comparaison';
import Energie from './component/dashboard/Energie';
import Home from './component/dashboard/Home';
import Meteo from './component/dashboard/Meteo';
import MonCompte from './component/mon-compte/MonCompte';
import NewPlace from './component/mon-compte/place/form/new/NewPlace';
import CallbackEnedis from './component/mon-compte/place/CallbackEnedis';
import CallbackGrdf from './component/mon-compte/place/CallbackGrdf';


export const router = createRouter({
  history: createWebHistory(),
  routes: [
    { name: 'home', path: '/app/', component: Home },
    { name: 'energie', path: '/app/energie', component: Energie },
    { name: 'meteo', path: '/app/meteo', component: Meteo },
    { name: 'analyse', path: '/app/analyse', component: Analyse },
    { name: 'comparaison', path: '/app/comparaison', component: Comparaison },
    { name: 'mon-compte', path: '/app/mon-compte', component: MonCompte },
    { name: 'new-place', path: '/app/mon-compte/nouvelle-adresse', component: NewPlace },
    { name: 'callback-enedis', path: '/app/mon-compte/callback/enedis/:placeId', component: CallbackEnedis },
    { name: 'callback-grdf', path: '/app/mon-compte/callback/grdf/:placeId', component: CallbackGrdf },
    { name: 'admin', path: '/app/admin', component: Admin },
    { name: 'about', path: '/app/a-propos', component: About },
  ],
})
