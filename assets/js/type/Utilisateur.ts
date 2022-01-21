import { Place } from "./Place";

export interface Utilisateur {
  id: number
  active: boolean,
  username: string,
  roles: string[],
  places: Place[],
}
