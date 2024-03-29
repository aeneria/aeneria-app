import { Configuration } from "@/type/Configuration";
import { Place } from "@/type/Place";
import { Notification } from "@/type/Notification";
import { Utilisateur } from "@/type/Utilisateur";
import { postData, queryData } from "@/utils";

export function queryPlaces(): Promise<Array<Place>> {
  return queryData(`/api/config/places`).then(data => {
    // return Object.values(data)
    if (!data) {
      return null
    }
    return JSON.parse(data, (key, value) => {
      if (['periodeMin', 'periodeMax'].includes(key)) {
        return value ? new Date(value.date) : null
      }
      return value
    })
  })
}

export function queryNotifications(): Promise<Notification[]> {
  return queryData(`/api/config/notifications`).then(data => Object.values(data))
}

export function queryUser(): Promise<Utilisateur> {
  return queryData(`/api/config/user`).then(data => data)
}

export function postUserPassword(oldPassword: string, newPassword: string, newPassword2: string): Promise<any> {
  return postData(
    `/api/config/user/edit-password`,
    {
      oldPassword: oldPassword,
      newPassword: newPassword,
      newPassword2: newPassword2
    },
    'POST',
    []
  )
}

export function postUserEmail(newEmail: string): Promise<any> {
  return postData(
    `/api/config/user/edit-email`,
    {
      newEmail: newEmail,
    },
    'POST',
    []
  )
}

export function postDeleteAccount(password: string, yesIamSure: boolean): void {
  const form = document.createElement('form')
  form.method = 'POST'
  form.action = '/api/config/user/delete'

  const passwordField = document.createElement('input')
  passwordField.type = 'hidden'
  passwordField.name = 'password'
  passwordField.value = password
  form.appendChild(passwordField)

  const IamSureField = document.createElement('input')
  IamSureField.type = 'hidden'
  IamSureField.name = 'yes-i-am-sure'
  IamSureField.value = yesIamSure ? '1' : '0'
  form.appendChild(IamSureField)

  document.body.appendChild(form)
  form.submit()
}

export function queryUsers(): Promise<Array<{id: number, username: string}>> {
  return queryData(`/api/config/users`).then(data => JSON.parse(data))
}

export function queryConfiguration(): Promise<Configuration> {
  return queryData(`/api/config`).then(data => {
    return {
      userMaxPlaces: parseInt(data.userMaxPlaces),
      userCanSharePlace: data.userCanSharePlace === '1',
      userCanFetch: data.userCanFetch === '1',
      userCanExport: data.userCanExport === '1',
      userCanImport: data.userCanImport === '1',
      placeCanBePublic: data.placeCanBePublic === '1',
      proxyForEnedis: data.proxyForEnedis === '1',
      proxyForGrdf: data.proxyForGrdf === '1',
      proxyUrl: data.proxyUrl,
      isDemoMode: data.isDemoMode === '1',
      version: data.version,
    }
  })
}
