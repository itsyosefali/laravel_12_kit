<script setup lang="ts">
import { ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { toast, Toaster } from 'vue-sonner'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'

const props = defineProps<{
  permissions: Array<{ id: number; name: string }>
}>()

const roleName = ref('')
const selectedPermissions = ref<number[]>([])
const loading = ref(false)
const breadcrumbs = [
  { title: 'Create Role', href: '/roles/create' }
]

const permissions = ref(props.permissions)

async function createRole() {
  loading.value = true
  router.post('/roles', {
    name: roleName.value,
    permissions: selectedPermissions.value,
  }, {
    onSuccess: () => {
      toast.success('Role created successfully!')
      setTimeout(() => {
        router.visit('/roles')
      }, 1000)
    },
    onError: (errors) => {
      toast.error(errors.name || 'Failed to create role')
    },
    onFinish: () => {
      loading.value = false
    }
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <Toaster position="top-center" />
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
          <label class="block font-medium mb-2">Permissions</label>
          <div class="grid grid-cols-2 gap-2 max-h-64 overflow-y-auto border rounded p-2 bg-muted/30">
            <label
              v-for="perm in permissions"
              :key="perm.id"
              class="flex items-center gap-2 p-2 hover:bg-muted/50 rounded"
            >
              <input
                type="checkbox"
                :value="perm.id"
                v-model="selectedPermissions"
                class="w-4 h-4 accent-primary"
              />
              <span class="text-sm">{{ perm.name }}</span>
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