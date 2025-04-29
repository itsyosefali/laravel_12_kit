<script setup lang="ts">
import { ref } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import {
  FormControl,
  FormDescription,
  FormField,
  FormItem,
  FormLabel,
  FormMessage,
} from '@/components/ui/form'
import { Input } from '@/components/ui/input'
import { toast } from 'vue-sonner'
import { Separator } from '@/components/ui/separator'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Loader2 } from 'lucide-vue-next'

interface Role {
  id: number
  name: string
}

const roles = usePage().props.roles as Role[]

const form = useForm({
  firstName: '',
  lastName: '',
  email: '',
  password: '',
  role: '',
})

const isSubmitting = ref(false)

const onSubmit = () => {
  isSubmitting.value = true
  form.post('/users', {
    onSuccess: () => {
      toast.success('User created successfully!')
      form.reset()
    },
    onError: (errors) => {
      if (Object.keys(errors).length) {
        toast.error(
          Object.values(errors).join('\n'),
          { duration: 10000 }
        )
      } else {
        toast.error('Failed to create user. Please try again.')
      }
    },
    onFinish: () => {
      isSubmitting.value = false
    }
  })
}
</script>

<template>
  <AppLayout :breadcrumbs="[{ title: 'Create User', href: '/users/create' }]">
    <div class="max-w-2xl mx-auto py-8 px-4">
      <div class="space-y-8">
        <div class="space-y-2">
          <h1 class="text-3xl font-bold tracking-tight">Create New User</h1>
          <p class="text-muted-foreground">
            Add a new user to your organization with specific role permissions.
          </p>
        </div>

        <form class="space-y-6" @submit.prevent="onSubmit">
          <div class="grid grid-cols-2 gap-6">
            <FormField name="firstName" v-slot="{ field }">
              <FormItem>
                <FormLabel>First Name</FormLabel>
                <FormControl>
                  <Input placeholder="John" v-model="form.firstName" />
                </FormControl>
                <FormMessage>{{ form.errors.firstName }}</FormMessage>
              </FormItem>
            </FormField>

            <FormField name="lastName" v-slot="{ field }">
              <FormItem>
                <FormLabel>Last Name</FormLabel>
                <FormControl>
                  <Input placeholder="Doe" v-model="form.lastName" />
                </FormControl>
                <FormMessage>{{ form.errors.lastName }}</FormMessage>
              </FormItem>
            </FormField>
          </div>

          <Separator />

          <div class="space-y-6">
            <FormField name="email" v-slot="{ field }">
              <FormItem>
                <FormLabel>Email Address</FormLabel>
                <FormControl>
                  <Input type="email" placeholder="john@example.com" v-model="form.email" />
                </FormControl>
                <FormDescription>This will be their login email</FormDescription>
                <FormMessage>{{ form.errors.email }}</FormMessage>
              </FormItem>
            </FormField>

            <FormField name="password" v-slot="{ field }">
              <FormItem>
                <FormLabel>Password</FormLabel>
                <FormControl>
                  <Input type="password" placeholder="••••••••" v-model="form.password" />
                </FormControl>
                <FormDescription>Minimum 6 characters</FormDescription>
                <FormMessage>{{ form.errors.password }}</FormMessage>
              </FormItem>
            </FormField>

            <FormField name="role" v-slot="{ field }">
              <FormItem>
                <FormLabel>User Role</FormLabel>
                <Select v-model="form.role">
                  <FormControl>
                    <SelectTrigger>
                      <SelectValue placeholder="Select a role" />
                    </SelectTrigger>
                  </FormControl>
                  <SelectContent>
                    <SelectItem 
                      v-for="role in roles"
                      :key="role.id"
                      :value="role.id"
                    >
                      {{ role.name }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <FormDescription>This determines system permissions</FormDescription>
                <FormMessage>{{ form.errors.role }}</FormMessage>
              </FormItem>
            </FormField>
          </div>

          <div class="flex justify-end gap-4 pt-6">
            <Button variant="outline" type="button" @click="form.reset">
              Reset
            </Button>
            <Button type="submit" :disabled="isSubmitting">
              <span v-if="!isSubmitting">Create User</span>
              <Loader2 v-else class="h-4 w-4 animate-spin" />
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>