<script setup lang="ts">
import { ref, onMounted } from 'vue'
import axios from 'axios'
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
import { useForm } from 'vee-validate'
import { toTypedSchema } from '@vee-validate/zod'
import * as z from 'zod'
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

// Define schema for form validation using zod.
const createUserSchema = toTypedSchema(z.object({
  firstName: z.string().min(1, "First name is required"),
  lastName: z.string().min(1, "Last name is required"),
  email: z.string().email("Invalid email address"),
  password: z.string().min(6, "Password must be at least 6 characters"),
  role: z.preprocess(val => {
    if (typeof val === "string" && val !== "") return parseInt(val)
    return val
  }, z.number({ invalid_type_error: "Role is required" }))
}))

// Initialize the form.
const { handleSubmit, resetForm } = useForm({
  validationSchema: createUserSchema,
  initialValues: {
    firstName: '',
    lastName: '',
    email: '',
    password: '',
    role: null
  }
})

const availableRoles = ref<Role[]>([])
const isSubmitting = ref(false)

// Fetch available roles when the component mounts.
onMounted(async () => {
  try {
    const { data } = await axios.get('/api/roles')
    availableRoles.value = Array.isArray(data) ? data : data?.roles ?? []
  } catch (err) {
    toast({
      title: 'Error',
      description: 'Failed to load roles',
      variant: 'destructive',
    })
  }
})

// Submission handler will call the API.
const onSubmit = handleSubmit(async (values) => {
  isSubmitting.value = true
  try {
    const payload = {
      first_name: values.firstName,
      last_name: values.lastName,
      email: values.email,
      password: values.password,
      role: values.role,
    }
    await axios.post('/api/users', payload)
    toast.success("User created successfully!")
    resetForm()
  } catch (err) {
    if (axios.isAxiosError(err)) {
      const responseData = err.response?.data
      
      if (responseData?.errors) {
        const errorMessages = Object.values(responseData.errors)
          .flat()
          .join('\n')
        
        toast.error(`Validation errors:\n${errorMessages}`, {
          duration: 10000, 
        })
      }
      else if (responseData?.message) {
        toast.error(responseData.message)
      }
      else {
        toast.error('Failed to create user. Please try again.')
      }
    } else {
      toast.error('An unexpected error occurred')
    }
  } finally {
    isSubmitting.value = false
  }
})
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
                    <Input placeholder="John" v-bind="field" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              </FormField>
  
              <FormField name="lastName" v-slot="{ field }">
                <FormItem>
                  <FormLabel>Last Name</FormLabel>
                  <FormControl>
                    <Input placeholder="Doe" v-bind="field" />
                  </FormControl>
                  <FormMessage />
                </FormItem>
              </FormField>
            </div>
  
            <Separator />
  
            <div class="space-y-6">
              <FormField name="email" v-slot="{ field }">
                <FormItem>
                  <FormLabel>Email Address</FormLabel>
                  <FormControl>
                    <Input type="email" placeholder="john@example.com" v-bind="field" />
                  </FormControl>
                  <FormDescription>This will be their login email</FormDescription>
                  <FormMessage />
                </FormItem>
              </FormField>
  
              <FormField name="password" v-slot="{ field }">
                <FormItem>
                  <FormLabel>Password</FormLabel>
                  <FormControl>
                    <Input type="password" placeholder="••••••••" v-bind="field" />
                  </FormControl>
                  <FormDescription>Minimum 6 characters</FormDescription>
                  <FormMessage />
                </FormItem>
              </FormField>
  
              <FormField name="role" v-slot="{ field }">
                <FormItem>
                  <FormLabel>User Role</FormLabel>
                  <Select v-bind="field">
                    <FormControl>
                      <SelectTrigger>
                        <SelectValue placeholder="Select a role" />
                      </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                      <SelectItem 
                        v-for="role in availableRoles"
                        :key="role.id"
                        :value="role.id"
                      >
                        {{ role.name }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                  <FormDescription>This determines system permissions</FormDescription>
                  <FormMessage />
                </FormItem>
              </FormField>
            </div>
  
            <div class="flex justify-end gap-4 pt-6">
              <Button variant="outline" type="button" @click="resetForm">
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