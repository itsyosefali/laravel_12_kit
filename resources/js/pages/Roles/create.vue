<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
import { toast } from 'vue-sonner'
import { Button } from '@/components/ui/button'
import AppLayout from '@/layouts/AppLayout.vue'
import { router } from '@inertiajs/vue3'

interface Permission {
  id: number
  name: string
}

const roleName = ref('')
const permissions = ref<Permission[]>([])
const selectedPermissions = ref<number[]>([])
const loading = ref(false)
const breadcrumbs = [
  { title: 'Create Role', href: '/roles/create' }
]

onMounted(async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/roles/permissions')
    if (Array.isArray(data)) {
      permissions.value = data
    } else if (Array.isArray(data?.permissions)) {
      permissions.value = data.permissions
    } else {
      permissions.value = []
      toast.error('Invalid permissions data')
    }
  } catch (error) {
    toast.error('Failed to load permissions')
    console.error(error)
  } finally {
    loading.value = false
  }
})

async function createRole() {
  try {
    await axios.post('/api/roles/', {
      name: roleName.value,
      permissions: selectedPermissions.value,
    })
    toast.success('Role created!')
    router.visit('/roles') // Redirect after success
  } catch (e: any) {
    toast.error(e?.response?.data?.message || 'Failed to create role')
  }
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="max-w-lg mx-auto mt-10 space-y-6">
      <h1 class="text-2xl font-bold mb-4">Create Role</h1>
      <form @submit.prevent="createRole" class="space-y-4">
        <div>
          <label class="block font-medium mb-1">Role Name</label>
          <input
            v-model="roleName"
            type="text"
            class="w-full border rounded px-3 py-2"
            placeholder="Enter role name"
            required
          />
        </div>
        <div>
          <label class="block font-medium mb-1 mb-2">Permissions</label>
          <div class="grid grid-cols-2 gap-2 max-h-64 overflow-y-auto border rounded p-2 bg-muted/30">
            <label
              v-for="perm in permissions"
              :key="perm.id"
              class="flex items-center gap-2"
            >
              <input
                type="checkbox"
                :value="perm.id"
                v-model="selectedPermissions"
              />
              {{ perm.name }}
            </label>
          </div>
        </div>
        <Button type="submit" :disabled="loading">
          {{ loading ? 'Creating...' : 'Create Role' }}
        </Button>
      </form>
    </div>
  </AppLayout>
</template>