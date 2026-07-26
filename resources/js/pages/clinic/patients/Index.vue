<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import Pagination from '@/components/Pagination.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Pacientes', href: '/clinic/patients' },
        ],
    },
});

interface Patient {
    id: number;
    first_name: string;
    last_name: string;
    phone: string;
    medical_record_number: string;
    email: string | null;
}

const props = defineProps<{
    patients: { data: Patient[] };
}>();

function destroy(id: number) {
    if (confirm('¿Estás seguro de eliminar este paciente?')) {
        router.delete(`/clinic/patients/${id}`);
    }
}
</script>

<template>
    <Head title="Pacientes" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Pacientes</h1>
            <Link href="/clinic/patients/create" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                + Nuevo Paciente
            </Link>
        </div>

        <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-800">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-sidebar-border/70 bg-gray-50 dark:border-sidebar-border dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 font-medium">N. Historia</th>
                        <th class="px-4 py-3 font-medium">Nombre</th>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Teléfono</th>
                        <th class="px-4 py-3 font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                    <tr v-for="patient in patients.data" :key="patient.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 font-medium text-gray-500">{{ patient.medical_record_number }}</td>
                        <td class="px-4 py-3 font-medium">{{ patient.last_name }}, {{ patient.first_name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ patient.email || '-' }}</td>
                        <td class="px-4 py-3">{{ patient.phone }}</td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <Link :href="`/clinic/patients/${patient.id}`" class="text-green-600 hover:underline">Ver</Link>
                                <Link :href="`/clinic/patients/${patient.id}/edit`" class="text-blue-600 hover:underline">Editar</Link>
                                <button @click="destroy(patient.id)" class="text-red-600 hover:underline">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :paginated="patients" />
    </div>
</template>
