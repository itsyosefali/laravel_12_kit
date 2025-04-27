<script setup lang="ts">
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import type {
  ColumnFiltersState,
  ExpandedState,
  SortingState,
  VisibilityState,
} from '@tanstack/vue-table'
import { Button } from '@/components/ui/button'
// Use pure JSON data for users
const users = ref([
  { id: '1', name: 'Alice Smith', email: 'alice@example.com', role: 'Admin' },
  { id: '2', name: 'Bob Johnson', email: 'bob@example.com', role: 'User' },
  { id: '3', name: 'Charlie Lee', email: 'charlie@example.com', role: 'Editor' },
])
const loading = ref(false)

function editUser(userId: string) {
  // Implement edit logic here
  alert(`Edit user ${userId}`)
}

function deleteUser(userId: string) {
  users.value = users.value.filter(user => user.id !== userId)
}
</script>

<template>
  <AppLayout>
    <Table>
      <TableCaption>
        A list of your users.
      </TableCaption>
      <TableHeader>
        <TableRow>
          <TableHead class="w-[100px]">ID</TableHead>
          <TableHead>Name</TableHead>
          <TableHead>Email</TableHead>
          <TableHead>Role</TableHead>
          <TableHead>Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow v-if="loading">
          <TableCell colspan="5">Loading...</TableCell>
        </TableRow>
        <TableRow v-else-if="users.length === 0">
          <TableCell colspan="5">No users found.</TableCell>
        </TableRow>
        <TableRow v-for="user in users" :key="user.id">
          <TableCell class="font-medium">{{ user.id }}</TableCell>
          <TableCell>{{ user.name }}</TableCell>
          <TableCell>{{ user.email }}</TableCell>
          <TableCell>{{ user.role }}</TableCell>
          <TableCell>
            <Button size="sm" @click="editUser(user.id)" class="mr-2">Edit</Button>
            <Button size="sm" variant="destructive" @click="deleteUser(user.id)">Delete</Button>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </AppLayout>
</template>