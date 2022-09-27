import { Place } from "@/type/Place";
import { postData, queryData } from "@/utils";

export function queryMeteoStationList(): Promise<Array<{key: string, isLabeledStatement: string}>> {
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

export function queryGrdfConsentUrl(placeId: number): Promise<string> {
  return queryData(`api/feed/grdf/consent/${placeId}`, )
}