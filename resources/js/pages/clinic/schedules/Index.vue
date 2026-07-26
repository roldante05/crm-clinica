<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Médicos', href: '/clinic/doctors' },
            { title: 'Horarios', href: '#' },
        ],
    },
});

const dayLabels: Record<number, string> = {
    0: 'Domingo',
    1: 'Lunes',
    2: 'Martes',
    3: 'Miércoles',
    4: 'Jueves',
    5: 'Viernes',
    6: 'Sábado',
};

interface Schedule {
    id: number;
    day_of_week: { value: number; label: string };
    start_time: string;
    end_time: string;
    slot_duration_minutes: number;
    is_active: boolean;
}

const props = defineProps<{
    doctor: { id: number; full_name: string; specialty: { name: string } };
    schedules: Schedule[];
}>();

function destroy(scheduleId: number) {
    if (confirm('¿Estás seguro de eliminar este horario?')) {
        router.delete(`/clinic/doctors/${props.doctor.id}/schedules/${scheduleId}`);
    }
}
</script>

<template>
    <Head :title="`Horarios — ${doctor.full_name}`" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Horarios — {{ doctor.full_name }}</h1>
                <p class="text-sm text-gray-500">{{ doctor.specialty.name }}</p>
            </div>
            <Link :href="`/clinic/doctors/${doctor.id}/schedules/create`" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                + Nuevo Horario
            </Link>
        </div>

        <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-800">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-sidebar-border/70 bg-gray-50 dark:border-sidebar-border dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 font-medium">Día</th>
                        <th class="px-4 py-3 font-medium">Inicio</th>
                        <th class="px-4 py-3 font-medium">Fin</th>
                        <th class="px-4 py-3 font-medium">Duración Turno</th>
                        <th class="px-4 py-3 font-medium">Estado</th>
                        <th class="px-4 py-3 font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                    <tr v-for="schedule in schedules" :key="schedule.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 font-medium">{{ schedule.day_of_week.label }}</td>
                        <td class="px-4 py-3">{{ schedule.start_time }}</td>
                        <td class="px-4 py-3">{{ schedule.end_time }}</td>
                        <td class="px-4 py-3">{{ schedule.slot_duration_minutes }} min</td>
                        <td class="px-4 py-3">
                            <span :class="['rounded-full px-2 py-1 text-xs font-medium', schedule.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800']">
                                {{ schedule.is_active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <Link :href="`/clinic/doctors/${doctor.id}/schedules/${schedule.id}/edit`" class="text-blue-600 hover:underline">Editar</Link>
                                <button @click="destroy(schedule.id)" class="text-red-600 hover:underline">Eliminar</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
