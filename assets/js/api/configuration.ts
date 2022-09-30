import { Configuration } from "@/type/Configuration";
import { Place } from "@/type/Place";
import { Notification } from "@/type/Notification";
import { Utilisateur } from "@/type/Utilisateur";
import { postData, queryData } from "@/utils";

export function queryPlaces(): Promise<Place[]> {
  return queryData(`/api/config/places`).then(data => Object.values(data))
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

export function postUserDelete(password: string, yesIamSure: boolean): void {
  const form = document.createElement('form')
  form.method = 'POST'
  form.action = '/delete-account'

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

export function queryConfiguration(): Promise<Configuration> {
  return queryData(`/api/config`).then(data => {
    return {
      userMaxPlaces: parseInt(data.userMaxPlaces),
      userCanSharePlace: data.userCanSharePlace === '1',
      userCanFetch: data.userCanFetch === '1',
      userCanExport: data.userCanExport === '1',
      userCanImport: data.userCanImport === '1',
      placeCanBePublic: data.placeCanBePublic === '1',
      isDemoMode: data.isDemoMode === '1',
      version: data.version,
    }
  })
}
