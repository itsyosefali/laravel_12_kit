<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import dayjs from 'dayjs'
import axios from 'axios'
import { toast } from 'vue-sonner'
import { Toaster } from 'vue-sonner'

import { Checkbox } from '@/components/ui/checkbox'
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
import { Label } from '@/components/ui/label'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import {
Dialog,
DialogContent,
DialogDescription,
DialogFooter,
DialogHeader,
DialogTitle,
DialogTrigger,
} from '@/components/ui/dialog'
import AppLayout from '@/layouts/AppLayout.vue'
import { BreadcrumbItem } from '@/types'
import { router } from '@inertiajs/vue3'

interface User {
  id: string
  name: string
  email: string
  roles: string[]  
  created_at: string
  updated_at: string
}
interface Role {
  id: number
  name: string
  guard_name: string
  created_at: string
  updated_at: string
}
function formatDate(dateString: string) {
  return dayjs(dateString).format('YYYY-MM-DD HH:mm')
}

// const users = ref<User[]>([])
const loading = ref(false)
const error = ref<Error | null>(null)
const deletingUserId = ref<string | null>(null)
const searchQuery = ref('')
const sortByField = ref('id')
const sortOrder = ref<'asc' | 'desc'>('asc')
const page = ref(1)
const limit = ref(10)
const editDialogOpen = ref(false)
const deleteDialogOpen = ref(false)
const currentUser = ref<User | null>(null)
const isSubmitting = ref(false)
const selectedroles = ref<number[]>([])

const props = defineProps<{
  users: Array<{
    id: string
    name: string
    email: string
    roles: string[]
    created_at: string
    updated_at: string
  }>
  roles: Array<{
    id: number
    name: string
  }>
}>()

const users = ref(props.users)
const availableroles = ref(props.roles)
console.log(props.users);

onMounted(() => { 
  users.value = props.users
  
});

// function fetchUsers() {

//   loading.value = true
//   error.value = null
//   axios.get('/api/users')
//     .then(({ data }) => {
//       users.value = Array.isArray(data) ? data : data?.users ?? []
//     })
//     .catch(err => {
//       error.value = err as Error
//       toast.error('Failed to load users')
//     })
//     .finally(() => {
//       loading.value = false
//     })
// }

// onMounted(fetchUsers)

// TODO:: API FROM SERVER-SIDE ONLY

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


function openEditDialog(user: any) {
  currentUser.value = { ...user }
  selectedroles.value = availableroles.value
    .filter(r => currentUser.value!.roles.includes(r.name))
    .map(r => r.id)
  editDialogOpen.value = true
}

function updateUser() {
  if (!currentUser.value) return
  isSubmitting.value = true
  
  router.put(`/users/${currentUser.value.id}`, {
    name: currentUser.value.name,
    email: currentUser.value.email,
    roles: selectedroles.value,
  }, {
    onSuccess: () => {
      const updatedUser = {
        ...currentUser.value,
        roles: availableroles.value
          .filter(r => selectedroles.value.includes(r.id))
          .map(r => r.name)
      }
      users.value = users.value.map(u => 
        u.id === updatedUser.id ? updatedUser : u
      )
      toast.success('User updated successfully')
      editDialogOpen.value = false
    },
    onError: (errors) => {
      toast.error(Object.values(errors).join('\n'))
    },
    onFinish: () => {
      isSubmitting.value = false
    }
  })
}
function deleteUser() {
  if (!currentUser.value) return
  isSubmitting.value = true
  
  router.delete(`/users/${currentUser.value.id}`, {
    onSuccess: () => {
      users.value = users.value.filter(u => u.id !== currentUser.value!.id)
      toast.success('User deleted successfully')
      deleteDialogOpen.value = false
    },
    onError: () => {
      toast.error('Failed to delete user')
    },
    onFinish: () => {
      isSubmitting.value = false
    }
  })
}

function openDeleteDialog(user: User) {
  currentUser.value = { ...user }
  deleteDialogOpen.value = true
}
function openAddDialog() {
  router.visit('/users/create')
}
function roleVariant(role: string) {
  switch (role) {
    case 'Admin': return 'default'
    case 'Editor': return 'secondary'
    default: return 'outline'
  }
}

function toggleRole(id: number) {
    if (selectedroles.value.includes(id)) {
      selectedroles.value = selectedroles.value.filter(x => x !== id)
    } else {
      selectedroles.value.push(id)
    }
  }
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Users Management', href: '/users' }
]
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Toaster position="top-center" />
        <Dialog v-model:open="editDialogOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Edit User</DialogTitle>
          <DialogDescription>Update user information below.</DialogDescription>
        </DialogHeader>

        <div class="space-y-4" v-if="currentUser">
          <div class="space-y-2">
            <Label>Name</Label>
            <Input v-model="currentUser.name" />
          </div>

          <div class="space-y-2">
            <Label>Email</Label>
            <Input v-model="currentUser.email" type="email" />
          </div>

          <div class="space-y-2">
            <Label>Roles</Label>
            <div v-for="role in availableroles" :key="role.id" class="flex items-center">
              <Checkbox 
              :id="`perm-${role.id}`"
              :value="role.id"
              :checked="selectedroles.includes(role.id)"
              @click="() => toggleRole(role.id)"
            />
              <div class="ml-2">
                <Badge :variant="roleVariant(role.name)" class="text-sm">
                  {{ role.name }}
                </Badge>
              </div>  
            </div>
          </div>
        </div>

        <DialogFooter>
          <Button variant="outline" @click="editDialogOpen = false">Cancel</Button>
          <Button @click="updateUser" :disabled="isSubmitting">
            Save
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <Dialog v-model:open="deleteDialogOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Delete User</DialogTitle>
          <DialogDescription>
            Are you sure you want to delete this user? This action cannot be undone.
          </DialogDescription>
        </DialogHeader>

        <DialogFooter>
          <Button variant="outline" @click="deleteDialogOpen = false">Cancel</Button>
          <Button variant="destructive" @click="deleteUser" :disabled="isSubmitting">
            Delete
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Main Content -->
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
              <div class="flex flex-wrap gap-1">
                <Badge 
                  v-for="(role, index) in user.roles" 
                  :key="index" 
                  variant="outline" 
                  class="text-sm"
                >
                  {{ role }}
                </Badge>
                <span v-if="user.roles.length === 0" class="text-muted-foreground text-sm">
                  No roles assigned
                </span>
              </div>
            </TableCell>
              <TableCell>{{ formatDate(user.created_at) }}</TableCell>
              <TableCell>{{ formatDate(user.updated_at) }}</TableCell>
              <TableCell class="text-right">
                <div class="flex justify-end gap-2">
                  <Button variant="outline" size="sm" @click="openEditDialog(user)">
                    <PencilIcon class="h-4 w-4 mr-1" />
                    Edit
                  </Button>
                  <Button variant="destructive" size="sm" @click="openDeleteDialog(user)">
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
          Showing {{ filteredUsers.length }} of {{ users.length }} users
        </div>
        <div class="flex items-center gap-2">
          <Button variant="outline" size="sm" @click="previousPage" :disabled="page === 1">
            Previous
          </Button>
          <Button variant="outline" size="sm" @click="nextPage" :disabled="(page * limit) >= users.length">
            Next
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>