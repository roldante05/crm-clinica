<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import Pagination from '@/components/Pagination.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Médicos', href: '/clinic/doctors' },
        ],
    },
});

interface Doctor {
    id: number;
    license_number: string;
    phone: string | null;
    is_active: boolean;
    full_name: string;
    user: { name: string; email: string };
    specialty: { name: string };
}

const props = defineProps<{
    doctors: { data: Doctor[] };
}>();

function destroy(id: number) {
    if (confirm('¿Estás seguro de eliminar este médico?')) {
        router.delete(`/clinic/doctors/${id}`);
    }
}
</script>

<template>
    <Head title="Médicos" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Médicos</h1>
            <Link href="/clinic/doctors/create" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                + Nuevo Médico
            </Link>
        </div>

        <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-800">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-sidebar-border/70 bg-gray-50 dark:border-sidebar-border dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 font-medium">Nombre</th>
                        <th class="px-4 py-3 font-medium">Especialidad</th>
                        <th class="px-4 py-3 font-medium">Matrícula</th>
                        <th class="px-4 py-3 font-medium">Email</th>
                        <th class="px-4 py-3 font-medium">Estado</th>
                        <th class="px-4 py-3 font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                    <tr v-for="doctor in doctors.data" :key="doctor.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 font-medium">{{ doctor.user.name }}</td>
                        <td class="px-4 py-3">{{ doctor.specialty.name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ doctor.license_number }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ doctor.user.email }}</td>
                        <td class="px-4 py-3">
                            <span :class="['rounded-full px-2 py-1 text-xs font-medium', doctor.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800']">
                                {{ doctor.is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <Link :href="`/clinic/doctors/${doctor.id}/edit`" class="text-blue-600 hover:underline">Editar</Link>
                                <Link :href="`/clinic/doctors/${doctor.id}/schedules`" class="text-green-600 hover:underline">Horarios</Link>
                                <button @click="destroy(doctor.id)" class="text-red-600 hover:underline">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :paginated="doctors" />
    </div>
</template>
