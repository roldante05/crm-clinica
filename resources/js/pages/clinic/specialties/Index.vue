<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import Pagination from '@/components/Pagination.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Especialidades', href: '/clinic/specialties' },
        ],
    },
});

interface Specialty {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
    doctors_count: number;
}

const props = defineProps<{
    specialties: { data: Specialty[] };
}>();

function toggleActive(specialty: Specialty) {
    router.put(`/clinic/specialties/${specialty.id}`, {
        ...specialty,
        is_active: !specialty.is_active,
    }, { preserveScroll: true });
}

function destroy(id: number) {
    if (confirm('¿Estás seguro de eliminar esta especialidad?')) {
        router.delete(`/clinic/specialties/${id}`);
    }
}
</script>

<template>
    <Head title="Especialidades" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Especialidades</h1>
            <Link href="/clinic/specialties/create" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                + Nueva Especialidad
            </Link>
        </div>

        <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-800">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-sidebar-border/70 bg-gray-50 dark:border-sidebar-border dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nombre</th>
                        <th class="px-4 py-3 font-medium">Descripción</th>
                        <th class="px-4 py-3 font-medium">Médicos</th>
                        <th class="px-4 py-3 font-medium">Estado</th>
                        <th class="px-4 py-3 font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                    <tr v-for="specialty in specialties.data" :key="specialty.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 font-medium">{{ specialty.name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ specialty.description || '-' }}</td>
                        <td class="px-4 py-3">{{ specialty.doctors_count }}</td>
                        <td class="px-4 py-3">
                            <button @click="toggleActive(specialty)" :class="['rounded-full px-2 py-1 text-xs font-medium', specialty.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800']">
                                {{ specialty.is_active ? 'Activa' : 'Inactiva' }}
                            </button>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <Link :href="`/clinic/specialties/${specialty.id}/edit`" class="text-blue-600 hover:underline">Editar</Link>
                                <button @click="destroy(specialty.id)" class="text-red-600 hover:underline">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :paginated="specialties" />
    </div>
</template>
