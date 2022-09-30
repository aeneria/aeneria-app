import { postUserDelete, postUserEmail, postUserPassword, queryConfiguration, queryNotifications, queryPlaces, queryUser } from '@/api/configuration'
import { postFeedMeteoUpdate, queryEnedisConsentUrl, queryGrdfConsentUrl } from '@/api/feed'
import { postPlaceCreate, postPlaceDataExport, postPlaceDataImport, postPlaceDataRefresh, postPlaceDelete, postPlaceName } from '@/api/place'
import { DataType, FeedDataType, getFeedDataType, isFeedDataEnergie } from '@/type/FeedData'
import { getGranularite, GranulariteType } from '@/type/Granularite'
import { Place } from '@/type/Place'
import { State } from 'vue'
import { createStore } from 'vuex'
import { INIT_CONFIGURATION, INIT_PLACE_LIST, PLACE_CREATE, PLACE_DELETE, PLACE_EDIT_METEO, PLACE_EDIT_NOM, PLACE_EXPORT_DATA, PLACE_IMPORT_DATA, PLACE_REFRESH_DATA, UPDATE_SELECTED_PLACE, USER_DELETE_ACCOUNT, USER_UPDATE_EMAIL, USER_UPDATE_PASSWORD } from './actions'
import { SET_CONFIGURATION, SET_PLACE_LIST, SET_SELECTED_ENERGIE, SET_SELECTED_GRANULARITE, SET_SELECTED_METEO_DATA, SET_SELECTED_PERIODE, SET_SELECTED_PLACE, SET_USER } from './mutations'
import { ToastServiceMethods } from "primevue/toastservice";

const lastMonth = new Date('2020-02-09');
lastMonth.setMonth(lastMonth.getMonth() -1)
const now = new Date('2021-10-04');

export const store = (toastService: ToastServiceMethods) => createStore({
  state: {
    toast: toastService,
    configuration: null,
    utilisateur: null,
    hasNoPlace: null as null|boolean,
    placeList: new Array<Place>(),
    selectedPlace: null as null|Place,
    selectedPeriode: [lastMonth, now],
    selectedEnergie: null as null|FeedDataType,
    selectedMeteoData: null as null|FeedDataType,
    selectedGranularite: getGranularite(GranulariteType.Jour),
  } as State,
  getters: {
    onlyOnePlace: (state) => state.placeList.length <= 1,
    onlyOneEnergie: (state, getters) => getters.feedDataTypeEnergieList?.length <= 1,
    isAdmin: (state) => state?.utilisateur?.roles.includes('ADMIN') ?? false,
    isDemoMode: (state) => state.configuration ? state.configuration.isDemoMode : true,
    feedDataTypeEnergieList (state) {
      let ret = new Array<FeedDataType>()

      if (state.selectedPlace && state.selectedPlace.feedList) {
        for(const feed of state.selectedPlace.feedList) {
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
    },
    selectedEnergieFeedDataId: (state) => {
      if (!(state.selectedPlace && state.selectedEnergie)) {
        return null
      }

      for(const feed of state.selectedPlace.feedList) {
        for(const feedData of feed.feedDataList) {
          if (state.selectedEnergie.id == feedData.type) {
            return feedData.id
          }
        }
      }

      return null
    },
    selectedMeteoFeedDataId: (state) => {
      if (!(state.selectedPlace && state.selectedMeteoData)) {
        return null
      }

      return selectedMeteoFeedDataId(state, state.selectedMeteoData.id)
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
      state.selectedPlace = place
    },
    [SET_SELECTED_ENERGIE] (state, feedDataType) {
      state.selectedEnergie = feedDataType
    },
    [SET_SELECTED_METEO_DATA] (state, feedDataType) {
      state.selectedMeteoData = feedDataType
    },
    [SET_SELECTED_GRANULARITE] (state, granularite) {
      state.selectedGranularite = granularite
    },
    [SET_SELECTED_PERIODE] (state, periode) {
      state.selectedPeriode = periode
    },
  },
  actions: {
    [INIT_CONFIGURATION] ({commit}) {
      queryConfiguration().then(data => {
        commit(SET_CONFIGURATION, data)
      })
      queryUser().then(data => {
        commit(SET_USER, data)
      })
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
    [INIT_PLACE_LIST] ({commit, dispatch, getters, state}) {
      queryPlaces().then(placeList => {
        commit(SET_PLACE_LIST, placeList)

        // On sélectionne une place
        if (!state.selectedPlace) {
          dispatch(UPDATE_SELECTED_PLACE, state.placeList[0] ?? null)
        }

        // On présélectionne les DJU
        commit(SET_SELECTED_METEO_DATA, getFeedDataType(DataType.Dju))
      })
    },
    [UPDATE_SELECTED_PLACE] ({commit, dispatch, getters, state}, place) {
      commit(SET_SELECTED_PLACE, place)

      if (place) {
        let energie = getFeedDataType(getters.feedDataTypeEnergieList[0].id)
        // let energie = undefined;
        // switch (getters.feedDataTypeEnergieList.length) {
        //   case 0:
        //     energie = null
        //     break
        //   case 1:
        //     energie = getFeedDataType(getters.feedDataTypeEnergieList[0].id)
        //     break
        //   default:
        //     energie = getFeedDataType(DataType.ConsoEnergie)
        // }
        commit(SET_SELECTED_ENERGIE, energie)
      }
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
          dispatch(INIT_PLACE_LIST)
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
        dispatch(INIT_PLACE_LIST)
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
  }
})

function selectedMeteoFeedDataId(state: State, dataType: DataType): null|Number {
  if (!(state.selectedPlace)) {
    return null
  }

  for(const feed of state.selectedPlace.feedList) {
    for(const feedData of feed.feedDataList) {
      if (dataType == feedData.type) {
        return feedData.id
      }
    }
  }

  return null
}
