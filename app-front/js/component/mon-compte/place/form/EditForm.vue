<template>
  <Dialog
    header="Modifier l'adresse"
    v-model:visible="visible"
    :style="{width: '650px'}"
    :breakpoints="{'650px': '100vw'}"
    :modal="true" :closable="false"
  >
    <div class="p-field p-grid">
      <label for="name" :class="{'p-error':v$.name.$invalid && submitted}" style="width:120px">
        Nom&nbsp;:
      </label>
      <div class="p-col p-d-bloc">
        <InputText
          id="name"
          v-model="name"
          :class="{'p-invalid':v$.name.$invalid && submitted}"
        />
      </div>
    </div>
    <div v-if="configuration.userCanSharePlace" class="p-field p-grid">
      <label for="allowedUsers" class="p-col-fixed" style="width:120px">
        Partagée avec&nbsp;:
      </label>
      <div class="p-col-fixed" >
        <MultiSelect
          id="allowedUsers"
          type="allowedUsers"
          style="width: 25rem;"
          v-model="allowedUsers"
          display="chip"
          :options="userListOption"
          :filter="true"
          optionLabel="username"
        />
      </div>
    </div>
    <div  v-if="configuration.userCanSharePlace" class="p-field p-grid">
      <label for="allowedUsers" class="p-col-fixed" style="width:120px">
        L'adresse est&nbsp;:
      </label>
      <div v-if="configuration.placeCanBePublic" class="p-col-fixed">
        <ToggleButton
          v-model="public"
          onLabel="Publique"
          offLabel="Privée"
          onIcon="pi pi-check"
          offIcon="pi pi-times"
        />
        <Button
          class="p-mx-1 p-button-lg p-button-rounded p-button-text p-button-plain"
          icon="pi pi-question-circle"
          v-tooltip="tooltipPublicPlace"
        />
      </div>
    </div>

    <template #footer>
      <Button
        label="Annuler"
        icon="pi pi-times"
        @click="closeBasic"
        class="p-button-text p-button-rounded p-button-secondary"
      />
      <Button
        label="Enregistrer"
        icon="pi pi-check"
        @click="post(!v$.$invalid)"
        autofocus
        class="p-button-rounded p-button-secondary"
      />
    </template>
  </Dialog>
</template>

<script lang="ts" src="./EditForm.ts" />
