import { Configuration } from "@/type/Configuration";
import { Place } from "@/type/Place";
import { Utilisateur } from "@/type/Utilisateur";
import { postData, queryData } from "@/utils";

export function queryPlaces(): Promise<Place[]> {
  return queryData(`/api/config/places`).then(data => Object.values(data));
}

export function queryUser(): Promise<Utilisateur> {
  return queryData(`/api/config/user`).then(data => data);
}

export function postUserPassword(oldPassword: string, newPassword: string, newPassword2: string): Promise<Utilisateur> {
  return postData(
    `/api/config/user/edit-password`,
    {
      oldPassword: oldPassword,
      newPassword: newPassword,
      newPassword2: newPassword2
    },
    'POST',
    []
  );
}

export function queryConfiguration(): Promise<Configuration> {
  return queryData(`/api/config`).then(data => data);
}
