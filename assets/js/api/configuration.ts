import { Place } from "@/type/Place";
import { queryData } from "@/utils";

export function queryPlaces(): Promise<Place[]> {
  return queryData(`/api/config/places`).then(data => Object.values(data));
}
