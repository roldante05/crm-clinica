<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Dashboard', href: '/dashboard' },
            { title: 'Pacientes', href: '/clinic/patients' },
            { title: 'Crear', href: '#' },
        ],
    },
});

const form = useForm({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    secondary_phone: '',
    document_type: 'DNI',
    document_number: '',
    date_of_birth: '',
    gender: '',
    address: '',
    city: '',
    notes: '',
});

function submit() {
    form.post('/clinic/patients');
}
</script>

<template>
    <Head title="Nuevo Paciente" />

    <div class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4">
        <h1 class="text-2xl font-bold">Nuevo Paciente</h1>

        <form @submit.prevent="submit" class="max-w-2xl space-y-4 rounded-xl border border-sidebar-border/70 bg-white p-6 dark:border-sidebar-border dark:bg-gray-800">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">Nombre *</label>
                    <input v-model="form.first_name" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700" />
                    <p v-if="form.errors.first_name" class="mt-1 text-sm text-red-600">{{ form.errors.first_name }}</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Apellido *</label>
                    <input v-model="form.last_name" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700" />
                    <p v-if="form.errors.last_name" class="mt-1 text-sm text-red-600">{{ form.errors.last_name }}</p>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">Email</label>
                    <input v-model="form.email" type="email" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Teléfono *</label>
                    <input v-model="form.phone" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700" />
                    <p v-if="form.errors.phone" class="mt-1 text-sm text-red-600">{{ form.errors.phone }}</p>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">Tipo Doc.</label>
                    <select v-model="form.document_type" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700">
                        <option value="DNI">DNI</option>
                        <option value="Pasaporte">Pasaporte</option>
                        <option value="Cédula">Cédula</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">N. Documento</label>
                    <input v-model="form.document_number" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700" />
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Fecha Nac.</label>
                    <input v-model="form.date_of_birth" type="date" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700" />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="mb-1 block text-sm font-medium">Género</label>
                    <select v-model="form.gender" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700">
                        <option value="">No especificado</option>
                        <option value="male">Masculino</option>
                        <option value="female">Femenino</option>
                        <option value="other">Otro</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Ciudad</label>
                    <input v-model="form.city" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700" />
                </div>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Dirección</label>
                <input v-model="form.address" type="text" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700" />
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">Notas</label>
                <textarea v-model="form.notes" rows="2" class="w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700"></textarea>
            </div>

            <div class="flex gap-2">
                <button type="submit" :disabled="form.processing" class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50">
                    Crear Paciente
                </button>
                <a href="/clinic/patients" class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50">Cancelar</a>
            </div>
        </form>
    </div>
</template>
