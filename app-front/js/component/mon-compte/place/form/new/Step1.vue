<template>
  <div class="parameter p-d-block">
    <p>Avant de lui associer un compteur Linky et/ou un compteur Gazpar, choisissez un nom et une station d'observation Météo France pour votre adresse.</p>

    <div class="p-field p-grid">
      <label for="name" :class="{'p-col-fixed': true,'p-error':v$.nameValue.$invalid && submitted}" style="width:320px">
        Nom&nbsp;:
      </label>
      <div class="p-col p-d-bloc">
        <InputText
          id="name"
          v-model="nameValue"
          :class="{'p-invalid':v$.nameValue.$invalid && submitted}"
        />
      </div>
    </div>
    <div class="p-field p-grid">
      <label for="meteo" :class="{'p-col-fixed': true,'p-error':v$.meteoValue.$invalid && submitted}" style="width:320px">
        Station météo&nbsp;:
      </label>
      <div class="p-col p-d-bloc">
        <Dropdown
          id="meteo"
          v-model="meteoValue"
          :options="meteoOptionList"
          optionLabel="label"
          :filter="true"
          :class="{'p-invalid':v$.meteoValue.$invalid && submitted}"
        ></Dropdown>
      </div>
    </div>
    <div class="p-d-flex p-jc-end">
      <Button
        label="Annuler"
        icon="pi pi-times"
        @click="cancel"
        class="p-button-text p-button-rounded p-button-secondary"
      />
      <Button
        label="Suivant"
        icon="pi pi-arrow-right"
        :disabled="v$.$invalid"
        @click="next()"
        autofocus
        class="p-button-rounded p-button-secondary"
      />
    </div>
  </div>
</template>

<script lang="ts" src="./Step1.ts" />
