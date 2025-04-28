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
import { Button } from '@/components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import AppLayout from '@/layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'

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
function openEditDialog(_role: Role) {}
function confirmDelete(_role: Role) {}
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

</script>

<template>
  <Head title="Dashboard" />
  <AppLayout :breadcrumbs="breadcrumbs">
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