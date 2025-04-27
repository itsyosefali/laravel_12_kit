<template>
  <FullScreenLayout>
    <div class="relative p-6 bg-white z-1 dark:bg-gray-900 sm:p-0">
      <div
        class="relative flex flex-col justify-center w-full h-screen lg:flex-row dark:bg-gray-900"
      >
        <div class="flex flex-col flex-1 w-full lg:w-1/2">
          <div class="w-full max-w-md pt-10 mx-auto">
          </div>
          <div class="flex flex-col justify-center flex-1 w-full max-w-md mx-auto">
            <div>
              <div class="mb-5 sm:mb-8">
                <h1
                  class="mb-2 font-semibold text-gray-800 text-title-sm dark:text-white/90 sm:text-title-md"
                >
                  Sign In
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                  Enter your email and password to sign in!
                </p>
              </div>
              <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
                {{ status }}
              </div>
              <div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-5">
                  <!-- Social login buttons remain the same -->
                </div>
                <div class="relative py-3 sm:py-5">
                  <!-- "Or" separator remains the same -->
                </div>
                <form @submit.prevent="submit">
                  <div class="space-y-5">
                    <!-- Email -->
                    <div>
                      <label
                        for="email"
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                      >
                        Email<span class="text-error-500">*</span>
                      </label>
                      <input
                        v-model="form.email"
                        type="email"
                        id="email"
                        name="email"
                        placeholder="info@gmail.com"
                        class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 py-2.5 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                      />
                      <InputError :message="form.errors.email" />
                    </div>
                    <!-- Password -->
                    <div>
                      <label
                        for="password"
                        class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400"
                      >
                        Password<span class="text-error-500">*</span>
                      </label>
                      <div class="relative">
                        <input
                          v-model="form.password"
                          :type="showPassword ? 'text' : 'password'"
                          id="password"
                          placeholder="Enter your password"
                          class="dark:bg-dark-900 h-11 w-full rounded-lg border border-gray-300 bg-transparent py-2.5 pl-4 pr-11 text-sm text-gray-800 shadow-theme-xs placeholder:text-gray-400 focus:border-brand-300 focus:outline-hidden focus:ring-3 focus:ring-brand-500/10 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90 dark:placeholder:text-white/30 dark:focus:border-brand-800"
                        />
                        <span
                          @click="togglePasswordVisibility"
                          class="absolute z-30 text-gray-500 -translate-y-1/2 cursor-pointer right-4 top-1/2 dark:text-gray-400"
                        >
                          <!-- Password visibility icons remain the same -->
                        </span>
                      </div>
                      <InputError :message="form.errors.password" />
                    </div>
                    <!-- Checkbox -->
                    <div class="flex items-center justify-between">
                      <div>
                        <label
                          for="remember"
                          class="flex items-center text-sm font-normal text-gray-700 cursor-pointer select-none dark:text-gray-400"
                        >
                          <div class="relative">
                            <input
                              v-model="form.remember"
                              type="checkbox"
                              id="remember"
                              class="sr-only"
                            />
                            <!-- Checkbox styling remains the same -->
                          </div>
                          Remember me
                        </label>
                      </div>
                      <TextLink
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm text-brand-500 hover:text-brand-600 dark:text-brand-400"
                      >
                        Forgot password?
                      </TextLink>
                    </div>
                    <!-- Button -->
                    <div>
                      <button
                        type="submit"
                        class="flex items-center justify-center w-full px-4 py-3 text-sm font-medium text-white transition rounded-lg bg-brand-500 shadow-theme-xs hover:bg-brand-600"
                      >
                        Sign In
                      </button>
                    </div>
                  </div>
                </form>
                <div class="mt-5">
                  <p
                    class="text-sm font-normal text-center text-gray-700 dark:text-gray-400 sm:text-start"
                  >
                    Don't have an account?
                    <TextLink :href="route('register')" class="text-brand-500 hover:text-brand-600 dark:text-brand-400">
                      Sign Up
                    </TextLink>
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div
          class="relative items-center hidden w-full h-full lg:w-1/2 bg-brand-950 dark:bg-white/5 lg:grid"
        >
            <div class="flex items-center justify-center z-1">
            <CommonGridShape />
            <div class="flex flex-col items-center max-w-xs">
              <router-link to="/" class="block mb-4">
              <img width="231" height="48" src="https://upload.wikimedia.org/wikipedia/commons/9/95/Vue.js_Logo_2.svg" alt="Logo" />
              </router-link>
              <p class="text-center text-gray-400 dark:text-white/60">
              Free and Open-Source Tailwind CSS Admin Dashboard Template
              </p>
              <img class="mt-6 rounded shadow" width="180" src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=400&q=80" alt="Sample" />
            </div>
            </div>
        </div>
      </div>
    </div>
  </FullScreenLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import InputError from '@/components/InputError.vue'
import TextLink from '@/components/TextLink.vue'
import CommonGridShape from '@/components/CommonGridShape.vue'
import FullScreenLayout from '@/layouts/FullScreenLayout.vue'
import { LoaderCircle } from 'lucide-vue-next'
import { useForm } from '@inertiajs/vue3'

defineProps<{
  status?: string
  canResetPassword: boolean
}>()

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const showPassword = ref(false)

const submit = () => {
  form.post(route('login'), {
    onFinish: () => form.reset('password'),
  })
}

const togglePasswordVisibility = () => {
  showPassword.value = !showPassword.value
}
</script>