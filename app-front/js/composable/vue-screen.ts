import { useScreen, useGrid } from 'vue-screen'

export const screen = useScreen()
export const grid = useGrid({
  sm: '576px',
  md: '768px',
  lg: '992px',
  xl: '1200px',
})
