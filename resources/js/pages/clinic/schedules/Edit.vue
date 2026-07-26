<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Médicos', href: '/clinic/doctors' },
            { title: 'Editar Horario', href: '#' },
        ],
    },
});

const props = defineProps<{
    doctor: { id: number; full_name: string };
    schedule: { id: number; day_of_week: { value: number }; start_time: string; end_time: string; slot_duration_minutes: number; is_active: boolean };
}>();

const form = useForm({
    day_of_week: props.schedule.day_of_week.value,
    start_time: props.schedule.start_time,
    end_time: props.schedule.end_time,
    slot_duration_minutes: props.schedule.slot_duration_minutes,
    is_active: props.schedule.is_active,
});

function submit() {
    form.put(`/clinic/doctors/${props.doctor.id}/schedules/${props.schedule.id}`);
}
</script>

<template>
    <Head :title="`Editar Horario — ${doctor.full_name}`" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <h1 class="text-2xl font-bold">Editar Horario — {{ doctor.full_name }}</h1>

        <form @submit.prevent="submit" class="max-w-xl space-y-4 rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800">
            <div>
                <label class="mb-1 block text-sm font-medium">Día *</label>
                <select v-model="form.day_of_week" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700">
                    <option value="0">Domingo</option>
                    <option value="1">Lunes</option>
                    <option value="2">Martes</option>
                    <option value="3">Miércoles</option>
                    <option value="4">Jueves</option>
                    <option value="5">Viernes</option>
                    <option value="6">Sábado</option>
                </select>
                <p v-if="form.errors.day_of_week" class="mt-1 text-sm text-red-600">{{ form.errors.day_of_week }}</p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">Hora Inicio *</label>
                    <input v-model="form.start_time" type="time" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700" />
                    <p v-if="form.errors.start_time" class="mt-1 text-sm text-red-600">{{ form.errors.start_time }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Hora Fin *</label>
                    <input v-model="form.end_time" type="time" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700" />
                    <p v-if="form.errors.end_time" class="mt-1 text-sm text-red-600">{{ form.errors.end_time }}</p>
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Duración del Turno (min) *</label>
                <select v-model="form.slot_duration_minutes" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700">
                    <option :value="15">15 min</option>
                    <option :value="20">20 min</option>
                    <option :value="30">30 min</option>
                    <option :value="45">45 min</option>
                    <option :value="60">60 min</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <input v-model="form.is_active" type="checkbox" id="is_active" class="rounded" />
                <label for="is_active" class="text-sm font-medium">Activo</label>
            </div>

            <div class="flex gap-2">
                <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                    Actualizar
                </button>
                <a :href="`/clinic/doctors/${doctor.id}/schedules`" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50">Cancelar</a>
            </div>
        </form>
    </div>
</template>
