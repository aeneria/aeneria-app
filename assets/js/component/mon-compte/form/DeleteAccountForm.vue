<template>
  <Dialog
    header="Supprimer mon compte æneria"
    v-model:visible="visible"
    :style="{width: '650px'}"
    :breakpoints="{'650px': '100vw'}"
    :modal="true" :closable="false"
  >
    <Message severity="error" :closable="false">
      Vous êtes sur le point de supprimer votre compte, <strong>cette action est irréversible</strong>.
    </Message>
    <Message severity="warn" :closable="false">
      <p>Avant de vous lancer, avez-vous pensé à exporter vos données ?</p>
      <p>On ne sait jamais, si vous voulez revenir un jour, vous pourrez toujours les réimporter !</p>
    </Message>

    <div class="p-d-flex p-jc-center">
      <label for="password" class="p-mr-2">Mot de passe</label>
      <Password
        id="password"
        v-model="password"
        :feedback="false"
        :toggleMask="true"
        :class="{'p-invalid':v$.password.$invalid && submitted}"
      />
    </div>
    <div class="p-d-flex p-jc-center p-mt-2">
      <Checkbox id="yes-i-am-sure" v-model="yesIamSure" :binary="true" />
      <label for="yes-i-am-sure" class="p-ml-2">Je suis sûr·e et certain·ne de vouloir supprimer mon compte</label>
    </div>

    <template #footer>
      <Button
        label="Annuler"
        icon="pi pi-times"
        @click="closeBasic"
        class="p-button-text p-button-rounded p-button-secondary"
      />
      <Button
        label="Supprimer mon compte"
        icon="pi pi-check"
        @click="confirmation"
        :disabled="!v$.$invalid"
        autofocus
        class="p-button-rounded p-button-danger"
      />
    </template>
  </Dialog>
</template>

<script lang="ts" src="./DeleteAccountForm.ts" />
