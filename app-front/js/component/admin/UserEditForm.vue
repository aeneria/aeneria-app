<template>
  <Dialog
    :header="'Modifier l\'utilisateur \'' + utilisateur?.username + '\''"
    v-model:visible="visible"
    :style="{width: '650px'}"
    :breakpoints="{'650px': '100vw'}"
    :modal="true" :closable="false"
  >
    <div class="p-field p-grid p-mb-2">
      <label for="name" :class="{'p-col-fixed': true,'p-error':v$.email.$invalid && submitted}" style="width:200px">
        Email&nbsp;:
      </label>
      <div class="p-col p-d-bloc">
        <InputText
          id="name"
          v-model="email"
          :class="{'p-invalid':v$.email.$invalid && submitted}"
        />
      </div>
    </div>
    <div class="p-field p-grid p-mb-2">
      <label for="password" class="p-col-fixed" style="width:200px">
        Mot de passe&nbsp;:
      </label>
      <div class="p-col p-d-bloc">
        <Password
          id="password"
          v-model="password"
          :feedback="false"
          :toggleMask="true"
        />
      </div>
    </div>
    <div class="p-field p-grid p-mb-2">
      <label for="active" class="p-col-fixed" style="width:200px">
        Activé&nbsp;:
      </label>
      <div class="p-col p-d-bloc">
        <InputSwitch
          id="active"
          v-model="active"
        />
      </div>
    </div>
    <div class="p-field p-grid p-mb-2">
      <label for="admin" class="p-col-fixed" style="width:200px">
        Admin&nbsp;:
      </label>
      <div class="p-col p-d-bloc">
        <InputSwitch
          id="admin"
          v-model="admin"
        />
      </div>
    </div>

    <template #footer>
      <Button
        label="Annuler"
        icon="pi pi-times"
        @click="cancelEdition"
        class="p-button-text p-button-rounded p-button-secondary"
      />
      <Button
        label="Enregistrer"
        icon="pi pi-check"
        @click="confirmEdition(!v$.$invalid)"
        autofocus
        class="p-button-rounded p-button-secondary"
      />
    </template>
  </Dialog>
</template>

<script lang="ts" src="./UserEditForm.ts"></script>

<style lang="scss">
.users {
  .true-icon {
    color:#256029
  }
  .false-icon {
    color:#c63737
  }
}
</style>