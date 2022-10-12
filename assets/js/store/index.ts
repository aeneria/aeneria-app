import { postUserDelete, postUserEmail, postUserPassword, queryConfiguration, queryNotifications, queryPlaces, queryUser } from '@/api/configuration'
import { postFeedMeteoUpdate, queryEnedisConsentUrl, queryGrdfConsentUrl } from '@/api/feed'
import { postPlaceCreate, postPlaceDataExport, postPlaceDataImport, postPlaceDataRefresh, postPlaceDelete, postPlaceName } from '@/api/place'
import { DataType, FeedDataType, getFeedDataType, isFeedDataEnergie } from '@/type/FeedData'
import { Place } from '@/type/Place'
import { State } from 'vue'
import { createStore } from 'vuex'
import { INIT_CONFIGURATION, PLACE_CREATE, PLACE_DELETE, PLACE_EDIT_METEO, PLACE_EDIT_NOM, PLACE_EXPORT_DATA, PLACE_IMPORT_DATA, PLACE_REFRESH_DATA, USER_DELETE_ACCOUNT, USER_UPDATE_EMAIL, USER_UPDATE_PASSWORD } from './actions'
import { SET_CONFIGURATION, SET_PLACE_LIST, SET_SELECTED_ENERGIE, SET_SELECTED_GRANULARITE, SET_SELECTED_METEO_DATA, SET_SELECTED_PERIODE, SET_SELECTED_PLACE, SET_USER } from './mutations'
import { ToastServiceMethods } from "primevue/toastservice";
import { granulariteList } from '@/type/Granularite'
// import VuexPersistence from 'vuex-persist'

const lastMonth = new Date();
lastMonth.setMonth(lastMonth.getMonth() -1)
const now = new Date();
const beforelastMonth = new Date();
beforelastMonth.setMonth(beforelastMonth.getMonth() -2)

// const vuexLocal = new VuexPersistence<State>({
//   storage: window.localStorage
// })

export const store = (toastService: ToastServiceMethods) => createStore({
  state: {
    toast: toastService,
    configuration: null,
    initialized: false,
    utilisateur: null,
    hasNoPlace: null as null|boolean,
    placeList: new Array<Place>(),
    selection: {
      place: null,
      periode: [lastMonth, now],
      periode2: [beforelastMonth, lastMonth],
      energie: null,
      meteoData: null,
      granularite: granulariteList[0],
    },
  } as State,
  getters: {
    onlyOnePlace: (state) => state.placeList.length <= 1,
    onlyOneEnergie: (state, getters) => getters.feedDataTypeEnergieList?.length <= 1,
    isAdmin: (state) => state?.utilisateur?.roles.includes('ADMIN') ?? false,
    isDemoMode: (state) => state.configuration ? state.configuration.isDemoMode : true,
    feedDataTypeEnergieList (state) {
      return getFeedDataTypeEnergieList(state)
    },
    selectedEnergieFeedDataId: (state) => {
      if (!(state.selection.place && state.selection.energie)) {
        return null
      }

      for(const feed of state.selection.place.feedList) {
        for(const feedData of feed.feedDataList) {
          if (state.selection.energie.id == feedData.type) {
            return feedData.id
          }
        }
      }

      return null
    },
    selectedMeteoFeedDataId: (state) => {
      if (!(state.selection.place && state.selection.meteoData)) {
        return null
      }

      return selectedMeteoFeedDataId(state, state.selection.meteoData.id)
    },
    selectedTemperatureFeedDataId: state => selectedMeteoFeedDataId(state, DataType.Temperature),
    selectedDjuFeedDataId: state => selectedMeteoFeedDataId(state, DataType.Dju),
    selectedNebulosityFeedDataId: state => selectedMeteoFeedDataId(state, DataType.Nebulosity),
    selectedRainFeedDataId: state => selectedMeteoFeedDataId(state, DataType.Rain),
    selectedHumidityFeedDataId: state => selectedMeteoFeedDataId(state, DataType.Humidity),
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
    [SET_SELECTED_PLACE] (state, place) {
      state.selection.place = place
      // @todo faire ça de manière plus fine !
      state.selection.periode = [lastMonth, now]
      state.selection.periode = [beforelastMonth, lastMonth]

      if (place) {
        const energieList = getFeedDataTypeEnergieList(state)
        if (energieList.length) {
          state.selection.energie = energieList[0]
        }
      }
    },
    [SET_SELECTED_ENERGIE] (state, feedDataType) {
      state.selection.energie = feedDataType
    },
    [SET_SELECTED_METEO_DATA] (state, feedDataType) {
      state.selection.meteoData = feedDataType
    },
    [SET_SELECTED_GRANULARITE] (state, granularite) {
      state.selection.granularite = granularite
    },
    [SET_SELECTED_PERIODE] (state, periode) {
      state.selection.periode = periode
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

        // On sélectionne une place
        if (!this.state.selection.place) {
          commit(SET_SELECTED_PLACE, placeList[0] ?? null)
        }

        // On présélectionne les DJU
        if (!this.state.selection.meteoData) {
          commit(SET_SELECTED_METEO_DATA, getFeedDataType(DataType.Dju))
        }
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

          this.state.toast.add({
            severity: notification.level,
            summary: "Notification du système",
            detail: notification.message
          })
        }
      })

    },
    [USER_UPDATE_PASSWORD] ({}, data) {
      postUserPassword(data.oldPassword, data.newPassword, data.newPassword2)
      this.state.toast.add({
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
      this.state.toast.add({
        severity:'success',
        summary: "Votre modification a été enregistrée.",
        detail: `Votre adresse e-mail est désormais ${data.newEmail}.`
      })
    },
    [USER_DELETE_ACCOUNT] ({}, data) {
      postUserDelete(data.password, data.yesIamSure)
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
        this.state.toast.add({
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
        this.state.toast.add({
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
        this.state.toast.add({severity:'success', summary: "L'adresse a été correctement supprimée."})
      })
    },
    [PLACE_EXPORT_DATA] ({}, data) {
      postPlaceDataExport(data.placeId, data.start, data.end)
    },
    [PLACE_IMPORT_DATA] ({}, data) {
      postPlaceDataImport(data.placeId, data.file).then(() => {
        this.state.toast.add({
          severity:'info',
          summary: "L'import a été programmé",
          detail: `Il s'effectuera dans les prochaines minutes`
        })
      })
    },
    [PLACE_REFRESH_DATA] ({}, data) {
      postPlaceDataRefresh(data.placeId, data.feedId, data.start, data.end).then(() => {
        this.state.toast.add({
          severity:'info',
          summary: "Le rafraissement des données a été programmé",
          detail: `Il s'effectuera dans les prochaines minutes`
        })
      })
    },
  },
  // plugins: [vuexLocal.plugin],
})

function selectedMeteoFeedDataId(state: State, dataType: DataType): null|Number {
  if (!(state.selection.place)) {
    return null
  }

  for(const feed of state.selection.place.feedList) {
    for(const feedData of feed.feedDataList) {
      if (dataType == feedData.type) {
        return feedData.id
      }
    }
  }

  return null
}

function getFeedDataTypeEnergieList (state: State) {
  let ret = new Array<FeedDataType>()

  if (state.selection.place && state.selection.place.feedList) {
    for(const feed of state.selection.place.feedList) {
      for(const feedData of feed.feedDataList)
      if (isFeedDataEnergie(feedData)) {
        ret.push(getFeedDataType(feedData.type))
      }
    }

    if (ret.length > 1) {
      ret.push(getFeedDataType(DataType.ConsoEnergie))
    }
  }

  return ret
}
