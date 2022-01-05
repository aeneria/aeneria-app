import { useScreen, useGrid } from 'vue-screen'

export const screen = useScreen()
export const grid = useGrid({
  sm: '640px',
  md: '720px',
  lg: '960px',
  xl: '1080px',
})