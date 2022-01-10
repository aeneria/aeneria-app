import { createRouter, createWebHistory } from 'vue-router';
import Admin from './component/admin/Admin';
import Analyse from './component/dashboard/Analyse';
import About from './component/misc/APropos';
import Comparaison from './component/dashboard/Comparaison';
import Energie from './component/dashboard/Energie';
import Home from './component/dashboard/Home';
import Meteo from './component/dashboard/Meteo';
import MonCompte from './component/mon-compte/MonCompte';


export const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/app/', component: Home },
    { path: '/app/energie', component: Energie },
    { path: '/app/meteo', component: Meteo },
    { path: '/app/analyse', component: Analyse },
    { path: '/app/comparaison', component: Comparaison },
    { path: '/app/mon-compte', component: MonCompte },
    { path: '/app/admin', component: Admin },
    { path: '/app/a-propos', component: About },
  ],
})
