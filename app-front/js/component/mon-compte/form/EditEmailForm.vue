<template>
  <Dialog
    header="Modifier mon adresse email"
    v-model:visible="visible"
    :style="{width: '650px'}"
    :breakpoints="{'650px': '100vw'}"
    :modal="true" :closable="false"
  >
    <Message severity="warn" :closable="false">
      En modifiant cette adresse, vous modifiez votre identifiant de connexion.
      C'est cette nouvelle adresse qu'il vous faudra utiliser pour vous connecter à l'application.
    </Message>

    <div class="p-d-flex p-jc-center">
      <InputText
        id="email"
        type="email"
        v-model="newEmail"
        :class="{'p-invalid':v$.newEmail.$invalid && submitted}"
        :placeholder="utilisateur.username"
      />
    </div>
    <div v-if="submitted && v$.newEmail.email.$invalid" class="p-d-flex p-jc-center">
      <small class="p-error">
        La valeur entrée ne correspond pas à un email.
      </small>
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

<script lang="ts" src="./EditEmailForm.ts" />
