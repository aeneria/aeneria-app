import { createRouter, createWebHistory } from 'vue-router';
import Admin from './component/admin/Admin';
import Analyse from './component/dashboard/Analyse';
import About from './component/misc/APropos';
import Aide from './component/aide/Aide';
import Comparaison from './component/dashboard/Comparaison';
import Energie from './component/dashboard/Energie';
import Home from './component/dashboard/Home';
import Meteo from './component/dashboard/Meteo';
import MonCompte from './component/mon-compte/MonCompte';
import NewPlace from './component/mon-compte/place/form/new/NewPlace';
import CallbackEnedis from './component/mon-compte/place/CallbackEnedis';
import CallbackGrdf from './component/mon-compte/place/CallbackGrdf';
import NotFound from './component/misc/NotFound';
import { Store } from 'vuex';
import { State } from 'vue';

export const router = (basePath: string, store: Store<State>) => createRouter({
  history: createWebHistory(),
  routes: [
    { name: 'home', path: basePath + '/', component: Home },
    { name: 'energie', path: basePath + '/energie', component: Energie },
    { name: 'meteo', path: basePath + '/meteo', component: Meteo },
    { name: 'analyse', path: basePath + '/analyse', component: Analyse },
    { name: 'comparaison', path: basePath + '/comparaison', component: Comparaison },
    { name: 'mon-compte', path: basePath + '/mon-compte', component: MonCompte },
    { name: 'new-place', path: basePath + '/mon-compte/nouvelle-adresse', component: NewPlace },
    { name: 'callback-enedis', path: basePath + '/mon-compte/callback/enedis/:placeId', component: CallbackEnedis },
    { name: 'callback-grdf', path: basePath + '/mon-compte/callback/grdf/:placeId', component: CallbackGrdf },
    {
      name: 'admin',
      path: basePath + '/admin',
      component: Admin,
      beforeEnter: (to, from) => {
        // reject the navigation
        if (!store.getters.isAdmin) {
          return { name: 'not-found' }
        }

        return true
      },
    },
    { name: 'about', path: basePath + '/a-propos', component: About },
    { name: 'aide', path: basePath + '/aide', component: Aide },
    { name: 'not-found', path: basePath + '/:pathMatch(.*)*', component: NotFound },
  ],
})
