<template>
  <div>
    <Dialog
      header="Supprimer l'adresse"
      v-model:visible="visible"
      :style="{width: '650px'}"
      :breakpoints="{'650px': '100vw'}"
      :modal="true" :closable="false"
    >
      <Message severity="error" :closable="false">
        Attention, cette action entrainera la suppression de <b>TOUTES</b> les données associées à cette adresse.<br>
        Cette action n'est <b>PAS</b> réversible
      </Message>

      <div class="p-fluid">
        <div class="p-field">
          <label for="validation">
            Pour valider le suppression, veuillez recopier le nom de l'adresse ({{ place.name }})
          </label>
          <InputText
            id="validation"
            v-model="confirmationTexte"
            :title="'Pour valider le suppression, veuillez recopier le nom de l\'adresse (' + place.name + ')'"
            :class="{'p-invalid':v$.confirmationTexte.$invalid && submitted}"
          />
          <small v-if="submitted && v$.confirmationTexte.sameAsPlaceName.$invalid" class="p-error">
            La valeur entrée ne correspond pas au nom de l'adresse.
          </small>
        </div>
      </div>

      <template #footer>
          <Button
            label="Annuler"
            icon="pi pi-times"
            @click="closeBasic()"
            class="p-button-text p-button-rounded p-button-secondary"
          />
          <Button
              label="Supprimer l'adresse et TOUTES ses données"
              icon="pi pi-check"
              @click="confirmation(!v$.$invalid)"
              autofocus
              class="p-button-rounded p-button-danger"
            />
      </template>
    </Dialog>
    <ConfirmDialog
      :group="place.id.toString()"
    />
  </div>
</template>

<script lang="ts" src="./DeleteForm.ts" />
