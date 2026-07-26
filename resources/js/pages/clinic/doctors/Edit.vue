<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

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
    doctor: { id: number; license_number: string; phone: string | null; is_active: boolean; user: { name: string }; specialty: { id: number; name: string } };
    specialties: { id: number; name: string }[];
}>();

const form = useForm({
    specialty_id: props.doctor.specialty.id,
    license_number: props.doctor.license_number,
    phone: props.doctor.phone || '',
    is_active: props.doctor.is_active,
});

function submit() {
    form.put(`/clinic/doctors/${props.doctor.id}`);
}
</script>

<template>
    <Head title="Editar Médico" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <h1 class="text-2xl font-bold">Editar Médico — {{ doctor.user.name }}</h1>

        <form @submit.prevent="submit" class="max-w-xl space-y-4 rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800">
            <div>
                <label class="mb-1 block text-sm font-medium">Especialidad *</label>
                <select v-model="form.specialty_id" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700">
                    <option v-for="specialty in specialties" :key="specialty.id" :value="specialty.id">{{ specialty.name }}</option>
                </select>
                <p v-if="form.errors.specialty_id" class="mt-1 text-sm text-red-600">{{ form.errors.specialty_id }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Número de Matrícula *</label>
                <input v-model="form.license_number" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700" />
                <p v-if="form.errors.license_number" class="mt-1 text-sm text-red-600">{{ form.errors.license_number }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Teléfono</label>
                <input v-model="form.phone" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700" />
            </div>

            <div class="flex items-center gap-2">
                <input v-model="form.is_active" type="checkbox" id="is_active" class="rounded" />
                <label for="is_active" class="text-sm font-medium">Activo</label>
            </div>

            <div class="flex gap-2">
                <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                    Actualizar
                </button>
                <a href="/clinic/doctors" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50">Cancelar</a>
            </div>
        </form>
    </div>
</template>
