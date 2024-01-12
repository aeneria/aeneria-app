import { Place } from "@/type/Place";
import { handleFetchError, postData, queryData, url } from "@/utils";
import { timeFormat } from "d3";

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

export function postFeedDataImport(placeId: string, feedId: string, file: File): Promise<any> {

  var data = new FormData()
  data.append('placeId', placeId)
  data.append('feedId', feedId)
  data.append('file', file)

  return fetch(url(`/api/feed/data/import`, []), {
    method: 'POST',
    mode: 'cors',
    cache: 'default',
    credentials: 'include',
    body: data,
  })
  .then(response => {
    if (response.ok) {
      return response.json()
    }

    return handleFetchError(response)
  })
}

export function postFeedDataRefresh(placeId: string, feedId: string, start: Date, end: Date): Promise<any> {
  return postData(
    `/api/feed/data/refresh`,
    {
      placeId: placeId,
      feedId: feedId,
      start: timeFormat("%d/%m/%Y")(start),
      end: timeFormat("%d/%m/%Y")(end),
    },
    'POST',
    []
  )
}