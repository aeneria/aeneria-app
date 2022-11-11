import { ToastMessageOptions } from 'primevue/toast';
import { ToastServiceMethods } from 'primevue/toastservice';
import { Store } from 'vuex'
import { Configuration } from './Configuration';
import { Place } from './Place';
import { Selection } from './Selection';
import { Utilisateur } from './Utilisateur';

declare module '@vue/runtime-core' {
  interface State {
    toast: ToastServiceMethods
    initialized: boolean
    configuration: null|Configuration
    utilisateur: null|Utilisateur
    hasNoPlace: null|boolean
    placeList: Array<Place>
    selection: Selection
    notifications: Array<ToastMessageOptions>
    isDisconnected: boolean
  }

  // provide typings for `this.$store`
  interface ComponentCustomProperties {
    $store: Store<State>
  }
}
