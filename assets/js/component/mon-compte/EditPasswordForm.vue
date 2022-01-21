<template>
  <Dialog
    header="Modifier mon adresse email"
    v-model:visible="visible"
    :style="{width: '650px'}"
    :breakpoints="{'650px': '100vw'}"
    :modal="true" :closable="false"
  >
    <div>
      <div class="p-field p-grid">
        <label for="oldPassword" :class="{'p-col-fixed': true,'p-error':v$.oldPassword.$invalid && submitted}" style="width:320px">
          Mot de passe actuel*&nbsp;:
        </label>
        <div class="p-col">
          <Password
            id="oldPassword"
            v-model="oldPassword"
            :feedback="false"
            :toggleMask="true"
            :class="{'p-invalid':v$.oldPassword.$invalid && submitted}"
          />
        </div>
      </div>
      <div class="p-field p-grid">
        <label for="newPassword" :class="{'p-col-fixed': true,'p-error':v$.newPassword.$invalid && submitted}" style="width:320px">
          Nouveau mot de passe*&nbsp;:
        </label>
        <div class="p-col">
          <Password
            id="newPassword"
            v-model="newPassword"
            :feedback="true"
            :toggleMask="true"
            :class="{'p-invalid':v$.newPassword.$invalid && submitted}"
          />
        </div>
      </div>
      <div class="p-field p-grid">
        <label for="newPassword2" :class="{'p-col-fixed': true,'p-error':v$.newPassword2.$invalid && submitted}" style="width:320px">
          Confirmer votre nouveau mot de passe*&nbsp;:
        </label>
        <div class="p-col p-d-bloc">
          <Password
            id="newPassword2"
            v-model="newPassword2"
            :feedback="false"
            :toggleMask="true"
            :class="{'p-invalid':v$.newPassword2.$invalid && submitted}"
          />
        </div>
      </div>
      <div v-if="submitted && v$.newPassword2.sameAsPassword.$invalid" class="p-field p-grid">
        <div class="p-col-fixed" style="width:320px"></div>
        <small class="p-col p-error">
          Le mot de passe ne correspond pas au premier.
        </small>
      </div>
    </div>
    <template #footer>
        <Button label="Annuler" icon="pi pi-times" @click="closeBasic" class="p-button-text p-button-secondary"/>
        <Button label="Enregistrer" icon="pi pi-check" @click="post(!v$.$invalid)" autofocus class="p-button-success"/>
    </template>
  </Dialog>
</template>

<script lang="ts" src="./EditPasswordForm.ts" />
