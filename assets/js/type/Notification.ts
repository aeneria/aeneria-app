import { Place } from "./Place"
import { Utilisateur } from "./Utilisateur"

export interface Notification {
  id: number
  type: string
  level: string
  user: Utilisateur
  place: Place|null
  date: Date
  message: string
}
