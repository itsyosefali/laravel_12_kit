<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'
import { toast } from 'vue-sonner'
import {
  ArrowUpDownIcon,
  LoaderCircle,
  AlertCircleIcon,
  PlusIcon,
  PencilIcon,
  TrashIcon,
  ShieldIcon,
} from 'lucide-vue-next'
import {
Dialog,
DialogContent,
DialogDescription,
DialogFooter,
DialogHeader,
DialogTitle,
DialogTrigger,
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import AppLayout from '@/layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'
import { Toaster } from 'vue-sonner'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import { Checkbox } from '@/components/ui/checkbox'
interface Permission {
  id: number
  name: string
  guard_name: string
  created_at: string
  updated_at: string
}

interface Role {
  id: number
  name: string
  guard_name: string
  created_at: string
  updated_at: string
  permissions: Permission[]
}

const roles = ref<Role[]>([])
const loading = ref(false)
const error = ref<Error | null>(null)
const sortByField = ref('id')
const sortOrder = ref<'asc' | 'desc'>('asc')
const searchQuery = ref('')
const page = ref(1)
const limit = ref(10)
const editDialogOpen = ref(false)
const deleteDialogOpen = ref(false)
const currentRole = ref<Role | null>(null)
const availablePermissions = ref<Permission[]>([])
const selectedPermissions = ref<number[]>([])
const isSubmitting = ref(false)
const breadcrumbs = [
  { title: 'Roles Management', href: '/roles' }
]
const filteredRoles = computed(() => {
  let result = [...roles.value]
  if (searchQuery.value) {
    result = result.filter(role =>
      role.name.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }
  result.sort((a, b) => {
    const valA = (a as any)[sortByField.value]
    const valB = (b as any)[sortByField.value]
    if (valA < valB) return sortOrder.value === 'asc' ? -1 : 1
    if (valA > valB) return sortOrder.value === 'asc' ? 1 : -1
    return 0
  })
  const start = (page.value - 1) * limit.value
  const end = start + limit.value
  return result.slice(start, end)
})
const sortedRoles = computed(() => {
  let result = [...roles.value]
  result.sort((a, b) => {
    const valA = (a as any)[sortByField.value]
    const valB = (b as any)[sortByField.value]
    if (valA < valB) return sortOrder.value === 'asc' ? -1 : 1
    if (valA > valB) return sortOrder.value === 'asc' ? 1 : -1
    return 0
  })
  return result
})

onMounted(async () => {
  try {
    loading.value = true
    const { data } = await axios.get('/api/roles')
    roles.value = Array.isArray(data) ? data : data?.roles ?? []
  } catch (err) {
    error.value = err as Error
    toast.error('Failed to load roles')
  } finally {
    loading.value = false
  }
})


function sortBy(field: string) {
  if (sortByField.value === field) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortByField.value = field
    sortOrder.value = 'asc'
  }
}

function openAddDialog() {
    router.visit('/roles/create')
}
function openEditDialog(role: Role) {
  currentRole.value = { ...role }
  // This should pre-populate selectedPermissions from role.permissions
  selectedPermissions.value = role.permissions.map(p => Number(p.id))
  fetchPermissions().then(() => {
    editDialogOpen.value = true
  })
}
function confirmDelete(role: Role) {
  currentRole.value = role
  deleteDialogOpen.value = true
}
function handleSearch() {
  page.value = 1
}
function previousPage() {
  if (page.value > 1) page.value--
}
function nextPage() {
  const totalCount = roles.value.length
  if (page.value * limit.value < totalCount) page.value++
}
async function fetchRoles() {
    try {
        loading.value = true
        const { data } = await axios.get('/api/roles')
        roles.value = Array.isArray(data) ? data : data?.roles ?? []
    } catch (err) {
        error.value = err as Error
        toast.error('Failed to load roles')
    } finally {
        loading.value = false
    }
}

async function updateRole() {
  if (!currentRole.value) return
  isSubmitting.value = true
  try {
    const payload = {
      name: currentRole.value.name,
      permissions: selectedPermissions.value, 
    }
    await axios.put(`/api/roles/${currentRole.value.id}`, payload)
    toast.success('Role updated successfully')
    await fetchRoles()
    editDialogOpen.value = false
  } catch (err) {
    toast.error('Failed to update role')
  } finally {
    isSubmitting.value = false
  }
}
async function deleteRole() {
    if (!currentRole.value) return
    isSubmitting.value = true
    try {
        await axios.delete(`/api/roles/${currentRole.value.id}`)
        toast.success('Role deleted successfully')
        await fetchRoles()
        deleteDialogOpen.value = false
    } catch (err) {
        toast.error('Failed to delete role')
    } finally {
        isSubmitting.value = false
    }
}

async function fetchPermissions() {
    try {
        const { data } = await axios.get('/api/roles/permissions')
        availablePermissions.value = Array.isArray(data)
            ? data
            : data?.permissions ?? []
    } catch (err) {
        toast.error('Failed to load permissions')
    }
}

function togglePermission(id: number) {
  console.log('hi bitch by MOAD')
    if (selectedPermissions.value.includes(id)) {
      selectedPermissions.value = selectedPermissions.value.filter(x => x !== id)
      console.log(selectedPermissions);
      
    } else {
      selectedPermissions.value.push(id)
    }
    console.log(selectedPermissions);
    
  }
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Toaster position="top-center" />
<Dialog v-model:open="editDialogOpen">
  <DialogContent>
    <DialogHeader>
      <DialogTitle>Edit Role</DialogTitle>
      <DialogDescription>
        Modify role details below. Click save when you're done.
      </DialogDescription>
    </DialogHeader>
    
    <div class="space-y-4" v-if="currentRole">
      <div class="space-y-2">
        <Label>Role Name</Label>
        <Input v-model="currentRole.name" />
      </div>
      
      <div class="space-y-2">
        <Label>Permissions</Label>
        <div class="grid grid-cols-2 gap-2 max-h-64 overflow-y-auto p-2 border rounded">
    <div
      v-for="perm in availablePermissions"
      :key="perm.id"
      class="flex items-center gap-2"
    >
      <Checkbox 
        :id="`perm-${perm.id}`"
        :value="perm.id"
        :checked="selectedPermissions.includes(perm.id)"
        @click="() => togglePermission(perm.id)"
      />
      <Label :for="`perm-${perm.id}`" class="text-sm font-normal">
        {{ perm.name }}
      </Label>
    </div>
  </div>
      </div>
    </div>

    <DialogFooter>
      <Button variant="outline" @click="editDialogOpen = false">Cancel</Button>
      <Button @click="updateRole" :disabled="isSubmitting">
        <LoaderCircle v-if="isSubmitting" class="h-4 w-4 mr-2 animate-spin" />
        Save Changes
      </Button>
    </DialogFooter>
  </DialogContent>
</Dialog>

<Dialog v-model:open="deleteDialogOpen">
  <DialogContent>
    <DialogHeader>
      <DialogTitle>Confirm Delete</DialogTitle>
      <DialogDescription>
        Are you sure you want to delete this role? This action cannot be undone.
      </DialogDescription>
    </DialogHeader>
    
    <div class="flex flex-col gap-4" v-if="currentRole">
      <div class="bg-destructive/10 p-4 rounded">
        <p class="text-destructive font-medium">
          Deleting role: {{ currentRole.name }}
        </p>
      </div>
    </div>

    <DialogFooter>
      <Button variant="outline" @click="deleteDialogOpen = false">Cancel</Button>
      <Button variant="destructive" @click="deleteRole" :disabled="isSubmitting">
        <LoaderCircle v-if="isSubmitting" class="h-4 w-4 mr-2 animate-spin" />
        Confirm Delete
      </Button>
    </DialogFooter>
  </DialogContent>
</Dialog>
    <div class="space-y-4">
      <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
        <input
          v-model="searchQuery"
          @input="handleSearch"
          placeholder="Search roles..."
          class="max-w-[400px] border rounded px-3 py-2"
        />
        <Button @click="openAddDialog">
          <PlusIcon class="h-4 w-4 mr-2" />
          Add Role
        </Button>
      </div>

      <div class="rounded-md border overflow-auto">
        <Table class="min-w-[700px]">
          <TableHeader class="bg-muted/50">
            <TableRow>
              <TableHead class="w-[80px] cursor-pointer" @click="sortBy('id')">
                ID
                <ArrowUpDownIcon class="h-4 w-4 ml-1 inline-block" />
              </TableHead>
              <TableHead class="cursor-pointer" @click="sortBy('name')">
                Role Name
                <ArrowUpDownIcon class="h-4 w-4 ml-1 inline-block" />
              </TableHead>
              <TableHead>
                Permissions
              </TableHead>
              <TableHead class="cursor-pointer" @click="sortBy('created_at')">
                Created
                <ArrowUpDownIcon class="h-4 w-4 ml-1 inline-block" />
              </TableHead>
              <TableHead class="w-[180px] text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-if="loading">
              <TableCell colspan="5" class="text-center py-12">
                <div class="flex items-center justify-center gap-2">
                  <LoaderCircle class="h-6 w-6 animate-spin" />
                  <span class="text-lg">Loading roles...</span>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-else-if="error">
              <TableCell colspan="5" class="text-center py-12 text-destructive">
                <div class="flex flex-col items-center gap-4">
                  <AlertCircleIcon class="h-12 w-12" />
                  <div class="text-lg font-medium">
                    Error loading roles: {{ error.message }}
                  </div>
                  <Button @click="onMounted">Retry</Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow v-else-if="!sortedRoles.length">
              <TableCell colspan="5" class="text-center py-12">
                <div class="flex flex-col items-center gap-4">
                  <ShieldIcon class="h-24 w-24 text-muted-foreground" />
                  <div class="text-xl font-medium">No roles found</div>
                  <Button @click="openAddDialog">Add New Role</Button>
                </div>
              </TableCell>
            </TableRow>
            <TableRow
              v-for="role in filteredRoles"
              :key="role.id"
              class="hover:bg-muted/50"
            >
              <TableCell class="font-medium">{{ role.id }}</TableCell>
              <TableCell>{{ role.name }}</TableCell>
              <TableCell>
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="perm in role.permissions"
                    :key="perm.id"
                    class="inline-block bg-muted px-2 py-1 rounded text-xs"
                  >
                    {{ perm.name }}
                  </span>
                </div>
              </TableCell>
              <TableCell>{{ role.created_at.slice(0, 10) }}</TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <Button
                    variant="outline"
                    size="sm"
                    @click="openEditDialog(role)"
                  >
                    <PencilIcon class="h-4 w-4 mr-1" />
                    Edit
                  </Button>
                  <Button
                    variant="destructive"
                    size="sm"
                    @click="confirmDelete(role)"
                  >
                    <TrashIcon class="h-4 w-4 mr-1" />
                    Delete
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <div class="flex flex-col sm:flex-row gap-4 items-center justify-between px-2">
        <div class="text-sm text-muted-foreground">
          Showing {{ filteredRoles.length }} of {{ roles.length }} roles
        </div>
        <div class="flex items-center gap-2">
          <Button
            variant="outline"
            size="sm"
            @click="previousPage"
            :disabled="page === 1"
          >
            Previous
          </Button>
          <Button
            variant="outline"
            size="sm"
            @click="nextPage"
            :disabled="(page * limit) >= roles.length"
          >
            Next
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>