<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import dayjs from 'dayjs'
// import { debounce } from 'lodash-es'
import axios from 'axios'
import { toast } from 'vue-sonner'
import {
  ArrowUpDownIcon,
  AlertCircleIcon,
  LoaderCircle,
  PencilIcon,
  PlusIcon,
  TrashIcon,
  UsersIcon,
} from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import AppLayout from '@/layouts/AppLayout.vue'

interface User {
  id: string
  name: string
  email: string
  role: 'Admin' | 'Editor' | 'User'
  created_at: string
  updated_at: string
}

function formatDate(dateString: string) {
  return dayjs(dateString).format('YYYY-MM-DD HH:mm')
}

const users = ref<User[]>([])
const loading = ref(false)
const error = ref<Error | null>(null)
const deletingUserId = ref<string | null>(null)
const searchQuery = ref('')
const sortByField = ref('id')
const sortOrder = ref<'asc' | 'desc'>('asc')
const page = ref(1)
const limit = ref(10)

onMounted(async () => {
  try {
    loading.value = true
    const { data } = await axios.get('/api/users')
    console.log(data);
    
    users.value = Array.isArray(data) ? data : data?.users ?? []
  } catch (err) {
    error.value = err as Error
    toast.error('Failed to load users')
  } finally {
    loading.value = false
  }
})

const filteredUsers = computed(() => {
  let result = [...users.value]

  if (searchQuery.value) {
    result = result.filter(u =>
      u.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      u.email.toLowerCase().includes(searchQuery.value.toLowerCase())
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

function handleSearch() {
  page.value = 1
}
function sortBy(field: string) {
  if (sortByField.value === field) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortByField.value = field
    sortOrder.value = 'asc'
  }
}
function previousPage() {
  if (page.value > 1) page.value--
}
function nextPage() {
  const totalCount = users.value.length
  if (page.value * limit.value < totalCount) page.value++
}
async function confirmDelete(user: User) {
  if (!confirm(`Delete user ${user.name}?`)) return
  try {
    deletingUserId.value = user.id
    await axios.delete(`/api/users/${user.id}`)
    users.value = users.value.filter(u => u.id !== user.id)
    toast.success('User deleted successfully')
  } catch (err) {
    toast.error('Deletion failed')
  } finally {
    deletingUserId.value = null
  }
}
function roleVariant(role: string) {
  switch (role) {
    case 'Admin': return 'default'
    case 'Editor': return 'secondary'
    default: return 'outline'
  }
}
function openAddDialog() {}
// function openEditDialog(_user: User) {}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Users Management', href: '/users' }
]
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-4">
      <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
        <Input
          v-model="searchQuery"
          placeholder="Search users..."
          class="max-w-[400px]"
          @update:model-value="handleSearch"
        />
        <Button @click="openAddDialog">
          <PlusIcon class="h-4 w-4 mr-2" />
          Add User
        </Button>
      </div>

      <div class="rounded-md border overflow-auto">
        <Table class="min-w-[800px]">
          <TableHeader class="bg-muted/50">
            <TableRow>
              <TableHead class="w-[80px] cursor-pointer" @click="sortBy('id')">
                ID
                <ArrowUpDownIcon class="h-4 w-4 ml-1 inline-block" />
              </TableHead>
              <TableHead class="cursor-pointer" @click="sortBy('name')">
                Name
                <ArrowUpDownIcon class="h-4 w-4 ml-1 inline-block" />
              </TableHead>
              <TableHead class="cursor-pointer" @click="sortBy('email')">
                Email
                <ArrowUpDownIcon class="h-4 w-4 ml-1 inline-block" />
              </TableHead>
              <TableHead class="cursor-pointer" @click="sortBy('role')">
                Role
                <ArrowUpDownIcon class="h-4 w-4 ml-1 inline-block" />
              </TableHead>
              <TableHead class="cursor-pointer" @click="sortBy('created_at')">
                Created
                <ArrowUpDownIcon class="h-4 w-4 ml-1 inline-block" />
              </TableHead>
              <TableHead class="cursor-pointer" @click="sortBy('updated_at')">
                Last Modified
                <ArrowUpDownIcon class="h-4 w-4 ml-1 inline-block" />
              </TableHead>
              <TableHead class="w-[200px] text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          
          <TableBody>
            <TableRow v-if="loading">
              <TableCell colspan="7" class="text-center py-12">
                <div class="flex items-center justify-center gap-2">
                  <LoaderCircle class="h-6 w-6 animate-spin" />
                  <span class="text-lg">Loading users...</span>
                </div>
              </TableCell>
            </TableRow>

            <TableRow v-else-if="error">
              <TableCell colspan="7" class="text-center py-12 text-destructive">
                <div class="flex flex-col items-center gap-4">
                  <AlertCircleIcon class="h-12 w-12" />
                  <div class="text-lg font-medium">
                    Error loading users: {{ error.message }}
                  </div>
                  <Button @click="onMounted">Retry</Button>
                </div>
              </TableCell>
            </TableRow>

            <TableRow v-else-if="!filteredUsers.length">
              <TableCell colspan="7" class="text-center py-12">
                <div class="flex flex-col items-center gap-4">
                  <UsersIcon class="h-24 w-24 text-muted-foreground" />
                  <div class="text-xl font-medium">No users found</div>
                  <Button @click="openAddDialog">Add New User</Button>
                </div>
              </TableCell>
            </TableRow>

            <TableRow
              v-for="user in filteredUsers"
              :key="user.id"
              class="hover:bg-muted/50"
            >
              <TableCell class="font-medium">{{ user.id }}</TableCell>
              <TableCell>{{ user.name }}</TableCell>
              <TableCell>{{ user.email }}</TableCell>
              <TableCell>
                <Badge :variant="roleVariant(user.role)" class="text-sm">
                  {{ user.role }}
                </Badge>
              </TableCell>
              <TableCell>{{ formatDate(user.created_at) }}</TableCell>
              <TableCell>{{ formatDate(user.updated_at) }}</TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <Button
                    variant="outline"
                    size="sm"
                    @click="openEditDialog(user)"
                    :disabled="deletingUserId === user.id"
                  >
                    <PencilIcon class="h-4 w-4 mr-1" />
                    Edit
                  </Button>
                  <Button
                    variant="destructive"
                    size="sm"
                    @click="confirmDelete(user)"
                    :disabled="deletingUserId === user.id"
                  >
                    <TrashIcon
                      v-if="deletingUserId !== user.id"
                      class="h-4 w-4 mr-1"
                    />
                    <LoaderCircle
                      v-else
                      class="h-4 w-4 mr-1 animate-spin"
                    />
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
          Showing {{ filteredUsers.length }} of {{ users.length }} users
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
            :disabled="(page * limit) >= users.length"
          >
            Next
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
