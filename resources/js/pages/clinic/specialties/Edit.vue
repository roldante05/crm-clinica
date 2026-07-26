<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Especialidades', href: '/clinic/specialties' },
            { title: 'Editar', href: '#' },
        ],
    },
});

const props = defineProps<{
    specialty: { id: number; name: string; description: string | null; is_active: boolean };
}>();

const form = useForm({
    name: props.specialty.name,
    description: props.specialty.description || '',
    is_active: props.specialty.is_active,
});

function submit() {
    form.put(`/clinic/specialties/${props.specialty.id}`);
}
</script>

<template>
    <Head title="Editar Especialidad" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <h1 class="text-2xl font-bold">Editar Especialidad</h1>

        <form @submit.prevent="submit" class="max-w-xl space-y-4 rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800">
            <div>
                <label class="mb-1 block text-sm font-medium">Nombre *</label>
                <input v-model="form.name" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700" />
                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Descripción</label>
                <textarea v-model="form.description" rows="3" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700"></textarea>
                <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">{{ form.errors.description }}</p>
            </div>

            <div class="flex items-center gap-2">
                <input v-model="form.is_active" type="checkbox" id="is_active" class="rounded" />
                <label for="is_active" class="text-sm font-medium">Activa</label>
            </div>

            <div class="flex gap-2">
                <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                    Actualizar
                </button>
                <a href="/clinic/specialties" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50">Cancelar</a>
            </div>
        </form>
    </div>
</template>
