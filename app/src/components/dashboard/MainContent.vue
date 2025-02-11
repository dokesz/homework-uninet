<template>
  <div class="main-content">
    <div class="navbar">
      <button class="sign-out-button" @click="handleSignOut">Kijelentkezés</button>
    </div>

    <div class="content">
      <h1>Üdv a Dashboard-on</h1>
      <template v-if="forbidden">
        <p>Hozzáférés megtagadva</p>
      </template>
      <template v-else>
        <DataTable
          :value="data"
          v-model:filters="filters"
          filterDisplay="menu"
          :globalFilterFields="['name', 'email']"
          sortField="name"
          :sortOrder="1"
          paginator
          :rows="20"
          :rowsPerPageOptions="[5, 10, 20, 50]"
          editMode="row"
          dataKey="id"
          tableStyle="min-width: 50rem"
        >
          <template #header>
            <div class="search-bar">
              <IconField>
                <InputText
                  v-model="filters['global'].value"
                  placeholder="Keresés (név vagy email)"
                />
              </IconField>
              <Button type="button" label="Filter törlés" outlined @click="clearFilter()" />
            </div>
          </template>

          <Column field="name" header="Name" sortable style="width: 30%">
            <template #body="{ data }">
              {{ data.name }}
            </template>
            <template #filter="{ filterModel }">
              <InputText v-model="filterModel.value" type="text" placeholder="Search by name" />
            </template>
          </Column>
          <Column field="email" header="Email" style="width: 30%">
            <template #filter="{ filterModel }">
              <InputText v-model="filterModel.value" type="text" placeholder="Search by email" />
            </template>
          </Column>
          <Column field="permissions" header="Jogosultságok" style="width: 30%">
            <template #body="slotProps">
              <ul>
                <li v-for="permission in slotProps.data.permissions" :key="permission">
                  {{ getPermissionLabel(permission) }}
                </li>
              </ul>
            </template>
          </Column>
          <Column :exportable="false" style="min-width: 8rem">
            <template #body="slotProps">
              <Button
                v-if="slotProps.data.name !== 'Admin'"
                icon="pi pi-pencil"
                outlined
                rounded
                class="mr-2 data-table-button"
                @click="editUser(slotProps.data)"
              >
                <Pencil />
              </Button>
              <Button
                v-if="slotProps.data.name !== 'Admin'"
                icon="pi pi-trash"
                outlined
                rounded
                severity="danger"
                class="data-table-button"
                @click="confirmDeleteUser(slotProps.data)"
              >
                <Trash />
              </Button>
            </template>
          </Column>
        </DataTable>
      </template>

      <Dialog
        v-model:visible="deleteUserDialog"
        :style="{ width: '450px' }"
        header="Felhasználó törlése"
        :modal="true"
      >
        <div class="flex items-center gap-4">
          <i class="pi pi-exclamation-triangle !text-3xl" />
          <span v-if="currentRow"
            >Biztosan törölni szeretnéd a(z) <b>{{ currentRow.name }}</b> felhasználót?</span
          >
        </div>
        <template #footer>
          <Button label="Nem" text @click="deleteUserDialog = false" />
          <Button label="Igen" @click="handleDeleteUser" />
        </template>
      </Dialog>

      <Dialog
        header="Felhasználó szerkesztés"
        v-model:visible="editDialogVisible"
        :modal="true"
        :closable="true"
      >
        <div class="modal-content">
          <div class="modal-field">
            <label for="name">Név</label>
            <InputText id="name" v-model="currentRow.name" />
          </div>
          <div class="modal-field">
            <label for="email">Email</label>
            <InputText id="email" v-model="currentRow.email" />
          </div>
          <div class="modal-field">
            <label for="permissions">Jogosultságok</label>
            <Tree
              v-model:selectionKeys="selectedKey"
              :value="permissionsTree"
              selectionMode="checkbox"
              class="w-full md:w-[30rem]"
            ></Tree>
          </div>
        </div>
        <div class="modal-footer">
          <Button
            label="Save"
            icon="pi pi-check"
            class="modal-button p-button-success"
            @click="saveEdit"
          >
            Save
          </Button>
          <Button
            label="Cancel"
            icon="pi pi-times"
            class="modal-button p-button-secondary"
            @click="cancelEdit"
          >
            Cancel
          </Button>
        </div>
      </Dialog>
    </div>
  </div>
</template>

<script setup>
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'
import Dialog from 'primevue/dialog'
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import Tree from 'primevue/tree'
import IconField from 'primevue/iconfield'
import InputIcon from 'primevue/inputicon'
import { useRouter } from 'vue-router'
import { logout } from '../../api/logout'
import { listUsers, updateUser, deleteUser } from '../../api/usersCrud'
import { handleError, forbidden } from '../../api/utils/errorHandlers'
import { ref, onMounted } from 'vue'
import { Pencil, Trash } from 'lucide-vue-next'

const router = useRouter()
const data = ref([])
const editDialogVisible = ref(false)
const deleteUserDialog = ref(false)
const selectedKey = ref({})
const currentRow = ref({})
const permissionsTree = ref([
  {
    key: 'all',
    label: 'Minden jog',
    children: [
      {
        key: 'users',
        label: 'Felhasználók',
        children: [
          { key: '1001', label: 'Felhasználók olvasása' },
          { key: '1002', label: 'Felhasználók írása' },
        ],
      },
    ],
  },
])

const filters = ref({
  global: { value: null, matchMode: 'contains' },
  name: { value: null, matchMode: 'contains' },
  email: { value: null, matchMode: 'contains' },
})

const clearFilter = () => {
  filters.value = {
    global: { value: null, matchMode: 'contains' },
    name: { value: null, matchMode: 'contains' },
    email: { value: null, matchMode: 'contains' },
  }
}

const permissionLabels = {
  1001: 'Felhasználók olvasása',
  1002: 'Felhasználók írása',
  1000: 'Felhasználók',
}

const getPermissionLabel = (key) => {
  return permissionLabels[key] || key
}

const getSelectedPermissions = () => {
  return Object.keys(selectedKey.value).filter(
    (key) => key.startsWith('1001') || key.startsWith('1002'),
  )
}

onMounted(async () => {
  try {
    const res = await listUsers()
    data.value = res.people
  } catch (error) {
    if (error.response && error.response.status === 401) {
      router.push('/')
    } else if (error.response && error.response.status === 403) {
      forbidden.value = true
    }
  }
})

const confirmDeleteUser = (user) => {
  currentRow.value = user
  deleteUserDialog.value = true
}

const handleDeleteUser = async () => {
  try {
    const res = await deleteUser(currentRow.value)
    data.value = data.value.filter((val) => val.id !== currentRow.value.id)
    deleteUserDialog.value = false
    currentRow.value = {}
  } catch (error) {
    handleError(error, router)
  }
}

const editUser = (user) => {
  const permissions = user.permissions || []
  currentRow.value = { ...user, permissions }

  const selectionKeys = {}
  const hasRead = permissions.includes(1001)
  const hasWrite = permissions.includes(1002)

  if (hasRead || hasWrite) {
    if (hasRead) selectionKeys['1001'] = { checked: true, partialChecked: false }
    if (hasWrite) selectionKeys['1002'] = { checked: true, partialChecked: false }

    selectionKeys['users'] = {
      checked: hasRead && hasWrite,
      partialChecked: hasRead !== hasWrite,
    }

    selectionKeys['all'] = {
      checked: hasRead && hasWrite,
      partialChecked: hasRead !== hasWrite,
    }
  }

  selectedKey.value = selectionKeys
  editDialogVisible.value = true
}

const saveEdit = async () => {
  const selectedPermissions = getSelectedPermissions()
  currentRow.value.permissions = selectedPermissions

  try {
    const response = await updateUser(currentRow.value)
    if (response) {
      const res = await listUsers()
      data.value = res.people
    }
  } catch (error) {
    handleError(error, router)
  }

  currentRow.value = {}
  selectedKey.value = {}
  editDialogVisible.value = false
}

const handleSignOut = () => {
  logout()
  router.push('/')
}

const cancelEdit = () => {
  editDialogVisible.value = false
}
</script>

<style scoped>
.main-content {
  flex: 1;
  display: flex;
  flex-direction: column;
}

.navbar {
  display: flex;
  justify-content: flex-end;
  padding: 10px 20px;
  background-color: #2c3e50;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.search-bar {
  display: flex;
  justify-content: space-between;
}

.sign-out-button {
  padding: 8px 16px;
  background-color: #e74c3c;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
}

.sign-out-button:hover {
  background-color: #c0392b;
}

.content {
  padding: 20px;
  background-color: #f9f9f9;
  color: #333333;
  flex: 1;
}

.modal-content {
  display: flex;
  flex-direction: column;
}

.modal-field {
  margin-bottom: 1rem;
  display: flex;
  flex-direction: column;
}

.modal-footer {
  display: flex;
  justify-content: center;
  gap: 1rem;
  padding: 0;
}

.modal-button {
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 2rem;
  cursor: pointer;
}

.data-table-button {
  margin-right: 0.5rem;
}
</style>
