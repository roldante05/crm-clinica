<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Pacientes', href: '/clinic/patients' },
            { title: 'Detalle', href: '#' },
        ],
    },
});

interface Appointment {
    id: number;
    appointment_date: string;
    appointment_time: string;
    status: { value: string; label: string; color: string };
    reason: string | null;
    doctor: { full_name: string; specialty: { name: string } };
}

const props = defineProps<{
    patient: {
        id: number;
        first_name: string;
        last_name: string;
        email: string | null;
        phone: string;
        secondary_phone: string | null;
        document_type: string | null;
        document_number: string | null;
        date_of_birth: string | null;
        gender: { value: string; label: string } | null;
        address: string | null;
        city: string | null;
        medical_record_number: string;
        notes: string | null;
        appointments: Appointment[];
    };
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
    <Head :title="`${patient.first_name} ${patient.last_name}`" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">{{ patient.first_name }} {{ patient.last_name }}</h1>
            <div class="flex gap-2">
                <Link :href="`/clinic/patients/${patient.id}/edit`" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50">Editar</Link>
                <Link href="/clinic/patients" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50">Volver</Link>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <!-- Patient Info -->
            <div class="space-y-4 rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800">
                <h2 class="text-lg font-semibold">Datos del Paciente</h2>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div><span class="text-gray-500">N. Historia:</span> <span class="font-medium">{{ patient.medical_record_number }}</span></div>
                    <div><span class="text-gray-500">Teléfono:</span> <span class="font-medium">{{ patient.phone }}</span></div>
                    <div><span class="text-gray-500">Email:</span> <span class="font-medium">{{ patient.email || '-' }}</span></div>
                    <div><span class="text-gray-500">Doc:</span> <span class="font-medium">{{ patient.document_type }} {{ patient.document_number }}</span></div>
                    <div><span class="text-gray-500">Nacimiento:</span> <span class="font-medium">{{ patient.date_of_birth || '-' }}</span></div>
                    <div><span class="text-gray-500">Género:</span> <span class="font-medium">{{ patient.gender?.label || '-' }}</span></div>
                    <div class="col-span-2"><span class="text-gray-500">Dirección:</span> <span class="font-medium">{{ patient.address || '-' }}</span></div>
                    <div v-if="patient.notes" class="col-span-2"><span class="text-gray-500">Notas:</span> <span class="font-medium">{{ patient.notes }}</span></div>
                </div>
            </div>

            <!-- Recent Appointments -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold">Últimos Turnos</h2>
                <div v-if="patient.appointments.length" class="space-y-2">
                    <div v-for="apt in patient.appointments" :key="apt.id" class="flex items-center justify-between rounded-lg border p-3 text-sm">
                        <div>
                            <p class="font-medium">{{ apt.appointment_date }} {{ apt.appointment_time }}</p>
                            <p class="text-gray-500">{{ apt.doctor.full_name }} — {{ apt.doctor.specialty.name }}</p>
                        </div>
                        <span :class="['rounded-full px-2 py-1 text-xs font-medium', statusColors[apt.status.value]]">
                            {{ apt.status.label }}
                        </span>
                    </div>
                </div>
                <p v-else class="text-sm text-gray-500">No hay turnos registrados</p>
            </div>
        </div>
    </div>
</template>
