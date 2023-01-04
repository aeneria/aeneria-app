import { Place } from "@/type/Place";
import { handleFetchError, postData, url } from "@/utils";
import { timeFormat } from 'd3';

export function postPlaceCreate(name: string, meteo: string): Promise<Place> {
  return postData(
    `/api/place/create`,
    {
      name: name,
      meteo: meteo,
    },
    'POST',
    []
  )
}

export function postPlaceEdit(placeId: string, name: string, isPublic: null|boolean, allowedUsers: null|Array<string>): Promise<any> {
  return postData(
    `/api/place/edit`,
    {
      placeId: placeId,
      name: name,
      public: isPublic,
      allowedUsers: allowedUsers,
    },
    'POST',
    []
  )
}

export function postPlaceDelete(placeId: string): Promise<any> {
  return postData(
    `/api/place/delete`,
    {
      placeId: placeId,
    },
    'POST',
    []
  )
}

export function postPlaceDataExport(placeId: string, start: null|Date, end: null|Date): void {
  const form = document.createElement('form')
  form.method = 'POST'
  form.action = '/api/place/data/export'

  const placeIdField = document.createElement('input')
  placeIdField.type = 'hidden'
  placeIdField.name = 'placeId'
  placeIdField.value = placeId
  form.appendChild(placeIdField)

  const startField = document.createElement('input')
  startField.type = 'hidden'
  startField.name = 'start'
  startField.value = start !== null ? timeFormat("%d/%m/%Y")(start) : ''
  form.appendChild(startField)

  const endField = document.createElement('input')
  endField.type = 'hidden'
  endField.name = 'end'
  endField.value = end !== null ? timeFormat("%d/%m/%Y")(end) : ''
  form.appendChild(endField)

  document.body.appendChild(form)
  form.submit()
}

export function postPlaceDataImport(placeId: string, file: File): Promise<any> {

  var data = new FormData()
  data.append('placeId', placeId)
  data.append('file', file)

  return fetch(url(`/api/place/data/import`, []), {
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

export function postPlaceDataRefresh(placeId: string, feedId: string, start: Date, end: Date): Promise<any> {
  return postData(
    `/api/place/data/refresh`,
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