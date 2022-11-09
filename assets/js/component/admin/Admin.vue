<template>
  <div class="parameter admin p-grid p-ai-start p-mt-2">
    <div class="p-col-12">
      <h1>Administration</h1>
      <TabView>
        <TabPanel header="Configuration courante">
          <DataTable :value="configs" stripedRows responsiveLayout="scroll">
            <Column field="libelle" header="Configuration"></Column>
            <Column field="valeur" header="Valeur"></Column>
          </DataTable>
          <Message severity="info" :closable="false">
            Ces paramètres peuvent être changés en surchargeant le fichier <code>.env</code>.
          </Message>
        </TabPanel>
        <TabPanel header="Liste des utilisateurs">
          <Users />
        </TabPanel>
        <TabPanel header="Logs">
          <Logs />
        </TabPanel>
      </TabView>
    </div>
  </div>
</template>

<script lang="ts" src="./Admin.ts"></script>

<style lang="scss">
.card-admin {
  height: 100%;
}
</style>
