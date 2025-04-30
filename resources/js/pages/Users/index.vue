<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import dayjs from 'dayjs'
import { usePage } from '@inertiajs/vue3'
import { toast } from 'vue-sonner'
import { Toaster } from 'vue-sonner'

import { Checkbox } from '@/components/ui/checkbox'
import { Card, CardContent } from '@/components/ui/card'
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
}

function formatDate(dateString: string) {
  return dayjs(dateString).format('YYYY-MM-DD HH:mm')
}

const users = ref<User[]>([])
const availableroles = ref<Role[]>([])
const loading = ref(false)
const error = ref<Error | null>(null)
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

const props = defineProps<{ users: User[]; roles: Role[] }>()
onMounted(() => {
  users.value = props.users
  availableroles.value = props.roles
})

const filteredUsers = computed(() => {
  let list = [...users.value]
  if (searchQuery.value) {
    list = list.filter(u =>
      u.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      u.email.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
  }
  list.sort((a, b) => {
    const va = (a as any)[sortByField.value]
    const vb = (b as any)[sortByField.value]
    return va < vb ? (sortOrder.value === 'asc' ? -1 : 1) : va > vb ? (sortOrder.value === 'asc' ? 1 : -1) : 0
  })
  const start = (page.value - 1) * limit.value
  return list.slice(start, start + limit.value)
})
interface AuthUser {
  user: any
  id: number
  name: string
  email: string
  roles: string[]
}
const authUser = usePage().props.auth as AuthUser
const userRoles = Array.isArray(authUser.user.roles)
  ? authUser.user.roles.map((r: any) => r.name)
  : []
const isAdmin = userRoles.includes('Admin')
function countByRole(name: string) {
  return users.value.filter(u => u.roles.includes(name)).length
}

function handleSearch() { page.value = 1 }
function sortBy(field: string) {
  if (sortByField.value === field) sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  else {
    sortByField.value = field
    sortOrder.value = 'asc'
  }
}
function previousPage() { if (page.value > 1) page.value-- }
function nextPage() { if (page.value * limit.value < users.value.length) page.value++ }

function openEditDialog(user: User) {
  currentUser.value = { ...user }
  selectedroles.value = availableroles.value
    .filter(r => user.roles.includes(r.name))
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
      const updated = {
        ...currentUser.value!,
        roles: availableroles.value.filter(r => selectedroles.value.includes(r.id)).map(r => r.name),
      }
      users.value = users.value.map(u => u.id === updated.id ? updated : u)
      toast.success('User updated')
      editDialogOpen.value = false
    },
    onError: (e) => toast.error(Object.values(e).flat().join('\n')),
    onFinish: () => isSubmitting.value = false,
  })
}
function openDeleteDialog(user: User) {
  currentUser.value = { ...user }
  deleteDialogOpen.value = true
}
function deleteUser() {
  if (!currentUser.value) return
  isSubmitting.value = true
  router.delete(`/users/${currentUser.value.id}`, {
    onSuccess: () => {
      users.value = users.value.filter(u => u.id !== currentUser.value!.id)
      toast.success('Deleted')
      deleteDialogOpen.value = false
    },
    onError: () => toast.error('Delete failed'),
    onFinish: () => isSubmitting.value = false,
  })
}
function openAddDialog() { router.visit('/users/create') }
function toggleRole(id: number) {
  selectedroles.value = selectedroles.value.includes(id)
    ? selectedroles.value.filter(x => x !== id)
    : [...selectedroles.value, id]
}
const breadcrumbs: BreadcrumbItem[] = [ { title: 'Users Management', href: '/users' } ]


</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    
    <Toaster position="top-center" />

    <!-- Edit Dialog -->
    <Dialog v-model:open="editDialogOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Edit User</DialogTitle>
          <DialogDescription>Update details below.</DialogDescription>
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
            <div v-for="r in availableroles" :key="r.id" class="flex items-center">
              <Checkbox :checked="selectedroles.includes(r.id)" @click="() => toggleRole(r.id)" />
              <Badge class="ml-2" variant="outline">{{ r.name }}</Badge>
            </div>
          </div>
        </div>
        <DialogFooter>
          <Button variant="outline" @click="editDialogOpen = false">Cancel</Button>
          <Button @click="updateUser" :disabled="isSubmitting">Save</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Delete Dialog -->
    <Dialog v-model:open="deleteDialogOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Delete User</DialogTitle>
          <DialogDescription>Are you sure? This cannot be undone.</DialogDescription>
        </DialogHeader>
        <DialogFooter>
          <Button variant="outline" @click="deleteDialogOpen = false">Cancel</Button>
          <Button variant="destructive" @click="deleteUser" :disabled="isSubmitting">Delete</Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <div v-if="isAdmin" class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center mb-4">
      <Input v-model="searchQuery" placeholder="Search users..." @update:model-value="handleSearch" class="max-w-[400px]" />
      <Button @click="openAddDialog" ><PlusIcon class="h-4 w-4 mr-2" />Add User</Button>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <Card><CardContent><div class="text-sm text-muted-foreground">Total Users</div><div class="text-2xl font-bold">{{ users.length }}</div></CardContent></Card>
      <Card v-for="r in availableroles" :key="r.id"><CardContent><div class="text-sm text-muted-foreground">{{ r.name }}</div><div class="text-2xl font-bold">{{ countByRole(r.name) }}</div></CardContent></Card>
    </div>

    <div class="rounded-md border overflow-auto">
      <Table class="min-w-[800px]">
        <TableHeader class="bg-muted/50">
          <TableRow>
            <TableHead @click="sortBy('id')" class="cursor-pointer w-[80px]">ID <ArrowUpDownIcon class="h-4 w-4 inline ml-1" /></TableHead>
            <TableHead @click="sortBy('name')" class="cursor-pointer">Name <ArrowUpDownIcon class="h-4 w-4 inline ml-1" /></TableHead>
            <TableHead @click="sortBy('email')" class="cursor-pointer">Email <ArrowUpDownIcon class="h-4 w-4 inline ml-1" /></TableHead>
            <TableHead @click="sortBy('roles')" class="cursor-pointer">Roles <ArrowUpDownIcon class="h-4 w-4 inline ml-1" /></TableHead>
            <TableHead @click="sortBy('created_at')" class="cursor-pointer">Created <ArrowUpDownIcon class="h-4 w-4 inline ml-1" /></TableHead>
            <TableHead @click="sortBy('updated_at')" class="cursor-pointer">Last Modified <ArrowUpDownIcon class="h-4 w-4 inline ml-1" /></TableHead>
            <TableHead class="w-[200px] text-right">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-if="loading"><TableCell colspan="7" class="text-center py-12"><div class="flex justify-center items-center gap-2"><LoaderCircle class="h-6 w-6 animate-spin" /><span>Loading users...</span></div></TableCell></TableRow>
          <TableRow v-else-if="error"><TableCell colspan="7" class="text-center py-12 text-destructive"><div class="flex flex-col items-center gap-4"><AlertCircleIcon class="h-12 w-12" /><div>Error loading: {{ error.message }}</div><Button @click="onMounted">Retry</Button></div></TableCell></TableRow>
          <TableRow v-else-if="!filteredUsers.length"><TableCell colspan="7" class="text-center py-12"><div class="flex flex-col items-center gap-4"><UsersIcon class="h-24 w-24 text-muted-foreground" /><div>No users found</div><Button @click="openAddDialog">Add User</Button></div></TableCell></TableRow>
          <TableRow v-for="u in filteredUsers" :key="u.id" class="hover:bg-muted/50"><TableCell>{{ u.id }}</TableCell><TableCell>{{ u.name }}</TableCell><TableCell>{{ u.email }}</TableCell><TableCell><div class="flex flex-wrap gap-1"><Badge v-for="role in u.roles" :key="role" variant="outline" class="text-sm">{{ role }}</Badge><span v-if="!u.roles.length" class="text-muted-foreground text-sm">No roles</span></div></TableCell><TableCell>{{ formatDate(u.created_at) }}</TableCell><TableCell>{{ formatDate(u.updated_at) }}</TableCell><TableCell class="text-right"><Button size="sm" variant="outline" @click="openEditDialog(u)"><PencilIcon class="h-4 w-4 mr-1"/>Edit</Button><Button size="sm" variant="destructive" @click="openDeleteDialog(u)"><TrashIcon class="h-4 w-4 mr-1"/>Delete</Button></TableCell></TableRow>
        </TableBody>
      </Table>
    </div>

    <!-- Pagination -->
    <div class="flex flex-col sm:flex-row gap-4 items-center justify-between px-2 mt-4">
      <div class="text-sm text-muted-foreground">Showing {{ filteredUsers.length }} of {{ users.length }}</div>
      <div class="flex gap-2"> <Button size="sm" variant="outline" @click="previousPage" :disabled="page===1">Previous</Button> <Button size="sm" variant="outline" @click="nextPage" :disabled="page*limit>=users.length">Next</Button> </div>
    </div>
  </AppLayout>
</template>
