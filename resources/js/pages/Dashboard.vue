<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

// Import Card components
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader>
                        <CardTitle>Users</CardTitle>
                        <CardDescription>Total registered users</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">1,234</div>
                        <!-- Chart placeholder -->
                        <div class="mt-4 h-24 rounded bg-gray-100 dark:bg-gray-800">
                            <!-- Insert chart component here -->
                        </div>
                    </CardContent>
                    <CardFooter>
                        <span class="text-muted-foreground text-xs">Updated just now</span>
                    </CardFooter>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle>Revenue</CardTitle>
                        <CardDescription>This month</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">$12,345</div>
                        <div class="mt-4 h-24 rounded bg-gray-100 dark:bg-gray-800"></div>
                    </CardContent>
                    <CardFooter>
                        <span class="text-muted-foreground text-xs">Updated 5 min ago</span>
                    </CardFooter>
                </Card>
                <Card>
                    <CardHeader>
                        <CardTitle>Active Sessions</CardTitle>
                        <CardDescription>Currently online</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">87</div>
                        <div class="mt-4 h-24 rounded bg-gray-100 dark:bg-gray-800"></div>
                    </CardContent>
                    <CardFooter>
                        <span class="text-muted-foreground text-xs">Live data</span>
                    </CardFooter>
                </Card>
            </div>
            <div class="border-sidebar-border/70 dark:border-sidebar-border relative min-h-[100vh] flex-1 rounded-xl border md:min-h-min">
                <!-- You can add more cards or charts here -->
            </div>
        </div>
    </AppLayout>
</template>
