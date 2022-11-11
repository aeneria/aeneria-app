import { postUserEmail, postUserPassword, queryConfiguration, queryNotifications, queryPlaces, queryUser } from '@/api/configuration'
import { postFeedMeteoUpdate, queryEnedisConsentUrl, queryGrdfConsentUrl } from '@/api/feed'
import { postPlaceCreate, postPlaceDataExport, postPlaceDataImport, postPlaceDataRefresh, postPlaceDelete, postPlaceName } from '@/api/place'
import { Place } from '@/type/Place'
import { State } from 'vue'
import { createStore } from 'vuex'
import { INIT_CONFIGURATION, INIT_SELECTION, PLACE_CREATE, PLACE_DELETE, PLACE_EDIT_METEO, PLACE_EDIT_NOM, PLACE_EXPORT_DATA, PLACE_IMPORT_DATA, PLACE_REFRESH_DATA, USER_UPDATE_EMAIL, USER_UPDATE_PASSWORD } from './actions'
import { RESET_NOTIFICATIONS, SET_CONFIGURATION, SET_DISCONNECTED, SET_PLACE_LIST, SET_USER } from './mutations'
import { moduleSelection, persistSelectionPlugin } from './modules/selection'
import { ToastMessageOptions } from 'primevue/toast'

export const store = createStore({
  state: {
    configuration: null,
    initialized: false,
    utilisateur: null,
    hasNoPlace: null as null|boolean,
    placeList: new Array<Place>(),
    notifications: new Array<ToastMessageOptions>(),
    isDisconnected: false,
  } as State,
  getters: {
    onlyOnePlace: (state) => state.placeList.length <= 1,
    isAdmin: (state) => state?.utilisateur?.roles.includes('ROLE_ADMIN') ?? false,
    isDemoMode: (state) => state.configuration ? state.configuration.isDemoMode : true,
  },
  mutations: {
    [SET_CONFIGURATION] (state, data) {
      state.configuration = data
    },
    [SET_USER] (state, data) {
      state.utilisateur = data
    },
    [SET_PLACE_LIST] (state, placeList) {
      state.placeList = placeList
      if (placeList.length === 0) {
        state.hasNoPlace = true
      }
    },
    [RESET_NOTIFICATIONS] (state) {
      state.notifications = []
    },
    [SET_DISCONNECTED] (state) {
      state.isDisconnected = true
    },
  },
  actions: {
    [INIT_CONFIGURATION] ({commit, dispatch}) {
      queryConfiguration()
      .then(data => {
        commit(SET_CONFIGURATION, data)
      })
      .then(() => queryUser())
      .then(data => {
        commit(SET_USER, data)
      })
      .then(() => queryPlaces())
      .then(placeList => {
        commit(SET_PLACE_LIST, placeList)

        return dispatch(INIT_SELECTION)
      })
      .then(() => {
        this.state.initialized = true
      })

      // Les notifications ne sont pas essentielles,
      // ça peut être fait en asynchrone
      queryNotifications().then( (data) => {
        for(const notification of data) {
          if (notification.level === 'danger') {
            notification.level = 'error'
          } else if (notification.level === 'success') {
            notification.level = 'success'
          } else {
            notification.level = 'info'
          }

          this.state.notifications.push({
            severity: notification.level,
            summary: "Notification du système",
            detail: notification.message
          })
        }
      })

    },
    [USER_UPDATE_PASSWORD] ({}, data) {
      postUserPassword(data.oldPassword, data.newPassword, data.newPassword2)
      this.state.notifications.push({
        severity:'success',
        summary: "Votre modification a été enregistrée.",
        detail: `Votre mot de passe a été correctement mis à jour.`
      })
    },
    [USER_UPDATE_EMAIL] ({commit}, data) {
      postUserEmail(data.newEmail).then(() => {
        queryUser().then(data => {
          commit(SET_USER, data)
        })
      })
      this.state.notifications.push({
        severity:'success',
        summary: "Votre modification a été enregistrée.",
        detail: `Votre adresse e-mail est désormais ${data.newEmail}.`
      })
    },
    [PLACE_CREATE] ({}, data) {
      postPlaceCreate(data.name, data.meteo.key).then((place) => {
        if(data.type == 'enedis') {
          return queryEnedisConsentUrl(place.id)
        } else if (data.type == 'grdf') {
          return queryGrdfConsentUrl(place.id)
        }

        throw new Error("Le choix ne peut être qu'enedis ou grdf.")
      }).then(url => {
        location.href = url
      })
    },
    [PLACE_EDIT_METEO] ({commit}, data) {
      postFeedMeteoUpdate(data.placeId, data.meteo.key).then(() => {
        queryUser().then(data => {
          commit(SET_USER, data)
        })
        this.state.notifications.push({
          severity:'success',
          summary: "L'adresse a été correctement mise à jour.",
          detail: `Elle utilisera maintenant les données de la station ${data.meteo.label}.`
        })
      })
    },
    [PLACE_EDIT_NOM] ({dispatch, commit}, data) {
      postPlaceName(data.placeId, data.newName).then(() => {
        queryUser().then(data => {
          dispatch(INIT_CONFIGURATION)
          commit(SET_USER, data)
        })
      }).then(() => {
        this.state.notifications.push({
          severity:'success',
          summary: "L'adresse a été correctement mise à jour.",
          detail: `Son nom est maintenant ${data.newName}.`
        })
      })
    },
    [PLACE_DELETE] ({dispatch, commit}, data) {
      postPlaceDelete(data.placeId, ).then(() => {
        dispatch(INIT_CONFIGURATION)
        queryUser().then(data => {
          commit(SET_USER, data)
        })
      }).then(() => {
        this.state.notifications.push({severity:'success', summary: "L'adresse a été correctement supprimée."})
      })
    },
    [PLACE_EXPORT_DATA] ({}, data) {
      postPlaceDataExport(data.placeId, data.start, data.end)
    },
    [PLACE_IMPORT_DATA] ({}, data) {
      postPlaceDataImport(data.placeId, data.file).then(() => {
        this.state.notifications.push({
          severity:'info',
          summary: "L'import a été programmé",
          detail: `Il s'effectuera dans les prochaines minutes`
        })
      })
    },
    [PLACE_REFRESH_DATA] ({}, data) {
      postPlaceDataRefresh(data.placeId, data.feedId, data.start, data.end).then(() => {
        this.state.notifications.push({
          severity:'info',
          summary: "Le rafraissement des données a été programmé",
          detail: `Il s'effectuera dans les prochaines minutes`
        })
      })
    },
  },
  modules: {
    selection: moduleSelection
  },
  plugins: [
    persistSelectionPlugin
  ]
})
