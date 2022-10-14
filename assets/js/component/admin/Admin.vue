<template>
  <div class="parameter admin p-d-grid p-ai-start p-mt-2">
    <div class="p-col-12">
        <h1>Administration</h1>
        <div class="p-grid dash-analyse">
          <div class="p-col-12 p-md-6 p-lg-6">
            <Card class="card-admin">
              <template #title>
                <div class="p-d-flex">
                  <i class="fa-solid fa-users-gear p-mr-4"></i>
                  <p class="p-my-0">Liste des utilisateurs</p>
                </div>
              </template>
              <template #content>
                <Users />
              </template>
            </Card>
          </div>
          <div class="p-col-12 p-md-6 p-lg-6">
            <Card class="card-admin">
              <template #title>
                <div class="p-d-flex">
                  <i class="fa-solid fa-sliders p-mr-4"></i>
                  <p class="p-my-0">Configuration courante</p>
                </div>
              </template>
              <template #content>
                <DataTable :value="configs" stripedRows responsiveLayout="scroll">
                  <Column field="libelle" header="Configuration"></Column>
                  <Column field="valeur" header="Valeur"></Column>
                </DataTable>
                <Message severity="info" :closable="false">
                  Ces paramètres peuvent être changés en surchargeant le fichier <code>.env</code>.
                </Message>
              </template>
            </Card>
          </div>
          <div class="p-col-12">
            <Card class="card-admin">
              <template #title>
                <div class="p-d-flex">
                  <i class="fa-solid fa-file-contract p-mr-4"></i>
                  <p class="p-my-0">Logs</p>
                </div>
              </template>
              <template #content>
                <Logs />
              </template>
            </Card>
          </div>
        </div>
    </div>
  </div>
</template>

<script lang="ts" src="./Admin.ts"></script>
