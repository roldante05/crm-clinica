<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import Pagination from '@/components/Pagination.vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Turnos', href: '/clinic/appointments' },
        ],
    },
});

interface Doctor {
    id: number;
    full_name: string;
    specialty: { name: string };
}

interface Patient {
    id: number;
    full_name: string;
}

interface Appointment {
    id: number;
    appointment_date: string;
    appointment_time: string;
    duration_minutes: number;
    status: { value: string; label: string };
    reason: string | null;
    doctor: { full_name: string; specialty: { name: string } };
    patient: { full_name: string; phone: string };
    createdBy: { name: string };
}

const props = defineProps<{
    appointments: {
        data: Appointment[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        first_page_url: string;
        last_page_url: string;
        next_page_url: string | null;
        prev_page_url: string | null;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: { date?: string; doctor_id?: string; status?: string };
    doctors: Doctor[];
}>();

const filterDate = ref(props.filters.date || '');
const filterDoctor = ref(props.filters.doctor_id || '');
const filterStatus = ref(props.filters.status || '');

function applyFilters() {
    const params: Record<string, string> = {};
    if (filterDate.value) params.date = filterDate.value;
    if (filterDoctor.value) params.doctor_id = filterDoctor.value;
    if (filterStatus.value) params.status = filterStatus.value;

    router.get('/clinic/appointments', params, {
        preserveScroll: true,
        only: ['appointments', 'filters'],
    });
}

function cancelAppointment(id: number) {
    const reason = prompt('Motivo de cancelación:');
    if (reason !== null) {
        router.put(`/clinic/appointments/${id}`, {
            status: 'cancelled',
            cancellation_reason: reason,
        });
    }
}

function confirmAppointment(id: number) {
    router.put(`/clinic/appointments/${id}`, { status: 'confirmed' });
}

const statusColors: Record<string, string> = {
    scheduled: 'bg-blue-100 text-blue-800',
    confirmed: 'bg-green-100 text-green-800',
    completed: 'bg-gray-100 text-gray-800',
    cancelled: 'bg-red-100 text-red-800',
    no_show: 'bg-orange-100 text-orange-800',
};
</script>

<template>
    <Head title="Turnos" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Turnos</h1>
            <Link href="/clinic/appointments/create" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700">
                + Nuevo Turno
            </Link>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-3 rounded-xl border border-sidebar-border/70 bg-white p-4 dark:border-sidebar-border dark:bg-gray-800">
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500">Fecha</label>
                <input v-model="filterDate" type="date" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-700" />
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500">Médico</label>
                <select v-model="filterDoctor" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-700">
                    <option value="">Todos</option>
                    <option v-for="doc in doctors" :key="doc.id" :value="doc.id">{{ doc.full_name }}</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-gray-500">Estado</label>
                <select v-model="filterStatus" class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm dark:border-gray-600 dark:bg-gray-700">
                    <option value="">Todos</option>
                    <option value="scheduled">Programado</option>
                    <option value="confirmed">Confirmado</option>
                    <option value="completed">Completado</option>
                    <option value="cancelled">Cancelado</option>
                    <option value="no_show">No asistió</option>
                </select>
            </div>
            <div class="flex items-end">
                <button @click="applyFilters" class="rounded-lg bg-gray-600 px-4 py-1.5 text-sm font-medium text-white hover:bg-gray-700">
                    Filtrar
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto rounded-xl border border-sidebar-border/70 bg-white dark:border-sidebar-border dark:bg-gray-800">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-sidebar-border/70 bg-gray-50 dark:border-sidebar-border dark:bg-gray-700">
                    <tr>
                        <th class="px-4 py-3 font-medium">Fecha</th>
                        <th class="px-4 py-3 font-medium">Hora</th>
                        <th class="px-4 py-3 font-medium">Paciente</th>
                        <th class="px-4 py-3 font-medium">Médico</th>
                        <th class="px-4 py-3 font-medium">Especialidad</th>
                        <th class="px-4 py-3 font-medium">Estado</th>
                        <th class="px-4 py-3 font-medium">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-sidebar-border/70 dark:divide-sidebar-border">
                    <tr v-for="apt in appointments.data" :key="apt.id" class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3">{{ apt.appointment_date }}</td>
                        <td class="px-4 py-3 font-medium">{{ apt.appointment_time }}</td>
                        <td class="px-4 py-3">{{ apt.patient.full_name }}</td>
                        <td class="px-4 py-3">{{ apt.doctor.full_name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ apt.doctor.specialty.name }}</td>
                        <td class="px-4 py-3">
                            <span :class="['rounded-full px-2 py-1 text-xs font-medium', statusColors[apt.status.value]]">
                                {{ apt.status.label }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                <Link :href="`/clinic/appointments/${apt.id}`" class="text-green-600 hover:underline">Ver</Link>
                                <button v-if="apt.status.value === 'scheduled'" @click="confirmAppointment(apt.id)" class="text-blue-600 hover:underline">Confirmar</button>
                                <button v-if="['scheduled', 'confirmed'].includes(apt.status.value)" @click="cancelAppointment(apt.id)" class="text-red-600 hover:underline">Cancelar</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <Pagination :paginated="appointments" />
    </div>
</template>
