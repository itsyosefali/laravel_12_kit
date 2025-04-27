<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="تسجيل الدخول" />
    
    <div class="min-h-screen flex">
        <!-- Left Side - Auth Form -->
        <div class="w-full md:w-1/2 flex items-center justify-center p-8 bg-background">
            <AuthBase 
                title="تسجيل الدخول إلى حسابك" 
                description="أدخل بريدك الإلكتروني وكلمة المرور للدخول"
                class="max-w-md w-full"
            >
                <div v-if="status" class="mb-4 text-center text-sm font-medium text-green-600">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="flex flex-col gap-6" dir="rtl">
                    <div class="grid gap-6">
                        <!-- Email Input -->
                        <div class="grid gap-2">
                            <Label for="email" class="text-right">البريد الإلكتروني</Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                autofocus
                                autocomplete="email"
                                v-model="form.email"
                                placeholder="example@email.com"
                            />
                            <InputError :message="form.errors.email" />
                        </div>

                        <!-- Password Input -->
                        <div class="grid gap-2">
                            <div class="flex items-center justify-between">
                                <Label for="password" class="text-right">كلمة المرور</Label>
                                <TextLink 
                                    v-if="canResetPassword" 
                                    :href="route('password.request')" 
                                    class="text-sm"
                                >
                                    نسيت كلمة المرور؟
                                </TextLink>
                            </div>
                            <Input
                                id="password"
                                type="password"
                                required
                                autocomplete="current-password"
                                v-model="form.password"
                                placeholder="كلمة المرور"
                            />
                            <InputError :message="form.errors.password" />
                        </div>

                        <!-- Remember Me -->
                        <div class="flex items-center justify-end gap-3">
                            <Label for="remember" class="flex items-center gap-2">
                                <span>تذكرني</span>
                                <Checkbox id="remember" v-model="form.remember" />
                            </Label>
                        </div>

                        <!-- Submit Button -->
                        <Button type="submit" :disabled="form.processing">
                            <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                            تسجيل الدخول
                        </Button>
                    </div>

                    <!-- Registration Link -->
                    <div class="text-center text-sm text-muted-foreground">
                        ليس لديك حساب؟
                        <TextLink :href="route('register')">إنشاء حساب</TextLink>
                    </div>
                </form>
            </AuthBase>
        </div>

        <!-- Right Side - Image -->
        <div class="hidden md:block relative w-1/2">
            <img 
            src="https://images.unsplash.com/photo-1506744038136-46273834b3fb?auto=format&fit=crop&w=900&q=80"
            alt="Decorative background"
            class="absolute inset-0 w-full h-full object-cover"
            />
            <!-- Optional Overlay -->
            <div class="absolute inset-0 bg-black/20"></div>
        </div>
    </div>
</template>