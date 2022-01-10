
// Loading CSS.
import '../css/app.scss';

import { createApp } from 'vue';
import { minWeekDayList, monthList, shortMonthList, shortWeekDayList, weekDayList } from './type/DataValue';
import { store } from './store';
import * as d3 from 'd3';
import App from './component/App';
import PrimeVue from 'primevue/config';
import Tooltip from 'primevue/tooltip';
import { d3LocaleDef } from './component/graphique/d3-helpers';
import { router } from './router';

const rootContainer = document.querySelector("#app")
if (rootContainer) {
  createApp(App)
    .use(router)
    .use(store)
    .use(PrimeVue, {locale: {
      startsWith: 'Commence par',
      contains: 'Contient',
      notContains: 'En contient pas',
      endsWith: 'Finit par',
      equals: 'Égale',
      notEquals: 'Différent de',
      noFilter: 'Pas de filtre',
      lt: 'Plus petit que',
      lte: 'Plus petit ou égale à',
      gt: 'Plus grand que',
      gte: 'Plus grand ou égale à',
      dateIs: 'La date est',
      dateIsNot: 'La dete n\'est pas',
      dateBefore: 'La date est avant',
      dateAfter: 'La date est après',
      clear: 'Effacer',
      apply: 'Appliquer',
      matchAll: 'Tous',
      matchAny: 'Au moins un',
      addRule: 'Ajouter une règle',
      removeRule: 'Supprimer une règle',
      accept: 'Oui',
      reject: 'Non',
      choose: 'Choisir',
      upload: 'Télécharger',
      cancel: 'Annuler',
      dayNames: weekDayList,
      dayNamesShort: shortWeekDayList,
      dayNamesMin: minWeekDayList,
      monthNames: monthList,
      monthNamesShort: shortMonthList,
      today: 'Aujourd\'hui',
      weekHeader: 'Sem',
      firstDayOfWeek: 1,
      dateFormat: 'dd/mm/yy',
      weak: 'Semaine',
      medium: 'Moyen',
      strong: 'Gras',
      passwordPrompt: 'Entrer un mot de passe',
      emptyFilterMessage: 'Aucun résultat',
      emptyMessage: 'Aucune option'
    }})
    .directive('tooltip', Tooltip)
    .mount(rootContainer)

  d3.timeFormatDefaultLocale(d3LocaleDef)
}
