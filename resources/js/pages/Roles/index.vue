<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { ArrowUpDownIcon, LoaderCircle, PencilIcon, PlusIcon, TrashIcon } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { toast, Toaster } from 'vue-sonner';

interface Permission {
    id: number;
    name: string;
    guard_name: string;
    created_at: string;
    updated_at: string;
}

interface Role {
    id: number;
    name: string;
    created_at: string;
    permissions: string[];
}

const loading = ref(false);
const error = ref<Error | null>(null);
const sortByField = ref('id');
const sortOrder = ref<'asc' | 'desc'>('asc');
const searchQuery = ref('');
const page = ref(1);
const limit = ref(10);
const editDialogOpen = ref(false);
const deleteDialogOpen = ref(false);
const currentRole = ref<Role | null>(null);
const selectedPermissions = ref<number[]>([]);
const isSubmitting = ref(false);

const breadcrumbs = [{ title: 'Roles Management', href: '/roles' }];

const props = defineProps<{
    roles: Role[];
    permissions: Permission[];
}>();

//use computed instead of refs to automatically change the values when the page reloads from the backend
const roles = computed<Role[]>(() => {
    return props.roles
});

const availablePermissions = computed<Permission[]>(() => {
    return props.permissions
});

const displayedRoles = computed(() => {
    let data = [...roles.value];

    data = data.filter((role) => role.name.toLowerCase().includes(searchQuery.value.toLowerCase()));

    data.sort((a, b) => {
        const valA = (a as any)[sortByField.value];
        const valB = (b as any)[sortByField.value];
        if (valA < valB) return sortOrder.value === 'asc' ? -1 : 1;
        if (valA > valB) return sortOrder.value === 'asc' ? 1 : -1;
        return 0;
    });

    const startIndex = (page.value - 1) * limit.value;
    const endIndex = startIndex + limit.value;
    return data.slice(startIndex, endIndex);
});

function sortBy(field: string) {
    if (sortByField.value === field) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortByField.value = field;
        sortOrder.value = 'asc';
    }
}

function openAddDialog() {
    router.visit('/roles/create');
}

function openEditDialog(role: Role) {
    currentRole.value = { ...role };
    selectedPermissions.value = role.permissions
        .map((perm) => {
            const found = availablePermissions.value.find((p) => p.id === perm.id);
            return found?.id;
        })
        .filter((id): id is number => id !== undefined);

    console.log(selectedPermissions.value);

    editDialogOpen.value = true;
}

function handleSearch() {
    page.value = 1;
}

function previousPage() {
    if (page.value > 1) page.value--;
}

function nextPage() {
    const totalCount = roles.value.length;
    if (page.value * limit.value < totalCount) page.value++;
}

function updateRole() {
    if (!currentRole.value) return;
    isSubmitting.value = true;
    router.put(
        //This is better
        //it called ziggy.js it migrate the routes names form your routes file in laravel to put them inside your vue or js file
        route('roles.update', currentRole.value.id),
        // `/roles/${currentRole.value.id}`,
        {
            name: currentRole.value.name,
            permissions: selectedPermissions.value,
        },
        {
            preserveScroll: true,
            onSuccess: () => {
                //TODO WTF
                //TODO just return the route and it automatically  refreshes

                // const idx = roles.value.findIndex((r) => r.id === currentRole.value?.id);
                // if (idx !== -1) {
                //     roles.value[idx].name = currentRole.value.name;
                //     roles.value[idx].permissions = selectedPermissions.value
                //         .map((id) => {
                //             const perm = availablePermissions.value.find((p) => p.id === id);
                //             return perm ? perm.name : '';
                //         })
                //         .filter(Boolean);
                // }
                editDialogOpen.value = false;
                isSubmitting.value = false;
                toast.success('Role updated successfully');
            },
            onError: () => {
                isSubmitting.value = false;
                toast.error('Failed to update role');
            },
        },
    );
}

function deleteRole() {
    if (!currentRole.value) return;
    isSubmitting.value = true;
    const deletedId = currentRole.value.id;
    router.delete(`/roles/${deletedId}`, {
        onSuccess: () => {
            roles.value = roles.value.filter((r) => r.id !== deletedId);
            deleteDialogOpen.value = false;
            isSubmitting.value = false;
            toast.success('Role deleted successfully');
        },
        onError: () => {
            isSubmitting.value = false;
            toast.error('Failed to delete role');
        },
    });
}

function togglePermission(id: number) {
    if (selectedPermissions.value.includes(id)) {
        selectedPermissions.value = selectedPermissions.value.filter((x) => x !== id);
    } else {
        selectedPermissions.value.push(id);
    }
}

function confirmDelete(role: Role) {
    currentRole.value = role;
    deleteDialogOpen.value = true;
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Toaster position="top-center" />
        <Dialog v-model:open="editDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit Role</DialogTitle>
                    <DialogDescription> Modify role details below. Click save when you're done.</DialogDescription>
                </DialogHeader>

                <div class="space-y-4" v-if="currentRole">
                    <div class="space-y-2">
                        <Label>Role Name</Label>
                        <Input v-model="currentRole.name" />
                    </div>

                    <div class="space-y-2">
                        <Label>Permissions</Label>
                        <div class="grid max-h-64 grid-cols-2 gap-2 overflow-y-auto rounded border p-2">
                            <div v-for="perm in availablePermissions" :key="perm.id" class="flex items-center gap-2">
                                <Checkbox
                                    :id="`perm-${perm.id}`"
                                    :value="perm.id"
                                    @click="togglePermission(perm.id)"
                                    :model-value="selectedPermissions.includes(perm.id)"
                                />
                                <Label :for="`perm-${perm.id}`" class="text-sm font-normal">
                                    {{ perm.name }}
                                </Label>
                            </div>
                        </div>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="editDialogOpen = false">Cancel</Button>
                    <Button @click="updateRole" :disabled="isSubmitting">
                        <LoaderCircle v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
                        Save Changes
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>

        <Dialog v-model:open="deleteDialogOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Confirm Delete</DialogTitle>
                    <DialogDescription> Are you sure you want to delete this role? This action cannot be undone. </DialogDescription>
                </DialogHeader>

                <div class="flex flex-col gap-4" v-if="currentRole">
                    <div class="bg-destructive/10 rounded p-4">
                        <p class="text-destructive font-medium">Deleting role: {{ currentRole.name }}</p>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="deleteDialogOpen = false">Cancel</Button>
                    <Button variant="destructive" @click="deleteRole" :disabled="isSubmitting">
                        <LoaderCircle v-if="isSubmitting" class="mr-2 h-4 w-4 animate-spin" />
                        Confirm Delete
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
        <div class="space-y-4">
            <div class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center">
                <input v-model="searchQuery" @input="handleSearch" placeholder="Search roles..." class="max-w-[400px] rounded border px-3 py-2" />
                <Button @click="openAddDialog">
                    <PlusIcon class="mr-2 h-4 w-4" />
                    Add Role
                </Button>
            </div>

            <div class="my-4 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                <div v-for="role in roles" :key="role.id" class="bg-muted/30 flex flex-col items-center rounded-lg border p-4">
                    <div class="mb-2 text-lg font-semibold">{{ role.name }}</div>
                    <div class="text-primary mb-1 text-4xl font-bold">{{ role.permissions.length }}</div>
                    <div class="text-muted-foreground text-xs">Permissions</div>
                </div>
            </div>

            <div class="overflow-auto rounded-md border">
                <Table class="min-w-[700px]">
                    <TableHeader class="bg-muted/50">
                        <TableRow>
                            <TableHead class="w-[80px] cursor-pointer" @click="sortBy('id')">
                                ID
                                <ArrowUpDownIcon class="ml-1 inline-block h-4 w-4" />
                            </TableHead>
                            <TableHead class="cursor-pointer" @click="sortBy('name')">
                                Role Name
                                <ArrowUpDownIcon class="ml-1 inline-block h-4 w-4" />
                            </TableHead>
                            <TableHead>Permissions</TableHead>
                            <TableHead class="cursor-pointer" @click="sortBy('created_at')">
                                Created
                                <ArrowUpDownIcon class="ml-1 inline-block h-4 w-4" />
                            </TableHead>
                            <TableHead class="w-[180px] text-right">Actions</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="role in displayedRoles" :key="role.id" class="hover:bg-muted/50">
                            <TableCell class="font-medium">{{ role.id }}</TableCell>
                            <TableCell>{{ role.name }}</TableCell>
                            <TableCell>
                                <div class="flex flex-wrap gap-1">
                                    <span
                                        v-for="(perm, index) in role.permissions"
                                        :key="index"
                                        class="bg-muted inline-block rounded px-2 py-1 text-xs"
                                    >
                                        {{ perm.name }}
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell>{{ role.created_at.slice(0, 10) }}</TableCell>
                            <TableCell class="text-right">
                                <div class="flex justify-end gap-2">
                                    <Button variant="outline" size="sm" @click="openEditDialog(role)">
                                        <PencilIcon class="mr-1 h-4 w-4" />
                                        Edit
                                    </Button>
                                    <Button variant="destructive" size="sm" @click="confirmDelete(role)">
                                        <TrashIcon class="mr-1 h-4 w-4" />
                                        Delete
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="flex flex-col items-center justify-between gap-4 px-2 sm:flex-row">
                <div class="text-muted-foreground text-sm">
                    Showing {{ displayedRoles.length }} of {{ roles.length }}
                    roles
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="previousPage" :disabled="page === 1"> Previous</Button>
                    <Button variant="outline" size="sm" @click="nextPage" :disabled="page * limit >= roles.length"> Next </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
