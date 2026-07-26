<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Turnos', href: '/clinic/appointments' },
            { title: 'Crear', href: '#' },
        ],
    },
});

interface Doctor {
    id: number;
    full_name: string;
    specialty: { name: string };
    schedules: { day_of_week: number; start_time: string; end_time: string; slot_duration_minutes: number }[];
}

const props = defineProps<{
    doctors: Doctor[];
    patients: { id: number; full_name: string; medical_record_number: string }[];
}>();

const form = useForm({
    doctor_id: '',
    patient_id: '',
    appointment_date: '',
    appointment_time: '',
    reason: '',
});

const selectedDoctor = computed(() => props.doctors.find(d => String(d.id) === String(form.doctor_id)));
const availableSlots = computed(() => {
    if (!selectedDoctor.value || !form.appointment_date) return [];

    const date = new Date(form.appointment_date);
    const dayOfWeek = date.getDay();
    const slots: string[] = [];

    const daySchedules = selectedDoctor.value.schedules.filter(s => s.day_of_week === dayOfWeek);

    for (const schedule of daySchedules) {
        let [hours, minutes] = schedule.start_time.split(':').map(Number);
        const [endHours] = schedule.end_time.split(':').map(Number);

        while (hours < endHours) {
            slots.push(`${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`);
            minutes += schedule.slot_duration_minutes;
            if (minutes >= 60) {
                hours++;
                minutes = 0;
            }
        }
    }

    return slots;
});

function submit() {
    form.post('/clinic/appointments');
}
</script>

<template>
    <Head title="Nuevo Turno" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <h1 class="text-2xl font-bold">Nuevo Turno</h1>

        <form @submit.prevent="submit" class="max-w-xl space-y-4 rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800">
            <div>
                <label class="mb-1 block text-sm font-medium">Médico *</label>
                <select v-model="form.doctor_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700">
                    <option value="">Seleccionar médico...</option>
                    <option v-for="doctor in doctors" :key="doctor.id" :value="doctor.id">
                        {{ doctor.full_name }} — {{ doctor.specialty.name }}
                    </option>
                </select>
                <p v-if="form.errors.doctor_id" class="mt-1 text-sm text-red-600">{{ form.errors.doctor_id }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Paciente *</label>
                <select v-model="form.patient_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700">
                    <option value="">Seleccionar paciente...</option>
                    <option v-for="patient in patients" :key="patient.id" :value="patient.id">
                        {{ patient.full_name }} ({{ patient.medical_record_number }})
                    </option>
                </select>
                <p v-if="form.errors.patient_id" class="mt-1 text-sm text-red-600">{{ form.errors.patient_id }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">Fecha *</label>
                    <input v-model="form.appointment_date" type="date" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700" />
                    <p v-if="form.errors.appointment_date" class="mt-1 text-sm text-red-600">{{ form.errors.appointment_date }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Hora *</label>
                    <select v-model="form.appointment_time" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700">
                        <option value="">Seleccionar hora...</option>
                        <option v-for="slot in availableSlots" :key="slot" :value="slot">{{ slot }}</option>
                    </select>
                    <p v-if="form.errors.appointment_time" class="mt-1 text-sm text-red-600">{{ form.errors.appointment_time }}</p>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Motivo</label>
                <input v-model="form.reason" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700" placeholder="Ej: Control anual, dolor de cabeza..." />
            </div>

            <div class="flex gap-2">
                <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                    Crear Turno
                </button>
                <a href="/clinic/appointments" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50">Cancelar</a>
            </div>
        </form>
    </div>
</template>
