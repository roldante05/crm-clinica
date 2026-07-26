<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Médicos', href: '/clinic/doctors' },
            { title: 'Editar', href: '#' },
        ],
    },
});

const props = defineProps<{
    appointment: {
        id: number;
        appointment_date: string;
        appointment_time: string;
        duration_minutes: number;
        reason: string | null;
        doctor: { id: number; full_name: string };
        patient: { id: number; full_name: string };
    };
    doctors: { id: number; full_name: string; schedules: { day_of_week: number; start_time: string; end_time: string; slot_duration_minutes: number }[] }[];
    patients: { id: number; full_name: string }[];
}>();

const form = useForm({
    appointment_date: props.appointment.appointment_date,
    appointment_time: props.appointment.appointment_time,
    duration_minutes: props.appointment.duration_minutes,
    reason: props.appointment.reason || '',
});

function submit() {
    form.put(`/clinic/appointments/${props.appointment.id}`);
}
</script>

<template>
    <Head title="Editar Turno" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <h1 class="text-2xl font-bold">Editar Turno</h1>

        <form @submit.prevent="submit" class="max-w-xl space-y-4 rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800">
            <div class="rounded-lg bg-gray-50 p-3 text-sm dark:bg-gray-700">
                <p><span class="text-gray-500">Médico:</span> <span class="font-medium">{{ appointment.doctor.full_name }}</span></p>
                <p><span class="text-gray-500">Paciente:</span> <span class="font-medium">{{ appointment.patient.full_name }}</span></p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">Fecha *</label>
                    <input v-model="form.appointment_date" type="date" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Hora *</label>
                    <input v-model="form.appointment_time" type="time" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700" />
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Duración (min)</label>
                <input v-model="form.duration_minutes" type="number" min="10" max="120" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700" />
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Motivo</label>
                <input v-model="form.reason" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700" />
            </div>

            <div class="flex gap-2">
                <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                    Actualizar
                </button>
                <a href="/clinic/appointments" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50">Cancelar</a>
            </div>
        </form>
    </div>
</template>
