import { Utilisateur } from "@/type/Utilisateur";
import { postData, queryData } from "@/utils";

export function queryUtilisateurs(limit: number, offset: number): Promise<Array<Utilisateur>> {
  return queryData(`/api/admin/user`, {
    limit: limit,
    offset: offset,
  }).then(data => {
    // return Object.values(data)
    if (!data) {
      return null
    }
    return JSON.parse(data)
  })
}

export function queryUtilisateurCount(): Promise<number> {
  return queryData(`/api/admin/user/count`)
}

export function queryLogs(): Promise<any> {
  return queryData(`/api/admin/log`)
}

export function postUserAdd(
  email: null|string,
  password: null|string,
  isActive: null|boolean,
  isAdmin: null|boolean
): Promise<any> {
  return postData(
    `/api/admin/user/add`,
    {
      email: email,
      password: password,
      isActive: isActive,
      isAdmin: isAdmin,
    },
    'POST',
    []
  )
}

export function postUserUpdate(
  userId: null|number,
  email: null|string,
  password: null|string,
  isActive: null|boolean,
  isAdmin: null|boolean
): Promise<any> {
  return postData(
    `/api/admin/user/${userId}/update`,
    {
      email: email,
      password: password,
      isActive: isActive,
      isAdmin: isAdmin,
    },
    'POST',
    []
  )
}

export function postUserDisable(userId: number, yesIamSure: boolean): Promise<any> {
  return postData(
    `/api/admin/user/${userId}/disable`,
    {
      yesIamSure: yesIamSure,
    },
    'POST',
    []
  )
}

export function postUserEnable(userId: number, yesIamSure: boolean): Promise<any> {
  return postData(
    `/api/admin/user/${userId}/enable`,
    {
      yesIamSure: yesIamSure,
    },
    'POST',
    []
  )
}

export function postUserDelete(userId: number, yesIamSure: boolean): Promise<any> {
  return postData(
    `/api/admin/user/${userId}/delete`,
    {
      yesIamSure: yesIamSure,
    },
    'POST',
    []
  )
}
