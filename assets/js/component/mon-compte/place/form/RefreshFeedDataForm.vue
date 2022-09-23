<template>
  <div class="p-formgroup-inline">
    <div class="p-field">
      <label :for="feed.id" class="">{{ feedLabel(feed) }}</label>
      <Calendar
        :id="feed.id"
        v-model="range"
        selectionMode="range"
        :manualInput="true"
        dateFormat="dd/mm/yy"
        :class="{'p-invalid':v$.$invalid && submitted}"
        placeholder="Sélectionnez une période"
      />
    </div>
    <Button
      label="Rafraichir les données"
      icon="pi pi-refresh"
      @click="post(!v$.$invalid)"
      class="p-button-rounded p-button-secondary"
    />
  </div>
</template>

<script lang="ts" src="./RefreshFeedDataForm.ts" />
