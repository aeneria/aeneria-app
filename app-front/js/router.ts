import { createRouter, createWebHistory } from 'vue-router';
import Admin from '@/page/Admin';
import Analyse from '@/page/dashboard/Analyse';
import About from '@/page/APropos';
import Aide from '@/page/Aide';
import Comparaison from '@/page/dashboard/Comparaison';
import Energie from '@/page/dashboard/Energie';
import Home from '@/page/dashboard/Home';
import Meteo from '@/page/dashboard/Meteo';
import MonCompte from '@/page/MonCompte';
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
