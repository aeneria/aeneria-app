export interface Configuration {
  userMaxPlaces: number
  userCanSharePlace: boolean
  userCanFetch: boolean
  userCanExport: boolean
  userCanImport: boolean
  placeCanBePublic: boolean
  proxyForEnedis: boolean
  proxyForGrdf: boolean
  proxyUrl: string
  isDemoMode: boolean
  version: string
}
