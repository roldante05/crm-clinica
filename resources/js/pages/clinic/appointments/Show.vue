<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Turnos', href: '/clinic/appointments' },
            { title: 'Detalle', href: '#' },
        ],
    },
});

interface Note {
    id: number;
    content: string;
    created_at: string;
    author: { name: string };
}

const props = defineProps<{
    appointment: {
        id: number;
        appointment_date: string;
        appointment_time: string;
        duration_minutes: number;
        status: { value: string; label: string; color: string };
        reason: string | null;
        cancellation_reason: string | null;
        confirmed_at: string | null;
        cancelled_at: string | null;
        doctor: { full_name: string; specialty: { name: string } | null; phone: string | null };
        patient: { full_name: string; phone: string; medical_record_number: string };
        created_by: { name: string };
        notes: Note[];
    };
}>();

const noteForm = useForm({ content: '' });

function addNote() {
    noteForm.post(`/clinic/appointments/${props.appointment.id}/notes`, {
        onSuccess: () => noteForm.reset(),
    });
}

function confirmAppointment() {
    router.put(`/clinic/appointments/${props.appointment.id}`, { status: 'confirmed' });
}

function completeAppointment() {
    router.put(`/clinic/appointments/${props.appointment.id}`, { status: 'completed' });
}

function noShowAppointment() {
    router.put(`/clinic/appointments/${props.appointment.id}`, { status: 'no_show' });
}

function cancelAppointment() {
    const reason = prompt('Motivo de cancelación:');
    if (reason !== null) {
        router.put(`/clinic/appointments/${props.appointment.id}`, {
            status: 'cancelled',
            cancellation_reason: reason,
        });
    }
}

const statusColors: Record<string, string> = {
    scheduled: 'bg-blue-100 text-blue-800',
    confirmed: 'bg-green-100 text-green-800',
    completed: 'bg-gray-100 text-gray-800',
    cancelled: 'bg-red-100 text-red-800',
    no_show: 'bg-orange-100 text-orange-800',
};

const activeStatuses = ['scheduled', 'confirmed'];
</script>

<template>
    <Head :title="`Turno ${appointment.appointment_date}`" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold">Turno — {{ appointment.appointment_date }} {{ appointment.appointment_time }}</h1>
            <Link href="/clinic/appointments" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50">Volver</Link>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <!-- Appointment Details -->
            <div class="space-y-4 rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold">Detalles del Turno</h2>
                    <span :class="['rounded-full px-3 py-1 text-xs font-medium', statusColors[appointment.status.value]]">
                        {{ appointment.status.label }}
                    </span>
                </div>

                <div class="space-y-2 text-sm">
                    <div><span class="text-gray-500">Fecha:</span> <span class="font-medium">{{ appointment.appointment_date }}</span></div>
                    <div><span class="text-gray-500">Hora:</span> <span class="font-medium">{{ appointment.appointment_time }}</span></div>
                    <div><span class="text-gray-500">Duración:</span> <span class="font-medium">{{ appointment.duration_minutes }} min</span></div>
                    <div><span class="text-gray-500">Médico:</span> <span class="font-medium">{{ appointment.doctor.full_name }}</span></div>
                    <div><span class="text-gray-500">Especialidad:</span> <span class="font-medium">{{ appointment.doctor.specialty?.name || '—' }}</span></div>
                    <div><span class="text-gray-500">Paciente:</span> <span class="font-medium">{{ appointment.patient.full_name }}</span></div>
                    <div><span class="text-gray-500">Motivo:</span> <span class="font-medium">{{ appointment.reason || '-' }}</span></div>
                    <div><span class="text-gray-500">Creado por:</span> <span class="font-medium">{{ appointment.created_by.name }}</span></div>
                    <div v-if="appointment.cancellation_reason"><span class="text-gray-500">Motivo cancelación:</span> <span class="font-medium text-red-600">{{ appointment.cancellation_reason }}</span></div>
                </div>

                <!-- Actions -->
                <div v-if="activeStatuses.includes(appointment.status.value)" class="flex flex-wrap gap-2 border-t pt-4">
                    <button v-if="appointment.status.value === 'scheduled'" @click="confirmAppointment" class="rounded-lg bg-green-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-green-700">Confirmar</button>
                    <button @click="completeAppointment" class="rounded-lg bg-gray-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-700">Completar</button>
                    <button @click="noShowAppointment" class="rounded-lg bg-orange-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-orange-700">No Show</button>
                    <button @click="cancelAppointment" class="rounded-lg bg-red-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-red-700">Cancelar</button>
                </div>
            </div>

            <!-- Notes -->
            <div class="rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800">
                <h2 class="mb-4 text-lg font-semibold">Notas Clínicas</h2>

                <div v-if="appointment.notes.length" class="mb-4 space-y-3">
                    <div v-for="note in appointment.notes" :key="note.id" class="rounded-lg border p-3 text-sm">
                        <p class="whitespace-pre-wrap">{{ note.content }}</p>
                        <p class="mt-2 text-xs text-gray-500">{{ note.author.name }} — {{ note.created_at }}</p>
                    </div>
                </div>
                <p v-else class="mb-4 text-sm text-gray-500">No hay notas registradas</p>

                <form @submit.prevent="addNote" class="space-y-2">
                    <textarea v-model="noteForm.content" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700" placeholder="Escribir nota clínica..."></textarea>
                    <p v-if="noteForm.errors.content" class="text-sm text-red-600">{{ noteForm.errors.content }}</p>
                    <button type="submit" :disabled="noteForm.processing || !noteForm.content" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                        Agregar Nota
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
