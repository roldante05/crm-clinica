<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { dashboard } from '@/routes';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

interface Stats {
    today_appointments: number;
    today_confirmed: number;
    today_pending: number;
    today_completed: number;
    today_cancelled: number;
    today_no_show: number;
    total_patients: number;
    total_doctors: number;
    total_specialties: number;
    upcoming_appointments: number;
}

interface Appointment {
    id: number;
    appointment_time: string;
    status: { value: string; label: string };
    reason: string | null;
    doctor: { full_name: string; specialty: { name: string } | null };
    patient: { full_name: string; phone: string };
}

const props = defineProps<{
    stats: Stats;
    todayAppointments: Appointment[];
}>();

const statusColors: Record<string, string> = {
    scheduled: 'bg-blue-100 text-blue-800',
    confirmed: 'bg-green-100 text-green-800',
    completed: 'bg-gray-100 text-gray-800',
    cancelled: 'bg-red-100 text-red-800',
    no_show: 'bg-orange-100 text-orange-800',
};
</script>

<template>
    <Head title="Dashboard" />
    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4">
        <!-- Stats Grid -->
        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-800">
                <p class="text-sm text-gray-500">Turnos Hoy</p>
                <p class="text-2xl font-bold">{{ stats.today_appointments }}</p>
            </div>
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-800">
                <p class="text-sm text-gray-500">Confirmados</p>
                <p class="text-2xl font-bold text-green-600">{{ stats.today_confirmed }}</p>
            </div>
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-800">
                <p class="text-sm text-gray-500">Pendientes</p>
                <p class="text-2xl font-bold text-yellow-600">{{ stats.today_pending }}</p>
            </div>
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-800">
                <p class="text-sm text-gray-500">Próximos Turnos</p>
                <p class="text-2xl font-bold">{{ stats.upcoming_appointments }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-800">
                <p class="text-sm text-gray-500">Completados Hoy</p>
                <p class="text-xl font-bold">{{ stats.today_completed }}</p>
            </div>
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-800">
                <p class="text-sm text-gray-500">Cancelados Hoy</p>
                <p class="text-xl font-bold text-red-600">{{ stats.today_cancelled }}</p>
            </div>
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-800">
                <p class="text-sm text-gray-500">No Show Hoy</p>
                <p class="text-xl font-bold text-orange-600">{{ stats.today_no_show }}</p>
            </div>
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-800">
                <p class="text-sm text-gray-500">Total Pacientes</p>
                <p class="text-xl font-bold">{{ stats.total_patients }}</p>
            </div>
        </div>

        <!-- Today's Appointments -->
        <div class="rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-800">
            <div class="border-b border-sidebar-border/70 p-4 dark:border-sidebar-border">
                <h2 class="text-lg font-semibold">Turnos de Hoy</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-sidebar-border/70 bg-gray-50 dark:border-sidebar-border dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 font-medium">Hora</th>
                            <th class="px-4 py-3 font-medium">Paciente</th>
                            <th class="px-4 py-3 font-medium">Médico</th>
                            <th class="px-4 py-3 font-medium">Especialidad</th>
                            <th class="px-4 py-3 font-medium">Estado</th>
                            <th class="px-4 py-3 font-medium">Motivo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                        <tr v-for="appointment in todayAppointments" :key="appointment.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 font-medium">{{ appointment.appointment_time }}</td>
                            <td class="px-4 py-3">{{ appointment.patient.full_name }}</td>
                            <td class="px-4 py-3">{{ appointment.doctor.full_name }}</td>
                            <td class="px-4 py-3">{{ appointment.doctor.specialty?.name ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <span :class="['rounded-full px-2 py-1 text-xs font-medium', statusColors[appointment.status.value] || 'bg-gray-100']">
                                    {{ appointment.status.label }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ appointment.reason || '-' }}</td>
                        </tr>
                        <tr v-if="todayAppointments.length === 0">
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No hay turnos programados para hoy</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
