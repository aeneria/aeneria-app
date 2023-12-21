<template>
  <div class="p-field">
    <div class="p-inputgroup">
      <span
        class="p-inputgroup-addon addon-periode"
        :style="{'background-color': energieColor1}"
      >
        1
      </span>
      <Calendar
        v-model="periode1"
        selectionMode="range"
        :yearNavigator="true"
        :showButtonBar="true"
        @update:modelValue="setSelectedPeriode1"
      />
      <Button
        icon="pi pi-ellipsis-v"
        @click="toggleSelectMenu1"
        aria-haspopup="true"
        aria-controls="overlay_menu_select"
        class="p-button-secondary"
      />
    </div>
  </div>
  <Menu
    id="overlay_menu_select"
    ref="selectMenu1"
    :model="selectMenuItem1"
    :popup="true"
  />
  <div class="p-field">
    <div class="p-inputgroup">
      <span
        class="p-inputgroup-addon addon-periode"
        :style="{'background-color': energieColor2}"
      >
        2
      </span>
      <Calendar
        v-model="periode2"
        selectionMode="range"
        :yearNavigator="true"
        :showButtonBar="true"
        @update:modelValue="setSelectedPeriode2"
      />
      <Button
        icon="pi pi-ellipsis-v"
        @click="toggleSelectMenu2"
        aria-haspopup="true"
        aria-controls="overlay_menu_select"
        class="p-button-secondary"
      />
    </div>
  </div>
  <Menu
    id="overlay_menu_select"
    ref="selectMenu2"
    :model="selectMenuItem2"
    :popup="true"
  />
</template>

<script lang="ts" src="./DoublePeriodeSelect.ts" />

<style lang="scss" >
  .addon-periode {
    color: #ffffff;
    font-size: 1rem;
    font-weight: 700;
    min-width: 2rem;
  }
</style>
