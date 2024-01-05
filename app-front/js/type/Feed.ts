import { FeedData } from "./FeedData"

export enum DataProvider {
  linky = 'LINKY',
  enedisDataConnect = 'ENEDIS_DATA_CONNECT',
  enedisDataConnectProxified = 'ENEDIS_DATA_CONNECT_PROXIFIED',
  grdfAdict = 'GRDF_ADICT',
  grdfAdictProxified = 'GRDF_ADICT_PROXIFIED',
  meteoFrance = 'METEO_FRANCE',
  fake = 'FAKE',
}

export enum FeedType {
  electricity = 'ELECTRICITY',
  gaz = 'GAZ',
  meteo = 'METEO',
}

export interface Feed {
  id: number
  name: string
  type: string
  dataProvider: DataProvider
  param: any[]
  frequencies: number[]
  feedDataList: FeedData[]
  fetchError: number
  createdAt: Date
  updatedAt: Date
}

export function feedLabelLong(feed: Feed): string {
  switch(feed.dataProvider) {
    case DataProvider.linky :
      return 'Compte Enedis&nbsp;: ' + feed.param['LOGIN']
    case DataProvider.enedisDataConnect :
    case DataProvider.enedisDataConnectProxified :
      return 'Linky&nbsp;: PDL&nbsp;-&nbsp;'+ JSON.parse(feed.param['ADDRESS'])?.usagePointId
    case DataProvider.grdfAdict :
    case DataProvider.grdfAdictProxified :
      return 'Gazpar&nbsp;: PCE&nbsp;-&nbsp;'+ feed.param['PCE']
    case DataProvider.meteoFrance :
      return ''+ feed.param['CITY']
    case DataProvider.fake :
      return 'FakeProvider&nbsp;: '+ feed.type
    default :
      return ''
  }
}

export function feedLabelShort(feed: Feed): string {
  switch(feed.dataProvider) {
    case DataProvider.linky :
      return 'Compteur Enedis'
    case DataProvider.enedisDataConnect :
    case DataProvider.enedisDataConnectProxified :
      return 'Compteur Linky'
    case DataProvider.grdfAdict :
    case DataProvider.grdfAdictProxified :
      return 'Compteur Gazpar'
    case DataProvider.meteoFrance :
      return 'Météo France'
    case DataProvider.fake :
      return 'FakeProvider'
    default :
      return ''
  }
}

export function feedTechnicalId(feed: Feed): string {
  switch(feed.dataProvider) {
    case DataProvider.linky :
      return 'N/A'
    case DataProvider.enedisDataConnect :
    case DataProvider.enedisDataConnectProxified :
      return JSON.parse(feed.param['ADDRESS'])?.usagePointId
    case DataProvider.grdfAdict :
    case DataProvider.grdfAdictProxified :
      return feed.param['PCE']
    case DataProvider.meteoFrance :
      return 'N/A'+ feed.param['CITY']
    case DataProvider.fake :
      return 'N/A'
    default :
      return ''
  }
}

export function feedIcon(feed: Feed): string {
  switch(feed.dataProvider) {
    case DataProvider.linky :
      return 'fa-solid fa-tachometer-alt'
    case DataProvider.enedisDataConnect :
    case DataProvider.enedisDataConnectProxified :
      return 'fa-solid fa-plug'
    case DataProvider.grdfAdict :
    case DataProvider.grdfAdictProxified :
      return 'fa-solid fa-fire'
    case DataProvider.meteoFrance :
      return 'fa-solid fa-cloud-sun'
    case DataProvider.fake :
      return 'fa-solid fa-code'
    default :
      return ''
  }
}

export function feedDescription(feed: Feed): string {
  switch(feed.dataProvider) {
    case DataProvider.linky :
      return 'Compteur Enedis associé'
    case DataProvider.enedisDataConnect :
    case DataProvider.enedisDataConnectProxified :
      return 'Compteur Linky associé'
    case DataProvider.grdfAdict :
    case DataProvider.grdfAdictProxified :
      return 'Compteur Gazpar associé'
    case DataProvider.meteoFrance :
      return 'Station météo associée'
    case DataProvider.fake :
      return 'Fake'
    default :
      return ''
  }
}
