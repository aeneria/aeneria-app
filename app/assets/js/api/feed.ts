import { Place } from "@/type/Place";
import { postData, queryData } from "@/utils";

export function queryMeteoStationList(): Promise<Array<{key: string, label: string}>> {
  return queryData(`/api/feed/meteo/station-list`).then(data => Object.values(data))
}

export function postFeedMeteoUpdate(placeId: number, meteo: string): Promise<Place> {
    return postData(
      `/api/feed/meteo/update/${placeId}`,
      {
        meteo: meteo,
      },
      'POST',
      []
    )
  }

export function queryEnedisConsentUrl(placeId: number): Promise<string> {
  return queryData(`api/feed/enedis/consent/${placeId}`, )
}

export function queryEnedisConsentCheck(placeId: number): Promise<any> {
  return queryData(`api/feed/enedis/consent/${placeId}/check`, )
}

export function queryGrdfConsentUrl(placeId: number): Promise<string> {
  return queryData(`api/feed/grdf/consent/${placeId}`, )
}

export function queryGrdfConsentCheck(placeId: number): Promise<any> {
  return queryData(`api/feed/grdf/consent/${placeId}/check`, )
}
